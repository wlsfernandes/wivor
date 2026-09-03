<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\UploadBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_unsold_media_is_deleted_but_tombstone_remains(): void
    {
        Storage::fake('media');
        config(['photo_uploads.disk' => 'media']);
        $user = User::factory()->create();
        $photographer = Photographer::create(['user_id' => $user->id, 'first_name' => 'Pat', 'last_name' => 'Lee']);
        $event = Event::create([
            'title' => 'Trail Race', 'slug' => 'trail-race', 'content' => '', 'status' => Event::STATUS_PUBLISHED,
            'published' => true, 'date_of_event' => now()->subMonths(3), 'sales_close_at' => now()->subDay(),
        ]);
        $assignment = EventAssignment::create(['event_id' => $event->id, 'photographer_id' => $photographer->id, 'status' => 'approved']);
        $batch = UploadBatch::create(['event_id' => $event->id, 'photographer_id' => $photographer->id, 'assignment_id' => $assignment->id, 'selected_count' => 1]);
        foreach (['original.jpg', 'preview.jpg', 'thumbnail.jpg'] as $key) {
            Storage::disk('media')->put($key, 'image');
        }
        $photo = Photo::create([
            'event_id' => $event->id, 'photographer_id' => $photographer->id, 'assignment_id' => $assignment->id,
            'upload_batch_id' => $batch->id, 'original_filename' => 'race.jpg', 'original_key' => 'original.jpg',
            'preview_key' => 'preview.jpg', 'thumbnail_key' => 'thumbnail.jpg', 'status' => Photo::STATUS_PUBLISHED,
            'expires_at' => now()->subHour(),
        ]);

        $this->artisan('media:enforce-retention')->assertSuccessful();

        Storage::disk('media')->assertMissing('original.jpg');
        $this->assertSame(Photo::STATUS_REMOVED, $photo->fresh()->status);
        $this->assertNotNull($photo->fresh()->deleted_at);
    }

    public function test_sold_original_is_protected_while_closed_gallery_derivatives_are_removed(): void
    {
        Storage::fake('media');
        config(['photo_uploads.disk' => 'media']);
        $user = User::factory()->create();
        $photographer = Photographer::create(['user_id' => $user->id, 'first_name' => 'Pat', 'last_name' => 'Lee']);
        $event = Event::create([
            'title' => 'Sold Race', 'slug' => 'sold-race', 'content' => '', 'status' => Event::STATUS_PUBLISHED,
            'published' => true, 'date_of_event' => now()->subMonths(3), 'sales_close_at' => now()->subDay(),
        ]);
        $assignment = EventAssignment::create(['event_id' => $event->id, 'photographer_id' => $photographer->id, 'status' => 'approved']);
        $batch = UploadBatch::create(['event_id' => $event->id, 'photographer_id' => $photographer->id, 'assignment_id' => $assignment->id, 'selected_count' => 1]);
        foreach (['sold-original.jpg', 'sold-preview.jpg', 'sold-thumbnail.jpg'] as $key) {
            Storage::disk('media')->put($key, 'image');
        }
        $photo = Photo::create([
            'event_id' => $event->id, 'photographer_id' => $photographer->id, 'assignment_id' => $assignment->id,
            'upload_batch_id' => $batch->id, 'original_filename' => 'sold.jpg', 'original_key' => 'sold-original.jpg',
            'preview_key' => 'sold-preview.jpg', 'thumbnail_key' => 'sold-thumbnail.jpg', 'status' => Photo::STATUS_PUBLISHED,
            'expires_at' => now()->subHour(), 'sale_count' => 1, 'most_recent_purchase_at' => now()->subDay(),
        ]);

        $this->artisan('media:enforce-retention')->assertSuccessful();

        Storage::disk('media')->assertExists('sold-original.jpg');
        Storage::disk('media')->assertMissing('sold-preview.jpg');
        $this->assertSame(Photo::STATUS_PUBLISHED, $photo->fresh()->status);
        $this->assertNull($photo->fresh()->preview_key);
        $this->assertTrue($photo->fresh()->expires_at->isFuture());
    }
}
