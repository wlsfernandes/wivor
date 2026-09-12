<?php

namespace App\Jobs;

use App\Models\Photo;
use App\Services\FaceRecognitionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeletePhotoFaces implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 180;

    public function __construct(public int $photoId) {}

    public function handle(FaceRecognitionService $service): void
    {
        $photo = Photo::with('event')->find($this->photoId);

        if (! $photo || ! $photo->event) {
            return;
        }

        $result = $service->deletePhotoFaces($photo);

        Log::info($result['faces_deleted'] > 0
            ? 'Face index cleanup completed.'
            : 'No indexed faces found for photo.', [
                'photo_uuid' => $photo->uuid,
                'event_uuid' => $photo->event->uuid,
                'faces_deleted' => $result['faces_deleted'],
            ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Face index cleanup failed.', [
            'photo_id' => $this->photoId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
