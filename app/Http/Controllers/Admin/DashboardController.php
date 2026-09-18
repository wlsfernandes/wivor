<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Photo;
use App\Models\Photographer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Display event-scoped operational and sales reporting for administrators.
 */
class DashboardController extends Controller
{
    /**
     * Show the dashboard using only metrics currently persisted by the application.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
        ]);
        $selectedEvent = isset($filters['event_id'])
            ? Event::findOrFail($filters['event_id'])
            : null;
        $eventId = $selectedEvent?->id;

        $photosQuery = Photo::query()
            ->when($eventId, fn (Builder $query) => $query->where('event_id', $eventId));
        $paidOrdersQuery = Order::query()
            ->where('payment_status', Order::PAYMENT_PAID)
            ->when($eventId, fn (Builder $query) => $query->where('event_id', $eventId));
        $allOrdersQuery = Order::query()
            ->when($eventId, fn (Builder $query) => $query->where('event_id', $eventId));
        $soldItemsQuery = OrderItem::query()
            ->whereHas('order', function (Builder $query) use ($eventId): void {
                $query->where('payment_status', Order::PAYMENT_PAID)
                    ->when($eventId, fn (Builder $query) => $query->where('event_id', $eventId));
            });

        $orderCount = (clone $paidOrdersQuery)->count();
        $checkoutCount = (clone $allOrdersQuery)->count();
        $nonPurchasedCheckoutCount = (clone $allOrdersQuery)
            ->whereIn('payment_status', [Order::PAYMENT_PENDING, Order::PAYMENT_CANCELLED])
            ->count();

        return view('admin.dashboard.index', [
            'events' => Event::orderByDesc('date_of_event')->orderBy('title')->get(['id', 'title', 'date_of_event']),
            'selectedEvent' => $selectedEvent,
            'summary' => [
                'uploadedPhotos' => (clone $photosQuery)->whereNotNull('uploaded_at')->count(),
                'publishedPhotos' => (clone $photosQuery)->where('status', Photo::STATUS_PUBLISHED)->count(),
                'soldPhotos' => (clone $soldItemsQuery)->count(),
                'orders' => $orderCount,
                'uniqueBuyers' => (clone $paidOrdersQuery)->whereNotNull('customer_email')->distinct()->count('customer_email'),
                'gmvCents' => (int) (clone $paidOrdersQuery)->sum('subtotal_cents'),
                'checkoutCount' => $checkoutCount,
                'nonPurchasedCheckoutCount' => $nonPurchasedCheckoutCount,
                'checkoutConversion' => $checkoutCount > 0 ? round(($orderCount / $checkoutCount) * 100, 1) : null,
            ],
            'photographerRows' => $this->photographerRows($eventId, $selectedEvent),
            'salesTimeline' => $this->salesTimeline($paidOrdersQuery),
        ]);
    }

    /**
     * Build upload and paid-sale totals grouped by photographer.
     *
     * @return Collection<int, array<string, int|string>>
     */
    private function photographerRows(?int $eventId, ?Event $selectedEvent): Collection
    {
        $photoTotals = Photo::query()
            ->selectRaw('photographer_id, COUNT(*) as uploaded_photos')
            ->whereNotNull('uploaded_at')
            ->when($eventId, fn (Builder $query) => $query->where('event_id', $eventId))
            ->groupBy('photographer_id')
            ->get()
            ->keyBy('photographer_id');

        $publishedTotals = Photo::query()
            ->selectRaw('photographer_id, COUNT(*) as published_photos')
            ->where('status', Photo::STATUS_PUBLISHED)
            ->when($eventId, fn (Builder $query) => $query->where('event_id', $eventId))
            ->groupBy('photographer_id')
            ->get()
            ->keyBy('photographer_id');

        $salesTotals = OrderItem::query()
            ->selectRaw('order_items.photographer_id, COUNT(*) as sold_photos, SUM(order_items.unit_price_cents) as gmv_cents')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', Order::PAYMENT_PAID)
            ->when($eventId, fn (Builder $query) => $query->where('orders.event_id', $eventId))
            ->groupBy('order_items.photographer_id')
            ->get()
            ->keyBy('photographer_id');

        $photographerIds = $selectedEvent
            ? $selectedEvent->photographers()->pluck('photographers.id')
            : $photoTotals->keys()->merge($salesTotals->keys())->unique();

        return Photographer::whereIn('id', $photographerIds)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function (Photographer $photographer) use ($photoTotals, $publishedTotals, $salesTotals): array {
                return [
                    'name' => $photographer->full_name,
                    'uploadedPhotos' => (int) ($photoTotals->get($photographer->id)?->uploaded_photos ?? 0),
                    'publishedPhotos' => (int) ($publishedTotals->get($photographer->id)?->published_photos ?? 0),
                    'soldPhotos' => (int) ($salesTotals->get($photographer->id)?->sold_photos ?? 0),
                    'gmvCents' => (int) ($salesTotals->get($photographer->id)?->gmv_cents ?? 0),
                ];
            });
    }

    /**
     * Group paid sales by day for the dashboard chart.
     *
     * @return array{labels: list<string>, gmv: list<float>, orders: list<int>}
     */
    private function salesTimeline(Builder $paidOrdersQuery): array
    {
        $dailySales = (clone $paidOrdersQuery)
            ->orderBy('paid_at')
            ->get(['paid_at', 'created_at', 'subtotal_cents'])
            ->groupBy(fn (Order $order): string => ($order->paid_at ?? $order->created_at)->format('Y-m-d'));

        return [
            'labels' => $dailySales->keys()->values()->all(),
            'gmv' => $dailySales->map(fn (Collection $orders): float => round($orders->sum('subtotal_cents') / 100, 2))->values()->all(),
            'orders' => $dailySales->map(fn (Collection $orders): int => $orders->count())->values()->all(),
        ];
    }
}
