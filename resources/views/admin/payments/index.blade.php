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
        <div class="card-header bg-transparent">
            <h1 class="h4 mb-1">Photo orders</h1>
            <p class="text-muted mb-0">Use the local order and Stripe identifiers to reconcile payments in the Stripe Dashboard.</p>
        </div>
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Order</th>
                        <th scope="col">Event</th>
                        <th scope="col">Customer</th>
                        <th scope="col">Photos</th>
                        <th scope="col">Total</th>
                        <th scope="col">Status</th>
                        <th scope="col">Stripe Session</th>
                        <th scope="col">Payment Intent</th>
                        <th scope="col">Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->orderNumber }}</td>
                            <td>{{ $order->eventTitle }}</td>
                            <td class="text-break">{{ $order->customerEmail }}</td>
                            <td>{{ $order->photoCount }}</td>
                            <td>{{ $order->totalLabel }}</td>
                            <td>{{ $order->paymentStatusLabel }}</td>
                            <td class="text-break"><code>{{ $order->stripeSessionId }}</code></td>
                            <td class="text-break"><code>{{ $order->stripePaymentIntentId }}</code></td>
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
