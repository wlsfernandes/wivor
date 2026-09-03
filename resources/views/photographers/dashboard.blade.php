@extends('layouts.app-sidebar')

@section('title', 'Photographer Dashboard | WivorPhotos')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <p class="text-uppercase text-muted mb-1">Photographer dashboard</p>
                <h1 class="h3 mb-0">Welcome, {{ $photographerName }}</h1>
            </div>
        </div>

        @if (!$payoutSetupComplete)
            <div class="alert alert-warning" role="alert">
                <h2 class="h5 alert-heading">Complete payout setup</h2>
                <p class="mb-0">Stripe onboarding must be complete before WivorPhotos can send payouts. Payout setup will
                    be enabled with the marketplace payment workflow.</p>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">Your account is approved</h2>
                <p class="text-muted mb-0">Available events, assignments, and uploads will appear here as the remaining MVP
                    workflows are enabled.</p>
            </div>
        </div>

        <h2 class="h4">Sales</h2>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Gross sales</p>
                        <p class="h5 mb-0">{{ $salesSummary['grossSalesLabel'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">WivorPhotos commission</p>
                        <p class="h5 mb-0">{{ $salesSummary['commissionLabel'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Payment-processing fees</p>
                        <p class="h5 mb-0">{{ $salesSummary['processingFeesLabel'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Refunds</p>
                        <p class="h5 mb-0">{{ $salesSummary['refundsLabel'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Net photographer earnings</p>
                        <p class="h5 mb-0">{{ $salesSummary['netEarningsLabel'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Pending payout</p>
                        <p class="h5 mb-0">{{ $salesSummary['pendingPayoutLabel'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Paid amount</p>
                        <p class="h5 mb-0">{{ $salesSummary['paidAmountLabel'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <form class="card card-body mb-4" method="GET" action="{{ route('photographer.dashboard') }}">
            <div class="row g-3 align-items-end">
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label" for="date_from">From</label>
                    <input class="form-control" id="date_from" name="date_from" type="date"
                        value="{{ $salesFilters['date_from'] ?? '' }}">
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label" for="date_to">To</label>
                    <input class="form-control" id="date_to" name="date_to" type="date"
                        value="{{ $salesFilters['date_to'] ?? '' }}">
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label" for="event_id">Event</label>
                    <select class="form-select" id="event_id" name="event_id">
                        <option value="">All events</option>
                        @foreach ($salesFilterEvents as $eventId => $eventTitle)
                            <option value="{{ $eventId }}" @selected(($salesFilters['event_id'] ?? null) == $eventId)>{{ $eventTitle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label" for="payment_status">Payment status</label>
                    <select class="form-select" id="payment_status" name="payment_status">
                        <option value="">All</option>
                        <option value="pending" @selected(($salesFilters['payment_status'] ?? '') === 'pending')>Pending</option>
                        <option value="paid" @selected(($salesFilters['payment_status'] ?? '') === 'paid')>Paid</option>
                        <option value="cancelled_expired" @selected(($salesFilters['payment_status'] ?? '') === 'cancelled_expired')>Cancelled/expired</option>
                    </select>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label" for="payout_status">Payout status</label>
                    <select class="form-select" id="payout_status" name="payout_status">
                        <option value="">All</option>
                        <option value="paid" @selected(($salesFilters['payout_status'] ?? '') === 'paid')>Paid out</option>
                        <option value="pending" @selected(($salesFilters['payout_status'] ?? '') === 'pending')>Pending payout</option>
                        <option value="not_applicable" @selected(($salesFilters['payout_status'] ?? '') === 'not_applicable')>Not applicable</option>
                    </select>
                </div>
                <div class="col-lg-1">
                    <button class="btn btn-primary w-100" type="submit">Filter</button>
                </div>
            </div>
        </form>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Sale date</th>
                            <th>Order number</th>
                            <th>Event</th>
                            <th>Photos</th>
                            <th>Gross</th>
                            <th>Fees</th>
                            <th>Net</th>
                            <th>Payment status</th>
                            <th>Payout status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr>
                                <td>{{ $sale->sale_date_label }}</td>
                                <td>{{ $sale->order_number }}</td>
                                <td>{{ $sale->event->title }}</td>
                                <td>{{ $sale->photo_count }}</td>
                                <td>{{ $sale->gross_amount_label }}</td>
                                <td>{{ $sale->fees_label }}</td>
                                <td>{{ $sale->net_amount_label }}</td>
                                <td>{{ $sale->payment_status_label }}</td>
                                <td>{{ $sale->payout_status_label }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-muted text-center py-4">No sales match these filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $sales->links() }}</div>
    </div>
@endsection
