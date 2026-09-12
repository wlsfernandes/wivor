<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Photo;
use App\Services\FaceRecognitionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class IndexPhotoFaces implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 180;

    public function __construct(public int $photoId) {}

    public function handle(FaceRecognitionService $service): void
    {
        $photo = Photo::with('event')->find($this->photoId);

        if (! $photo
            || $photo->status !== Photo::STATUS_PUBLISHED
            || ! $photo->event
            || $photo->event->status !== Event::STATUS_PUBLISHED) {
            return;
        }

        $result = $service->indexPhoto($photo);

        Log::info('Face indexing completed.', [
            'photo_uuid' => $photo->uuid,
            'event_uuid' => $photo->event->uuid,
            'faces_indexed' => $result['faces_indexed'],
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Face indexing failed.', [
            'photo_id' => $this->photoId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
