<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
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

    /** Revalidate the selection, freeze pricing, and create the pending order and its immutable, per-photographer items. */
    public function createPendingOrder(Event $event, Collection $photos): Order
    {
        if ($photos->isEmpty() || ! $event->isSellable()) {
            throw ValidationException::withMessages(['cart' => 'Your selection is no longer available for purchase.']);
        }

        if ($photos->contains(fn ($photo) => ! $photo->photographer?->isReadyForPayouts())) {
            throw ValidationException::withMessages(['cart' => 'One of the selected photographers is not yet ready to receive payments. Please remove that photo and try again.']);
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
     * Funds are collected into the platform account; because one order can include
     * multiple photographers, payouts are issued as separate Transfers per photographer
     * after the webhook confirms payment (see StripeWebhookController), not as a single
     * destination charge here.
     */
    public function createCheckoutSession(Order $order): object
    {
        $session = $this->stripe->checkout->sessions->create([
            'mode' => 'payment',
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
            'payment_intent_data' => [
                'transfer_group' => $order->order_number,
            ],
            'success_url' => route('checkout.success', ['order' => $order->order_number]).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel', ['order' => $order->order_number]),
            'metadata' => [
                'wivor_order_id' => (string) $order->id,
                'wivor_order_number' => $order->order_number,
            ],
        ]);

        $order->update(['stripe_checkout_session_id' => $session->id]);

        return $session;
    }
}

