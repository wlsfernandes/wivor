<!doctype html>
<html lang="en">
<body>
    <h1>Thank you for your order</h1>
    <p>Order {{ $order->order_number }} for {{ $order->event->title }} is confirmed.</p>
    <p>Purchase total: ${{ number_format($order->total_cents / 100, 2) }} ({{ $order->photo_count }} photo(s))</p>
    <p><a href="{{ $orderUrl }}">View and Download Photos</a></p>
    <p>Downloads are available until {{ $order->download_expires_at?->format('F j, Y') }}.</p>
    <p>Questions? <a href="{{ route('contact_us') }}">Contact WivorPhotos support</a>.</p>
</body>
</html>
