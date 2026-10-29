{{-- $bodyHtml is administrator content already personalized with escaped values and sanitized; it is data, not a template. --}}
<x-email.layout :title="$subject" :postal-address="$footer['postalAddress']">
    @if ($previewText !== '')
        <div style="display: none; max-height: 0; overflow: hidden; mso-hide: all;">{{ $previewText }}</div>
    @endif

    {!! $bodyHtml !!}

    @if ($callToAction)
        <table cellpadding="0" cellspacing="0" border="0" role="presentation" style="margin: 28px 0 8px;">
            <tr>
                <td style="background-color: #ff6700; border-radius: 6px;">
                    <a href="{{ $callToAction['url'] }}" class="email-button" target="_blank" rel="noopener noreferrer"
                        style="color: #ffffff; display: inline-block; font-family: Arial, Helvetica, sans-serif; font-size: 16px; font-weight: bold; padding: 14px 26px; text-decoration: none;">{{ $callToAction['label'] }}</a>
                </td>
            </tr>
        </table>
    @endif

    <x-slot:footerNotice>
        <p style="color: #d0d0d0; font-family: Arial, Helvetica, sans-serif; font-size: 13px; line-height: 1.5; margin: 0 0 14px; text-align: center;">
            {{ $footer['reason'] }}
            @if ($footer['unsubscribeUrl'])
                <br><a href="{{ $footer['unsubscribeUrl'] }}" style="color: #ff8a3d; text-decoration: underline;">Unsubscribe</a>
            @else
                <br>[The unsubscribe link is added when the campaign is sent.]
            @endif
        </p>
    </x-slot:footerNotice>
</x-email.layout>
