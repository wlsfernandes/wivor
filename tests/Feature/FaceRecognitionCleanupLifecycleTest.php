<?php

namespace Tests\Feature;

use App\Jobs\DeletePhotoFaces;
use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\Role;
use App\Models\UploadBatch;
use App\Models\User;
use App\Services\FaceRecognitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class FaceRecognitionCleanupLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media');
        config(['photo_uploads.disk' => 'media']);
    }

    public function test_admin_unpublish_dispatches_face_cleanup_when_public_feature_is_disabled(): void
    {
        Queue::fake();
        config(['face_recognition.enabled' => false]);
        [$admin, $event, $photo] = $this->fixture();

        $this->actingAs($admin)
            ->patch(route('admin.media.unpublish', [$event, $photo]))
            ->assertRedirect();

        $this->assertSame(Photo::STATUS_READY, $photo->fresh()->status);
        Queue::assertPushed(DeletePhotoFaces::class, fn ($job) => $job->photoId === $photo->id);
    }

    public function test_admin_removal_dispatches_face_cleanup(): void
    {
        Queue::fake();
        [$admin, $event, $photo] = $this->fixture();
        Storage::disk('media')->put($photo->original_key, 'image');

        $this->actingAs($admin)
            ->delete(route('admin.media.remove', [$event, $photo]), ['reason' => 'Approved removal request.'])
            ->assertRedirect();

        $this->assertSame(Photo::STATUS_REMOVED, $photo->fresh()->status);
        Storage::disk('media')->assertMissing($photo->original_key);
        Queue::assertPushed(DeletePhotoFaces::class, fn ($job) => $job->photoId === $photo->id);
    }

    public function test_aws_failure_does_not_block_photo_unpublish(): void
    {
        [$admin, $event, $photo] = $this->fixture();
        $service = $this->createMock(FaceRecognitionService::class);
        $service->method('deletePhotoFaces')->willThrowException(new RuntimeException('AWS unavailable.'));
        $this->app->instance(FaceRecognitionService::class, $service);

        $this->actingAs($admin)
            ->patch(route('admin.media.unpublish', [$event, $photo]))
            ->assertRedirect()
            ->assertSessionDoesntHaveErrors();

        $this->assertSame(Photo::STATUS_READY, $photo->fresh()->status);
        $this->assertNull($photo->fresh()->published_at);
    }

    /** @return array{User, Event, Photo} */
    private function fixture(): array
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));
        $photographerUser = User::factory()->create();
        $photographer = Photographer::create([
            'user_id' => $photographerUser->id,
            'first_name' => 'Lifecycle',
            'last_name' => 'Tester',
        ]);
        $event = Event::create([
            'title' => 'Cleanup Lifecycle Event',
            'slug' => 'cleanup-lifecycle-event-'.uniqid(),
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
        $photo = Photo::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'assignment_id' => $assignment->id,
            'upload_batch_id' => $batch->id,
            'original_filename' => 'cleanup.jpg',
            'original_key' => 'events/test/cleanup/original.jpg',
            'preview_key' => 'events/test/cleanup/preview.jpg',
            'thumbnail_key' => 'events/test/cleanup/thumbnail.jpg',
            'status' => Photo::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        return [$admin, $event, $photo];
    }
}
