<?php

namespace App\Mail;

use App\Models\Photographer;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** Notifies a photographer that an application was declined or access suspended. */
class PhotographerAccountStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public string $heading;
    public string $messageText;

    public function __construct(public User $user, public string $status)
    {
        [$this->heading, $this->messageText] = $status === Photographer::STATUS_SUSPENDED
            ? ['Your WivorPhotos access has been suspended', 'Your photographer access is currently suspended. Please contact WivorPhotos support if you have questions.']
            : ['Update on your WivorPhotos application', 'Thank you for applying. We are unable to approve your photographer application at this time.'];
    }

    /** Build the account-state message without exposing internal review notes. */
    public function build(): self
    {
        return $this->subject($this->heading)
            ->view('emails.photographers.account-status-changed');
    }
}
