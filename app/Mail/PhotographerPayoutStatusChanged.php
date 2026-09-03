<?php

namespace App\Mail;

use App\Models\Photographer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PhotographerPayoutStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $status) {}

    public function build(): self
    {
        $subject = $this->status === Photographer::STRIPE_READY
            ? 'Your WivorPhotos payout setup is complete'
            : 'Your WivorPhotos payout setup needs attention';

        return $this->subject($subject)->view('emails.photographers.payout-status-changed');
    }
}
