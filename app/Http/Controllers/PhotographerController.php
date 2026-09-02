<?php

namespace App\Http\Controllers;

use App\Models\Photographer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Handles public and authenticated photographer pages retained by existing routes. */
class PhotographerController extends Controller
{
    /** Redirect photographers to the public event directory. */
    public function allEvents(): RedirectResponse
    {
        return redirect()->route('events.listEvents');
    }

    /** Redirect photographers to their managed events. */
    public function myEvents(): RedirectResponse
    {
        return redirect()->route('events.index');
    }

    /** Redirect photographers to the existing event creation page. */
    public function newEvent(): RedirectResponse
    {
        return redirect()->route('events.create');
    }

    /** Display the existing public photographer application page. */
    public function photographers(): View
    {
        return view('photographers.page');
    }

    /** Display the approved photographer dashboard. */
    public function dashboard(Request $request): View
    {
        $photographer = $request->user()->photographer;

        return view('photographers.dashboard', [
            'photographerName' => $photographer->first_name,
            'payoutSetupComplete' => $photographer->stripe_onboarding_status === 'complete',
        ]);
    }

    /** Display the authenticated photographer's application state. */
    public function applicationStatus(Request $request): View
    {
        $photographer = $request->user()->photographer;
        $isEmailVerified = $request->user()->hasVerifiedEmail();
        $statusMessage = match (true) {
            ! $isEmailVerified => 'Verify your email address before your application can be activated.',
            $photographer->status === Photographer::STATUS_PENDING => 'Your email is verified and your application is under review. We will email you when a decision is ready.',
            $photographer->status === Photographer::STATUS_DECLINED => 'We are unable to approve your photographer application at this time. Contact support if you have questions.',
            $photographer->status === Photographer::STATUS_SUSPENDED => 'Your photographer access is currently suspended. Contact support if you have questions.',
            default => 'Your account is ready.',
        };

        return view('photographers.application-status', [
            'statusLabel' => $photographer->statusLabel(),
            'statusMessage' => $statusMessage,
            'showVerificationResend' => ! $isEmailVerified,
            'canOpenDashboard' => $request->user()->canAccessPhotographerArea(),
        ]);
    }
}
