<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Order;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\UploadBatch;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Stripe\StripeClient;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_checkout_creates_a_pending_order_with_frozen_pricing_and_redirects_to_stripe(): void
    {
        $stripe = $this->fakeStripe();
        config(['commission.percentage' => 20]);
        $event = $this->publishedEvent(['price_cents' => 1000]);
        [$photo, $photographer] = $this->publishedPhotoForEvent($event, true);

        $this->post(route('cart.items.store'), ['photo' => $photo->uuid])->assertRedirect();

        $response = $this->startCheckout();
        $response->assertRedirect('https://checkout.stripe.com/test-session');

        $order = Order::firstOrFail();
        $this->assertSame($event->id, $order->event_id);
        $this->assertSame(1, $order->photo_count);
        $this->assertSame(1000, $order->unit_price_cents);
        $this->assertSame(1000, $order->subtotal_cents);
        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
        $this->assertSame('cs_test_123', $order->stripe_checkout_session_id);
        $this->assertSame(['card'], $stripe->checkout->sessions->lastParams['payment_method_types']);
        $this->assertSame(
            "wivor-checkout-{$order->order_number}",
            $stripe->checkout->sessions->lastOptions['idempotency_key']
        );

        $item = $order->items()->firstOrFail();
        $this->assertSame($photo->uuid, $item->photo_uuid);
        $this->assertSame($photographer->id, $item->photographer_id);
        $this->assertSame(200, $item->commission_cents);
        $this->assertSame(800, $item->photographer_allocation_cents);

        // The immutable order survives even if the event price later changes.
        $event->update(['price_cents' => 5000]);
        $order->refresh();
        $this->assertSame(1000, $order->unit_price_cents);

        $this->get(route('cart.show'))->assertSee('1 photo selected');

        $this->get(route('checkout.success', [
            'order' => $order->order_number,
            'session_id' => $order->stripe_checkout_session_id,
        ]))
            ->assertOk()
            ->assertSee('Confirming your payment')
            ->assertSee('window.setTimeout', false);

        $this->get(route('checkout.success', [
            'order' => $order->order_number,
            'session_id' => $order->stripe_checkout_session_id,
            'confirmation_attempt' => 10,
        ]))
            ->assertOk()
            ->assertDontSee('window.setTimeout', false);

        $this->get(route('cart.show'))->assertSee('You have not selected any photos yet.');
    }

    public function test_checkout_creates_one_order_spanning_multiple_photographers(): void
    {
        $this->fakeStripe();
        config(['commission.percentage' => 20]);
        $event = $this->publishedEvent(['price_cents' => 1000]);
        [$photoOne, $photographerOne] = $this->publishedPhotoForEvent($event, true);
        [$photoTwo, $photographerTwo] = $this->publishedPhotoForEvent($event, true);

        $this->post(route('cart.items.store'), ['photo' => $photoOne->uuid])->assertRedirect();
        $this->post(route('cart.items.store'), ['photo' => $photoTwo->uuid])->assertRedirect();
        $this->startCheckout()->assertRedirect('https://checkout.stripe.com/test-session');

        $order = Order::firstOrFail();
        $this->assertSame(2, $order->photo_count);
        $this->assertSame(2000, $order->subtotal_cents);

        $photographerIds = $order->items()->pluck('photographer_id')->sort()->values()->all();
        $this->assertSame(collect([$photographerOne->id, $photographerTwo->id])->sort()->values()->all(), $photographerIds);
    }

    public function test_pending_order_creation_allows_manual_payout_when_stripe_setup_is_incomplete(): void
    {
        $this->fakeStripe();
        $event = $this->publishedEvent();
        [$photo] = $this->publishedPhotoForEvent($event, false);
        $photo->loadMissing('photographer');

        $order = app(CheckoutService::class)->createPendingOrder($event, new Collection([$photo]));

        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
        $this->assertSame($photo->photographer_id, $order->items()->firstOrFail()->photographer_id);
    }

    public function test_cancel_page_is_read_only_and_preserves_the_cart(): void
    {
        $this->fakeStripe();
        $event = $this->publishedEvent();
        [$photo] = $this->publishedPhotoForEvent($event, true);

        $this->post(route('cart.items.store'), ['photo' => $photo->uuid])->assertRedirect();
        $this->startCheckout()->assertRedirect();

        $order = Order::firstOrFail();

        $this->get(route('checkout.cancel', ['order' => $order->order_number]))
            ->assertOk()
            ->assertSee('Payment not completed');

        $order->refresh();
        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
        $this->assertNull($order->cancelled_at);
        $this->get(route('cart.show'))->assertSee('1 photo selected');
    }

    public function test_success_page_rejects_a_checkout_session_from_another_order(): void
    {
        $this->fakeStripe();
        $event = $this->publishedEvent();
        [$photo] = $this->publishedPhotoForEvent($event, false);

        $this->post(route('cart.items.store'), ['photo' => $photo->uuid]);
        $this->startCheckout();
        $order = Order::firstOrFail();

        $this->get(route('checkout.success', [
            'order' => $order->order_number,
            'session_id' => 'cs_wrong',
        ]))->assertNotFound();
    }

    public function test_checkout_form_token_can_only_be_used_once(): void
    {
        $this->fakeStripe();
        $event = $this->publishedEvent();
        [$photo] = $this->publishedPhotoForEvent($event, false);

        $this->post(route('cart.items.store'), ['photo' => $photo->uuid]);
        $this->get(route('cart.show'));
        $checkoutToken = (string) session('wivor_checkout_token');

        $this->post(route('checkout.store'), ['checkout_token' => $checkoutToken])
            ->assertRedirect('https://checkout.stripe.com/test-session');
        $this->post(route('checkout.store'), ['checkout_token' => $checkoutToken])
            ->assertRedirect(route('cart.show'))
            ->assertSessionHasErrors('cart');

        $this->assertSame(1, Order::count());
    }

    public function test_stripe_failure_preserves_the_cart_for_another_attempt(): void
    {
        $this->app->instance(StripeClient::class, new class extends StripeClient
        {
            public $checkout;

            public function __construct()
            {
                $this->checkout = new class
                {
                    public $sessions;

                    public function __construct()
                    {
                        $this->sessions = new class
                        {
                            public function create(array $params, array $options = []): object
                            {
                                throw new \RuntimeException('Stripe is unavailable.');
                            }
                        };
                    }
                };
            }
        });
        $event = $this->publishedEvent();
        [$photo] = $this->publishedPhotoForEvent($event, false);

        $this->post(route('cart.items.store'), ['photo' => $photo->uuid]);
        $this->startCheckout()
            ->assertRedirect(route('cart.show'))
            ->assertSessionHasErrors('cart');

        $this->get(route('cart.show'))->assertSee('1 photo selected');
    }

    private function startCheckout(): TestResponse
    {
        $this->get(route('cart.show'));

        return $this->post(route('checkout.store'), [
            'checkout_token' => (string) session('wivor_checkout_token'),
        ]);
    }

    private function fakeStripe(): StripeClient
    {
        $stripe = new class extends StripeClient
        {
            public $checkout;

            public function __construct()
            {
                $this->checkout = new class
                {
                    public $sessions;

                    public function __construct()
                    {
                        $this->sessions = new class
                        {
                            public array $lastParams = [];
                            public array $lastOptions = [];
                            private ?object $createdSession = null;

                            public function create(array $params, array $options = []): object
                            {
                                $this->lastParams = $params;
                                $this->lastOptions = $options;
                                $this->createdSession = (object) [
                                    'id' => 'cs_test_123',
                                    'url' => 'https://checkout.stripe.com/test-session',
                                    'payment_status' => 'paid',
                                    'currency' => $params['line_items'][0]['price_data']['currency'],
                                    'amount_total' => $params['line_items'][0]['price_data']['unit_amount'] * $params['line_items'][0]['quantity'],
                                    'metadata' => (object) $params['metadata'],
                                ];

                                return $this->createdSession;
                            }

                            public function retrieve(string $id, array $params = []): object
                            {
                                return $this->createdSession;
                            }
                        };
                    }
                };
            }
        };

        $this->app->instance(StripeClient::class, $stripe);

        return $stripe;
    }

    private function publishedEvent(array $eventOverrides = []): Event
    {
        return Event::create(array_merge([
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
        ], $eventOverrides));
    }

    /** @return array{Photo, Photographer} */
    private function publishedPhotoForEvent(Event $event, bool $photographerStripeReady): array
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $photographer = Photographer::create([
            'user_id' => $user->id,
            'first_name' => 'Alex',
            'last_name' => 'Rivera',
        ]);
        $photographer->forceFill(['status' => Photographer::STATUS_APPROVED]);
        if ($photographerStripeReady) {
            $photographer->forceFill([
                'stripe_account_id' => 'acct_'.uniqid(),
                'stripe_onboarding_status' => Photographer::STRIPE_COMPLETE,
            ]);
        }
        $photographer->save();

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
            'detected_mime' => 'image/jpeg',
            'width' => 2400,
            'height' => 1600,
            'checksum' => hash('sha256', uniqid('', true)),
            'status' => Photo::STATUS_PUBLISHED,
            'uploaded_at' => now()->subDay(),
            'published_at' => now()->subHours(12),
        ]);

        return [$photo, $photographer];
    }
}
