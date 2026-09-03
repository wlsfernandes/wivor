<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Photo;
use App\Models\PhotoRemovalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Handles the public "Report or Request Removal" form shown on a photo preview. */
class PhotoRemovalRequestController extends Controller
{
    /** Display the removal-request form for one published photo. */
    public function create(Event $event, Photo $photo): View
    {
        abort_unless($photo->event_id === $event->id && $photo->status === Photo::STATUS_PUBLISHED, 404);

        return view('photos.removal-request', [
            'event' => $event,
            'photo' => $photo,
            'reasons' => PhotoRemovalRequest::reasons(),
            'layout' => 'layouts.app',
        ]);
    }

    /** Submit a removal request for administrator review. */
    public function store(Request $request, Event $event, Photo $photo): RedirectResponse
    {
        abort_unless($photo->event_id === $event->id, 404);

        $validated = $request->validate([
            'requester_name' => ['required', 'string', 'max:255'],
            'requester_email' => ['required', 'email', 'max:255'],
            'reason' => ['required', Rule::in(array_keys(PhotoRemovalRequest::reasons()))],
            'explanation' => ['nullable', 'string', 'max:2000'],
        ]);

        PhotoRemovalRequest::create($validated + ['photo_id' => $photo->id]);

        return redirect()->route('events.photos.show', ['event' => $event->slug, 'photo' => $photo])
            ->with('success', 'Thank you. Your request has been sent to WivorPhotos for review.');
    }
}
