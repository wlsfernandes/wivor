<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPhoto;
use App\Models\Event;
use App\Models\MediaActivityLog;
use App\Models\Photo;
use App\Models\UploadBatch;
use App\Services\PhotographerUploadAccess;
use App\Services\PhotoStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PhotographerUploadController extends Controller
{
    public function __construct(private PhotographerUploadAccess $access, private PhotoStorage $storage) {}

    public function show(Request $request, Event $event): View
    {
        $assignment = $this->access->assignment($request->user(), $event);
        $photographer = $request->user()->photographer;
        $photos = Photo::where('event_id', $event->id)
            ->where('photographer_id', $photographer->id)
            ->latest()->paginate(60);
        $counts = Photo::where('event_id', $event->id)->where('photographer_id', $photographer->id)
            ->selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');
        $acceptedCount = Photo::where('event_id', $event->id)->where('photographer_id', $photographer->id)
            ->whereNotIn('status', [Photo::STATUS_REJECTED, Photo::STATUS_REMOVED])->count();
        $deadline = $event->uploadDeadlineFor($photographer);
        $galleryStatus = match (true) {
            $event->sales_close_at?->isPast() => 'Sales closed',
            ! $event->gallery_published_at => 'Not published',
            (int) ($counts[Photo::STATUS_READY] ?? 0) > 0 => 'Partially published',
            default => 'Published',
        };

        return view('photographers.upload', [
            'event' => $event, 'assignment' => $assignment, 'photos' => $photos, 'counts' => $counts,
            'acceptedCount' => $acceptedCount,
            'remainingCount' => max(0, config('photo_uploads.max_event_photos') - $acceptedCount),
            'deadline' => $deadline, 'uploadOpen' => $deadline->isFuture(),
            'galleryStatus' => $galleryStatus,
            'scheduledDeletionCount' => Photo::where('event_id', $event->id)->where('photographer_id', $photographer->id)
                ->where('status', Photo::STATUS_PUBLISHED)->where('sale_count', 0)->count(),
            'rules' => config('photo_uploads'),
        ]);
    }

    public function createBatch(Request $request, Event $event): JsonResponse
    {
        $assignment = $this->access->assignment($request->user(), $event, true);
        $maxBatch = config('photo_uploads.max_batch_size');
        $validated = $request->validate([
            'rights_confirmed' => ['required', 'accepted'],
            'files' => ['required', 'array', 'min:1', "max:{$maxBatch}"],
            'files.*.name' => ['required', 'string', 'max:255', 'regex:/\.(jpe?g)$/i'],
            'files.*.size' => ['required', 'integer', 'min:1', 'max:'.config('photo_uploads.max_file_bytes')],
            'files.*.type' => ['nullable', 'string', 'in:image/jpeg'],
        ], [
            'files.*.name.regex' => 'Unsupported format. Upload a JPG or JPEG file.',
            'files.*.size.max' => 'File is larger than 40 MB. Export a smaller JPEG and try again.',
            'files.max' => "Select no more than {$maxBatch} photos per batch.",
        ]);
        $photographer = $request->user()->photographer;

        [$batch, $photos] = DB::transaction(function () use ($validated, $event, $photographer, $assignment): array {
            $lockedAssignment = $assignment->newQuery()->lockForUpdate()->findOrFail($assignment->id);
            $currentCount = Photo::where('event_id', $event->id)->where('photographer_id', $photographer->id)
                ->whereNotIn('status', [Photo::STATUS_REJECTED, Photo::STATUS_REMOVED])->count();
            if ($currentCount + count($validated['files']) > config('photo_uploads.max_event_photos')) {
                throw ValidationException::withMessages(['files' => 'Event photo limit reached. No additional photos can be uploaded.']);
            }

            if (! $lockedAssignment->rights_confirmed_at) {
                $lockedAssignment->update(['rights_confirmed_at' => now()]);
            }
            $batch = UploadBatch::create([
                'event_id' => $event->id, 'photographer_id' => $photographer->id,
                'assignment_id' => $assignment->id, 'selected_count' => count($validated['files']),
                'status' => 'in_progress', 'started_at' => now(),
            ]);
            $photos = collect($validated['files'])->map(function (array $file) use ($batch, $event, $photographer, $assignment): Photo {
                $uuid = (string) Str::uuid();
                return Photo::create([
                    'uuid' => $uuid, 'event_id' => $event->id, 'photographer_id' => $photographer->id,
                    'assignment_id' => $assignment->id, 'upload_batch_id' => $batch->id,
                    'original_filename' => basename(str_replace('\\', '/', $file['name'])),
                    'file_size' => $file['size'],
                    'original_key' => "events/{$event->uuid}/photographers/{$photographer->uuid}/photos/{$uuid}/original.jpg",
                ]);
            });
            return [$batch, $photos];
        });

        return response()->json([
            'batch' => $batch->uuid,
            'photos' => $photos->map(fn (Photo $photo) => $this->uploadPayload($photo))->values(),
        ], 201);
    }

    public function retryUrl(Request $request, Event $event, Photo $photo): JsonResponse
    {
        $this->access->assignment($request->user(), $event, true);
        $this->assertOwned($request, $event, $photo);
        abort_unless(in_array($photo->status, [Photo::STATUS_QUEUED, Photo::STATUS_UPLOADING], true), 409, 'This photo is not waiting for upload.');
        return response()->json($this->uploadPayload($photo));
    }

    public function complete(Request $request, Event $event, Photo $photo): JsonResponse
    {
        $this->access->assignment($request->user(), $event, true);
        $this->assertOwned($request, $event, $photo);
        abort_unless(in_array($photo->status, [Photo::STATUS_QUEUED, Photo::STATUS_UPLOADING], true), 409, 'This upload was already completed.');

        $disk = Storage::disk(config('photo_uploads.disk'));
        try {
            abort_unless($disk->exists($photo->original_key), 422, 'The upload did not reach storage. Retry the file.');
            if ($disk->size($photo->original_key) > config('photo_uploads.max_file_bytes')) {
                $disk->delete($photo->original_key);
                throw ValidationException::withMessages(['file' => 'File is larger than 40 MB. Export a smaller JPEG and try again.']);
            }
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::warning('Could not confirm direct photo upload.', ['photo_id' => $photo->id, 'exception' => $exception->getMessage()]);
            throw ValidationException::withMessages(['file' => 'The upload could not be confirmed. Wait a moment and retry.']);
        }

        $photo->update(['status' => Photo::STATUS_PROCESSING, 'uploaded_at' => now(), 'processing_started_at' => now()]);
        MediaActivityLog::create(['event_id' => $event->id, 'photo_id' => $photo->id, 'actor_id' => $request->user()->id, 'action' => 'upload_completed']);
        ProcessPhoto::dispatch($photo->id);

        return response()->json(['status' => Photo::STATUS_PROCESSING], 202);
    }

    public function statuses(Request $request, Event $event): JsonResponse
    {
        $this->access->assignment($request->user(), $event);
        $photos = Photo::where('event_id', $event->id)->where('photographer_id', $request->user()->photographer->id)
            ->latest()->limit(config('photo_uploads.max_event_photos'))->get();
        return response()->json([
            'photos' => $photos->map(fn (Photo $photo) => $this->photoPayload($photo)),
            'counts' => $photos->countBy('status'),
        ]);
    }

    public function publish(Request $request, Event $event): RedirectResponse
    {
        $this->access->assignment($request->user(), $event);
        $validated = $request->validate(['photo_ids' => ['nullable', 'array'], 'photo_ids.*' => ['uuid']]);
        $query = Photo::where('event_id', $event->id)->where('photographer_id', $request->user()->photographer->id)
            ->where('status', Photo::STATUS_READY);
        if (! empty($validated['photo_ids'])) {
            $query->whereIn('uuid', $validated['photo_ids']);
        }
        $photos = $query->get();
        if ($photos->isEmpty()) {
            return back()->withErrors(['photos' => 'Select at least one ready photo to publish.']);
        }

        DB::transaction(function () use ($photos, $event, $request): void {
            if (! $event->gallery_published_at) {
                $event->update([
                    'gallery_published_at' => now(),
                    'sales_close_at' => now()->addDays(config('photo_uploads.sales_window_days')),
                ]);
                $event->refresh();
            }
            foreach ($photos as $photo) {
                $photo->update(['status' => Photo::STATUS_PUBLISHED, 'published_at' => now(), 'expires_at' => $event->sales_close_at]);
                MediaActivityLog::create(['event_id' => $event->id, 'photo_id' => $photo->id, 'actor_id' => $request->user()->id, 'action' => 'published']);
            }
        });
        $photos->pluck('upload_batch_id')->unique()->each(fn ($id) => UploadBatch::find($id)?->recalculate());
        return back()->with('success', $photos->count().' photo(s) published.');
    }

    public function destroy(Request $request, Event $event, Photo $photo): RedirectResponse|JsonResponse
    {
        $this->access->assignment($request->user(), $event);
        $this->assertOwned($request, $event, $photo);
        abort_unless($photo->sale_count === 0 && in_array($photo->status, [Photo::STATUS_QUEUED, Photo::STATUS_UPLOADING, Photo::STATUS_READY, Photo::STATUS_REJECTED], true), 409, 'Only queued or unpublished unsold photos can be removed.');
        Storage::disk(config('photo_uploads.disk'))->delete(array_filter([$photo->original_key, $photo->preview_key, $photo->thumbnail_key]));
        $photo->update(['status' => Photo::STATUS_REMOVED, 'deleted_at' => now(), 'deletion_reason' => 'photographer_removal']);
        MediaActivityLog::create(['event_id' => $event->id, 'photo_id' => $photo->id, 'actor_id' => $request->user()->id, 'action' => 'removed']);
        $photo->batch->recalculate();
        return $request->expectsJson() ? response()->json(['status' => Photo::STATUS_REMOVED]) : back()->with('success', 'Photo removed.');
    }

    private function uploadPayload(Photo $photo): array
    {
        try {
            $upload = $this->storage->uploadUrl($photo->original_key);
            return ['id' => $photo->uuid, 'name' => $photo->original_filename, 'url' => $upload['url'], 'headers' => $upload['headers'] ?? []];
        } catch (Throwable $exception) {
            Log::error('Could not issue a direct upload URL.', ['photo_id' => $photo->id, 'exception' => $exception->getMessage()]);
            return ['id' => $photo->uuid, 'name' => $photo->original_filename, 'error' => 'Upload permission could not be created. Retry in a moment.'];
        }
    }

    private function photoPayload(Photo $photo): array
    {
        return [
            'id' => $photo->uuid, 'name' => $photo->original_filename, 'status' => $photo->status,
            'reason' => $photo->rejection_reason,
            'thumbnail_url' => in_array($photo->status, [Photo::STATUS_READY, Photo::STATUS_PUBLISHED], true)
                ? route('photographer.uploads.preview', [$photo->event_id, $photo]) : null,
        ];
    }

    private function assertOwned(Request $request, Event $event, Photo $photo): void
    {
        abort_unless($photo->event_id === $event->id && $photo->photographer_id === $request->user()->photographer->id, 404);
    }
}
