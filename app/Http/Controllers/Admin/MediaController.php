<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPhoto;
use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\MediaActivityLog;
use App\Models\MediaRetentionHold;
use App\Models\Photo;
use App\Models\Photographer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(): View
    {
        $events = Event::withCount([
            'photos',
            'photos as processing_count' => fn ($query) => $query->where('status', Photo::STATUS_PROCESSING),
            'photos as ready_count' => fn ($query) => $query->where('status', Photo::STATUS_READY),
            'photos as rejected_count' => fn ($query) => $query->where('status', Photo::STATUS_REJECTED),
            'photos as published_count' => fn ($query) => $query->where('status', Photo::STATUS_PUBLISHED),
            'photos as removed_count' => fn ($query) => $query->where('status', Photo::STATUS_REMOVED),
        ])->latest('date_of_event')->paginate(30);

        return view('admin.media.index', compact('events'));
    }

    public function show(Event $event): View
    {
        $assignments = EventAssignment::with('photographer.user')->where('event_id', $event->id)->get();
        $photos = $event->photos()->with('photographer')->latest()->paginate(100);
        $holds = MediaRetentionHold::where('event_id', $event->id)->whereNull('released_at')->latest()->get();
        $deletionFailures = MediaActivityLog::where('event_id', $event->id)->where('action', 'deletion_failed')->latest()->limit(20)->get();

        return view('admin.media.show', compact('event', 'assignments', 'photos', 'holds', 'deletionFailures'));
    }

    public function extendDeadline(Request $request, Event $event, Photographer $photographer): RedirectResponse
    {
        $validated = $request->validate(['upload_deadline_at' => ['required', 'date', 'after:now']]);
        $assignment = EventAssignment::where('event_id', $event->id)->where('photographer_id', $photographer->id)->firstOrFail();
        $assignment->update($validated);
        MediaActivityLog::create(['event_id' => $event->id, 'actor_id' => $request->user()->id, 'action' => 'upload_deadline_extended', 'details' => ['photographer_id' => $photographer->id]]);
        return back()->with('success', 'Upload deadline extended.');
    }

    public function retry(Request $request, Event $event, Photo $photo): RedirectResponse
    {
        $this->assertEventPhoto($event, $photo);
        abort_unless($photo->rejection_code === 'processing_failed' && Storage::disk(config('photo_uploads.disk'))->exists($photo->original_key), 409, 'This upload cannot be reprocessed.');
        $photo->update(['status' => Photo::STATUS_PROCESSING, 'rejection_code' => null, 'rejection_reason' => null, 'processing_started_at' => now()]);
        MediaActivityLog::create(['event_id' => $event->id, 'photo_id' => $photo->id, 'actor_id' => $request->user()->id, 'action' => 'processing_retried']);
        ProcessPhoto::dispatch($photo->id);
        return back()->with('success', 'Photo processing queued again.');
    }

    public function unpublish(Request $request, Event $event, Photo $photo): RedirectResponse
    {
        $this->assertEventPhoto($event, $photo);
        abort_unless($photo->status === Photo::STATUS_PUBLISHED, 409);
        $photo->update(['status' => Photo::STATUS_READY, 'published_at' => null]);
        MediaActivityLog::create(['event_id' => $event->id, 'photo_id' => $photo->id, 'actor_id' => $request->user()->id, 'action' => 'unpublished']);
        return back()->with('success', 'Photo unpublished.');
    }

    public function remove(Request $request, Event $event, Photo $photo): RedirectResponse
    {
        $this->assertEventPhoto($event, $photo);
        $validated = $request->validate(['reason' => ['required', 'string', 'max:500']]);
        Storage::disk(config('photo_uploads.disk'))->delete(array_filter([$photo->original_key, $photo->preview_key, $photo->thumbnail_key]));
        $photo->update(['status' => Photo::STATUS_REMOVED, 'deleted_at' => now(), 'deletion_reason' => $validated['reason']]);
        MediaActivityLog::create(['event_id' => $event->id, 'photo_id' => $photo->id, 'actor_id' => $request->user()->id, 'action' => 'admin_removed', 'details' => ['reason' => $validated['reason']]]);
        return back()->with('success', 'Photo media removed; its tombstone was retained.');
    }

    public function closeGallery(Request $request, Event $event): RedirectResponse
    {
        $event->update(['sales_close_at' => now()]);
        $event->photos()->where('status', Photo::STATUS_PUBLISHED)->where('sale_count', 0)->update(['expires_at' => now()]);
        MediaActivityLog::create(['event_id' => $event->id, 'actor_id' => $request->user()->id, 'action' => 'gallery_closed']);
        return back()->with('success', 'Gallery closed. Eligible media will be removed by the retention task.');
    }

    public function hold(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'photo_id' => ['nullable', Rule::exists('photos', 'id')->where('event_id', $event->id)],
            'reason' => ['required', 'string', 'max:1000'], 'review_at' => ['nullable', 'date', 'after:now'],
        ]);
        MediaRetentionHold::create($validated + ['event_id' => $event->id, 'created_by' => $request->user()->id]);
        MediaActivityLog::create(['event_id' => $event->id, 'photo_id' => $validated['photo_id'] ?? null, 'actor_id' => $request->user()->id, 'action' => 'retention_hold_created']);
        return back()->with('success', 'Retention hold added.');
    }

    public function releaseHold(Request $request, Event $event, MediaRetentionHold $hold): RedirectResponse
    {
        abort_unless($hold->event_id === $event->id && ! $hold->released_at, 404);
        $hold->update(['released_at' => now(), 'released_by' => $request->user()->id]);
        MediaActivityLog::create(['event_id' => $event->id, 'photo_id' => $hold->photo_id, 'actor_id' => $request->user()->id, 'action' => 'retention_hold_released']);
        return back()->with('success', 'Retention hold released.');
    }

    private function assertEventPhoto(Event $event, Photo $photo): void
    {
        abort_unless($photo->event_id === $event->id, 404);
    }
}
