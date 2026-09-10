<?php

namespace Tests\Feature;

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
use Tests\TestCase;

class PhotoBibNumberSavingTest extends TestCase
{
    use RefreshDatabase;

    public function test_detected_bibs_are_saved_once_with_confidence_and_photo_is_marked_scanned(): void
    {
        $photo = $this->photo();
        $handler = new MockHandler([
            new Result(['TextDetections' => [
                ['DetectedText' => '123', 'Confidence' => 97.456],
                ['DetectedText' => '123', 'Confidence' => 91],
                ['DetectedText' => '456', 'Confidence' => 88.2],
            ]]),
        ]);
        $detector = new BibNumberDetector($this->client($handler));

        $firstResult = $detector->scanAndSave($photo);
        $secondResult = $detector->scanAndSave($photo->fresh());

        $this->assertSame($firstResult, $secondResult);
        $this->assertDatabaseCount('photo_bib_numbers', 2);
        $this->assertDatabaseHas('photo_bib_numbers', [
            'photo_id' => $photo->id,
            'bib_number' => '123',
            'confidence' => 97.46,
        ]);
        $this->assertDatabaseHas('photo_bib_numbers', [
            'photo_id' => $photo->id,
            'bib_number' => '456',
            'confidence' => 88.20,
        ]);
        $this->assertNotNull($photo->fresh()->bib_scanned_at);
        $this->assertCount(0, $handler);
    }

    public function test_no_valid_bib_is_successful_and_marks_photo_scanned(): void
    {
        $photo = $this->photo();
        $handler = new MockHandler([
            new Result(['TextDetections' => [
                ['DetectedText' => 'FINISH', 'Confidence' => 99],
                ['DetectedText' => '123456', 'Confidence' => 99],
                ['DetectedText' => '42', 'Confidence' => 70],
            ]]),
        ]);

        $result = (new BibNumberDetector($this->client($handler)))->scanAndSave($photo);

        $this->assertSame([], $result);
        $this->assertDatabaseCount('photo_bib_numbers', 0);
        $this->assertNotNull($photo->fresh()->bib_scanned_at);
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

    private function photo(): Photo
    {
        $user = User::factory()->create();
        $photographer = Photographer::create([
            'user_id' => $user->id,
            'first_name' => 'Phase',
            'last_name' => 'Three',
        ]);
        $event = Event::create([
            'title' => 'Bib Test Event',
            'slug' => 'bib-test-event-'.uniqid(),
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
            'original_filename' => 'phase-three.jpg',
            'original_key' => 'events/test/original.jpg',
        ]);
    }
}
