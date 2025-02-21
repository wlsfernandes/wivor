<!DOCTYPE html>
<html>

<head>
    <title>Welcome to the WiVor</title>
</head>

<body>
    <h1>Welcome to AETH, {{ $user->name }}!</h1>
    <p>Your account has been created successfully.</p>
    <p>Your login credentials are:</p>
    <ul>
        <li>Email: {{ $user->email }}</li>
        <li>Password: {{ $password }}</li>
    </ul>
    <p>Please change your password after logging in for the first time.</p>
    <p>Thank you for joining us!</p>
</body>

</html>
