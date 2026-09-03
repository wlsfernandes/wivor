<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Photographer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Handles public and authenticated photographer pages retained by existing routes. */
class PhotographerController extends Controller
{
    /** Redirect photographers to the public event directory. */
    public function allEvents(): RedirectResponse
    {
        return redirect()->route('events.listEvents');
    }

    /** Redirect photographers to their managed events. */
    public function myEvents(): RedirectResponse
    {
        return redirect()->route('events.index');
    }

    /** Redirect photographers to the existing event creation page. */
    public function newEvent(): RedirectResponse
    {
        return redirect()->route('events.create');
    }

    /** Display the existing public photographer application page. */
    public function photographers(): View
    {
        return view('photographers.page');
    }

    /** Display the approved photographer dashboard with a filterable sales panel. */
    public function dashboard(Request $request): View
    {
        $photographer = $request->user()->photographer;

        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'event_id' => ['nullable', 'integer'],
            'payment_status' => ['nullable', Rule::in([Order::PAYMENT_PENDING, Order::PAYMENT_PAID, Order::PAYMENT_CANCELLED])],
            'payout_status' => ['nullable', Rule::in(['paid', 'pending', 'not_applicable'])],
        ]);

        $itemsQuery = $this->filteredItems($photographer, $filters);

        $sales = Order::whereIn('id', (clone $itemsQuery)->select('order_id')->distinct())
            ->with(['event', 'items' => fn ($query) => $query->where('photographer_id', $photographer->id)])
            ->latest()->paginate(20)->withQueryString()
            ->through(fn (Order $order) => $this->salesRow($order));

        return view('photographers.dashboard', [
            'photographerName' => $photographer->first_name,
            'payoutSetupComplete' => $photographer->stripe_onboarding_status === 'complete',
            'salesFilters' => $filters,
            'salesFilterEvents' => $photographer->events()->orderBy('events.title')->get(['events.id', 'events.title'])->pluck('title', 'id'),
            'salesSummary' => $this->salesSummary($itemsQuery),
            'sales' => $sales,
        ]);
    }

    /** Build the photographer's own order items, scoped by the submitted sales-panel filters. */
    private function filteredItems(Photographer $photographer, array $filters): Builder
    {
        return OrderItem::where('order_items.photographer_id', $photographer->id)
            ->whereHas('order', function (Builder $query) use ($filters): void {
                $query->when($filters['date_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                    ->when($filters['date_to'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date))
                    ->when($filters['event_id'] ?? null, fn (Builder $q, $eventId) => $q->where('event_id', $eventId))
                    ->when($filters['payment_status'] ?? null, fn (Builder $q, $status) => $q->where('payment_status', $status));
            })
            ->when(($filters['payout_status'] ?? null) === 'paid', fn (Builder $query) => $query->whereNotNull('stripe_transfer_id'))
            ->when(($filters['payout_status'] ?? null) === 'pending', fn (Builder $query) => $query->whereNull('stripe_transfer_id')
                ->whereHas('order', fn (Builder $q) => $q->where('payment_status', Order::PAYMENT_PAID)))
            ->when(($filters['payout_status'] ?? null) === 'not_applicable', fn (Builder $query) => $query
                ->whereHas('order', fn (Builder $q) => $q->where('payment_status', '!=', Order::PAYMENT_PAID)));
    }

    /** Reduce one order to only this photographer's items as display-ready values. */
    private function salesRow(Order $order): object
    {
        $items = $order->items;
        $isPaid = $order->payment_status === Order::PAYMENT_PAID;
        $allTransferred = $isPaid && $items->isNotEmpty() && $items->every(fn (OrderItem $item) => $item->stripe_transfer_id !== null);

        return (object) [
            'order_number' => $order->order_number,
            'event' => $order->event,
            'sale_date_label' => $order->sale_date_label,
            'photo_count' => $items->count(),
            'gross_amount_label' => '$'.number_format($items->sum('unit_price_cents') / 100, 2),
            'fees_label' => '$'.number_format($items->sum('commission_cents') / 100, 2),
            'net_amount_label' => '$'.number_format($items->sum('photographer_allocation_cents') / 100, 2),
            'payment_status_label' => $order->payment_status_label,
            'payout_status_label' => ! $isPaid ? 'Not applicable' : ($allTransferred ? 'Paid out' : 'Pending payout'),
        ];
    }

    /**
     * Summarize the photographer's own paid items as display-ready dollar amounts.
     *
     * Refunds are not implemented for this MVP (no refund workflow), so that figure is
     * fixed at $0.00. Processing fees are charged to WivorPhotos' commission, not the
     * photographer, and are not attributed per photographer here.
     */
    private function salesSummary(Builder $itemsQuery): array
    {
        $paidItems = (clone $itemsQuery)->whereHas('order', fn (Builder $q) => $q->where('payment_status', Order::PAYMENT_PAID));

        $totals = (clone $paidItems)->selectRaw('
                COALESCE(SUM(unit_price_cents), 0) as gross_cents,
                COALESCE(SUM(commission_cents), 0) as commission_cents,
                COALESCE(SUM(photographer_allocation_cents), 0) as net_cents
            ')->first();
        $paidOutCents = (clone $paidItems)->whereNotNull('stripe_transfer_id')->sum('photographer_allocation_cents');

        return [
            'grossSalesLabel' => '$'.number_format($totals->gross_cents / 100, 2),
            'commissionLabel' => '$'.number_format($totals->commission_cents / 100, 2),
            'processingFeesLabel' => '$0.00',
            'refundsLabel' => '$0.00',
            'netEarningsLabel' => '$'.number_format($totals->net_cents / 100, 2),
            'pendingPayoutLabel' => '$'.number_format(($totals->net_cents - $paidOutCents) / 100, 2),
            'paidAmountLabel' => '$'.number_format($paidOutCents / 100, 2),
        ];
    }

    /** Display the authenticated photographer's application state. */
    public function applicationStatus(Request $request): View
    {
        $photographer = $request->user()->photographer;
        $isEmailVerified = $request->user()->hasVerifiedEmail();
        $statusMessage = match (true) {
            ! $isEmailVerified => 'Verify your email address before your application can be activated.',
            $photographer->status === Photographer::STATUS_PENDING => 'Your email is verified and your application is under review. We will email you when a decision is ready.',
            $photographer->status === Photographer::STATUS_DECLINED => 'We are unable to approve your photographer application at this time. Contact support if you have questions.',
            $photographer->status === Photographer::STATUS_SUSPENDED => 'Your photographer access is currently suspended. Contact support if you have questions.',
            default => 'Your account is ready.',
        };

        return view('photographers.application-status', [
            'statusLabel' => $photographer->statusLabel(),
            'statusMessage' => $statusMessage,
            'showVerificationResend' => ! $isEmailVerified,
            'canOpenDashboard' => $request->user()->canAccessPhotographerArea(),
        ]);
    }
}
