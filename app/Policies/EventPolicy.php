<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

/** Authorizes administrative and photographer Event management. */
class EventPolicy
{
    /** Allow platform administrators to manage every event. */
    public function before(User $user): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    /** Determine whether the user can open the event management list. */
    public function viewAny(User $user): bool
    {
        return $user->canAccessPhotographerArea();
    }

    /** Determine whether the user can create an event. */
    public function create(User $user): bool
    {
        return $user->canAccessPhotographerArea();
    }

    /** Determine whether the user owns and can update the event. */
    public function update(User $user, Event $event): bool
    {
        return $user->canAccessPhotographerArea() && $event->photographers()
            ->where('photographers.user_id', $user->id)
            ->exists();
    }

    /** Determine whether the user can archive the event. */
    public function delete(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }
}
