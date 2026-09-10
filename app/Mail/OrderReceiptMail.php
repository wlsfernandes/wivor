<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** Sends a paid customer their receipt and secure download link. */
class OrderReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $orderNumber;
    public string $eventTitle;
    public string $purchaseTotal;
    public string $photoCountLabel;
    public string $downloadExpirationDate;

    public function __construct(public Order $order)
    {
        $this->orderNumber = $order->order_number;
        $this->eventTitle = $order->event->title;
        $this->purchaseTotal = '$'.number_format($order->total_cents / 100, 2);
        $this->photoCountLabel = $order->photo_count.' '.($order->photo_count === 1 ? 'photo' : 'photos');
        $this->downloadExpirationDate = $order->download_expires_at?->format('F j, Y') ?? 'the date shown on your order page';
    }

    /** Build the order receipt and download-access email. */
    public function build(): self
    {
        return $this->subject("Your WivorPhotos order {$this->order->order_number}")
            ->view('emails.orders.receipt')
            ->with([
                'orderUrl' => route('orders.show', ['accessToken' => $this->order->access_token]),
            ]);
    }
}
