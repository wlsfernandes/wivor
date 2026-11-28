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
                                @if ($order->soldPhotos->isNotEmpty())
                                    <button type="button" class="btn btn-sm btn-outline-secondary text-nowrap"
                                        data-bs-toggle="modal" data-bs-target="#sold-photos-modal"
                                        data-order-number="{{ $order->orderNumber }}"
                                        aria-label="View {{ $order->soldPhotos->count() }} {{ Str::plural('photo', $order->soldPhotos->count()) }} sold in order {{ $order->orderNumber }}">
                                        <i class="fa fa-images" aria-hidden="true"></i> {{ $order->soldPhotos->count() }}
                                    </button>
                                    <template id="sold-photos-{{ $order->orderNumber }}">
                                        <p class="text-muted mb-3">{{ $order->eventTitle }}</p>
                                        <div class="row row-cols-2 row-cols-sm-3 g-3">
                                            @foreach ($order->soldPhotos as $soldPhoto)
                                                <figure class="col mb-0">
                                                    @if ($soldPhoto->thumbnailUrl)
                                                        <a href="{{ $soldPhoto->thumbnailUrl }}" target="_blank" rel="noopener" class="d-block">
                                                            <img src="{{ $soldPhoto->thumbnailUrl }}" alt="Photo {{ $soldPhoto->referenceNumber }}"
                                                                class="w-100 rounded border" style="aspect-ratio: 1; object-fit: cover;">
                                                        </a>
                                                    @else
                                                        <div class="w-100 rounded border bg-light text-muted small d-flex align-items-center justify-content-center text-center p-2" style="aspect-ratio: 1;">
                                                            No preview. The photo files were deleted.
                                                        </div>
                                                    @endif
                                                    <figcaption class="mt-1"><code>{{ $soldPhoto->referenceNumber }}</code></figcaption>
                                                </figure>
                                            @endforeach
                                        </div>
                                    </template>
                                @else
                                    <span class="text-muted small">None</span>
                                @endif
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

    <div class="modal fade" id="sold-photos-modal" tabindex="-1" aria-labelledby="sold-photos-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="sold-photos-modal-title">Photos sold</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body"></div>
            </div>
        </div>
    </div>
@endsection

@section('script-bottom')
    <script>
        // Each order's photo grid waits in an inert <template>, so its images load only when the admin opens it.
        document.getElementById('sold-photos-modal').addEventListener('show.bs.modal', (event) => {
            const orderNumber = event.relatedTarget.dataset.orderNumber;
            const modal = event.currentTarget;
            modal.querySelector('.modal-title').textContent = `Photos sold in ${orderNumber}`;
            modal.querySelector('.modal-body').replaceChildren(
                document.getElementById(`sold-photos-${orderNumber}`).content.cloneNode(true)
            );
        });
    </script>
@endsection
