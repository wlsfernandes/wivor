<?php

namespace App\Jobs;

use App\Models\MediaActivityLog;
use App\Models\Photo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProcessPhoto implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;

    public function __construct(public int $photoId) {}

    public function handle(): void
    {
        $photo = Photo::findOrFail($this->photoId);
        if ($photo->status !== Photo::STATUS_PROCESSING) {
            return;
        }

        $disk = Storage::disk(config('photo_uploads.disk'));
        $temporaryPath = tempnam(sys_get_temp_dir(), 'wivor-photo-');
        if ($temporaryPath === false) {
            throw new RuntimeException('Unable to create a processing file.');
        }

        try {
            $input = $disk->readStream($photo->original_key);
            $output = fopen($temporaryPath, 'wb');
            if (! is_resource($input) || ! is_resource($output)) {
                throw new RuntimeException('Unable to open the uploaded object.');
            }
            stream_copy_to_stream($input, $output);
            fclose($input);
            fclose($output);

            $this->validateAndProcess($photo, $temporaryPath);
        } finally {
            @unlink($temporaryPath);
        }
    }

    private function validateAndProcess(Photo $photo, string $path): void
    {
        $maxBytes = config('photo_uploads.max_file_bytes');
        $size = filesize($path);
        if ($size === false || $size > $maxBytes) {
            $this->reject($photo, 'file_too_large', 'File is larger than 40 MB. Export a smaller JPEG and try again.');
            return;
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        $imageInfo = @getimagesize($path);
        if ($mime !== 'image/jpeg' || ($imageInfo[2] ?? null) !== IMAGETYPE_JPEG) {
            $this->reject($photo, 'unsupported_format', 'Unsupported format. Upload a JPG or JPEG file.');
            return;
        }

        if (! $imageInfo || ! ($image = @imagecreatefromjpeg($path))) {
            $this->reject($photo, 'corrupt', 'File is corrupted or cannot be read. Export the original again.');
            return;
        }

        [$width, $height] = $imageInfo;
        if (max($width, $height) < config('photo_uploads.min_longest_side')) {
            imagedestroy($image);
            $this->reject($photo, 'too_small', 'Image is too small. The longest side must be at least 2,400 pixels.');
            return;
        }
        if ($width > config('photo_uploads.max_side') || $height > config('photo_uploads.max_side')) {
            imagedestroy($image);
            $this->reject($photo, 'dimensions_too_large', 'Image dimensions are too large. Neither side may exceed 12,000 pixels.');
            return;
        }
        if (($imageInfo['channels'] ?? 3) !== 3) {
            imagedestroy($image);
            $this->reject($photo, 'unsupported_color_mode', 'Unsupported color mode. Convert the image to RGB/sRGB.');
            return;
        }

        $checksum = hash_file('sha256', $path);
        if (Photo::where('event_id', $photo->event_id)
            ->where('photographer_id', $photo->photographer_id)
            ->where('checksum', $checksum)
            ->whereKeyNot($photo->id)
            ->exists()) {
            imagedestroy($image);
            $this->reject($photo, 'duplicate', 'Duplicate photo. This exact file was already uploaded to this event.');
            return;
        }

        $image = $this->orient($image, $path);
        $base = "events/{$photo->event->uuid}/photographers/{$photo->photographer->uuid}/photos/{$photo->uuid}";
        $previewKey = "{$base}/preview.jpg";
        $thumbnailKey = "{$base}/thumbnail.jpg";

        try {
            $this->writeDerivative($image, $previewKey, config('photo_uploads.preview_max_side'), config('photo_uploads.preview_quality'));
            $this->writeDerivative($image, $thumbnailKey, config('photo_uploads.thumbnail_max_side'), config('photo_uploads.thumbnail_quality'));
        } finally {
            imagedestroy($image);
        }

        $photo->update([
            'preview_key' => $previewKey,
            'thumbnail_key' => $thumbnailKey,
            'detected_mime' => $mime,
            'file_size' => $size,
            'width' => $width,
            'height' => $height,
            'color_mode' => 'RGB',
            'checksum' => $checksum,
            'status' => Photo::STATUS_READY,
            'processed_at' => now(),
            'rejection_code' => null,
            'rejection_reason' => null,
        ]);

        MediaActivityLog::create(['event_id' => $photo->event_id, 'photo_id' => $photo->id, 'action' => 'processing_ready']);
        $photo->batch->recalculate();
    }

    private function orient(\GdImage $image, string $path): \GdImage
    {
        $exif = function_exists('exif_read_data') ? @exif_read_data($path) : false;
        $orientation = is_array($exif) ? ($exif['Orientation'] ?? 1) : 1;
        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
        if ($rotated !== $image) {
            imagedestroy($image);
        }
        return $rotated;
    }

    private function writeDerivative(\GdImage $source, string $key, int $maxSide, int $quality): void
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $maxSide / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $derived = imagescale($source, $targetWidth, $targetHeight, IMG_BICUBIC_FIXED);
        if (! $derived instanceof \GdImage) {
            throw new RuntimeException('Image resize failed.');
        }

        $this->applyWatermark($derived);
        $temporary = tempnam(sys_get_temp_dir(), 'wivor-derived-');
        try {
            if ($temporary === false || ! imagejpeg($derived, $temporary, $quality)) {
                throw new RuntimeException('JPEG encoding failed.');
            }
            $stream = fopen($temporary, 'rb');
            $stored = is_resource($stream) && Storage::disk(config('photo_uploads.disk'))->put($key, $stream, [
                'visibility' => 'private', 'ContentType' => 'image/jpeg',
            ]);
            if (is_resource($stream)) {
                fclose($stream);
            }
            if (! $stored) {
                throw new RuntimeException('Derived image storage failed.');
            }
        } finally {
            imagedestroy($derived);
            if (is_string($temporary)) {
                @unlink($temporary);
            }
        }
    }

    private function applyWatermark(\GdImage $image): void
    {
        $label = 'WivorPhotos';
        $font = 5;
        $labelWidth = imagefontwidth($font) * strlen($label);
        $labelHeight = imagefontheight($font);
        $gapX = max(140, $labelWidth + 60);
        $gapY = max(100, $labelHeight + 60);
        $white = imagecolorallocatealpha($image, 255, 255, 255, 62);
        $shadow = imagecolorallocatealpha($image, 0, 0, 0, 75);

        for ($y = 30; $y < imagesy($image); $y += $gapY) {
            for ($x = 20 + (($y / $gapY) % 2 ? (int) ($gapX / 2) : 0); $x < imagesx($image); $x += $gapX) {
                imagestring($image, $font, $x + 1, $y + 1, $label, $shadow);
                imagestring($image, $font, $x, $y, $label, $white);
            }
        }
    }

    private function reject(Photo $photo, string $code, string $message): void
    {
        Storage::disk(config('photo_uploads.disk'))->delete(array_filter([$photo->original_key, $photo->preview_key, $photo->thumbnail_key]));
        $photo->update([
            'status' => Photo::STATUS_REJECTED,
            'rejection_code' => $code,
            'rejection_reason' => $message,
            'processed_at' => now(),
            'deleted_at' => now(),
            'deletion_reason' => 'rejected_upload',
        ]);
        MediaActivityLog::create(['event_id' => $photo->event_id, 'photo_id' => $photo->id, 'action' => 'processing_rejected', 'details' => ['code' => $code]]);
        $photo->batch->recalculate();
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Photo processing failed.', ['photo_id' => $this->photoId, 'exception' => $exception->getMessage()]);
        $photo = Photo::find($this->photoId);
        if ($photo && $photo->status === Photo::STATUS_PROCESSING) {
            $photo->update([
                'status' => Photo::STATUS_REJECTED,
                'rejection_code' => 'processing_failed',
                'rejection_reason' => 'WivorPhotos could not process this file. Export the original again and retry.',
                'processed_at' => now(),
            ]);
            $photo->batch->recalculate();
        }
    }
}
