<?php

namespace Tests\Feature;

use App\Mail\PhotographerPayoutStatusChanged;
use App\Models\Photographer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\InvalidRequestException;
use Stripe\StripeClient;
use Tests\TestCase;

class PhotographerPayoutSetupTest extends TestCase
{
    use RefreshDatabase;

    private const WEBHOOK_SECRET = 'whsec_connect_test';

    private object $fakeStripe;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.stripe.secret' => 'sk_test_fake',
            'services.stripe.connect_webhook_secret' => self::WEBHOOK_SECRET,
        ]);

        $this->fakeStripe = new class extends StripeClient
        {
            public $accounts;

            public $accountLinks;

            public $v2;

            public function __construct()
            {
                $this->accounts = new class
                {
                    public int $createCount = 0;

                    public array $createParams = [];

                    public array $createOptions = [];

                    public ?\Throwable $createException = null;

                    public object $account;

                    public function __construct()
                    {
                        $this->account = $this->state('inactive', false, false);
                    }

                    public function create(array $params, array $options = []): object
                    {
                        if ($this->createException) {
                            throw $this->createException;
                        }

                        $this->createCount++;
                        $this->createParams = $params;
                        $this->createOptions = $options;

                        return $this->account;
                    }

                    public function retrieve(string $accountId, array $params = []): object
                    {
                        return $this->account;
                    }

                    public function createLoginLink(string $accountId): object
                    {
                        return (object) ['url' => 'https://connect.stripe.test/dashboard'];
                    }

                    public function setReady(): void
                    {
                        $this->account = $this->state('active', true, true);
                    }

                    public function setActionRequired(): void
                    {
                        $this->account = $this->state('inactive', false, true, ['individual.verification.document']);
                    }

                    private function state(
                        string $transfers,
                        bool $payoutsEnabled,
                        bool $detailsSubmitted,
                        array $currentlyDue = []
                    ): object
                    {
                        return (object) [
                            'id' => 'acct_connect_test',
                            'capabilities' => (object) ['transfers' => $transfers],
                            'payouts_enabled' => $payoutsEnabled,
                            'details_submitted' => $detailsSubmitted,
                            'requirements' => (object) [
                                'currently_due' => $currentlyDue,
                                'eventually_due' => [],
                                'past_due' => [],
                                'pending_verification' => [],
                                'disabled_reason' => null,
                                'current_deadline' => null,
                            ],
                        ];
                    }
                };

                $this->accountLinks = new class
                {
                    public int $createCount = 0;

                    public array $createParams = [];

                    public function create(array $params): object
                    {
                        $this->createCount++;
                        $this->createParams = $params;

                        return (object) ['url' => 'https://connect.stripe.test/onboarding'];
                    }
                };

                $this->v2 = (object) [
                    'core' => (object) [
                        'accounts' => $this->accounts,
                        'accountLinks' => $this->accountLinks,
                    ],
                ];
            }
        };

        $this->app->instance(StripeClient::class, $this->fakeStripe);
    }

    public function test_starting_onboarding_reuses_the_same_connected_account(): void
    {
        [$user, $photographer] = $this->approvedPhotographer();

        $this->actingAs($user)->post(route('photographer.payouts.start'))
            ->assertRedirect('https://connect.stripe.test/onboarding');
        $this->actingAs($user)->post(route('photographer.payouts.start'))
            ->assertRedirect('https://connect.stripe.test/onboarding');

        $this->assertSame(1, $this->fakeStripe->accounts->createCount);
        $this->assertSame(2, $this->fakeStripe->accountLinks->createCount);
        $this->assertTrue($this->fakeStripe->accounts->createParams['configuration']['merchant']['capabilities']['card_payments']['requested']);
        $this->assertTrue($this->fakeStripe->accounts->createParams['configuration']['recipient']['capabilities']['stripe_balance']['stripe_transfers']['requested']);
        $this->assertSame('full', $this->fakeStripe->accounts->createParams['dashboard']);
        $this->assertSame([
            'fees_collector' => 'stripe',
            'losses_collector' => 'stripe',
        ], $this->fakeStripe->accounts->createParams['defaults']['responsibilities']);
        $this->assertSame('wivor-v2-photographer-'.$photographer->id, $this->fakeStripe->accounts->createOptions['idempotency_key']);
        $this->assertSame(['merchant', 'recipient'], $this->fakeStripe->accountLinks->createParams['use_case']['account_onboarding']['configurations']);
        $this->assertSame('acct_connect_test', $photographer->fresh()->stripe_account_id);
        $this->assertSame(Photographer::STRIPE_INCOMPLETE, $photographer->fresh()->stripe_onboarding_status);
    }

    public function test_return_from_stripe_synchronizes_ready_status(): void
    {
        [$user, $photographer] = $this->approvedPhotographer();
        $photographer->forceFill(['stripe_account_id' => 'acct_connect_test'])->save();
        $this->fakeStripe->accounts->setReady();

        $this->actingAs($user)->get(route('photographer.payouts.return'))
            ->assertRedirect(route('photographer.dashboard'))
            ->assertSessionHas('success', 'Payout setup is complete.');

        $photographer->refresh();
        $this->assertSame(Photographer::STRIPE_READY, $photographer->stripe_onboarding_status);
        $this->assertTrue($photographer->stripe_transfers_active);
        $this->assertTrue($photographer->stripe_payouts_enabled);
        $this->assertNotNull($photographer->stripe_ready_at);
        $this->assertNotNull($photographer->stripe_last_synced_at);

        $this->actingAs($user)->post(route('photographer.payouts.dashboard'))
            ->assertRedirect('https://dashboard.stripe.com');
    }

    public function test_starting_onboarding_logs_safe_stripe_error_details(): void
    {
        Log::spy();
        [$user, $photographer] = $this->approvedPhotographer();
        $this->fakeStripe->accounts->createException = InvalidRequestException::factory(
            'The connected account configuration is invalid.',
            400,
            null,
            null,
            ['Request-Id' => 'req_connect_test'],
            'invalid_fields',
        );

        $this->actingAs($user)->post(route('photographer.payouts.start'))
            ->assertRedirect(route('photographer.dashboard'))
            ->assertSessionHasErrors('payouts');

        Log::shouldHaveReceived('error')->once()->with(
            'Stripe payout onboarding could not start.',
            \Mockery::on(fn (array $context): bool => $context === [
                'event' => 'photographers.payouts.start',
                'photographer_id' => $photographer->id,
                'exception_class' => InvalidRequestException::class,
                'exception_message' => 'The connected account configuration is invalid.',
                'stripe_error_code' => 'invalid_fields',
                'stripe_request_id' => 'req_connect_test',
                'http_status' => 400,
            ]),
        );
    }

    public function test_account_updated_webhook_is_idempotent_and_notifies_when_ready(): void
    {
        Mail::fake();
        [$user, $photographer] = $this->approvedPhotographer();
        $photographer->forceFill([
            'stripe_account_id' => 'acct_connect_test',
            'stripe_onboarding_status' => Photographer::STRIPE_INCOMPLETE,
        ])->save();
        $this->fakeStripe->accounts->setReady();
        $payload = [
            'id' => 'evt_connect_ready',
            'type' => 'account.updated',
            'account' => 'acct_connect_test',
            'livemode' => false,
            'data' => ['object' => ['id' => 'acct_connect_test']],
        ];

        $this->postConnectWebhook($payload)->assertOk();
        $this->postConnectWebhook($payload)->assertOk();

        $photographer->refresh();
        $this->assertSame(Photographer::STRIPE_READY, $photographer->stripe_onboarding_status);
        $this->assertSame('evt_connect_ready', $photographer->stripe_last_event_id);
        Mail::assertSent(PhotographerPayoutStatusChanged::class, 1);
    }

    public function test_account_updated_notifies_when_new_action_is_required(): void
    {
        Mail::fake();
        [, $photographer] = $this->approvedPhotographer();
        $photographer->forceFill([
            'stripe_account_id' => 'acct_connect_test',
            'stripe_onboarding_status' => Photographer::STRIPE_READY,
        ])->save();
        $this->fakeStripe->accounts->setActionRequired();

        $this->postConnectWebhook([
            'id' => 'evt_connect_action_required',
            'type' => 'account.updated',
            'account' => 'acct_connect_test',
            'livemode' => false,
            'data' => ['object' => ['id' => 'acct_connect_test']],
        ])->assertOk();

        $this->assertSame(Photographer::STRIPE_ACTION_REQUIRED, $photographer->fresh()->stripe_onboarding_status);
        Mail::assertSent(PhotographerPayoutStatusChanged::class, 1);
    }

    public function test_unapproved_photographer_cannot_start_payout_setup(): void
    {
        $role = Role::firstOrCreate(['name' => 'photographer']);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->attach($role);
        Photographer::create([
            'user_id' => $user->id,
            'first_name' => 'Alex',
            'last_name' => 'Rivera',
        ]);

        $this->actingAs($user)->post(route('photographer.payouts.start'))->assertForbidden();
        $this->assertSame(0, $this->fakeStripe->accounts->createCount);
    }

    private function approvedPhotographer(): array
    {
        $role = Role::firstOrCreate(['name' => 'photographer']);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->attach($role);
        $photographer = Photographer::create([
            'user_id' => $user->id,
            'first_name' => 'Alex',
            'last_name' => 'Rivera',
        ]);
        $photographer->forceFill(['status' => Photographer::STATUS_APPROVED])->save();

        return [$user, $photographer];
    }

    private function postConnectWebhook(array $payload)
    {
        $body = json_encode($payload);
        $timestamp = time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$body}", self::WEBHOOK_SECRET);

        return $this->call('POST', route('stripe.connect-webhook'), [], [], [], [
            'HTTP_Stripe-Signature' => "t={$timestamp},v1={$signature}",
            'CONTENT_TYPE' => 'application/json',
        ], $body);
    }
}
