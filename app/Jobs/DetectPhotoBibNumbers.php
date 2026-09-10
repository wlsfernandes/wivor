<?php

namespace App\Jobs;

use App\Models\Photo;
use App\Services\BibNumberDetector;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class DetectPhotoBibNumbers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;

    public function __construct(public int $photoId) {}

    public function handle(BibNumberDetector $detector): void
    {
        $photo = Photo::find($this->photoId);

        if (! $photo || $photo->bib_scanned_at) {
            return;
        }

        $detector->scanAndSave($photo);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Bib-number detection failed.', [
            'photo_id' => $this->photoId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
