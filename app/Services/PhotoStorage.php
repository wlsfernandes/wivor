<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class PhotoStorage
{
    public function uploadUrl(string $key): array
    {
        return Storage::disk(config('photo_uploads.disk'))->temporaryUploadUrl(
            $key,
            now()->addMinutes(config('photo_uploads.upload_url_minutes')),
            ['ContentType' => 'image/jpeg']
        );
    }

    public function deliveryUrl(string $key): string
    {
        return Storage::disk(config('photo_uploads.disk'))->temporaryUrl(
            $key,
            now()->addMinutes(config('photo_uploads.delivery_url_minutes')),
            ['ResponseContentType' => 'image/jpeg']
        );
    }

    /** Signed URL that makes the browser save the file under its original name instead of displaying it. */
    public function downloadUrl(string $key, string $filename): string
    {
        $safeFilename = str_replace(['"', '\\', "\r", "\n"], '', $filename);

        return Storage::disk(config('photo_uploads.disk'))->temporaryUrl(
            $key,
            now()->addMinutes(config('photo_uploads.delivery_url_minutes')),
            ['ResponseContentType' => 'image/jpeg', 'ResponseContentDisposition' => 'attachment; filename="'.$safeFilename.'"']
        );
    }
}
