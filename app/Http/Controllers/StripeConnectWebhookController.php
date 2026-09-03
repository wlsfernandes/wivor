<?php

namespace App\Http\Controllers;

use App\Mail\PhotographerPayoutStatusChanged;
use App\Models\Photographer;
use App\Services\StripeConnectService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Throwable;
use UnexpectedValueException;

class StripeConnectWebhookController extends Controller
{
    public function __construct(private readonly StripeConnectService $connect) {}

    /** Verify and synchronize connected-account requirement changes idempotently. */
    public function handle(Request $request): Response
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                (string) config('services.stripe.connect_webhook_secret'),
            );
        } catch (SignatureVerificationException|UnexpectedValueException $exception) {
            Log::error('Stripe Connect webhook signature verification failed.', ['event' => 'stripe.connect_webhook']);

            return response('Invalid signature.', 400);
        }

        if ($event->type !== 'account.updated') {
            return response('OK', 200);
        }

        $liveMode = Str::contains((string) config('services.stripe.secret'), '_live_');
        if ((bool) $event->livemode !== $liveMode) {
            Log::warning('Stripe Connect webhook environment mismatch.', ['event' => 'stripe.connect_webhook.environment']);

            return response('Environment mismatch.', 400);
        }

        $accountId = $event->account ?? $event->data->object->id ?? null;
        $photographer = $accountId
            ? Photographer::where('stripe_account_id', $accountId)->first()
            : null;

        if (! $photographer || $photographer->stripe_last_event_id === $event->id) {
            return response('OK', 200);
        }

        try {
            [$photographer, $statusChanged] = DB::transaction(function () use ($photographer, $event): array {
                $locked = Photographer::query()->lockForUpdate()->findOrFail($photographer->id);
                $previousStatus = $locked->stripe_onboarding_status;
                $synchronized = $this->connect->synchronize($locked, $event->id);

                return [$synchronized, $previousStatus !== $synchronized->stripe_onboarding_status];
            });

            if ($statusChanged && in_array($photographer->stripe_onboarding_status, [
                    Photographer::STRIPE_ACTION_REQUIRED,
                    Photographer::STRIPE_READY,
                    Photographer::STRIPE_RESTRICTED,
                ], true)) {
                $this->notifyPhotographer($photographer);
            }
        } catch (Throwable) {
            Log::error('Stripe Connect account synchronization failed.', [
                'event' => 'stripe.connect_webhook.sync',
                'photographer_id' => $photographer->id,
            ]);

            return response('Synchronization failed.', 500);
        }

        return response('OK', 200);
    }

    private function notifyPhotographer(Photographer $photographer): void
    {
        try {
            Mail::to($photographer->user)->send(
                new PhotographerPayoutStatusChanged($photographer->stripe_onboarding_status)
            );
        } catch (Throwable) {
            Log::error('Photographer payout status email failed.', [
                'event' => 'photographers.payouts.status_email',
                'photographer_id' => $photographer->id,
            ]);
        }
    }
}
