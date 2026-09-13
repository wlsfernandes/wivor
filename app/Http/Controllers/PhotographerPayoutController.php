<?php

namespace App\Http\Controllers;

use App\Models\Photographer;
use App\Services\StripeConnectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PhotographerPayoutController extends Controller
{
    public function __construct(private readonly StripeConnectService $connect) {}

    /** Start or continue Stripe-hosted payout onboarding. */
    public function start(Request $request): RedirectResponse
    {
        try {
            return redirect()->away($this->connect->onboardingUrl($this->photographer($request)));
        } catch (Throwable $exception) {
            Log::error('Stripe payout onboarding could not start.', [
                'event' => 'photographers.payouts.start',
                'photographer_id' => $request->user()->photographer->id,
                'exception_class' => $exception::class,
                'exception_message' => $exception->getMessage(),
                'stripe_error_code' => method_exists($exception, 'getStripeCode') ? $exception->getStripeCode() : null,
                'stripe_request_id' => method_exists($exception, 'getRequestId') ? $exception->getRequestId() : null,
                'http_status' => method_exists($exception, 'getHttpStatus') ? $exception->getHttpStatus() : null,
            ]);

            return redirect()->route('photographer.dashboard')
                ->withErrors(['payouts' => 'Payout setup is temporarily unavailable. Please try again.']);
        }
    }

    /** Replace an expired or consumed Account Link for the same Stripe account. */
    public function refresh(Request $request): RedirectResponse
    {
        return $this->start($request);
    }

    /** Synchronize Stripe status after the photographer leaves onboarding. */
    public function returned(Request $request): RedirectResponse
    {
        try {
            $photographer = $this->connect->synchronize($this->photographer($request));

            return redirect()->route('photographer.dashboard')
                ->with('success', $photographer->isReadyForPayouts()
                    ? 'Payout setup is complete.'
                    : 'Your payout setup status was updated.');
        } catch (Throwable) {
            Log::error('Stripe payout return synchronization failed.', [
                'event' => 'photographers.payouts.return',
                'photographer_id' => $request->user()->photographer->id,
            ]);

            return redirect()->route('photographer.dashboard')
                ->withErrors(['payouts' => 'We could not check your payout status. Please try again.']);
        }
    }

    /** Manually retrieve the latest Stripe payout status. */
    public function synchronize(Request $request): RedirectResponse
    {
        return $this->returned($request);
    }

    /** Open the photographer's Stripe Dashboard. */
    public function dashboard(Request $request): RedirectResponse
    {
        $photographer = $this->photographer($request);
        abort_unless($photographer->isReadyForPayouts(), 403);

        try {
            return redirect()->away($this->connect->dashboardUrl($photographer));
        } catch (Throwable) {
            Log::error('Stripe Dashboard could not open.', [
                'event' => 'photographers.payouts.dashboard',
                'photographer_id' => $photographer->id,
            ]);

            return redirect()->route('photographer.dashboard')
                ->withErrors(['payouts' => 'The Stripe Dashboard is temporarily unavailable. Please try again.']);
        }
    }

    private function photographer(Request $request): Photographer
    {
        $photographer = $request->user()->photographer;
        abort_unless($photographer?->isApproved(), 403);

        return $photographer;
    }
}
