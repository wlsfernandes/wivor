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
}
