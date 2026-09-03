<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventMediaExpirationWarning extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Event $event, public int $days) {}

    public function via(object $notifiable): array { return ['mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        $close = $this->event->sales_close_at->timezone($this->event->timezone)->format('F j, Y \a\t g:i A T');

        return (new MailMessage)
            ->subject("{$this->event->title} gallery closes in {$this->days} days")
            ->greeting('WivorPhotos media expiration notice')
            ->line("This event gallery closes on {$close}. Unsold photos will be permanently removed.")
            ->line('Keep your own backup; WivorPhotos is not permanent storage.')
            ->line('Sold originals may remain longer for authorized customer downloads.')
            ->action('Review event photos', route('photographer.uploads.show', $this->event));
    }
}
