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
use Illuminate\Validation\ValidationException;
use Stripe\StripeClient;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_checkout_creates_a_pending_order_with_frozen_pricing_and_redirects_to_stripe(): void
    {
        $this->fakeStripe();
        config(['commission.percentage' => 20]);
        $event = $this->publishedEvent(['price_cents' => 1000]);
        [$photo, $photographer] = $this->publishedPhotoForEvent($event, true);

        $this->post(route('cart.items.store'), ['photo' => $photo->uuid])->assertRedirect();

        $response = $this->post(route('checkout.store'));
        $response->assertRedirect('https://checkout.stripe.com/test-session');

        $order = Order::firstOrFail();
        $this->assertSame($event->id, $order->event_id);
        $this->assertSame(1, $order->photo_count);
        $this->assertSame(1000, $order->unit_price_cents);
        $this->assertSame(1000, $order->subtotal_cents);
        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
        $this->assertSame('cs_test_123', $order->stripe_checkout_session_id);

        $item = $order->items()->firstOrFail();
        $this->assertSame($photo->uuid, $item->photo_uuid);
        $this->assertSame($photographer->id, $item->photographer_id);
        $this->assertSame(200, $item->commission_cents);
        $this->assertSame(800, $item->photographer_allocation_cents);

        // The immutable order survives even if the event price later changes.
        $event->update(['price_cents' => 5000]);
        $order->refresh();
        $this->assertSame(1000, $order->unit_price_cents);

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
        $this->post(route('checkout.store'))->assertRedirect('https://checkout.stripe.com/test-session');

        $order = Order::firstOrFail();
        $this->assertSame(2, $order->photo_count);
        $this->assertSame(2000, $order->subtotal_cents);

        $photographerIds = $order->items()->pluck('photographer_id')->sort()->values()->all();
        $this->assertSame(collect([$photographerOne->id, $photographerTwo->id])->sort()->values()->all(), $photographerIds);
    }

    public function test_pending_order_creation_rejects_a_photo_whose_photographer_is_not_stripe_ready(): void
    {
        $this->fakeStripe();
        $event = $this->publishedEvent();
        [$photo] = $this->publishedPhotoForEvent($event, false);
        $photo->loadMissing('photographer');

        $this->expectException(ValidationException::class);

        app(CheckoutService::class)->createPendingOrder($event, new Collection([$photo]));
    }

    public function test_cancel_page_marks_a_pending_order_as_cancelled(): void
    {
        $this->fakeStripe();
        $event = $this->publishedEvent();
        [$photo] = $this->publishedPhotoForEvent($event, true);

        $this->post(route('cart.items.store'), ['photo' => $photo->uuid])->assertRedirect();
        $this->post(route('checkout.store'))->assertRedirect();

        $order = Order::firstOrFail();

        $this->get(route('checkout.cancel', ['order' => $order->order_number]))
            ->assertOk()
            ->assertSee('Payment not completed');

        $order->refresh();
        $this->assertSame(Order::PAYMENT_CANCELLED, $order->payment_status);
        $this->assertNotNull($order->cancelled_at);
    }

    private function fakeStripe(): void
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
                            public function create(array $params): object
                            {
                                return (object) ['id' => 'cs_test_123', 'url' => 'https://checkout.stripe.com/test-session'];
                            }
                        };
                    }
                };
            }
        });
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
