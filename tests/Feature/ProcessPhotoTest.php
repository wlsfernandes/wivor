<?php

namespace Tests\Feature;

use App\Jobs\ProcessPhoto;
use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\UploadBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessPhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_real_jpeg_is_validated_and_private_derivatives_are_created(): void
    {
        Storage::fake('media');
        config(['photo_uploads.disk' => 'media']);
        [$photo] = $this->processingPhoto();

        $image = imagecreatetruecolor(2400, 1200);
        $path = tempnam(sys_get_temp_dir(), 'valid-jpeg-');
        imagejpeg($image, $path, 88);
        imagedestroy($image);
        Storage::disk('media')->put($photo->original_key, fopen($path, 'rb'));
        unlink($path);

        (new ProcessPhoto($photo->id))->handle();

        $photo->refresh();
        $this->assertSame(Photo::STATUS_READY, $photo->status);
        $this->assertSame('image/jpeg', $photo->detected_mime);
        $this->assertSame(2400, $photo->width);
        Storage::disk('media')->assertExists($photo->preview_key);
        Storage::disk('media')->assertExists($photo->thumbnail_key);
        Storage::disk('media')->assertExists($photo->original_key);
    }

    public function test_renamed_non_jpeg_is_rejected_and_deleted(): void
    {
        Storage::fake('media');
        config(['photo_uploads.disk' => 'media']);
        [$photo] = $this->processingPhoto();
        Storage::disk('media')->put($photo->original_key, 'not a jpeg');

        (new ProcessPhoto($photo->id))->handle();

        $this->assertSame(Photo::STATUS_REJECTED, $photo->fresh()->status);
        $this->assertSame('unsupported_format', $photo->fresh()->rejection_code);
        Storage::disk('media')->assertMissing($photo->original_key);
    }

    private function processingPhoto(): array
    {
        $user = User::factory()->create();
        $photographer = Photographer::create(['user_id' => $user->id, 'first_name' => 'Sam', 'last_name' => 'Cole']);
        $event = Event::create(['title' => 'Road Race', 'slug' => 'road-race-'.uniqid(), 'content' => '', 'date_of_event' => now()]);
        $assignment = EventAssignment::create(['event_id' => $event->id, 'photographer_id' => $photographer->id, 'status' => 'approved']);
        $batch = UploadBatch::create(['event_id' => $event->id, 'photographer_id' => $photographer->id, 'assignment_id' => $assignment->id, 'selected_count' => 1]);
        $photo = Photo::create([
            'event_id' => $event->id, 'photographer_id' => $photographer->id, 'assignment_id' => $assignment->id,
            'upload_batch_id' => $batch->id, 'original_filename' => 'photo.jpg', 'original_key' => 'original.jpg',
            'status' => Photo::STATUS_PROCESSING,
        ]);

        return [$photo];
    }
}
