@extends($layout)

@section('title', 'Confirming Your Payment | WivorPhotos')

@section('content')
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 text-center">
                @if ($confirmationUnavailable)
                    <div class="alert alert-warning" role="alert">Stripe confirmation is temporarily unavailable. Your payment status will still be updated securely, and your download link will be emailed after confirmation.</div>
                @endif
                @if ($isPaid)
                    <h1 class="h3">Thank you for your order!</h1>
                    <p class="text-muted">Order {{ $order->order_number }} is paid. A receipt and download link have been
                        sent to your email.</p>
                    <a class="btn btn-primary" href="{{ $orderUrl }}">View and download photos</a>
                @else
                    <h1 class="h3">Confirming your payment</h1>
                    <p class="text-muted">We are waiting for Stripe to confirm your payment for order
                        {{ $order->order_number }}. Your photos unlock only after secure confirmation.</p>
                    <a class="btn btn-primary" href="{{ $refreshUrl }}">Refresh payment status</a>
                @endif
                <a class="btn btn-outline-secondary" href="{{ route('events.listEvents') }}">Back to events</a>
            </div>
        </div>
    </main>
@endsection
