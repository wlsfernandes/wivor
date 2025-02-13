<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class StorageS3
{
    /**
     * Upload a file to S3 and return its URL.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string|null
     */
    public static function uploadToS3($file)
    {
        try {
            if ($file->isValid()) {

                // Generate a unique ID for the file
                $uniqueId = Str::uuid()->toString();
                // Get the original file extension
                $extension = $file->getClientOriginalExtension();
                // Construct the file name
                $fileName = $uniqueId . '.' . $extension;

                // Save the file to S3
                Storage::disk('s3')->put($fileName, file_get_contents($file));

                // Return the file's public URL
                return Storage::cloud()->url($fileName);

            } else {
                throw new Exception('Invalid file upload.');

            }

        } catch (Exception $e) {
            // Log the error (optional)
            Log::error('S3 Upload Error: ' . $e->getMessage());

            // Throw the exception to be handled by the controller
            throw $e;
        }
    }
}
