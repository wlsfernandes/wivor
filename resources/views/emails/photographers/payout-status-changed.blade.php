<!doctype html>
<html lang="en">
<body>
    @if ($status === \App\Models\Photographer::STRIPE_READY)
        <p>Your payout setup is complete. You can now publish photos for sale and receive earnings through Stripe.</p>
        <p><a href="{{ route('photographer.dashboard') }}">Open your photographer dashboard</a></p>
    @else
        <p>Stripe needs additional information to keep your WivorPhotos payouts active.</p>
        <p><a href="{{ route('photographer.dashboard') }}">Review Payout Setup</a></p>
    @endif
</body>
</html>
