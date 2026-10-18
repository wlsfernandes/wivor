<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\Role;
use App\Models\UploadBatch;
use App\Models\User;
use App\Services\PhotoStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMediaThumbnailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PhotoStorage::class, new class extends PhotoStorage
        {
            public function deliveryUrl(string $key): string
            {
                return "https://signed.example.test/{$key}";
            }
        });
    }

    public function test_admin_media_page_shows_a_thumbnail_for_each_photo(): void
    {
        $event = $this->event();
        $photo = $this->photo($event, ['thumbnail_key' => 'events/1/thumb-race.jpg']);

        $this->actingAs($this->admin())
            ->get(route('admin.media.show', $event))
            ->assertOk()
            ->assertSee('src="https://signed.example.test/events/1/thumb-race.jpg"', false)
            ->assertSee('alt="Photo #'.$photo->id.'"', false);
    }

    public function test_admin_media_page_says_when_a_thumbnail_is_not_available(): void
    {
        $event = $this->event();
        $this->photo($event, ['thumbnail_key' => null, 'status' => Photo::STATUS_PROCESSING]);

        $this->actingAs($this->admin())
            ->get(route('admin.media.show', $event))
            ->assertOk()
            ->assertSee('No preview')
            ->assertDontSee('signed.example.test');
    }

    public function test_removed_photo_shows_no_preview_instead_of_a_broken_image(): void
    {
        $event = $this->event();
        $this->photo($event, ['thumbnail_key' => 'events/1/deleted-thumb.jpg', 'status' => Photo::STATUS_REMOVED]);

        $this->actingAs($this->admin())
            ->get(route('admin.media.show', $event))
            ->assertOk()
            ->assertSee('No preview')
            ->assertDontSee('deleted-thumb.jpg');
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

        return $admin;
    }

    private function event(): Event
    {
        return Event::create([
            'title' => 'Thumb Race', 'slug' => 'thumb-race-'.uniqid(), 'content' => '',
            'status' => Event::STATUS_PUBLISHED, 'published' => true, 'date_of_event' => now(),
        ]);
    }

    private function photo(Event $event, array $overrides = []): Photo
    {
        $user = User::factory()->create();
        $photographer = Photographer::create(['user_id' => $user->id, 'first_name' => 'Pat', 'last_name' => 'Lee']);
        $assignment = EventAssignment::create(['event_id' => $event->id, 'photographer_id' => $photographer->id, 'status' => 'approved']);
        $batch = UploadBatch::create(['event_id' => $event->id, 'photographer_id' => $photographer->id, 'assignment_id' => $assignment->id, 'selected_count' => 1]);

        return Photo::create(array_merge([
            'event_id' => $event->id, 'photographer_id' => $photographer->id, 'assignment_id' => $assignment->id,
            'upload_batch_id' => $batch->id, 'original_filename' => 'race.jpg', 'original_key' => 'original.jpg',
            'preview_key' => 'preview.jpg', 'thumbnail_key' => 'thumbnail.jpg', 'status' => Photo::STATUS_PUBLISHED,
        ], $overrides));
    }
}
