<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Photo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Stripe\StripeClient;

/** Builds a pending Wivor order and its Stripe-hosted Checkout Session. */
class CheckoutService
{
    public function __construct(private readonly StripeClient $stripe)
    {
    }

    /** Revalidate the selection, freeze pricing, and create the pending order and its immutable items. */
    public function createPendingOrder(Event $event, Collection $photos): Order
    {
        if ($photos->isEmpty() || ! $event->isSellable()) {
            throw ValidationException::withMessages(['cart' => 'Your selection is no longer available for purchase.']);
        }

        if ($photos->contains(fn (Photo $photo) => $photo->event_id !== $event->id
            || $photo->status !== Photo::STATUS_PUBLISHED
            || blank($photo->original_key)
            || ! $photo->photographer_id)) {
            throw ValidationException::withMessages(['cart' => 'Your selection is no longer available for purchase.']);
        }

        $unitPriceCents = $event->price_cents;
        $photoCount = $photos->count();
        $subtotalCents = $unitPriceCents * $photoCount;
        $commissionPercentage = (float) config('commission.percentage');
        $itemCommissionCents = (int) round($unitPriceCents * $commissionPercentage / 100);
        $itemAllocationCents = $unitPriceCents - $itemCommissionCents;

        return DB::transaction(function () use (
            $event, $photos, $unitPriceCents, $photoCount,
            $subtotalCents, $commissionPercentage, $itemCommissionCents, $itemAllocationCents
        ): Order {
            $order = Order::create([
                'event_id' => $event->id,
                'currency' => 'usd',
                'photo_count' => $photoCount,
                'unit_price_cents' => $unitPriceCents,
                'subtotal_cents' => $subtotalCents,
                'commission_percentage' => $commissionPercentage,
                'total_cents' => $subtotalCents,
                'payment_status' => Order::PAYMENT_PENDING,
                'fulfillment_status' => Order::FULFILLMENT_PENDING,
            ]);

            foreach ($photos as $photo) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'photo_id' => $photo->id,
                    'photographer_id' => $photo->photographer_id,
                    'photo_uuid' => $photo->uuid,
                    'original_key' => $photo->original_key,
                    'unit_price_cents' => $unitPriceCents,
                    'commission_cents' => $itemCommissionCents,
                    'photographer_allocation_cents' => $itemAllocationCents,
                ]);
            }

            return $order;
        });
    }

    /**
     * Create the Stripe-hosted Checkout Session for the whole order.
     *
     * Funds are collected into the Wivor platform account. Photographer allocations are
     * retained in the order ledger for the MVP's manual payout process.
     */
    public function createCheckoutSession(Order $order): object
    {
        $session = $this->stripe->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $order->currency,
                    'product_data' => [
                        'name' => "High-resolution event photos — {$order->event->title}",
                    ],
                    'unit_amount' => $order->unit_price_cents,
                ],
                'quantity' => $order->photo_count,
            ]],
            'success_url' => route('checkout.success', ['order' => $order->order_number]).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel', ['order' => $order->order_number]),
            'metadata' => [
                'wivor_order_id' => (string) $order->id,
                'wivor_order_number' => $order->order_number,
            ],
        ], [
            'idempotency_key' => "wivor-checkout-{$order->order_number}",
        ]);

        DB::transaction(fn () => $order->update(['stripe_checkout_session_id' => $session->id]));

        return $session;
    }

    /** Retrieve and verify the Stripe Session used to return to a local order. */
    public function retrieveCheckoutSession(Order $order, string $sessionId): object
    {
        if (! $order->stripe_checkout_session_id
            || ! hash_equals($order->stripe_checkout_session_id, $sessionId)) {
            throw ValidationException::withMessages(['checkout' => 'The checkout confirmation is invalid.']);
        }

        $session = $this->stripe->checkout->sessions->retrieve($sessionId, []);
        $metadataOrderId = (string) ($session->metadata->wivor_order_id ?? '');
        $metadataOrderNumber = (string) ($session->metadata->wivor_order_number ?? '');

        if ((string) $order->id !== $metadataOrderId
            || $order->order_number !== $metadataOrderNumber
            || $order->currency !== ($session->currency ?? null)
            || $order->total_cents !== ($session->amount_total ?? null)) {
            throw ValidationException::withMessages(['checkout' => 'The checkout confirmation does not match this order.']);
        }

        return $session;
    }
}
