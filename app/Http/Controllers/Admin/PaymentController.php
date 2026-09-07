<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

/** Displays the administrator's Stripe order reconciliation list. */
class PaymentController extends Controller
{
    /** Display recent photo orders with their local and Stripe identifiers. */
    public function index(): View
    {
        $orders = Order::query()
            ->with('event')
            ->latest()
            ->paginate(30)
            ->through(fn (Order $order): object => (object) [
                'orderNumber' => $order->order_number,
                'eventTitle' => $order->event->title,
                'customerEmail' => $order->customer_email ?: 'Not captured',
                'photoCount' => $order->photo_count,
                'totalLabel' => '$'.number_format($order->total_cents / 100, 2).' '.strtoupper($order->currency),
                'paymentStatusLabel' => $order->payment_status_label,
                'stripeSessionId' => $order->stripe_checkout_session_id ?: 'Not created',
                'stripePaymentIntentId' => $order->stripe_payment_intent_id ?: 'Not available',
                'createdAtLabel' => $order->created_at->format('M j, Y g:i A'),
            ]);

        return view('payments.index', ['orders' => $orders]);
    }
}
