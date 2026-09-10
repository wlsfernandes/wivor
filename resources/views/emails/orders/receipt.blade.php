<x-email.layout title="WivorPhotos order receipt">
    <h1>Thank you for your order</h1>
    <p>Order {{ $orderNumber }} for {{ $eventTitle }} is confirmed.</p>
    <p><strong>Purchase total:</strong> {{ $purchaseTotal }} ({{ $photoCountLabel }})</p>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
        <tr>
            <td align="center" style="padding: 10px 0 28px;">
                <a class="email-button" href="{{ $orderUrl }}" target="_blank" rel="noopener noreferrer" style="background-color: #ff6700; border-radius: 5px; color: #ffffff; display: inline-block; font-size: 16px; font-weight: bold; padding: 13px 24px; text-decoration: none;">View and Download Photos</a>
            </td>
        </tr>
    </table>
    <p>Downloads are available until {{ $downloadExpirationDate }}.</p>
    <p>Questions? <a href="{{ route('contact_us') }}">Contact WivorPhotos support</a>.</p>
</x-email.layout>
