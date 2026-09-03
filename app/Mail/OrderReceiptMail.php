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

    public function __construct(public Order $order)
    {
    }

    /** Build the order receipt and download-access email. */
    public function build(): self
    {
        return $this->subject("Your WivorPhotos order {$this->order->order_number}")
            ->view('emails.orders.receipt')
            ->with([
                'order' => $this->order,
                'orderUrl' => route('orders.show', ['accessToken' => $this->order->access_token]),
            ]);
    }
}
