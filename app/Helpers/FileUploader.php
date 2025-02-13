<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploader
{
    public static function uploadImageToS3Storage($file, $directory = 'img/')
    {
        // Generate a unique file name
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $directory . $fileName;

        // Upload the file to S3
        Storage::disk('s3')->put($path, file_get_contents($file));

        // Get the full URL of the stored file
        return Storage::disk('s3')->url($path);
    }

    public static function uploadPDFToS3Storage($file, $directory = 'blog/')
    {
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $directory . $fileName;
        Storage::disk('s3')->put($path, file_get_contents($file));
        return Storage::disk('s3')->url($path);
    }

}
