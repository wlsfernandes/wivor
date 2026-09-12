<x-email.layout title="Welcome to WivorPhotos">
    <h1>Welcome to WivorPhotos</h1>
    <p>Hello {{ $user->name }},</p>
    <p>Your WivorPhotos photographer profile is approved. Complete secure Stripe account setup so WivorPhotos can track your payout readiness and you can access Stripe account tools.</p>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
        <tr>
            <td align="center" style="padding: 10px 0 28px;">
                <a class="email-button" href="{{ route('photographer.dashboard') }}" target="_blank" rel="noopener noreferrer" style="background-color: #ff6700; border-radius: 5px; color: #ffffff; display: inline-block; font-size: 16px; font-weight: bold; padding: 13px 24px; text-decoration: none;">Complete Payout Setup</a>
            </td>
        </tr>
    </table>
    <p>For your security, WivorPhotos will never send your password by email.</p>
</x-email.layout>
