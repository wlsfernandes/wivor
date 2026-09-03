@extends($layout)

@section('title', 'Confirming Your Payment | WivorPhotos')

@section('content')
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 text-center">
                @if ($isPaid)
                    <h1 class="h3">Thank you for your order!</h1>
                    <p class="text-muted">Order {{ $order->order_number }} is paid. A receipt and download link have been
                        sent to your email.</p>
                @else
                    <h1 class="h3">Confirming your payment</h1>
                    <p class="text-muted">We are waiting for Stripe to confirm your payment for order
                        {{ $order->order_number }}. This page will not unlock your photos automatically — check your email
                        shortly for your secure download link.</p>
                @endif
                <a class="btn btn-outline-secondary" href="{{ route('events.listEvents') }}">Back to events</a>
            </div>
        </div>
    </main>
@endsection
