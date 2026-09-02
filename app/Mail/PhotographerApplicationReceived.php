<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** Confirms that Wivor received a photographer application. */
class PhotographerApplicationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user)
    {
    }

    /** Build the application-received message. */
    public function build(): self
    {
        return $this->subject('We received your WivorPhotos application')
            ->view('emails.photographers.application-received');
    }
}
