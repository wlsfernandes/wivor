<?php

namespace Tests\Feature;

use App\Jobs\DetectPhotoBibNumbers;
use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\UploadBatch;
use App\Models\User;
use App\Services\BibNumberDetector;
use Aws\MockHandler;
use Aws\Rekognition\RekognitionClient;
use Aws\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

class DetectPhotoBibNumbersJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_saves_bibs_and_duplicate_execution_is_idempotent(): void
    {
        $photo = $this->readyPhoto();
        $handler = new MockHandler([
            new Result(['TextDetections' => [
                ['DetectedText' => '347', 'Confidence' => 98.91],
                ['DetectedText' => '347', 'Confidence' => 96.10],
                ['DetectedText' => '456', 'Confidence' => 87.40],
            ]]),
        ]);
        $detector = new BibNumberDetector($this->client($handler));
        $job = new DetectPhotoBibNumbers($photo->id);

        $job->handle($detector);
        $job->handle($detector);

        $this->assertDatabaseCount('photo_bib_numbers', 2);
        $this->assertDatabaseHas('photo_bib_numbers', [
            'photo_id' => $photo->id,
            'bib_number' => '347',
            'confidence' => 98.91,
        ]);
        $this->assertDatabaseHas('photo_bib_numbers', [
            'photo_id' => $photo->id,
            'bib_number' => '456',
            'confidence' => 87.40,
        ]);
        $this->assertNotNull($photo->fresh()->bib_scanned_at);
        $this->assertSame(Photo::STATUS_READY, $photo->fresh()->status);
        $this->assertCount(0, $handler);
    }

    public function test_job_treats_no_bib_as_a_successful_scan(): void
    {
        $photo = $this->readyPhoto();
        $handler = new MockHandler([
            new Result(['TextDetections' => [
                ['DetectedText' => 'FINISH', 'Confidence' => 99],
            ]]),
        ]);

        (new DetectPhotoBibNumbers($photo->id))->handle(new BibNumberDetector($this->client($handler)));

        $this->assertDatabaseCount('photo_bib_numbers', 0);
        $this->assertNotNull($photo->fresh()->bib_scanned_at);
        $this->assertSame(Photo::STATUS_READY, $photo->fresh()->status);
    }

    public function test_rekognition_failure_leaves_the_uploaded_photo_usable_and_is_logged_on_final_failure(): void
    {
        $photo = $this->readyPhoto();
        $exception = new RuntimeException('Rekognition temporarily unavailable.');
        $handler = new MockHandler([$exception]);
        $job = new DetectPhotoBibNumbers($photo->id);
        Log::spy();

        try {
            $job->handle(new BibNumberDetector($this->client($handler)));
            $this->fail('The job should allow the exception to reach Laravel queue retry handling.');
        } catch (RuntimeException $caught) {
            $this->assertSame($exception, $caught);
        }

        $job->failed($exception);

        $this->assertSame(Photo::STATUS_READY, $photo->fresh()->status);
        $this->assertNull($photo->fresh()->bib_scanned_at);
        $this->assertDatabaseCount('photo_bib_numbers', 0);
        Log::shouldHaveReceived('error')->once()->with('Bib-number detection failed.', [
            'photo_id' => $photo->id,
            'exception' => $exception->getMessage(),
        ]);
    }

    public function test_job_skips_safely_when_photo_no_longer_exists(): void
    {
        $detector = $this->createMock(BibNumberDetector::class);
        $detector->expects($this->never())->method('scanAndSave');

        (new DetectPhotoBibNumbers(999999))->handle($detector);
    }

    private function client(MockHandler $handler): RekognitionClient
    {
        config([
            'photo_uploads.disk' => 's3',
            'filesystems.disks.s3.bucket' => 'wivor-test-bucket',
            'bib_recognition.min_confidence' => 80,
        ]);

        return new RekognitionClient([
            'version' => 'latest',
            'region' => 'us-east-1',
            'credentials' => false,
            'handler' => $handler,
        ]);
    }

    private function readyPhoto(): Photo
    {
        $user = User::factory()->create();
        $photographer = Photographer::create([
            'user_id' => $user->id,
            'first_name' => 'Queue',
            'last_name' => 'Tester',
        ]);
        $event = Event::create([
            'title' => 'Queued Bib Event',
            'slug' => 'queued-bib-event-'.uniqid(),
            'content' => '',
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
            'original_filename' => 'queued-bib.jpg',
            'original_key' => 'events/test/queued-bib/original.jpg',
            'status' => Photo::STATUS_READY,
            'processed_at' => now(),
        ]);
    }
}
