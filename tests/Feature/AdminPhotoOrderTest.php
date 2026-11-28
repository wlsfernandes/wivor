<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\Role;
use App\Models\UploadBatch;
use App\Models\User;
use App\Services\PhotoStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPhotoOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reconcile_a_photo_order_with_stripe_identifiers(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin']));
        $event = Event::create([
            'title' => 'City Run',
            'slug' => 'city-run',
            'content' => 'A city road race.',
            'status' => Event::STATUS_PUBLISHED,
            'published' => true,
            'price_cents' => 1000,
            'timezone' => 'America/New_York',
            'country_code' => 'US',
        ]);
        $order = Order::create([
            'event_id' => $event->id,
            'customer_email' => 'buyer@example.com',
            'currency' => 'usd',
            'photo_count' => 2,
            'unit_price_cents' => 1000,
            'subtotal_cents' => 2000,
            'commission_percentage' => 20,
            'total_cents' => 2000,
            'payment_status' => Order::PAYMENT_PAID,
            'fulfillment_status' => Order::FULFILLMENT_READY,
            'stripe_checkout_session_id' => 'cs_test_reconcile',
            'stripe_payment_intent_id' => 'pi_test_reconcile',
        ]);

        $this->actingAs($admin)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('buyer@example.com')
            ->assertSee('$20.00 USD')
            ->assertSee('cs_test_reconcile')
            ->assertSee('pi_test_reconcile');
    }

    public function test_admin_sees_which_photos_each_order_sold(): void
    {
        $this->app->instance(PhotoStorage::class, new class extends PhotoStorage
        {
            public function deliveryUrl(string $key): string
            {
                return "https://signed.example.test/{$key}";
            }
        });
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin']));
        $event = Event::create([
            'title' => 'City Run', 'slug' => 'city-run', 'content' => 'A city road race.',
            'status' => Event::STATUS_PUBLISHED, 'published' => true, 'price_cents' => 1000,
            'timezone' => 'America/New_York', 'country_code' => 'US',
        ]);
        $photographerUser = User::factory()->create();
        $photographer = Photographer::create(['user_id' => $photographerUser->id, 'first_name' => 'Pat', 'last_name' => 'Lee']);
        $assignment = EventAssignment::create(['event_id' => $event->id, 'photographer_id' => $photographer->id, 'status' => 'approved']);
        $batch = UploadBatch::create(['event_id' => $event->id, 'photographer_id' => $photographer->id, 'assignment_id' => $assignment->id, 'selected_count' => 2]);
        $publishedPhoto = Photo::create([
            'event_id' => $event->id, 'photographer_id' => $photographer->id, 'assignment_id' => $assignment->id,
            'upload_batch_id' => $batch->id, 'original_filename' => 'finish.jpg', 'original_key' => 'original.jpg',
            'preview_key' => 'preview.jpg', 'thumbnail_key' => 'events/1/thumb-finish.jpg', 'status' => Photo::STATUS_PUBLISHED,
        ]);
        $removedPhoto = Photo::create([
            'event_id' => $event->id, 'photographer_id' => $photographer->id, 'assignment_id' => $assignment->id,
            'upload_batch_id' => $batch->id, 'original_filename' => 'start.jpg', 'original_key' => 'original-2.jpg',
            'preview_key' => 'preview-2.jpg', 'thumbnail_key' => 'events/1/thumb-start.jpg', 'status' => Photo::STATUS_REMOVED,
        ]);
        $order = Order::create([
            'event_id' => $event->id, 'customer_email' => 'buyer@example.com', 'currency' => 'usd',
            'photo_count' => 2, 'unit_price_cents' => 1000, 'subtotal_cents' => 2000,
            'commission_percentage' => 20, 'total_cents' => 2000, 'payment_status' => Order::PAYMENT_PAID,
            'fulfillment_status' => Order::FULFILLMENT_READY,
        ]);
        foreach ([$publishedPhoto, $removedPhoto] as $photo) {
            OrderItem::create([
                'order_id' => $order->id, 'photo_id' => $photo->id, 'photographer_id' => $photographer->id,
                'photo_uuid' => $photo->uuid, 'original_key' => $photo->original_key, 'unit_price_cents' => 1000,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('https://signed.example.test/events/1/thumb-finish.jpg', false)
            ->assertDontSee('https://signed.example.test/events/1/thumb-start.jpg', false)
            ->assertSee($publishedPhoto->reference_number)
            ->assertSee($removedPhoto->reference_number)
            ->assertSee('No preview')
            ->assertSee('View 2 photos sold in order '.$order->order_number);
    }

    public function test_guest_cannot_open_the_photo_order_reconciliation_page(): void
    {
        $this->get(route('payments.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_open_the_payment_process_manual(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin']));

        $this->actingAs($admin)
            ->get(route('payments.manual'))
            ->assertOk()
            ->assertSee('How customer payments and photographer payouts work')
            ->assertSee('Automatic photographer transfers')
            ->assertSee('Important refund limitation')
            ->assertSee('Payment Process Manual');
    }

    public function test_guest_cannot_open_the_payment_process_manual(): void
    {
        $this->get(route('payments.manual'))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_open_the_payment_process_manual(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('payments.manual'))
            ->assertForbidden();
    }
}
