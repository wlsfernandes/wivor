<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\UploadBatch;
use App\Models\User;
use App\Services\FaceRecognitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class SearchEventFacesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_resolves_only_published_photos_from_the_selected_event(): void
    {
        Storage::fake('media');
        config(['photo_uploads.disk' => 'media']);
        $event = $this->event();
        $otherEvent = $this->event();
        $matchedPhoto = $this->photo($event, Photo::STATUS_PUBLISHED);
        $unpublishedPhoto = $this->photo($event, Photo::STATUS_READY);
        $otherEventPhoto = $this->photo($otherEvent, Photo::STATUS_PUBLISHED);
        $imagePath = $this->imagePath();
        $imageBytes = file_get_contents($imagePath);
        $collectionId = 'wivor-event-'.$event->uuid;
        $service = $this->createMock(FaceRecognitionService::class);
        $service->expects($this->once())
            ->method('searchEventByImage')
            ->with(
                $this->callback(fn (Event $candidate): bool => $candidate->is($event)),
                $imageBytes,
                90.0,
                50,
            )
            ->willReturn([
                'collection_id' => $collectionId,
                'face_detected' => true,
                'matches' => [
                    ['photo_uuid' => $matchedPhoto->uuid, 'similarity' => 97.843],
                    ['photo_uuid' => $unpublishedPhoto->uuid, 'similarity' => 99.2],
                    ['photo_uuid' => $otherEventPhoto->uuid, 'similarity' => 99.9],
                ],
            ]);
        $this->app->instance(FaceRecognitionService::class, $service);

        $exitCode = Artisan::call('face-recognition:search-event', [
            'event' => $event->uuid,
            'image' => $imagePath,
        ]);
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Event: '.$event->title, $output);
        $this->assertStringContainsString('Collection: '.$collectionId, $output);
        $this->assertStringContainsString('97.84%', $output);
        $this->assertStringContainsString($matchedPhoto->uuid, $output);
        $this->assertStringNotContainsString($unpublishedPhoto->uuid, $output);
        $this->assertStringNotContainsString($otherEventPhoto->uuid, $output);
        $this->assertStringContainsString('1 Wivor photo matched.', $output);
        $this->assertSame([], Storage::disk('media')->allFiles());
        $this->assertFileExists($imagePath);
        $this->assertSame($imageBytes, file_get_contents($imagePath));
    }

    public function test_command_reports_when_no_clear_face_is_detected(): void
    {
        $event = $this->event();
        $imagePath = $this->imagePath();
        $service = $this->createMock(FaceRecognitionService::class);
        $service->method('searchEventByImage')->willReturn([
            'collection_id' => 'wivor-event-'.$event->uuid,
            'face_detected' => false,
            'matches' => [],
        ]);
        $this->app->instance(FaceRecognitionService::class, $service);

        $exitCode = Artisan::call('face-recognition:search-event', [
            'event' => $event->id,
            'image' => $imagePath,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('No clear face was detected.', Artisan::output());
    }

    public function test_command_rejects_invalid_image_and_search_options_before_aws(): void
    {
        $event = $this->event();
        $textPath = tempnam(sys_get_temp_dir(), 'wivor-not-image-');
        file_put_contents($textPath, 'not an image');
        $this->beforeApplicationDestroyed(fn () => @unlink($textPath));
        $service = $this->createMock(FaceRecognitionService::class);
        $service->expects($this->never())->method('searchEventByImage');
        $this->app->instance(FaceRecognitionService::class, $service);

        $invalidImage = Artisan::call('face-recognition:search-event', [
            'event' => $event->uuid,
            'image' => $textPath,
        ]);
        $invalidThreshold = Artisan::call('face-recognition:search-event', [
            'event' => $event->uuid,
            'image' => $this->imagePath(),
            '--threshold' => 101,
        ]);
        $invalidMaximum = Artisan::call('face-recognition:search-event', [
            'event' => $event->uuid,
            'image' => $this->imagePath(),
            '--max' => 101,
        ]);

        $this->assertSame(1, $invalidImage);
        $this->assertSame(1, $invalidThreshold);
        $this->assertSame(1, $invalidMaximum);
    }

    public function test_aws_failure_is_controlled_and_changes_no_application_state(): void
    {
        $event = $this->event();
        $photo = $this->photo($event, Photo::STATUS_PUBLISHED);
        $imagePath = $this->imagePath();
        $imageBytes = file_get_contents($imagePath);
        $eventUpdatedAt = $event->updated_at;
        $photoUpdatedAt = $photo->updated_at;
        $service = $this->createMock(FaceRecognitionService::class);
        $service->method('searchEventByImage')->willThrowException(new RuntimeException('AWS unavailable.'));
        $this->app->instance(FaceRecognitionService::class, $service);

        $exitCode = Artisan::call('face-recognition:search-event', [
            'event' => $event->uuid,
            'image' => $imagePath,
            '--threshold' => 85,
            '--max' => 25,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Face search failed: AWS unavailable.', Artisan::output());
        $this->assertTrue($eventUpdatedAt->equalTo($event->fresh()->updated_at));
        $this->assertTrue($photoUpdatedAt->equalTo($photo->fresh()->updated_at));
        $this->assertSame(Photo::STATUS_PUBLISHED, $photo->fresh()->status);
        $this->assertFileExists($imagePath);
        $this->assertSame($imageBytes, file_get_contents($imagePath));
    }

    private function event(): Event
    {
        return Event::create([
            'title' => 'Face Search Event '.uniqid(),
            'slug' => 'face-search-event-'.uniqid(),
            'content' => '',
            'status' => Event::STATUS_PUBLISHED,
            'published' => true,
            'date_of_event' => now(),
        ]);
    }

    private function photo(Event $event, string $status): Photo
    {
        $user = User::factory()->create();
        $photographer = Photographer::create([
            'user_id' => $user->id,
            'first_name' => 'Search',
            'last_name' => 'Tester',
        ]);
        $assignment = EventAssignment::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'status' => 'approved',
        ]);
        $batch = UploadBatch::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'assignment_id' => $assignment->id,
            'selected_count' => 1,
        ]);

        return Photo::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'assignment_id' => $assignment->id,
            'upload_batch_id' => $batch->id,
            'original_filename' => 'face-search.jpg',
            'original_key' => 'events/test/face-search/original.jpg',
            'status' => $status,
            'processed_at' => now(),
            'published_at' => $status === Photo::STATUS_PUBLISHED ? now() : null,
        ]);
    }

    private function imagePath(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'wivor-selfie-');
        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2hZ4AAAAASUVORK5CYII=',
        ));
        $this->beforeApplicationDestroyed(fn () => @unlink($path));

        return $path;
    }
}
