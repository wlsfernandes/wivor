<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Photographer;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class PhotographerUploadAccess
{
    /** Resolve and authorize the authenticated photographer's approved event assignment. */
    public function assignment(User $user, Event $event, bool $requireOpenDeadline = false): EventAssignment
    {
        $photographer = $user->photographer;

        if (! $user->hasVerifiedEmail() || ! $photographer instanceof Photographer || ! $photographer->isApproved()) {
            throw new AuthorizationException('Your photographer account is not approved for uploads.');
        }

        $assignment = EventAssignment::query()
            ->where('event_id', $event->id)
            ->where('photographer_id', $photographer->id)
            ->first();

        if (! $assignment || $assignment->status !== 'approved') {
            throw new AuthorizationException('You do not have an approved assignment for this event.');
        }

        if (in_array($event->status, [Event::STATUS_ARCHIVED, Event::STATUS_CANCELLED], true)) {
            throw new AuthorizationException('Uploads are unavailable for this event.');
        }

        if ($requireOpenDeadline && $event->uploadDeadlineFor($photographer)->isPast()) {
            throw ValidationException::withMessages([
                'files' => 'Upload deadline passed. Contact WivorPhotos if an extension is required.',
            ]);
        }

        return $assignment;
    }
}
