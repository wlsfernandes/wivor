<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Photographer;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

/** Creates the pending order and Stripe-hosted Checkout Session for the current cart. */
class CheckoutController extends Controller
{
    public function __construct(private readonly CartService $cart, private readonly CheckoutService $checkout)
    {
    }

    /** Revalidate the cart, create the pending order, and redirect to Stripe Checkout. */
    public function store(): RedirectResponse
    {
        $event = $this->cart->event();
        $photos = $this->cart->photos();

        if (! $event || $photos->isEmpty()) {
            return redirect()->route('cart.show')->withErrors(['cart' => 'Your selection is empty.']);
        }

        $photographer = Photographer::find($photos->first()->photographer_id);

        try {
            $order = $this->checkout->createPendingOrder($event, $photographer, $photos);
            $session = $this->checkout->createCheckoutSession($order);
        } catch (ValidationException $exception) {
            return redirect()->route('cart.show')->withErrors($exception->errors());
        } catch (Throwable $exception) {
            Log::error('Checkout session creation failed.', ['event' => 'checkout.store', 'exception' => $exception->getMessage()]);

            return redirect()->route('cart.show')->withErrors(['cart' => 'Checkout is temporarily unavailable. Please try again.']);
        }

        $this->cart->clear();

        return redirect()->away($session->url);
    }

    /** Display the payment-confirmation return page. */
    public function success(Order $order): View
    {
        return view('checkout.success', [
            'order' => $order,
            'isPaid' => $order->payment_status === Order::PAYMENT_PAID,
            'layout' => 'layouts.app',
        ]);
    }

    /** Display the cancellation return page and leave the order unpaid. */
    public function cancel(Order $order): View
    {
        if ($order->payment_status === Order::PAYMENT_PENDING) {
            $order->update(['cancelled_at' => now(), 'payment_status' => Order::PAYMENT_CANCELLED]);
        }

        return view('checkout.cancel', [
            'order' => $order,
            'layout' => 'layouts.app',
        ]);
    }
}
