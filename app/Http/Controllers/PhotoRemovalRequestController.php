<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Photo;
use App\Models\PhotoRemovalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

/** Handles the public "Report or Request Removal" form shown on a photo preview. */
class PhotoRemovalRequestController extends Controller
{
    /** Display the general public removal-request form. */
    public function general(): View
    {
        return view('photos.removal-request-general', [
            'reasons' => PhotoRemovalRequest::reasons(),
            'contactEmail' => config('contact.email'),
        ]);
    }

    /** Submit a general removal request for administrator review without changing the photo. */
    public function storeGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'requester_name' => ['required', 'string', 'max:255'],
            'requester_email' => ['required', 'email', 'max:255'],
            'event' => ['required', 'string', 'max:255'],
            'photo_identifier' => ['required', 'string', 'max:2048'],
            'reason' => ['required', Rule::in(array_keys(PhotoRemovalRequest::reasons()))],
            'additional_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $photo = $this->findPhoto($validated['photo_identifier']);

        if (! $photo) {
            throw ValidationException::withMessages([
                'photo_identifier' => 'We could not locate that photo. Enter its full WivorPhotos URL, UUID, or eight-character reference number.',
            ]);
        }

        $explanation = 'Event provided: '.trim($validated['event']);
        if (filled($validated['additional_notes'] ?? null)) {
            $explanation .= "\n\nAdditional notes: ".trim($validated['additional_notes']);
        }

        try {
            DB::transaction(fn () => PhotoRemovalRequest::create([
                'photo_id' => $photo->id,
                'requester_name' => $validated['requester_name'],
                'requester_email' => $validated['requester_email'],
                'reason' => $validated['reason'],
                'explanation' => $explanation,
            ]));
        } catch (Throwable $exception) {
            Log::error('General photo removal request failed.', [
                'event' => 'photo_removal.store',
                'photo_id' => $photo->id,
                'exception' => $exception->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                'request' => 'Your request could not be submitted. Please try again or contact WivorPhotos.',
            ]);
        }

        return redirect()->route('photo-removal.create')
            ->with('success', 'Thank you. Your request has been submitted for administrative review.');
    }

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

    /** Locate a photo from a WivorPhotos URL, UUID, or displayed reference number. */
    private function findPhoto(string $identifier): ?Photo
    {
        if (preg_match('/[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}/i', $identifier, $matches)) {
            return Photo::where('uuid', strtolower($matches[0]))->first();
        }

        if (! preg_match('/^[0-9a-f]{8}$/i', trim($identifier))) {
            return null;
        }

        $photos = Photo::where('uuid', 'like', strtolower(trim($identifier)).'%')->limit(2)->get();

        return $photos->count() === 1 ? $photos->first() : null;
    }
}
