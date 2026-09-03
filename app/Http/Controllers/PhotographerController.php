<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
            'payout_status' => ['nullable', Rule::in(['pending', 'not_applicable'])],
        ]);

        $salesQuery = $this->filteredSales($photographer, $filters);
        $sales = (clone $salesQuery)->with('event')->latest()->paginate(20)->withQueryString();

        return view('photographers.dashboard', [
            'photographerName' => $photographer->first_name,
            'payoutSetupComplete' => $photographer->stripe_onboarding_status === 'complete',
            'salesFilters' => $filters,
            'salesFilterEvents' => $photographer->events()->orderBy('events.title')->get(['events.id', 'events.title'])->pluck('title', 'id'),
            'salesSummary' => $this->salesSummary($salesQuery),
            'sales' => $sales,
        ]);
    }

    /** Build the photographer's own orders, scoped by the submitted sales-panel filters. */
    private function filteredSales(Photographer $photographer, array $filters): Builder
    {
        return Order::where('photographer_id', $photographer->id)
            ->when($filters['date_from'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when($filters['event_id'] ?? null, fn (Builder $query, $eventId) => $query->where('event_id', $eventId))
            ->when($filters['payment_status'] ?? null, fn (Builder $query, $status) => $query->where('payment_status', $status))
            ->when(($filters['payout_status'] ?? null) === 'pending', fn (Builder $query) => $query->where('payment_status', Order::PAYMENT_PAID))
            ->when(($filters['payout_status'] ?? null) === 'not_applicable', fn (Builder $query) => $query->where('payment_status', '!=', Order::PAYMENT_PAID));
    }

    /**
     * Summarize the photographer's paid sales as display-ready dollar amounts.
     *
     * Refunds and payout tracking are not yet implemented (no refund workflow and no
     * ingested Stripe Connect payout events), so those figures are fixed at $0.00 and
     * the full net amount is reported as pending payout.
     */
    private function salesSummary(Builder $salesQuery): array
    {
        $totals = (clone $salesQuery)->where('payment_status', Order::PAYMENT_PAID)
            ->selectRaw('
                COALESCE(SUM(subtotal_cents), 0) as gross_cents,
                COALESCE(SUM(commission_cents), 0) as commission_cents,
                COALESCE(SUM(stripe_fee_cents), 0) as fee_cents,
                COALESCE(SUM(photographer_allocation_cents), 0) as net_cents
            ')->first();

        return [
            'grossSalesLabel' => '$'.number_format($totals->gross_cents / 100, 2),
            'commissionLabel' => '$'.number_format($totals->commission_cents / 100, 2),
            'processingFeesLabel' => '$'.number_format($totals->fee_cents / 100, 2),
            'refundsLabel' => '$0.00',
            'netEarningsLabel' => '$'.number_format($totals->net_cents / 100, 2),
            'pendingPayoutLabel' => '$'.number_format($totals->net_cents / 100, 2),
            'paidAmountLabel' => '$0.00',
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
