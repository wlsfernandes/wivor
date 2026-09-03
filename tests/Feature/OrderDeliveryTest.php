<?php

namespace Tests\Feature;

use App\Mail\OrderReceiptMail;
use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\UploadBatch;
use App\Models\User;
use App\Services\PhotoStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Stripe\StripeClient;
use Tests\TestCase;

class OrderDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private const WEBHOOK_SECRET = 'whsec_test_secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.stripe.webhook_secret' => self::WEBHOOK_SECRET]);
        $this->fakeStorage();
        $this->fakeStripe();
    }

    public function test_secure_order_page_shows_a_download_link_for_a_paid_entitled_item(): void
    {
        [$order, , $photo] = $this->paidOrder();

        $this->get(route('orders.show', ['accessToken' => $order->access_token]))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee(route('orders.download', ['accessToken' => $order->access_token, 'photo' => $photo->uuid]), false);
    }

    public function test_invalid_access_token_returns_not_found(): void
    {
        $this->get(route('orders.show', ['accessToken' => 'does-not-exist']))->assertNotFound();
    }

    public function test_download_redirects_to_a_signed_url_for_an_entitled_item(): void
    {
        [$order, , $photo] = $this->paidOrder();

        $this->get(route('orders.download', ['accessToken' => $order->access_token, 'photo' => $photo->uuid]))
            ->assertRedirect("https://signed.example.test/{$photo->original_key}");
    }

    public function test_download_is_blocked_once_the_entitlement_expires(): void
    {
        [$order, $item, $photo] = $this->paidOrder();
        $item->update(['download_expires_at' => now()->subDay()]);

        $this->get(route('orders.download', ['accessToken' => $order->access_token, 'photo' => $photo->uuid]))
            ->assertForbidden();
    }

    public function test_download_is_blocked_when_the_order_is_not_paid(): void
    {
        [$order, $item, $photo] = $this->paidOrder();
        $order->update(['payment_status' => Order::PAYMENT_PENDING]);
        $item->update(['download_status' => OrderItem::DOWNLOAD_PENDING]);

        $this->get(route('orders.download', ['accessToken' => $order->access_token, 'photo' => $photo->uuid]))
            ->assertForbidden();
    }

    public function test_webhook_fulfillment_sends_the_receipt_email_exactly_once(): void
    {
        Mail::fake();
        [$order] = $this->pendingOrder();
        $payload = $this->checkoutCompletedPayload($order->stripe_checkout_session_id);

        $this->postWebhook($payload)->assertOk();
        $this->postWebhook($payload)->assertOk();

        Mail::assertSent(OrderReceiptMail::class, 1);
        Mail::assertSent(OrderReceiptMail::class, function (OrderReceiptMail $mail) use ($order) {
            return $mail->order->id === $order->id
                && $mail->hasTo('buyer@example.com');
        });
    }

    private function fakeStorage(): void
    {
        $this->app->instance(PhotoStorage::class, new class extends PhotoStorage
        {
            public function deliveryUrl(string $key): string
            {
                return "https://signed.example.test/{$key}";
            }
        });
    }

    private function fakeStripe(): void
    {
        $this->app->instance(StripeClient::class, new class extends StripeClient
        {
            public $transfers;

            public function __construct()
            {
                $this->transfers = new class
                {
                    public function create(array $params): object
                    {
                        return (object) ['id' => 'tr_test_123'];
                    }
                };
            }
        });
    }

    /** @return array{Order, OrderItem, Photo} */
    private function paidOrder(): array
    {
        [$order, $photo] = $this->pendingOrder();
        $paidAt = now();

        $order->update([
            'payment_status' => Order::PAYMENT_PAID,
            'fulfillment_status' => Order::FULFILLMENT_READY,
            'customer_email' => 'buyer@example.com',
            'paid_at' => $paidAt,
            'fulfilled_at' => $paidAt,
            'download_expires_at' => $paidAt->copy()->addDays(90),
        ]);
        $item = $order->items()->firstOrFail();
        $item->update([
            'download_status' => OrderItem::DOWNLOAD_READY,
            'download_expires_at' => $paidAt->copy()->addDays(90),
        ]);

        return [$order, $item, $photo];
    }

    /** @return array{Order, Photo} */
    private function pendingOrder(): array
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $photographer = Photographer::create([
            'user_id' => $user->id,
            'first_name' => 'Alex',
            'last_name' => 'Rivera',
        ]);
        $photographer->forceFill([
            'status' => Photographer::STATUS_APPROVED,
            'stripe_account_id' => 'acct_'.uniqid(),
            'stripe_onboarding_status' => Photographer::STRIPE_COMPLETE,
        ])->save();

        $event = Event::create([
            'title' => 'City Run '.uniqid(),
            'slug' => Event::generateUniqueSlug('City Run '.uniqid()),
            'sport' => 'Running',
            'content' => 'A city road race.',
            'status' => Event::STATUS_PUBLISHED,
            'published' => true,
            'published_at' => now()->subDay(),
            'date_of_event' => '2026-08-30',
            'sales_close_at' => now()->addMonth(),
            'price_cents' => 1000,
            'timezone' => 'America/New_York',
            'city' => 'Orlando',
            'state' => 'FL',
            'country_code' => 'US',
        ]);

        $assignment = EventAssignment::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'status' => 'approved',
            'upload_deadline_at' => now()->addDays(3),
        ]);
        $batch = UploadBatch::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'assignment_id' => $assignment->id,
            'selected_count' => 1,
            'status' => 'completed',
        ]);
        $photo = Photo::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'assignment_id' => $assignment->id,
            'upload_batch_id' => $batch->id,
            'original_filename' => 'finish.jpg',
            'original_key' => 'photos/original-'.uniqid().'.jpg',
            'thumbnail_key' => 'photos/thumbnail-'.uniqid().'.jpg',
            'detected_mime' => 'image/jpeg',
            'width' => 2400,
            'height' => 1600,
            'checksum' => hash('sha256', uniqid('', true)),
            'status' => Photo::STATUS_PUBLISHED,
            'uploaded_at' => now()->subDay(),
            'published_at' => now()->subHours(12),
        ]);

        $order = Order::create([
            'event_id' => $event->id,
            'currency' => 'usd',
            'photo_count' => 1,
            'unit_price_cents' => 1000,
            'subtotal_cents' => 1000,
            'commission_percentage' => 20,
            'total_cents' => 1000,
            'payment_status' => Order::PAYMENT_PENDING,
            'fulfillment_status' => Order::FULFILLMENT_PENDING,
            'stripe_checkout_session_id' => 'cs_test_'.uniqid(),
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'photo_id' => $photo->id,
            'photographer_id' => $photographer->id,
            'photo_uuid' => $photo->uuid,
            'original_key' => $photo->original_key,
            'unit_price_cents' => 1000,
            'commission_cents' => 200,
            'photographer_allocation_cents' => 800,
        ]);

        return [$order, $photo];
    }

    private function checkoutCompletedPayload(string $sessionId): array
    {
        return [
            'id' => 'evt_'.uniqid(),
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => $sessionId,
                    'payment_status' => 'paid',
                    'payment_intent' => 'pi_123',
                    'customer_details' => ['email' => 'buyer@example.com'],
                ],
            ],
        ];
    }

    private function postWebhook(array $payload)
    {
        $body = json_encode($payload);
        $timestamp = time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$body}", self::WEBHOOK_SECRET);

        return $this->call('POST', route('stripe.webhook'), [], [], [], [
            'HTTP_Stripe-Signature' => "t={$timestamp},v1={$signature}",
            'CONTENT_TYPE' => 'application/json',
        ], $body);
    }
}
