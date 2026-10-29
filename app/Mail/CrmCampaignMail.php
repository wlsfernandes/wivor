<?php

namespace App\Mail;

use App\Services\CrmRenderedEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Sends one already-rendered CRM campaign email with its plain-text alternative. */
class CrmCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CrmRenderedEmail $email)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('crm.from_address') ?: config('mail.from.address'),
                config('crm.from_name') ?: config('mail.from.name')
            ),
            subject: $this->email->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->email->html,
            text: 'emails.crm.campaign-text',
            with: ['textBody' => $this->email->text],
        );
    }
}
