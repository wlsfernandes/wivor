<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** Welcomes an approved photographer without transmitting credentials. */
class PhotographerApplicationApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user)
    {
    }

    /** Build the approval message. */
    public function build(): self
    {
        return $this->subject('Your WivorPhotos application is approved')
            ->view('emails.photographers.application-approved');
    }
}
