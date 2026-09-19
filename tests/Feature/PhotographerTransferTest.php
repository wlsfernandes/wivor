<?php

namespace Tests\Feature;

use App\Jobs\ProcessPhotographerTransfer;
use App\Mail\OrderReceiptMail;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Photographer;
use App\Models\PhotographerTransfer;
use App\Models\User;
use App\Services\PhotographerTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use RuntimeException;
use Stripe\StripeClient;
use Tests\TestCase;

class PhotographerTransferTest extends TestCase
{
    use RefreshDatabase;

    private const PAYMENT_WEBHOOK_SECRET = 'whsec_transfer_test';
    private const CONNECT_WEBHOOK_SECRET = 'whsec_connect_transfer_test';

    private object $fakeStripe;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'queue.default' => 'sync',
            'services.stripe.secret' => 'sk_test_fake',
            'services.stripe.webhook_secret' => self::PAYMENT_WEBHOOK_SECRET,
            'services.stripe.connect_webhook_secret' => self::CONNECT_WEBHOOK_SECRET,
        ]);

        $this->fakeStripe = new class extends StripeClient
        {
            public $accounts;
            public $paymentIntents;
            public $transfers;

            public function __construct()
            {
                $this->accounts = new class
                {
                    public bool $ready = true;

                    public function retrieve(string $stripeAccountId, array $params = []): object
                    {
                        return (object) [
                            'id' => $stripeAccountId,
                            'capabilities' => (object) [
                                'transfers' => $this->ready ? 'active' : 'inactive',
                            ],
                            'payouts_enabled' => $this->ready,
                            'details_submitted' => true,
                            'requirements' => (object) [
                                'currently_due' => $this->ready ? [] : ['individual.verification.document'],
                                'eventually_due' => [],
                                'past_due' => [],
                                'pending_verification' => [],
                                'disabled_reason' => null,
                                'current_deadline' => null,
                            ],
                        ];
                    }
                };

                $this->paymentIntents = new class
                {
                    public int $retrieveCount = 0;
                    public string $latestChargeId = 'ch_test_source';

                    public function retrieve(string $paymentIntentId, array $params = []): object
                    {
                        $this->retrieveCount++;

                        return (object) [
                            'id' => $paymentIntentId,
                            'latest_charge' => $this->latestChargeId,
                        ];
                    }
                };

                $this->transfers = new class
                {
                    public int $createCount = 0;
                    public array $createParams = [];
                    public array $createOptions = [];
                    public ?\Throwable $createException = null;

                    public function create(array $params, array $options = []): object
                    {
                        $this->createCount++;
                        $this->createParams = $params;
                        $this->createOptions = $options;

                        if ($this->createException) {
                            throw $this->createException;
                        }

                        return (object) ['id' => 'tr_test_'.$this->createCount];
                    }
                };
            }
        };

        $this->app->instance(StripeClient::class, $this->fakeStripe);
    }

    public function test_paid_order_creates_one_grouped_transfer_per_photographer(): void
    {
        Queue::fake();
        $photographerOne = $this->photographer('acct_photographer_one');
        $photographerTwo = $this->photographer('acct_photographer_two');
        $order = $this->paidOrder([
            [$photographerOne, 700],
            [$photographerOne, 800],
            [$photographerTwo, 900],
        ]);

        app(PhotographerTransferService::class)->createTransfersForPaidOrder($order);

        $this->assertDatabaseCount('photographer_transfers', 2);
        $this->assertDatabaseHas('photographer_transfers', [
            'order_id' => $order->id,
            'photographer_id' => $photographerOne->id,
            'amount_cents' => 1500,
            'currency' => 'usd',
        ]);
        $this->assertDatabaseHas('photographer_transfers', [
            'order_id' => $order->id,
            'photographer_id' => $photographerTwo->id,
            'amount_cents' => 900,
            'currency' => 'usd',
        ]);
        Queue::assertPushed(ProcessPhotographerTransfer::class, 2);
    }

    public function test_transfer_uses_frozen_amount_destination_source_charge_and_idempotency_key(): void
    {
        $photographer = $this->photographer('acct_destination');
        $order = $this->paidOrder([
            [$photographer, 700],
            [$photographer, 800],
        ]);
        $orderItems = $order->items()->orderBy('id')->get();
        $originalCommissionCents = $orderItems->pluck('commission_cents')->all();
        $originalAllocationCents = $orderItems->pluck('photographer_allocation_cents')->all();
        $photographerTransfer = $this->transferFor($order, $photographer, 1500);

        app(PhotographerTransferService::class)->processTransfer($photographerTransfer);

        $photographerTransfer->refresh();
        $this->assertSame(PhotographerTransfer::STATUS_SUCCEEDED, $photographerTransfer->status);
        $this->assertSame('acct_destination', $photographerTransfer->stripe_account_id);
        $this->assertSame('tr_test_1', $photographerTransfer->stripe_transfer_id);
        $this->assertNotNull($photographerTransfer->transferred_at);
        $this->assertSame(1500, $this->fakeStripe->transfers->createParams['amount']);
        $this->assertSame('usd', $this->fakeStripe->transfers->createParams['currency']);
        $this->assertSame('acct_destination', $this->fakeStripe->transfers->createParams['destination']);
        $this->assertSame('ch_test_source', $this->fakeStripe->transfers->createParams['source_transaction']);
        $this->assertSame("wivor_order_{$order->id}", $this->fakeStripe->transfers->createParams['transfer_group']);
        $this->assertSame(
            "wivor-transfer-order-{$order->id}-photographer-{$photographer->id}",
            $this->fakeStripe->transfers->createOptions['idempotency_key'],
        );
        $this->assertSame('ch_test_source', $order->fresh()->stripe_charge_id);
        $this->assertSame(1, $this->fakeStripe->paymentIntents->retrieveCount);
        $this->assertSame(['tr_test_1'], $order->items()->pluck('stripe_transfer_id')->unique()->values()->all());
        $this->assertSame($originalCommissionCents, $order->items()->orderBy('id')->pluck('commission_cents')->all());
        $this->assertSame($originalAllocationCents, $order->items()->orderBy('id')->pluck('photographer_allocation_cents')->all());
    }

    public function test_missing_stripe_account_fails_without_calling_stripe(): void
    {
        $photographer = $this->photographer(null);
        $order = $this->paidOrder([[$photographer, 800]]);
        $photographerTransfer = $this->transferFor($order, $photographer, 800);

        app(PhotographerTransferService::class)->processTransfer($photographerTransfer);

        $photographerTransfer->refresh();
        $this->assertSame(PhotographerTransfer::STATUS_FAILED, $photographerTransfer->status);
        $this->assertSame('Photographer Stripe account is not ready for transfers.', $photographerTransfer->last_error);
        $this->assertSame(0, $this->fakeStripe->transfers->createCount);
    }

    public function test_unready_stripe_account_fails_without_calling_stripe(): void
    {
        $this->fakeStripe->accounts->ready = false;
        $photographer = $this->photographer('acct_unready');
        $order = $this->paidOrder([[$photographer, 800]]);
        $photographerTransfer = $this->transferFor($order, $photographer, 800);

        app(PhotographerTransferService::class)->processTransfer($photographerTransfer);

        $this->assertSame(PhotographerTransfer::STATUS_FAILED, $photographerTransfer->fresh()->status);
        $this->assertSame(0, $this->fakeStripe->transfers->createCount);
    }

    public function test_stripe_failure_is_recorded_and_a_retry_can_succeed(): void
    {
        $photographer = $this->photographer('acct_retry');
        $order = $this->paidOrder([[$photographer, 800]]);
        $photographerTransfer = $this->transferFor($order, $photographer, 800);
        $this->fakeStripe->transfers->createException = new RuntimeException('Temporary Stripe failure.');

        try {
            app(PhotographerTransferService::class)->processTransfer($photographerTransfer);
            $this->fail('The Stripe exception was not rethrown for the queue to retry.');
        } catch (RuntimeException) {
            $this->assertSame(PhotographerTransfer::STATUS_FAILED, $photographerTransfer->fresh()->status);
            $this->assertSame('Stripe transfer failed.', $photographerTransfer->fresh()->last_error);
        }

        $this->fakeStripe->transfers->createException = null;
        app(PhotographerTransferService::class)->processTransfer($photographerTransfer->fresh());

        $this->assertSame(PhotographerTransfer::STATUS_SUCCEEDED, $photographerTransfer->fresh()->status);
        $this->assertSame('tr_test_2', $photographerTransfer->fresh()->stripe_transfer_id);
        $this->assertSame(2, $this->fakeStripe->transfers->createCount);
    }

    public function test_succeeded_transfer_is_never_repeated(): void
    {
        $photographer = $this->photographer('acct_once');
        $order = $this->paidOrder([[$photographer, 800]]);
        $photographerTransfer = $this->transferFor($order, $photographer, 800);
        $photographerTransferService = app(PhotographerTransferService::class);

        $photographerTransferService->processTransfer($photographerTransfer);
        $photographerTransferService->processTransfer($photographerTransfer->fresh());

        $this->assertSame(1, $this->fakeStripe->transfers->createCount);
    }

    public function test_duplicate_payment_webhook_creates_only_one_stripe_transfer(): void
    {
        Mail::fake();
        $photographer = $this->photographer('acct_webhook');
        $order = $this->paidOrder([[$photographer, 800]]);
        $order->update([
            'payment_status' => Order::PAYMENT_PENDING,
            'fulfillment_status' => Order::FULFILLMENT_PENDING,
            'paid_at' => null,
            'fulfilled_at' => null,
        ]);
        $webhookPayload = $this->checkoutCompletedPayload($order);

        $this->postPaymentWebhook($webhookPayload)->assertOk();
        $this->postPaymentWebhook($webhookPayload)->assertOk();

        $this->assertDatabaseCount('photographer_transfers', 1);
        $photographerTransfer = PhotographerTransfer::firstOrFail();
        $this->assertSame(
            PhotographerTransfer::STATUS_SUCCEEDED,
            $photographerTransfer->status,
            (string) $photographerTransfer->last_error,
        );
        $this->assertSame(1, $this->fakeStripe->transfers->createCount);
        Mail::assertSent(OrderReceiptMail::class, 1);
    }

    public function test_ready_account_transition_redispatches_failed_transfer_only_once(): void
    {
        Queue::fake();
        Mail::fake();
        $photographer = $this->photographer('acct_recovery', Photographer::STRIPE_INCOMPLETE);
        $order = $this->paidOrder([[$photographer, 800]]);
        $photographerTransfer = $this->transferFor($order, $photographer, 800, PhotographerTransfer::STATUS_FAILED);

        $this->postConnectWebhook($photographer, 'evt_ready_transition')->assertOk();
        $this->postConnectWebhook($photographer, 'evt_already_ready')->assertOk();

        $this->assertSame(Photographer::STRIPE_READY, $photographer->fresh()->stripe_onboarding_status);
        Queue::assertPushed(
            ProcessPhotographerTransfer::class,
            fn (ProcessPhotographerTransfer $job): bool => $job->photographerTransferId === $photographerTransfer->id,
        );
        Queue::assertPushed(ProcessPhotographerTransfer::class, 1);
    }

    private function photographer(?string $stripeAccountId, string $stripeStatus = Photographer::STRIPE_READY): Photographer
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $photographer = Photographer::create([
            'user_id' => $user->id,
            'first_name' => 'Alex',
            'last_name' => 'Rivera',
        ]);
        $photographer->forceFill([
            'status' => Photographer::STATUS_APPROVED,
            'stripe_account_id' => $stripeAccountId,
            'stripe_onboarding_status' => $stripeStatus,
        ])->save();

        return $photographer;
    }

    /** @param array<int, array{Photographer, int}> $photographerAllocations */
    private function paidOrder(array $photographerAllocations): Order
    {
        $event = Event::create([
            'title' => 'Transfer Test '.uniqid(),
            'slug' => Event::generateUniqueSlug('Transfer Test '.uniqid()),
            'sport' => 'Running',
            'content' => 'Transfer test event.',
            'status' => Event::STATUS_PUBLISHED,
            'published' => true,
            'published_at' => now()->subDay(),
            'date_of_event' => '2026-09-01',
            'sales_close_at' => now()->addMonth(),
            'price_cents' => 1000,
            'timezone' => 'America/New_York',
            'city' => 'Orlando',
            'state' => 'FL',
            'country_code' => 'US',
        ]);
        $photoCount = count($photographerAllocations);
        $order = Order::create([
            'event_id' => $event->id,
            'currency' => 'usd',
            'photo_count' => $photoCount,
            'unit_price_cents' => 1000,
            'subtotal_cents' => 1000 * $photoCount,
            'commission_percentage' => 20,
            'total_cents' => 1000 * $photoCount,
            'payment_status' => Order::PAYMENT_PAID,
            'fulfillment_status' => Order::FULFILLMENT_READY,
            'stripe_checkout_session_id' => 'cs_test_'.uniqid(),
            'stripe_payment_intent_id' => 'pi_test_'.uniqid(),
            'paid_at' => now(),
            'fulfilled_at' => now(),
        ]);

        foreach ($photographerAllocations as [$photographer, $photographerAmountCents]) {
            OrderItem::create([
                'order_id' => $order->id,
                'photographer_id' => $photographer->id,
                'photo_uuid' => (string) Str::uuid(),
                'original_key' => 'photos/original-'.uniqid().'.jpg',
                'unit_price_cents' => 1000,
                'commission_cents' => 1000 - $photographerAmountCents,
                'photographer_allocation_cents' => $photographerAmountCents,
            ]);
        }

        return $order;
    }

    private function transferFor(
        Order $order,
        Photographer $photographer,
        int $photographerAmountCents,
        string $status = PhotographerTransfer::STATUS_PENDING,
    ): PhotographerTransfer {
        return PhotographerTransfer::create([
            'order_id' => $order->id,
            'photographer_id' => $photographer->id,
            'amount_cents' => $photographerAmountCents,
            'currency' => $order->currency,
            'status' => $status,
        ]);
    }

    private function checkoutCompletedPayload(Order $order): array
    {
        return [
            'id' => 'evt_'.uniqid(),
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => $order->stripe_checkout_session_id,
                    'payment_status' => 'paid',
                    'payment_intent' => $order->stripe_payment_intent_id,
                    'currency' => $order->currency,
                    'amount_total' => $order->total_cents,
                    'metadata' => [
                        'wivor_order_id' => (string) $order->id,
                        'wivor_order_number' => $order->order_number,
                    ],
                    'customer_details' => ['email' => 'buyer@example.com'],
                ],
            ],
        ];
    }

    private function postPaymentWebhook(array $webhookPayload)
    {
        return $this->postSignedWebhook(route('stripe.webhook'), $webhookPayload, self::PAYMENT_WEBHOOK_SECRET);
    }

    private function postConnectWebhook(Photographer $photographer, string $eventId)
    {
        return $this->postSignedWebhook(route('stripe.connect-webhook'), [
            'id' => $eventId,
            'type' => 'account.updated',
            'account' => $photographer->stripe_account_id,
            'livemode' => false,
            'data' => ['object' => ['id' => $photographer->stripe_account_id]],
        ], self::CONNECT_WEBHOOK_SECRET);
    }

    private function postSignedWebhook(string $url, array $webhookPayload, string $webhookSecret)
    {
        $body = json_encode($webhookPayload);
        $timestamp = time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$body}", $webhookSecret);

        return $this->call('POST', $url, [], [], [], [
            'HTTP_Stripe-Signature' => "t={$timestamp},v1={$signature}",
            'CONTENT_TYPE' => 'application/json',
        ], $body);
    }
}
