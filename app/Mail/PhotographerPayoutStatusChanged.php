<?php

namespace App\Mail;

use App\Models\Photographer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PhotographerPayoutStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public string $heading;
    public string $messageText;
    public string $actionText;
    public string $subjectLine;

    public function __construct(public string $status)
    {
        [$this->heading, $this->messageText, $this->actionText, $this->subjectLine] = $status === Photographer::STRIPE_READY
            ? [
                'Your payout setup is complete',
                'You can now publish photos for sale and receive earnings through Stripe.',
                'Open Your Photographer Dashboard',
                'Your WivorPhotos payout setup is complete',
            ]
            : [
                'Your payout setup needs attention',
                'Stripe needs additional information to keep your WivorPhotos payouts active.',
                'Review Payout Setup',
                'Your WivorPhotos payout setup needs attention',
            ];
    }

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->view('emails.photographers.payout-status-changed');
    }
}
