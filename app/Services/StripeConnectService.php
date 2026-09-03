<?php

namespace App\Services;

use App\Models\Photographer;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;

class StripeConnectService
{
    public function __construct(private readonly StripeClient $stripe) {}

    /** Create or reuse the photographer's Express account and return a fresh onboarding URL. */
    public function onboardingUrl(Photographer $photographer): string
    {
        $photographer = DB::transaction(function () use ($photographer): Photographer {
            $locked = Photographer::query()->lockForUpdate()->findOrFail($photographer->id);

            if (! $locked->stripe_account_id) {
                $account = $this->stripe->accounts->create([
                    'type' => 'express',
                    'country' => 'US',
                    'email' => $locked->user->email,
                    'capabilities' => [
                        'transfers' => ['requested' => true],
                    ],
                    'business_profile' => [
                        'product_description' => 'Event photography sold through WivorPhotos',
                    ],
                    'metadata' => [
                        'wivor_photographer_id' => (string) $locked->id,
                    ],
                ], [
                    'idempotency_key' => "wivor-photographer-{$locked->id}",
                ]);

                $locked->forceFill([
                    'stripe_account_id' => $account->id,
                    'stripe_onboarding_status' => Photographer::STRIPE_INCOMPLETE,
                    'stripe_setup_started_at' => now(),
                ])->save();
            }

            return $locked;
        });

        $link = $this->stripe->accountLinks->create([
            'account' => $photographer->stripe_account_id,
            'refresh_url' => route('photographer.payouts.refresh'),
            'return_url' => route('photographer.payouts.return'),
            'type' => 'account_onboarding',
            'collection_options' => [
                'fields' => 'eventually_due',
                'future_requirements' => 'include',
            ],
        ]);

        return $link->url;
    }

    /** Retrieve Stripe's authoritative account state and cache only its payout summary. */
    public function synchronize(Photographer $photographer, ?string $eventId = null): Photographer
    {
        if (! $photographer->stripe_account_id || ($eventId && $photographer->stripe_last_event_id === $eventId)) {
            return $photographer;
        }

        $account = $this->stripe->accounts->retrieve($photographer->stripe_account_id, []);
        $requirements = $account->requirements ?? (object) [];
        $capabilities = $account->capabilities ?? (object) [];
        $currentlyDue = collect($requirements->currently_due ?? []);
        $eventuallyDue = collect($requirements->eventually_due ?? []);
        $pastDue = collect($requirements->past_due ?? []);
        $pendingVerification = collect($requirements->pending_verification ?? []);
        $transfersActive = ($capabilities->transfers ?? null) === 'active';
        $payoutsEnabled = (bool) ($account->payouts_enabled ?? false);
        $disabledReason = $requirements->disabled_reason ?? null;

        $status = match (true) {
            $transfersActive && $payoutsEnabled && $currentlyDue->isEmpty() && $pastDue->isEmpty()
                => Photographer::STRIPE_READY,
            filled($disabledReason)
                => Photographer::STRIPE_RESTRICTED,
            $pendingVerification->isNotEmpty()
                => Photographer::STRIPE_UNDER_REVIEW,
            (bool) ($account->details_submitted ?? false) && ($currentlyDue->isNotEmpty() || $pastDue->isNotEmpty())
                => Photographer::STRIPE_ACTION_REQUIRED,
            default
                => Photographer::STRIPE_INCOMPLETE,
        };

        $now = now();
        $photographer->forceFill([
            'stripe_onboarding_status' => $status,
            'stripe_transfers_active' => $transfersActive,
            'stripe_payouts_enabled' => $payoutsEnabled,
            'stripe_requirements_due' => $currentlyDue->isNotEmpty() || $eventuallyDue->isNotEmpty() || $pastDue->isNotEmpty(),
            'stripe_requirements_deadline_at' => isset($requirements->current_deadline)
                ? $now->copy()->setTimestamp((int) $requirements->current_deadline)
                : null,
            'stripe_last_synced_at' => $now,
            'stripe_last_event_id' => $eventId ?? $photographer->stripe_last_event_id,
            'stripe_ready_at' => $status === Photographer::STRIPE_READY
                ? ($photographer->stripe_ready_at ?? $now)
                : $photographer->stripe_ready_at,
            'stripe_restricted_at' => $status === Photographer::STRIPE_RESTRICTED
                ? ($photographer->stripe_restricted_at ?? $now)
                : $photographer->stripe_restricted_at,
            'stripe_disabled_at' => filled($disabledReason)
                ? ($photographer->stripe_disabled_at ?? $now)
                : null,
        ])->save();

        return $photographer->refresh();
    }

    /** Return a fresh, single-use Express Dashboard login URL. */
    public function dashboardUrl(Photographer $photographer): string
    {
        return $this->stripe->accounts->createLoginLink($photographer->stripe_account_id)->url;
    }
}
