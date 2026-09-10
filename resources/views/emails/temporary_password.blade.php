<x-email.layout title="Welcome to WivorPhotos">
    <h1>Welcome to WivorPhotos, {{ $name }}!</h1>
    <p>Your account has been created successfully.</p>
    <p>Your login credentials are:</p>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="background-color: #fff7f2; border-left: 4px solid #ff6700; margin: 20px 0 24px;">
        <tr>
            <td style="padding: 18px 20px;">
                <strong>Email:</strong> {{ $email }}<br>
                <strong>Temporary password:</strong> {{ $password }}
            </td>
        </tr>
    </table>
    <p>Please change your password after logging in for the first time.</p>
    <p>Thank you for joining us!</p>
</x-email.layout>
