@extends($layout)

@section('title', 'Your Order | WivorPhotos')

@section('content')
    <main class="container py-5">
        <header class="mb-4">
            <h1>Order {{ $order->order_number }}</h1>
            <p class="text-muted mb-0">{{ $order->event->title }}</p>
            @if ($order->paid_at)
                <p class="text-muted mb-0">Paid on {{ $order->paid_at->format('F j, Y') }}</p>
            @endif
            <p class="text-muted mb-0">Status: {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}</p>
        </header>

        @if (! $isPaid)
            <div class="alert alert-info" role="status">Your payment has not been confirmed yet. This page will show your downloads once it is.</div>
        @else
            @if ($order->download_expires_at)
                <p class="text-muted">Downloads are available until {{ $order->download_expires_at->format('F j, Y') }}.</p>
            @endif

            <div class="row g-3">
                @foreach ($order->items as $item)
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            @if ($thumbnailUrls[$item->id] ?? null)
                                <img class="card-img-top" style="aspect-ratio: 1 / 1; object-fit: cover;" src="{{ $thumbnailUrls[$item->id] }}" alt="Purchased photo">
                            @endif
                            <div class="card-body p-2 text-center">
                                @if ($downloadableItemIds->contains($item->id))
                                    <a class="btn btn-sm btn-primary w-100" href="{{ route('orders.download', ['accessToken' => $order->access_token, 'photo' => $item->photo_uuid]) }}">Download original</a>
                                @else
                                    <span class="text-muted small">Not available</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>
@endsection
