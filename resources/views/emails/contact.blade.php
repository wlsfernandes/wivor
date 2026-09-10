<x-email.layout title="New WivorPhotos contact message">
    <h1>New website contact message</h1>
    <p>A visitor submitted the WivorPhotos contact form.</p>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="border-collapse: collapse;">
        <tr>
            <td style="border-bottom: 1px solid #eeeeee; font-weight: bold; padding: 10px 12px 10px 0; width: 90px;">Name</td>
            <td style="border-bottom: 1px solid #eeeeee; padding: 10px 0;">{{ $data['username'] }}</td>
        </tr>
        <tr>
            <td style="border-bottom: 1px solid #eeeeee; font-weight: bold; padding: 10px 12px 10px 0;">Email</td>
            <td style="border-bottom: 1px solid #eeeeee; padding: 10px 0;"><a href="mailto:{{ $data['email'] }}">{{ $data['email'] }}</a></td>
        </tr>
        <tr>
            <td style="border-bottom: 1px solid #eeeeee; font-weight: bold; padding: 10px 12px 10px 0;">Phone</td>
            <td style="border-bottom: 1px solid #eeeeee; padding: 10px 0;">{{ $data['phone'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; padding: 10px 12px 10px 0; vertical-align: top;">Message</td>
            <td style="padding: 10px 0; white-space: pre-line;">{{ $data['message'] }}</td>
        </tr>
    </table>
</x-email.layout>
