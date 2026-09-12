<?php

namespace Tests\Feature;

use App\Jobs\IndexPhotoFaces;
use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\UploadBatch;
use App\Models\User;
use App\Services\FaceRecognitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

class IndexPhotoFacesJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_indexes_a_published_photo_and_logs_only_operational_results(): void
    {
        $photo = $this->photo(Photo::STATUS_PUBLISHED);
        $service = $this->createMock(FaceRecognitionService::class);
        $service->expects($this->once())
            ->method('indexPhoto')
            ->with($this->callback(fn (Photo $candidate): bool => $candidate->is($photo)))
            ->willReturn([
                'collection_id' => 'wivor-event-'.$photo->event->uuid,
                'faces_indexed' => 3,
            ]);
        Log::spy();

        (new IndexPhotoFaces($photo->id))->handle($service);

        Log::shouldHaveReceived('info')->once()->with('Face indexing completed.', [
            'photo_uuid' => $photo->uuid,
            'event_uuid' => $photo->event->uuid,
            'faces_indexed' => 3,
        ]);
    }

    public function test_job_skips_an_unpublished_photo(): void
    {
        $photo = $this->photo(Photo::STATUS_READY);
        $service = $this->createMock(FaceRecognitionService::class);
        $service->expects($this->never())->method('indexPhoto');

        (new IndexPhotoFaces($photo->id))->handle($service);
    }

    public function test_job_skips_a_photo_when_its_event_is_not_published(): void
    {
        $photo = $this->photo(Photo::STATUS_PUBLISHED, Event::STATUS_DRAFT);
        $service = $this->createMock(FaceRecognitionService::class);
        $service->expects($this->never())->method('indexPhoto');

        (new IndexPhotoFaces($photo->id))->handle($service);
    }

    public function test_rekognition_failure_does_not_change_publication_state(): void
    {
        $photo = $this->photo(Photo::STATUS_PUBLISHED);
        $exception = new RuntimeException('Rekognition temporarily unavailable.');
        $service = $this->createMock(FaceRecognitionService::class);
        $service->method('indexPhoto')->willThrowException($exception);
        $job = new IndexPhotoFaces($photo->id);
        Log::spy();

        try {
            $job->handle($service);
            $this->fail('The job should allow the exception to reach Laravel queue retry handling.');
        } catch (RuntimeException $caught) {
            $this->assertSame($exception, $caught);
        }

        $job->failed($exception);

        $this->assertSame(Photo::STATUS_PUBLISHED, $photo->fresh()->status);
        Log::shouldHaveReceived('error')->once()->with('Face indexing failed.', [
            'photo_id' => $photo->id,
            'exception' => $exception->getMessage(),
        ]);
    }

    public function test_job_skips_safely_when_photo_no_longer_exists(): void
    {
        $service = $this->createMock(FaceRecognitionService::class);
        $service->expects($this->never())->method('indexPhoto');

        (new IndexPhotoFaces(999999))->handle($service);
    }

    public function test_developer_command_indexes_a_published_photo_by_uuid(): void
    {
        $photo = $this->photo(Photo::STATUS_PUBLISHED);
        $collectionId = 'wivor-event-'.$photo->event->uuid;
        $service = $this->createMock(FaceRecognitionService::class);
        $service->expects($this->once())
            ->method('indexPhoto')
            ->with($this->callback(fn (Photo $candidate): bool => $candidate->is($photo)))
            ->willReturn(['collection_id' => $collectionId, 'faces_indexed' => 2]);
        $this->app->instance(FaceRecognitionService::class, $service);

        $this->artisan('face-recognition:index-photo', ['photo' => $photo->uuid])
            ->expectsTable(
                ['Photo UUID', 'Event UUID', 'Collection', 'Faces indexed'],
                [[$photo->uuid, $photo->event->uuid, $collectionId, 2]],
            )
            ->assertSuccessful();
    }

    private function photo(string $photoStatus, string $eventStatus = Event::STATUS_PUBLISHED): Photo
    {
        $user = User::factory()->create();
        $photographer = Photographer::create([
            'user_id' => $user->id,
            'first_name' => 'Face',
            'last_name' => 'Tester',
        ]);
        $event = Event::create([
            'title' => 'Face Index Event',
            'slug' => 'face-index-event-'.uniqid(),
            'content' => '',
            'status' => $eventStatus,
            'published' => $eventStatus === Event::STATUS_PUBLISHED,
            'date_of_event' => now(),
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
            'original_filename' => 'face-index.jpg',
            'original_key' => 'events/test/face-index/original.jpg',
            'status' => $photoStatus,
            'processed_at' => now(),
            'published_at' => $photoStatus === Photo::STATUS_PUBLISHED ? now() : null,
        ]);
    }
}
