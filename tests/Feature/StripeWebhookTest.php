<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\UploadBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const WEBHOOK_SECRET = 'whsec_test_secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.stripe.webhook_secret' => self::WEBHOOK_SECRET]);
    }

    public function test_paid_checkout_session_fulfills_the_order_and_protects_the_photo(): void
    {
        [$order, $photo] = $this->pendingOrder();

        $this->postWebhook($this->checkoutCompletedPayload($order->stripe_checkout_session_id))
            ->assertOk();

        $order->refresh();
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertSame(Order::FULFILLMENT_READY, $order->fulfillment_status);
        $this->assertSame('buyer@example.com', $order->customer_email);
        $this->assertSame('pi_123', $order->stripe_payment_intent_id);
        $this->assertNotNull($order->paid_at);
        $this->assertNotNull($order->download_expires_at);
        $this->assertEqualsWithDelta(
            $order->paid_at->addDays((int) config('photo_uploads.sold_original_days'))->timestamp,
            $order->download_expires_at->timestamp,
            2
        );

        $item = $order->items()->firstOrFail();
        $this->assertSame(OrderItem::DOWNLOAD_READY, $item->download_status);
        $this->assertNotNull($item->download_expires_at);

        $photo->refresh();
        $this->assertSame(1, $photo->sale_count);
        $this->assertNotNull($photo->most_recent_purchase_at);
    }

    public function test_duplicate_delivery_of_the_same_event_does_not_fulfill_twice(): void
    {
        [$order, $photo] = $this->pendingOrder();
        $payload = $this->checkoutCompletedPayload($order->stripe_checkout_session_id);

        $this->postWebhook($payload)->assertOk();
        $this->postWebhook($payload)->assertOk();

        $photo->refresh();
        $this->assertSame(1, $photo->sale_count);
    }

    public function test_invalid_signature_is_rejected_and_leaves_the_order_pending(): void
    {
        [$order] = $this->pendingOrder();
        $payload = $this->checkoutCompletedPayload($order->stripe_checkout_session_id);
        $body = json_encode($payload);

        $this->call('POST', route('stripe.webhook'), [], [], [], [
            'HTTP_Stripe-Signature' => 't='.time().',v1=deadbeef',
            'CONTENT_TYPE' => 'application/json',
        ], $body)->assertStatus(400);

        $order->refresh();
        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
    }

    public function test_unknown_checkout_session_is_acknowledged_without_error(): void
    {
        $this->postWebhook($this->checkoutCompletedPayload('cs_does_not_exist'))->assertOk();
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
        $photographer->forceFill(['status' => Photographer::STATUS_APPROVED])->save();

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
            'photographer_id' => $photographer->id,
            'currency' => 'usd',
            'photo_count' => 1,
            'unit_price_cents' => 1000,
            'subtotal_cents' => 1000,
            'commission_percentage' => 20,
            'commission_cents' => 200,
            'photographer_allocation_cents' => 800,
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
