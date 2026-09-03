<?php

namespace App\Http\Controllers;

use App\Mail\OrderReceiptMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Throwable;
use UnexpectedValueException;

/** Receives verified Stripe payment notifications and fulfills paid orders exactly once. */
class StripeWebhookController extends Controller
{
    /** Verify the Stripe signature and fulfill the checkout.session.completed event. */
    public function handle(Request $request): Response
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                (string) config('services.stripe.webhook_secret'),
            );
        } catch (SignatureVerificationException|UnexpectedValueException $exception) {
            Log::error('Stripe webhook signature verification failed.', ['event' => 'stripe.webhook', 'exception' => $exception->getMessage()]);

            return response('Invalid signature.', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            try {
                $this->fulfill($event->data->object);
            } catch (Throwable $exception) {
                Log::error('Stripe webhook fulfillment failed.', [
                    'event' => 'stripe.webhook.fulfill',
                    'stripe_session_id' => $event->data->object->id ?? null,
                    'exception' => $exception->getMessage(),
                ]);

                return response('Fulfillment failed.', 500);
            }
        }

        return response('OK', 200);
    }

    /** Mark the matching pending order paid, create download entitlements, and record the sale. */
    private function fulfill(object $session): void
    {
        if (($session->payment_status ?? null) !== 'paid') {
            return;
        }

        $order = DB::transaction(function () use ($session): ?Order {
            $order = Order::where('stripe_checkout_session_id', $session->id)->lockForUpdate()->first();

            if (! $order || $order->payment_status !== Order::PAYMENT_PENDING) {
                return null;
            }

            $paidAt = now();
            $downloadExpiresAt = $paidAt->copy()->addDays((int) config('photo_uploads.sold_original_days'));

            $order->update([
                'payment_status' => Order::PAYMENT_PAID,
                'fulfillment_status' => Order::FULFILLMENT_READY,
                'customer_email' => $session->customer_details->email ?? null,
                'stripe_payment_intent_id' => $session->payment_intent ?? null,
                'paid_at' => $paidAt,
                'fulfilled_at' => $paidAt,
                'download_expires_at' => $downloadExpiresAt,
            ]);

            $order->items()->update([
                'download_status' => OrderItem::DOWNLOAD_READY,
                'download_expires_at' => $downloadExpiresAt,
            ]);

            $photoIds = $order->items()->pluck('photo_id')->filter()->all();
            if ($photoIds) {
                Photo::whereIn('id', $photoIds)->increment('sale_count', 1, ['most_recent_purchase_at' => $paidAt]);
            }

            return $order;
        });

        // Only the call that actually transitioned the order sends the receipt, so retried webhooks never resend it.
        if ($order) {
            $this->sendReceipt($order);
        }
    }

    /** Email the receipt and download link without undoing fulfillment if delivery fails. */
    private function sendReceipt(Order $order): void
    {
        try {
            Mail::to($order->customer_email)->send(new OrderReceiptMail($order->fresh(['event', 'items'])));
        } catch (Throwable $exception) {
            Log::error('Order receipt email delivery failed.', [
                'event' => 'stripe.webhook.receipt_email',
                'order_id' => $order->id,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
