<?php

namespace App\Http\Requests;

use App\Models\Event;

/** Validates updates to an existing Wivor sports event. */
class UpdateEventRequest extends StoreEventRequest
{
    /** Allow administrators or the assigned photographer to update the event. */
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event instanceof Event
            && ($this->user()?->can('update', $event) ?? false);
    }
}
