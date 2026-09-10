<x-email.layout :title="$heading">
    <h1>{{ $heading }}</h1>
    <p>{{ $messageText }}</p>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
        <tr>
            <td align="center" style="padding: 10px 0 18px;">
                <a class="email-button" href="{{ route('photographer.dashboard') }}" target="_blank" rel="noopener noreferrer" style="background-color: #ff6700; border-radius: 5px; color: #ffffff; display: inline-block; font-size: 16px; font-weight: bold; padding: 13px 24px; text-decoration: none;">{{ $actionText }}</a>
            </td>
        </tr>
    </table>
</x-email.layout>
