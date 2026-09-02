<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PhotographerAccountStatusChanged;
use App\Mail\PhotographerApplicationApproved;
use App\Models\Photographer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

/** Handles photographer application review in the Wivor admin area. */
class PhotographerController extends Controller
{
    /** Display photographer applications with an optional state filter. */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in($this->statuses())],
        ]);

        $photographers = Photographer::query()
            ->with('user:id,name,email,email_verified_at')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Photographer $photographer): array => [
                'name' => "{$photographer->first_name} {$photographer->last_name}",
                'location' => "{$photographer->city}, {$photographer->state}",
                'email' => $photographer->user->email,
                'email_verified_label' => $photographer->user->hasVerifiedEmail() ? 'Yes' : 'No',
                'status_label' => $photographer->statusLabel(),
                'registered_at_label' => $photographer->created_at->format('M j, Y'),
                'review_url' => route('admin.photographers.show', $photographer),
            ]);

        return view('admin.photographers.index', [
            'photographers' => $photographers,
            'filters' => $filters,
            'statusOptions' => [
                Photographer::STATUS_PENDING => 'Pending review',
                Photographer::STATUS_APPROVED => 'Approved',
                Photographer::STATUS_DECLINED => 'Declined',
                Photographer::STATUS_SUSPENDED => 'Suspended',
            ],
        ]);
    }

    /** Display all information required to review one application. */
    public function show(Photographer $photographer): View
    {
        $photographer->load(['user:id,name,email,email_verified_at', 'reviewer:id,name']);

        return view('admin.photographers.show', [
            'photographer' => $photographer,
            'statusLabel' => $photographer->statusLabel(),
            'emailVerifiedLabel' => $photographer->user->hasVerifiedEmail()
                ? $photographer->user->email_verified_at->format('M j, Y g:i A')
                : 'Not verified',
            'registeredAtLabel' => $photographer->created_at->format('M j, Y g:i A'),
            'reviewedAtLabel' => $photographer->reviewed_at?->format('M j, Y g:i A'),
            'reviewerName' => $photographer->reviewer?->name ?? 'Unknown administrator',
            'stripeOnboardingLabel' => ucfirst(str_replace('_', ' ', $photographer->stripe_onboarding_status)),
            'canApprove' => in_array($photographer->status, [Photographer::STATUS_PENDING, Photographer::STATUS_DECLINED], true),
            'canDecline' => $photographer->status === Photographer::STATUS_PENDING,
            'canSuspend' => $photographer->status === Photographer::STATUS_APPROVED,
            'canRestore' => $photographer->status === Photographer::STATUS_SUSPENDED,
        ]);
    }

    /** Approve a pending or previously declined photographer. */
    public function approve(Request $request, Photographer $photographer): RedirectResponse
    {
        abort_unless(in_array($photographer->status, [Photographer::STATUS_PENDING, Photographer::STATUS_DECLINED], true), 422);

        return $this->changeStatus(
            $request,
            $photographer,
            Photographer::STATUS_APPROVED,
            null,
            new PhotographerApplicationApproved($photographer->user),
            'Photographer approved.'
        );
    }

    /** Decline a pending photographer with an internal reason. */
    public function decline(Request $request, Photographer $photographer): RedirectResponse
    {
        abort_unless($photographer->status === Photographer::STATUS_PENDING, 422);
        $reason = $request->validate(['reason' => ['required', 'string', 'max:2000']])['reason'];

        return $this->changeStatus(
            $request,
            $photographer,
            Photographer::STATUS_DECLINED,
            $reason,
            new PhotographerAccountStatusChanged($photographer->user, Photographer::STATUS_DECLINED),
            'Photographer application declined.'
        );
    }

    /** Suspend an approved photographer with an internal reason. */
    public function suspend(Request $request, Photographer $photographer): RedirectResponse
    {
        abort_unless($photographer->status === Photographer::STATUS_APPROVED, 422);
        $reason = $request->validate(['reason' => ['required', 'string', 'max:2000']])['reason'];

        return $this->changeStatus(
            $request,
            $photographer,
            Photographer::STATUS_SUSPENDED,
            $reason,
            new PhotographerAccountStatusChanged($photographer->user, Photographer::STATUS_SUSPENDED),
            'Photographer suspended.'
        );
    }

    /** Restore a suspended photographer to approved status. */
    public function restore(Request $request, Photographer $photographer): RedirectResponse
    {
        abort_unless($photographer->status === Photographer::STATUS_SUSPENDED, 422);

        return $this->changeStatus(
            $request,
            $photographer,
            Photographer::STATUS_APPROVED,
            null,
            new PhotographerApplicationApproved($photographer->user),
            'Photographer access restored.'
        );
    }

    /** Persist an account-state transition and notify the photographer. */
    private function changeStatus(
        Request $request,
        Photographer $photographer,
        string $status,
        ?string $reason,
        Mailable $mail,
        string $successMessage
    ): RedirectResponse {
        try {
            DB::transaction(function () use ($request, $photographer, $status, $reason): void {
                $photographer->forceFill([
                    'status' => $status,
                    'status_reason' => $reason,
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                ])->save();
            });

        } catch (Throwable $exception) {
            Log::error('Photographer status change failed.', [
                'event' => 'admin.photographers.status',
                'photographer_id' => $photographer->id,
                'target_status' => $status,
                'exception' => $exception->getMessage(),
            ]);

            return back()->withErrors(['error' => 'The photographer status could not be changed. Please try again.']);
        }

        try {
            Mail::to($photographer->user)->send($mail);
        } catch (Throwable $exception) {
            Log::error('Photographer status email delivery failed.', [
                'event' => 'admin.photographers.status_email',
                'photographer_id' => $photographer->id,
                'status' => $status,
                'exception' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', $successMessage);
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return [
            Photographer::STATUS_PENDING,
            Photographer::STATUS_APPROVED,
            Photographer::STATUS_DECLINED,
            Photographer::STATUS_SUSPENDED,
        ];
    }
}
