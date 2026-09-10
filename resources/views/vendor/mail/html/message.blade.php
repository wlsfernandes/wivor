<x-email.layout>
    {!! Illuminate\Mail\Markdown::parse($slot) !!}

    @isset($subcopy)
        <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="border-top: 1px solid #e5e5e5; margin-top: 28px; padding-top: 22px;">
            <tr>
                <td style="color: #777777; font-size: 13px; line-height: 1.5;">
                    {!! Illuminate\Mail\Markdown::parse($subcopy) !!}
                </td>
            </tr>
        </table>
    @endisset
</x-email.layout>
