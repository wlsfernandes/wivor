<!doctype html>
<html lang="en">
<body>
    <h1>Welcome to WivorPhotos</h1>
    <p>Hello {{ $user->name }},</p>
    <p>Your WivorPhotos photographer profile is approved. Complete secure Stripe payout setup before publishing photos for sale.</p>
    <p><a href="{{ route('photographer.dashboard') }}">Complete Payout Setup</a></p>
    <p>For your security, WivorPhotos will never send your password by email.</p>
</body>
</html>
