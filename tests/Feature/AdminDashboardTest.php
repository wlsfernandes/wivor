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
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_real_dashboard_metrics_by_event(): void
    {
        $admin = $this->adminUser();
        [$photographer, $event] = $this->eventWithPhotographer('River Run');
        $firstPhoto = $this->publishedPhoto($event, $photographer, 'river-one.jpg');
        $this->publishedPhoto($event, $photographer, 'river-two.jpg');
        $this->order($event, $photographer, $firstPhoto, Order::PAYMENT_PAID, 'buyer@example.com');
        $this->order($event, $photographer, $firstPhoto, Order::PAYMENT_PENDING);

        [$otherPhotographer, $otherEvent] = $this->eventWithPhotographer('Other Event');
        $otherPhoto = $this->publishedPhoto($otherEvent, $otherPhotographer, 'other.jpg');
        $this->order($otherEvent, $otherPhotographer, $otherPhoto, Order::PAYMENT_PAID, 'other@example.com');

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['event_id' => $event->id]));

        $response->assertOk()
            ->assertViewHas('selectedEvent', fn (Event $selectedEvent): bool => $selectedEvent->is($event))
            ->assertViewHas('summary', fn (array $summary): bool => $summary === [
                'uploadedPhotos' => 2,
                'publishedPhotos' => 2,
                'soldPhotos' => 1,
                'orders' => 1,
                'uniqueBuyers' => 1,
                'gmvCents' => 1000,
                'checkoutCount' => 2,
                'nonPurchasedCheckoutCount' => 1,
                'checkoutConversion' => 50.0,
            ])
            ->assertViewHas('photographerRows', function ($rows) use ($photographer): bool {
                return $rows->count() === 1
                    && $rows->first()['name'] === $photographer->full_name
                    && $rows->first()['uploadedPhotos'] === 2
                    && $rows->first()['soldPhotos'] === 1
                    && $rows->first()['gmvCents'] === 1000;
            })
            ->assertSee('River Run')
            ->assertSee('$10.00');
    }

    public function test_non_admin_cannot_access_the_event_report(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/index')
            ->assertForbidden();
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin']));

        return $admin;
    }

    /** @return array{Photographer, Event} */
    private function eventWithPhotographer(string $title): array
    {
        $photographer = Photographer::create([
            'user_id' => User::factory()->create()->id,
            'first_name' => $title,
            'last_name' => 'Photographer',
        ]);
        $event = Event::create([
            'title' => $title,
            'slug' => Event::generateUniqueSlug($title),
            'sport' => 'Running',
            'content' => 'Event report fixture.',
            'status' => Event::STATUS_PUBLISHED,
            'published' => true,
            'published_at' => now()->subDay(),
            'date_of_event' => now()->toDateString(),
            'sales_close_at' => now()->addMonth(),
            'price_cents' => 1000,
            'timezone' => 'America/New_York',
            'country_code' => 'US',
        ]);
        EventAssignment::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'status' => 'approved',
            'upload_deadline_at' => now()->addDays(3),
        ]);

        return [$photographer, $event];
    }

    private function publishedPhoto(Event $event, Photographer $photographer, string $filename): Photo
    {
        $assignment = EventAssignment::where('event_id', $event->id)
            ->where('photographer_id', $photographer->id)
            ->firstOrFail();
        $batch = UploadBatch::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'assignment_id' => $assignment->id,
            'selected_count' => 1,
            'uploaded_count' => 1,
            'published_count' => 1,
            'status' => 'completed',
        ]);

        return Photo::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'assignment_id' => $assignment->id,
            'upload_batch_id' => $batch->id,
            'original_filename' => $filename,
            'original_key' => "photos/{$filename}",
            'checksum' => hash('sha256', $event->id.$filename),
            'status' => Photo::STATUS_PUBLISHED,
            'uploaded_at' => now()->subDay(),
            'published_at' => now()->subHours(12),
        ]);
    }

    private function order(
        Event $event,
        Photographer $photographer,
        Photo $photo,
        string $paymentStatus,
        ?string $customerEmail = null
    ): Order {
        $isPaid = $paymentStatus === Order::PAYMENT_PAID;
        $order = Order::create([
            'event_id' => $event->id,
            'customer_email' => $customerEmail,
            'currency' => 'usd',
            'photo_count' => 1,
            'unit_price_cents' => 1000,
            'subtotal_cents' => 1000,
            'commission_percentage' => 20,
            'total_cents' => 1000,
            'payment_status' => $paymentStatus,
            'fulfillment_status' => $isPaid ? Order::FULFILLMENT_READY : Order::FULFILLMENT_PENDING,
            'paid_at' => $isPaid ? now() : null,
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

        return $order;
    }
}