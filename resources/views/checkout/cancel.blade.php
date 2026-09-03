@extends($layout)

@section('title', 'Payment Not Completed | WivorPhotos')

@section('content')
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 text-center">
                <h1 class="h3">Payment not completed</h1>
                <p class="text-muted">Your payment for order {{ $order->order_number }} was not completed, and no photos have
                    been unlocked. You can return to the event and try again whenever you are ready.</p>
                <a class="btn btn-outline-secondary"
                    href="{{ route('events.show', ['event' => $order->event->slug]) }}">Return to event</a>
            </div>
        </div>
    </main>
@endsection
