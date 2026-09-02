<!doctype html>
<html lang="en">
<body>
    <h1>Welcome to WivorPhotos</h1>
    <p>Hello {{ $user->name }},</p>
    <p>Your photographer application has been approved.</p>
    <p><a href="{{ route('photographer.dashboard') }}">Open your photographer dashboard</a></p>
    <p>For your security, WivorPhotos will never send your password by email.</p>
</body>
</html>
