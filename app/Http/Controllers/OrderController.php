<?php

namespace App\Http\Controllers;

use App\Models\MediaActivityLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Photo;
use App\Services\PhotoStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Displays the guest secure order page and issues short-lived original download URLs. */
class OrderController extends Controller
{
    public function __construct(private readonly PhotoStorage $storage)
    {
    }

    /** Display the secure order and download page for a valid, unguessable access token. */
    public function show(string $accessToken): View
    {
        $order = Order::where('access_token', $accessToken)->with(['event', 'items.photo'])->firstOrFail();

        $thumbnailUrls = $order->items->mapWithKeys(fn (OrderItem $item) => [
            $item->id => $item->photo?->thumbnail_key ? $this->storage->deliveryUrl($item->photo->thumbnail_key) : null,
        ]);
        $downloadableItemIds = $order->items
            ->filter(fn (OrderItem $item) => $item->download_status === OrderItem::DOWNLOAD_READY && ! $item->download_expires_at?->isPast())
            ->pluck('id');

        return view('orders.show', [
            'order' => $order,
            'isPaid' => $order->payment_status === Order::PAYMENT_PAID,
            'thumbnailUrls' => $thumbnailUrls,
            'downloadableItemIds' => $downloadableItemIds,
            'layout' => 'layouts.app',
        ]);
    }

    /** Verify the download entitlement and redirect to a short-lived signed original URL. */
    public function download(string $accessToken, Photo $photo): RedirectResponse
    {
        $order = Order::where('access_token', $accessToken)->firstOrFail();
        $item = $order->items()->where('photo_uuid', $photo->uuid)->firstOrFail();

        abort_unless(
            $order->payment_status === Order::PAYMENT_PAID
                && $item->download_status === OrderItem::DOWNLOAD_READY
                && ! $item->download_expires_at?->isPast(),
            403,
            'This download link is no longer available.'
        );

        MediaActivityLog::create([
            'event_id' => $order->event_id,
            'photo_id' => $item->photo_id,
            'action' => 'order_download_issued',
            'details' => ['order_number' => $order->order_number],
        ]);

        return redirect()->away($this->storage->deliveryUrl($item->original_key));
    }
}
