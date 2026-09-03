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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PhotographerSalesPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_summarizes_only_the_photographers_own_paid_sales(): void
    {
        [$user, $photographer] = $this->approvedPhotographer();
        $event = $this->publishedEvent(1000);
        $this->paidOrder($event, $photographer, 'WVR-MINE0001', 800);
        $this->pendingOrder($event, $photographer, 'WVR-MINE0002');

        [, $otherPhotographer] = $this->approvedPhotographer();
        $otherEvent = $this->publishedEvent(1000);
        $this->paidOrder($otherEvent, $otherPhotographer, 'WVR-OTHER001', 800);

        $response = $this->actingAs($user)->get(route('photographer.dashboard'));

        $response->assertOk()
            ->assertSee('$10.00') // gross sales: only the one paid order belonging to this photographer
            ->assertSee('$2.00')  // commission
            ->assertSee('$8.00')  // net earnings / pending payout
            ->assertSee('WVR-MINE0001')
            ->assertSee('WVR-MINE0002')
            ->assertDontSee('WVR-OTHER001');
    }

    public function test_payment_status_filter_narrows_the_sales_list(): void
    {
        [$user, $photographer] = $this->approvedPhotographer();
        $event = $this->publishedEvent(1000);
        $this->paidOrder($event, $photographer, 'WVR-PAID0001', 800);
        $this->pendingOrder($event, $photographer, 'WVR-PEND0001');

        $this->actingAs($user)
            ->get(route('photographer.dashboard', ['payment_status' => 'paid']))
            ->assertOk()
            ->assertSee('WVR-PAID0001')
            ->assertDontSee('WVR-PEND0001');
    }

    public function test_payout_status_filter_maps_to_payment_status(): void
    {
        [$user, $photographer] = $this->approvedPhotographer();
        $event = $this->publishedEvent(1000);
        $this->paidOrder($event, $photographer, 'WVR-PAYOUT01', 800);
        $this->pendingOrder($event, $photographer, 'WVR-PEND0002');

        $this->actingAs($user)
            ->get(route('photographer.dashboard', ['payout_status' => 'pending']))
            ->assertOk()
            ->assertSee('WVR-PAYOUT01')
            ->assertDontSee('WVR-PEND0002');
    }

    public function test_dashboard_shows_only_this_photographers_share_of_a_shared_order(): void
    {
        [$user, $photographer] = $this->approvedPhotographer();
        [, $otherPhotographer] = $this->approvedPhotographer();
        $event = $this->publishedEvent(1000);

        $order = $this->paidOrder($event, $photographer, 'WVR-SHARED01', 800);
        OrderItem::create([
            'order_id' => $order->id,
            'photo_id' => $this->photoFor($event, $otherPhotographer)->id,
            'photographer_id' => $otherPhotographer->id,
            'photo_uuid' => (string) Str::uuid(),
            'original_key' => 'photos/other-'.uniqid().'.jpg',
            'unit_price_cents' => 1000,
            'commission_cents' => 200,
            'photographer_allocation_cents' => 800,
        ]);
        $order->update(['photo_count' => 2, 'subtotal_cents' => 2000, 'total_cents' => 2000]);

        // Both this photographer's own item ($10.00 gross / $8.00 net) and the sale summary must
        // reflect only their share of the order, never the other photographer's item in the same order.
        $this->actingAs($user)->get(route('photographer.dashboard'))
            ->assertOk()
            ->assertSee('WVR-SHARED01')
            ->assertSee('$10.00')
            ->assertSee('$8.00')
            ->assertDontSee('$20.00')
            ->assertDontSee('$16.00');
    }

    public function test_paid_out_filter_only_matches_items_with_a_completed_transfer(): void
    {
        [$user, $photographer] = $this->approvedPhotographer();
        $event = $this->publishedEvent(1000);
        $paidOrder = $this->paidOrder($event, $photographer, 'WVR-TRANSFER1', 800);
        $paidOrder->items()->update(['stripe_transfer_id' => 'tr_test_123']);
        $this->paidOrder($event, $photographer, 'WVR-NOTRANSFER', 800);

        $this->actingAs($user)
            ->get(route('photographer.dashboard', ['payout_status' => 'paid']))
            ->assertOk()
            ->assertSee('WVR-TRANSFER1')
            ->assertDontSee('WVR-NOTRANSFER');
    }

    /** @return array{User, Photographer} */
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

    private function publishedEvent(int $priceCents): Event
    {
        return Event::create([
            'title' => 'City Run '.uniqid(),
            'slug' => Event::generateUniqueSlug('City Run '.uniqid()),
            'sport' => 'Running',
            'content' => 'A city road race.',
            'status' => Event::STATUS_PUBLISHED,
            'published' => true,
            'published_at' => now()->subDay(),
            'date_of_event' => '2026-08-30',
            'sales_close_at' => now()->addMonth(),
            'price_cents' => $priceCents,
            'timezone' => 'America/New_York',
            'city' => 'Orlando',
            'state' => 'FL',
            'country_code' => 'US',
        ]);
    }

    private function photoFor(Event $event, Photographer $photographer): Photo
    {
        $assignment = EventAssignment::firstOrCreate(
            ['event_id' => $event->id, 'photographer_id' => $photographer->id],
            ['status' => 'approved', 'upload_deadline_at' => now()->addDays(3)]
        );
        $batch = UploadBatch::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'assignment_id' => $assignment->id,
            'selected_count' => 1,
            'status' => 'completed',
        ]);

        return Photo::create([
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
    }

    private function paidOrder(Event $event, Photographer $photographer, string $orderNumber, int $allocationCents): Order
    {
        $photo = $this->photoFor($event, $photographer);
        $order = Order::create([
            'order_number' => $orderNumber,
            'event_id' => $event->id,
            'currency' => 'usd',
            'photo_count' => 1,
            'unit_price_cents' => $event->price_cents,
            'subtotal_cents' => $event->price_cents,
            'commission_percentage' => 20,
            'total_cents' => $event->price_cents,
            'payment_status' => Order::PAYMENT_PAID,
            'fulfillment_status' => Order::FULFILLMENT_READY,
            'customer_email' => 'buyer@example.com',
            'paid_at' => now(),
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'photo_id' => $photo->id,
            'photographer_id' => $photographer->id,
            'photo_uuid' => $photo->uuid,
            'original_key' => $photo->original_key,
            'unit_price_cents' => $event->price_cents,
            'commission_cents' => $event->price_cents - $allocationCents,
            'photographer_allocation_cents' => $allocationCents,
        ]);

        return $order;
    }

    private function pendingOrder(Event $event, Photographer $photographer, string $orderNumber): Order
    {
        $photo = $this->photoFor($event, $photographer);
        $commissionCents = (int) round($event->price_cents * 0.2);
        $order = Order::create([
            'order_number' => $orderNumber,
            'event_id' => $event->id,
            'currency' => 'usd',
            'photo_count' => 1,
            'unit_price_cents' => $event->price_cents,
            'subtotal_cents' => $event->price_cents,
            'commission_percentage' => 20,
            'total_cents' => $event->price_cents,
            'payment_status' => Order::PAYMENT_PENDING,
            'fulfillment_status' => Order::FULFILLMENT_PENDING,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'photo_id' => $photo->id,
            'photographer_id' => $photographer->id,
            'photo_uuid' => $photo->uuid,
            'original_key' => $photo->original_key,
            'unit_price_cents' => $event->price_cents,
            'commission_cents' => $commissionCents,
            'photographer_allocation_cents' => $event->price_cents - $commissionCents,
        ]);

        return $order;
    }
}
