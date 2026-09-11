<?php

namespace Tests\Feature;

use App\Jobs\ProcessPhoto;
use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\Role;
use App\Models\UploadBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotographerUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_approved_assigned_photographer_can_open_uploader(): void
    {
        [$user, $photographer, $event] = $this->approvedAssignment();

        $this->actingAs($user)->get(route('photographer.uploads.show', $event))->assertOk()->assertSee('Photo requirements');

        EventAssignment::where('event_id', $event->id)->where('photographer_id', $photographer->id)->update(['status' => 'pending']);
        $this->actingAs($user)->get(route('photographer.uploads.show', $event))->assertForbidden();
    }

    public function test_expired_uploader_keeps_upload_controls_visible_but_disabled(): void
    {
        [$user, , $event, $assignment] = $this->approvedAssignment();
        $assignment->update(['upload_deadline_at' => now()->subMinute()]);

        $response = $this->actingAs($user)->get(route('photographer.uploads.show', $event));

        $response->assertOk()->assertSee('Add Photos');
        $this->assertMatchesRegularExpression('/id="drop-zone"[^>]*disabled/', $response->getContent());
        $this->actingAs($user)->get(route('events.index'))->assertOk()->assertSee('Upload photos');
    }

    public function test_publish_controls_are_disabled_when_no_photos_are_ready(): void
    {
        [$user, , $event] = $this->approvedAssignment();

        $response = $this->actingAs($user)->get(route('photographer.uploads.show', $event));

        $this->assertMatchesRegularExpression('/id="select-all-ready"[^>]*disabled/', $response->getContent());
        $this->assertMatchesRegularExpression('/id="publish-ready-button"[^>]*disabled/', $response->getContent());
    }

    public function test_batch_creation_reserves_exact_private_object_keys_and_records_confirmation(): void
    {
        [$user, $photographer, $event] = $this->approvedAssignment();

        $response = $this->actingAs($user)->postJson(route('photographer.uploads.batches.store', $event), [
            'rights_confirmed' => true,
            'files' => [['name' => '../finish-line.jpg', 'size' => 1_000_000, 'type' => 'image/jpeg']],
        ]);

        $response->assertCreated()->assertJsonCount(1, 'photos');
        $photo = Photo::firstOrFail();
        $this->assertSame('finish-line.jpg', $photo->original_filename);
        $this->assertSame("events/{$event->uuid}/photographers/{$photographer->uuid}/photos/{$photo->uuid}/original.jpg", $photo->original_key);
        $this->assertNotNull(EventAssignment::firstOrFail()->rights_confirmed_at);
    }

    public function test_successful_upload_completion_queues_the_existing_photo_processing_flow(): void
    {
        Queue::fake();
        Storage::fake('media');
        config(['photo_uploads.disk' => 'media']);
        [$user, $photographer, $event, $assignment] = $this->approvedAssignment();
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
            'original_filename' => 'finish.jpg',
            'original_key' => 'events/test/finish/original.jpg',
        ]);
        Storage::disk('media')->put($photo->original_key, 'uploaded jpeg bytes');

        $this->actingAs($user)
            ->postJson(route('photographer.uploads.complete', [$event, $photo]))
            ->assertAccepted()
            ->assertJson(['status' => Photo::STATUS_PROCESSING]);

        $this->assertSame(Photo::STATUS_PROCESSING, $photo->fresh()->status);
        $this->assertNotNull($photo->fresh()->uploaded_at);
        Queue::assertPushed(ProcessPhoto::class, fn ($job) => $job->photoId === $photo->id);
    }

    public function test_ready_photos_can_be_published_incrementally(): void
    {
        [$user, $photographer, $event, $assignment] = $this->approvedAssignment();
        $photographer->forceFill([
            'stripe_account_id' => 'acct_ready',
            'stripe_onboarding_status' => Photographer::STRIPE_READY,
        ])->save();
        $batch = UploadBatch::create([
            'event_id' => $event->id, 'photographer_id' => $photographer->id, 'assignment_id' => $assignment->id,
            'selected_count' => 1, 'status' => 'in_progress',
        ]);
        $photo = Photo::create([
            'event_id' => $event->id, 'photographer_id' => $photographer->id, 'assignment_id' => $assignment->id,
            'upload_batch_id' => $batch->id, 'original_filename' => 'race.jpg',
            'original_key' => 'private/original.jpg', 'preview_key' => 'private/preview.jpg',
            'thumbnail_key' => 'private/thumb.jpg', 'status' => Photo::STATUS_READY,
        ]);

        $this->actingAs($user)->post(route('photographer.uploads.publish', $event), ['photo_ids' => [$photo->uuid]])->assertRedirect();

        $this->assertSame(Photo::STATUS_PUBLISHED, $photo->fresh()->status);
        $this->assertNotNull($event->fresh()->gallery_published_at);
        $this->assertNotNull($event->fresh()->sales_close_at);
    }

    public function test_ready_photos_can_be_published_before_payout_setup_is_ready(): void
    {
        [$user, $photographer, $event, $assignment] = $this->approvedAssignment();
        $batch = UploadBatch::create([
            'event_id' => $event->id, 'photographer_id' => $photographer->id, 'assignment_id' => $assignment->id,
            'selected_count' => 1, 'status' => 'in_progress',
        ]);
        $photo = Photo::create([
            'event_id' => $event->id, 'photographer_id' => $photographer->id, 'assignment_id' => $assignment->id,
            'upload_batch_id' => $batch->id, 'original_filename' => 'race.jpg',
            'original_key' => 'private/original.jpg', 'preview_key' => 'private/preview.jpg',
            'thumbnail_key' => 'private/thumb.jpg', 'status' => Photo::STATUS_READY,
        ]);

        $this->actingAs($user)
            ->post(route('photographer.uploads.publish', $event), ['photo_ids' => [$photo->uuid]])
            ->assertRedirect()
            ->assertSessionDoesntHaveErrors();

        $this->assertSame(Photo::STATUS_PUBLISHED, $photo->fresh()->status);
        $this->assertNotNull($event->fresh()->gallery_published_at);
    }

    private function approvedAssignment(): array
    {
        $role = Role::firstOrCreate(['name' => 'photographer']);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->attach($role);
        $photographer = Photographer::create([
            'user_id' => $user->id, 'first_name' => 'Alex', 'last_name' => 'Rivera',
        ]);
        $photographer->forceFill(['status' => Photographer::STATUS_APPROVED])->save();
        $event = Event::create([
            'title' => 'City Run', 'slug' => 'city-run-'.uniqid(), 'content' => '',
            'status' => Event::STATUS_PUBLISHED, 'published' => true,
            'date_of_event' => now()->toDateString(), 'ends_at' => now()->addHour(),
            'timezone' => 'America/New_York',
        ]);
        $assignment = EventAssignment::create([
            'event_id' => $event->id, 'photographer_id' => $photographer->id,
            'status' => 'approved', 'upload_deadline_at' => now()->addDays(3),
        ]);

        return [$user, $photographer, $event, $assignment];
    }
}
