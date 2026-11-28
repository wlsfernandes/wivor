<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Photo;
use App\Services\PhotoStorage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Displays the administrator's Stripe order reconciliation list. */
class PaymentController extends Controller
{
    /** Display recent photo orders with the photos sold and their local and Stripe identifiers. */
    public function index(PhotoStorage $storage): View
    {
        $orders = Order::query()
            ->with(['event', 'items.photo'])
            ->latest()
            ->paginate(30)
            ->through(fn (Order $order): object => (object) [
                'orderNumber' => $order->order_number,
                'soldPhotos' => $order->items->map(fn (OrderItem $item): object => $this->soldPhoto($item, $storage)),
                'eventTitle' => $order->event->title,
                'customerEmail' => $order->customer_email ?: 'Not captured',
                'photoCount' => $order->photo_count,
                'totalLabel' => '$'.number_format($order->total_cents / 100, 2).' '.strtoupper($order->currency),
                'paymentStatusLabel' => $order->payment_status_label,
                'stripeSessionId' => $order->stripe_checkout_session_id ?: 'Not created',
                'stripePaymentIntentId' => $order->stripe_payment_intent_id ?: 'Not available',
                'createdAtLabel' => $order->created_at->format('M j, Y g:i A'),
            ]);

        return view('admin.payments.index', ['orders' => $orders]);
    }

    /** Describe one sold photo by its reference number and, while its files still exist, a thumbnail. */
    private function soldPhoto(OrderItem $item, PhotoStorage $storage): object
    {
        $photo = $item->photo;
        // Removed photos keep their keys after the files are deleted, so they have no thumbnail to show.
        $hasThumbnail = $photo?->thumbnail_key && $photo->status !== Photo::STATUS_REMOVED;

        return (object) [
            // The item keeps the photo UUID, so the reference survives even when the photo row is gone.
            'referenceNumber' => $photo?->reference_number ?? Str::upper(substr(str_replace('-', '', $item->photo_uuid), 0, 8)),
            'thumbnailUrl' => $hasThumbnail ? $storage->deliveryUrl($photo->thumbnail_key) : null,
        ];
    }
}
