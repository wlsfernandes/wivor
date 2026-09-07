<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function store(Request $request): RedirectResponse
    {
        $submittedToken = (string) $request->input('checkout_token', '');
        $expectedToken = (string) $request->session()->pull('wivor_checkout_token', '');

        if ($submittedToken === '' || $expectedToken === '' || ! hash_equals($expectedToken, $submittedToken)) {
            return redirect()->route('cart.show')->withErrors(['cart' => 'This checkout request has expired. Please try again.']);
        }

        $event = $this->cart->event();
        $photos = $this->cart->photos();

        if (! $event || $photos->isEmpty()) {
            return redirect()->route('cart.show')->withErrors(['cart' => 'Your selection is empty.']);
        }

        try {
            $order = $this->checkout->createPendingOrder($event, $photos);
            $session = $this->checkout->createCheckoutSession($order);
        } catch (ValidationException $exception) {
            return redirect()->route('cart.show')->withErrors($exception->errors());
        } catch (Throwable $exception) {
            Log::error('Checkout session creation failed.', ['event' => 'checkout.store', 'exception' => $exception->getMessage()]);

            return redirect()->route('cart.show')->withErrors(['cart' => 'Checkout is temporarily unavailable. Please try again.']);
        }

        return redirect()->away($session->url);
    }

    /** Verify the returned Stripe Session and display the payment-confirmation page. */
    public function success(Request $request, Order $order): View
    {
        $sessionId = (string) $request->query('session_id', '');
        abort_if($sessionId === '', 404);

        $confirmationUnavailable = false;

        try {
            $session = $this->checkout->retrieveCheckoutSession($order, $sessionId);
        } catch (ValidationException) {
            abort(404);
        } catch (Throwable $exception) {
            $confirmationUnavailable = true;
            Log::error('Checkout return verification failed.', [
                'event' => 'checkout.success',
                'order_id' => $order->id,
                'stripe_session_id' => $sessionId,
                'exception' => $exception->getMessage(),
            ]);
        }

        if (! $confirmationUnavailable) {
            abort_unless(($session->payment_status ?? null) === 'paid', 404);
            $this->cart->clear();
        }

        $order->refresh();
        $isPaid = $order->payment_status === Order::PAYMENT_PAID;
        $confirmationAttempt = min(max((int) $request->query('confirmation_attempt', 0), 0), 10);

        return view('checkout.success', [
            'order' => $order,
            'isPaid' => $isPaid,
            'confirmationUnavailable' => $confirmationUnavailable,
            'orderUrl' => route('orders.show', ['accessToken' => $order->access_token]),
            'refreshUrl' => route('checkout.success', [
                'order' => $order->order_number,
                'session_id' => $sessionId,
                'confirmation_attempt' => $confirmationAttempt + 1,
            ]),
            'shouldAutoRefresh' => ! $isPaid && $confirmationAttempt < 10,
            'layout' => 'layouts.app',
        ]);
    }

    /** Display a read-only cancellation page while preserving the customer's cart. */
    public function cancel(Order $order): View
    {
        return view('checkout.cancel', [
            'order' => $order,
            'layout' => 'layouts.app',
        ]);
    }
}
