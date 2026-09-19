<?php

namespace App\Services;

use App\Jobs\ProcessPhotographerTransfer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PhotographerTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Throwable;
use UnexpectedValueException;

/** Creates and processes Stripe transfers for photographers' stored order allocations. */
class PhotographerTransferService
{
    public function __construct(
        private readonly StripeClient $stripe,
        private readonly StripeConnectService $stripeConnect,
    ) {}

    /**
     * Create one transfer obligation per photographer in a paid order using the
     * allocation amounts already frozen on its order items.
     */
    public function createTransfersForPaidOrder(Order $order): void
    {
        if ($order->payment_status !== Order::PAYMENT_PAID) {
            return;
        }

        $orderItems = $order->items()->get();

        foreach ($orderItems->groupBy('photographer_id') as $photographerId => $photographerOrderItems) {
            $photographerAmountCents = (int) $photographerOrderItems->sum('photographer_allocation_cents');
            $photographerTransfer = PhotographerTransfer::firstOrCreate(
                [
                    'order_id' => $order->id,
                    'photographer_id' => (int) $photographerId,
                ],
                [
                    'amount_cents' => $photographerAmountCents,
                    'currency' => $order->currency,
                    'status' => PhotographerTransfer::STATUS_PENDING,
                ],
            );

            if ($photographerTransfer->amount_cents !== $photographerAmountCents
                || $photographerTransfer->currency !== $order->currency) {
                $this->markTransferFailed($photographerTransfer, 'Stored transfer does not match the order allocation.');

                Log::error('Photographer transfer does not match its order allocation.', [
                    'order_id' => $order->id,
                    'photographer_id' => (int) $photographerId,
                    'photographer_transfer_id' => $photographerTransfer->id,
                ]);

                continue;
            }

            if ($photographerTransfer->status !== PhotographerTransfer::STATUS_SUCCEEDED) {
                ProcessPhotographerTransfer::dispatch($photographerTransfer->id);
            }
        }
    }

    /**
     * Validate and send one photographer's grouped allocation to the connected
     * Stripe account, preserving the same destination and idempotency identity on retries.
     */
    public function processTransfer(PhotographerTransfer $photographerTransfer): void
    {
        $photographerTransfer->refresh()->load(['order', 'photographer']);

        if ($photographerTransfer->status === PhotographerTransfer::STATUS_SUCCEEDED) {
            return;
        }

        $order = $photographerTransfer->order;
        $photographer = $photographerTransfer->photographer;

        if ($order->payment_status !== Order::PAYMENT_PAID) {
            $this->markTransferFailed($photographerTransfer, 'The order is not paid.');

            return;
        }

        if (blank($photographer->stripe_account_id)) {
            $this->markTransferFailed($photographerTransfer, 'Photographer Stripe account is not ready for transfers.');

            return;
        }

        try {
            $photographer = $this->stripeConnect->synchronize($photographer);

            if (! $photographer->isReadyForPayouts()) {
                $this->markTransferFailed($photographerTransfer, 'Photographer Stripe account is not ready for transfers.');

                return;
            }

            $stripeAccountId = (string) $photographer->stripe_account_id;

            if ($photographerTransfer->stripe_account_id
                && $photographerTransfer->stripe_account_id !== $stripeAccountId) {
                $this->markTransferFailed($photographerTransfer, 'Photographer Stripe account no longer matches this transfer.');

                return;
            }

            if (! $photographerTransfer->stripe_account_id) {
                $photographerTransfer->update(['stripe_account_id' => $stripeAccountId]);
            }

            $stripeChargeId = $this->stripeChargeId($order);
            $idempotencyKey = "wivor-transfer-order-{$order->id}-photographer-{$photographer->id}";
            $stripeTransfer = $this->stripe->transfers->create([
                'amount' => $photographerTransfer->amount_cents,
                'currency' => $photographerTransfer->currency,
                'destination' => $photographerTransfer->stripe_account_id,
                'source_transaction' => $stripeChargeId,
                'transfer_group' => "wivor_order_{$order->id}",
                'metadata' => [
                    'wivor_order_id' => (string) $order->id,
                    'wivor_photographer_id' => (string) $photographer->id,
                ],
            ], [
                'idempotency_key' => $idempotencyKey,
            ]);

            $this->markTransferSucceeded($photographerTransfer, (string) $stripeTransfer->id);
        } catch (Throwable $exception) {
            $lastError = $exception instanceof UnexpectedValueException
                ? $exception->getMessage()
                : 'Stripe transfer failed.';

            $this->markTransferFailed($photographerTransfer, $lastError);

            Log::error('Stripe photographer transfer failed.', [
                'order_id' => $order->id,
                'photographer_id' => $photographer->id,
                'photographer_transfer_id' => $photographerTransfer->id,
                'exception_class' => $exception::class,
                'stripe_error_code' => method_exists($exception, 'getStripeCode') ? $exception->getStripeCode() : null,
                'stripe_request_id' => method_exists($exception, 'getRequestId') ? $exception->getRequestId() : null,
            ]);

            throw $exception;
        }
    }

    /** Return the order's source charge, retrieving and caching it from Stripe when necessary. */
    private function stripeChargeId(Order $order): string
    {
        if ($order->stripe_charge_id) {
            return $order->stripe_charge_id;
        }

        if (! $order->stripe_payment_intent_id) {
            throw new UnexpectedValueException('Stripe charge is not available for this paid order.');
        }

        $paymentIntent = $this->stripe->paymentIntents->retrieve($order->stripe_payment_intent_id, []);
        $latestCharge = $paymentIntent->latest_charge ?? null;
        $stripeChargeId = is_object($latestCharge) ? ($latestCharge->id ?? null) : $latestCharge;

        if (blank($stripeChargeId)) {
            throw new UnexpectedValueException('Stripe charge is not available for this paid order.');
        }

        $order->update(['stripe_charge_id' => (string) $stripeChargeId]);

        return (string) $stripeChargeId;
    }

    /** Persist a completed Stripe transfer and mirror its ID onto the grouped order items. */
    private function markTransferSucceeded(PhotographerTransfer $photographerTransfer, string $stripeTransferId): void
    {
        DB::transaction(function () use ($photographerTransfer, $stripeTransferId): void {
            $photographerTransfer->update([
                'stripe_transfer_id' => $stripeTransferId,
                'status' => PhotographerTransfer::STATUS_SUCCEEDED,
                'last_error' => null,
                'transferred_at' => now(),
            ]);

            // Existing photographer screens read this item field, so every item mirrors the grouped transfer ID.
            OrderItem::where('order_id', $photographerTransfer->order_id)
                ->where('photographer_id', $photographerTransfer->photographer_id)
                ->update(['stripe_transfer_id' => $stripeTransferId]);
        });
    }

    /** Preserve the transfer obligation while recording why it has not been paid. */
    private function markTransferFailed(PhotographerTransfer $photographerTransfer, string $lastError): void
    {
        if ($photographerTransfer->status === PhotographerTransfer::STATUS_SUCCEEDED) {
            return;
        }

        $photographerTransfer->update([
            'status' => PhotographerTransfer::STATUS_FAILED,
            'last_error' => $lastError,
        ]);
    }
}
