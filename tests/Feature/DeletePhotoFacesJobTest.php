<?php

namespace Tests\Feature;

use App\Jobs\DeletePhotoFaces;
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

class DeletePhotoFacesJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_deletes_photo_faces_and_logs_only_operational_results(): void
    {
        $photo = $this->photo();
        $service = $this->createMock(FaceRecognitionService::class);
        $service->expects($this->once())
            ->method('deletePhotoFaces')
            ->with($this->callback(fn (Photo $candidate): bool => $candidate->is($photo)))
            ->willReturn([
                'collection_id' => 'wivor-event-'.$photo->event->uuid,
                'faces_deleted' => 3,
            ]);
        Log::spy();

        (new DeletePhotoFaces($photo->id))->handle($service);

        Log::shouldHaveReceived('info')->once()->with('Face index cleanup completed.', [
            'photo_uuid' => $photo->uuid,
            'event_uuid' => $photo->event->uuid,
            'faces_deleted' => 3,
        ]);
    }

    public function test_job_skips_safely_when_photo_no_longer_exists(): void
    {
        $service = $this->createMock(FaceRecognitionService::class);
        $service->expects($this->never())->method('deletePhotoFaces');

        (new DeletePhotoFaces(999999))->handle($service);
    }

    public function test_aws_failure_leaves_the_photo_lifecycle_state_unchanged(): void
    {
        $photo = $this->photo(Photo::STATUS_READY);
        $exception = new RuntimeException('Rekognition temporarily unavailable.');
        $service = $this->createMock(FaceRecognitionService::class);
        $service->method('deletePhotoFaces')->willThrowException($exception);
        $job = new DeletePhotoFaces($photo->id);
        Log::spy();

        try {
            $job->handle($service);
            $this->fail('The job should allow the exception to reach Laravel queue retry handling.');
        } catch (RuntimeException $caught) {
            $this->assertSame($exception, $caught);
        }

        $job->failed($exception);

        $this->assertSame(Photo::STATUS_READY, $photo->fresh()->status);
        Log::shouldHaveReceived('error')->once()->with('Face index cleanup failed.', [
            'photo_id' => $photo->id,
            'exception' => $exception->getMessage(),
        ]);
    }

    private function photo(string $status = Photo::STATUS_PUBLISHED): Photo
    {
        $user = User::factory()->create();
        $photographer = Photographer::create([
            'user_id' => $user->id,
            'first_name' => 'Cleanup',
            'last_name' => 'Tester',
        ]);
        $event = Event::create([
            'title' => 'Face Cleanup Event',
            'slug' => 'face-cleanup-event-'.uniqid(),
            'content' => '',
            'status' => Event::STATUS_PUBLISHED,
            'published' => true,
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
            'original_filename' => 'face-cleanup.jpg',
            'original_key' => 'events/test/face-cleanup/original.jpg',
            'status' => $status,
            'processed_at' => now(),
            'published_at' => $status === Photo::STATUS_PUBLISHED ? now() : null,
        ]);
    }
}
