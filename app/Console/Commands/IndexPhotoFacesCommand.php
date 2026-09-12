<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Photo;
use App\Services\FaceRecognitionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class IndexPhotoFacesCommand extends Command
{
    protected $signature = 'face-recognition:index-photo {photo : Photo ID or UUID}';

    protected $description = 'Index the faces in one published event photo with Amazon Rekognition.';

    public function handle(FaceRecognitionService $service): int
    {
        $identifier = (string) $this->argument('photo');
        $photo = Photo::with('event')
            ->where(function ($query) use ($identifier): void {
                $query->where('uuid', $identifier);

                if (ctype_digit($identifier)) {
                    $query->orWhereKey((int) $identifier);
                }
            })
            ->first();

        if (! $photo) {
            $this->error('Photo not found.');

            return self::FAILURE;
        }

        if ($photo->status !== Photo::STATUS_PUBLISHED || $photo->event?->status !== Event::STATUS_PUBLISHED) {
            $this->error('The photo and its event must both be published before face indexing.');

            return self::FAILURE;
        }

        try {
            $result = $service->indexPhoto($photo);
        } catch (Throwable $exception) {
            Log::error('Manual face indexing failed.', [
                'photo_uuid' => $photo->uuid,
                'event_uuid' => $photo->event->uuid,
                'exception' => $exception->getMessage(),
            ]);
            $this->error('Face indexing failed. Check the application log for details.');

            return self::FAILURE;
        }

        $this->table(['Photo UUID', 'Event UUID', 'Collection', 'Faces indexed'], [[
            $photo->uuid,
            $photo->event->uuid,
            $result['collection_id'],
            $result['faces_indexed'],
        ]]);

        return self::SUCCESS;
    }
}
