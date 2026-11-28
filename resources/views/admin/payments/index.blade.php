@extends('layouts.master')

@section('title', 'Photo Orders | WivorPhotos')

@section('content')
    @component('common-components.breadcrumb')
        @slot('pagetitle')
            Payments
        @endslot
        @slot('title')
            Photo orders
        @endslot
    @endcomponent

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h1 class="h4 mb-1">Photo orders</h1>
                <p class="text-muted mb-0">Use the local order and Stripe identifiers to reconcile payments in the Stripe Dashboard.</p>
            </div>
            <a class="btn btn-outline-primary flex-shrink-0" href="{{ route('payments.manual') }}">
                <i class="fa fa-book-open me-1" aria-hidden="true"></i> Payment Process Manual
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Photos sold</th>
                        <th scope="col">Order</th>
                        <th scope="col">Event</th>
                        <th scope="col">Customer</th>
                        <th scope="col">Photos</th>
                        <th scope="col">Total</th>
                        <th scope="col">Status</th>
                        <th scope="col">Stripe Session / Payment Intent</th>
                        <th scope="col">Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    @forelse ($order->soldPhotos as $soldPhoto)
                                        <div class="text-center">
                                            @if ($soldPhoto->thumbnailUrl)
                                                <a href="{{ $soldPhoto->thumbnailUrl }}" target="_blank" rel="noopener">
                                                    <img src="{{ $soldPhoto->thumbnailUrl }}" alt="Photo {{ $soldPhoto->referenceNumber }}" loading="lazy"
                                                        style="width: 48px; height: 48px; object-fit: cover;" class="rounded">
                                                </a>
                                            @else
                                                <span class="d-block text-muted small">No preview</span>
                                            @endif
                                            <code class="small">{{ $soldPhoto->referenceNumber }}</code>
                                        </div>
                                    @empty
                                        <span class="text-muted small">No photos</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>{{ $order->orderNumber }}</td>
                            <td>{{ $order->eventTitle }}</td>
                            <td class="text-break">{{ $order->customerEmail }}</td>
                            <td>{{ $order->photoCount }}</td>
                            <td>{{ $order->totalLabel }}</td>
                            <td>{{ $order->paymentStatusLabel }}</td>
                            <td class="text-break">
                                <code class="d-block">{{ $order->stripeSessionId }}</code>
                                <code class="d-block">{{ $order->stripePaymentIntentId }}</code>
                            </td>
                            <td>{{ $order->createdAtLabel }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No photo orders have been created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $orders->links() }}</div>
@endsection
