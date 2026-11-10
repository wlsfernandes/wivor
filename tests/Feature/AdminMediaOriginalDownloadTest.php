<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\MediaActivityLog;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\Role;
use App\Models\UploadBatch;
use App\Models\User;
use App\Services\PhotoStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMediaOriginalDownloadTest extends TestCase
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

            public function downloadUrl(string $key, string $filename): string
            {
                return "https://signed.example.test/download/{$key}?filename={$filename}";
            }
        });
    }

    public function test_admin_downloads_the_original_photo_through_a_signed_url(): void
    {
        $event = $this->event();
        $photo = $this->photo($event, ['original_key' => 'events/1/original-race.jpg']);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.media.original', [$event, $photo]))
            ->assertRedirect('https://signed.example.test/download/events/1/original-race.jpg?filename=race.jpg');

        $this->assertDatabaseHas(MediaActivityLog::class, [
            'event_id' => $event->id, 'photo_id' => $photo->id, 'actor_id' => $admin->id, 'action' => 'admin_original_downloaded',
        ]);
    }

    public function test_media_page_links_each_photo_to_its_original_download(): void
    {
        $event = $this->event();
        $photo = $this->photo($event);

        $this->actingAs($this->admin())
            ->get(route('admin.media.show', $event))
            ->assertOk()
            ->assertSee(route('admin.media.original', [$event, $photo]), false);
    }

    public function test_removed_photo_has_no_original_download(): void
    {
        $event = $this->event();
        $photo = $this->photo($event, ['status' => Photo::STATUS_REMOVED]);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.media.show', $event))
            ->assertOk()
            ->assertDontSee(route('admin.media.original', [$event, $photo]), false);

        $this->actingAs($admin)->get(route('admin.media.original', [$event, $photo]))->assertNotFound();
    }

    public function test_photo_from_another_event_cannot_be_downloaded_through_this_event(): void
    {
        $event = $this->event();
        $otherEventPhoto = $this->photo($this->event());

        $this->actingAs($this->admin())
            ->get(route('admin.media.original', [$event, $otherEventPhoto]))
            ->assertNotFound();
    }

    public function test_non_admin_cannot_download_the_original_photo(): void
    {
        $event = $this->event();
        $photo = $this->photo($event);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.media.original', [$event, $photo]))
            ->assertForbidden();
    }

    public function test_search_by_photo_id_shows_only_that_photo(): void
    {
        $event = $this->event();
        $wanted = $this->photo($event, ['original_filename' => 'wanted-shot.jpg']);
        $this->photo($event, ['original_filename' => 'other-shot.jpg']);

        $this->actingAs($this->admin())
            ->get(route('admin.media.show', [$event, 'search' => (string) $wanted->id]))
            ->assertOk()
            ->assertSee('wanted-shot.jpg')
            ->assertDontSee('other-shot.jpg')
            ->assertSee('value="'.$wanted->id.'"', false);
    }

    public function test_search_by_reference_number_the_customer_sees_finds_the_photo(): void
    {
        $event = $this->event();
        $wanted = $this->photo($event, ['uuid' => '54c9dda1-0000-4000-8000-000000000001', 'original_filename' => 'wanted-shot.jpg']);
        $this->photo($event, ['uuid' => '11111111-0000-4000-8000-000000000002', 'original_filename' => 'other-shot.jpg']);

        $this->actingAs($this->admin())
            ->get(route('admin.media.show', [$event, 'search' => $wanted->reference_number]))
            ->assertOk()
            ->assertSee('wanted-shot.jpg')
            ->assertDontSee('other-shot.jpg');

        $this->actingAs($this->admin())
            ->get(route('admin.media.show', [$event, 'search' => '54c9dda1']))
            ->assertOk()
            ->assertSee('wanted-shot.jpg')
            ->assertDontSee('other-shot.jpg');
    }

    public function test_search_by_full_uuid_finds_the_photo(): void
    {
        $event = $this->event();
        $wanted = $this->photo($event, ['original_filename' => 'wanted-shot.jpg']);
        $this->photo($event, ['original_filename' => 'other-shot.jpg']);

        $this->actingAs($this->admin())
            ->get(route('admin.media.show', [$event, 'search' => strtoupper($wanted->uuid)]))
            ->assertOk()
            ->assertSee('wanted-shot.jpg')
            ->assertDontSee('other-shot.jpg');
    }

    public function test_eight_digit_search_matches_both_a_database_id_and_a_reference_number(): void
    {
        $event = $this->event();
        $byReference = $this->photo($event, ['uuid' => '12345678-0000-4000-8000-000000000001', 'original_filename' => 'by-reference.jpg']);
        $byId = $this->photo($event, ['original_filename' => 'by-id.jpg']);
        $byId->forceFill(['id' => 12345678])->save();
        $this->photo($event, ['original_filename' => 'unrelated.jpg']);

        $this->actingAs($this->admin())
            ->get(route('admin.media.show', [$event, 'search' => '12345678']))
            ->assertOk()
            ->assertSee($byReference->original_filename)
            ->assertSee('by-id.jpg')
            ->assertDontSee('unrelated.jpg');
    }

    public function test_search_for_a_photo_from_another_event_finds_nothing(): void
    {
        $event = $this->event();
        $this->photo($event, ['original_filename' => 'this-event.jpg']);
        $otherEventPhoto = $this->photo($this->event(), ['original_filename' => 'other-event.jpg']);

        $this->actingAs($this->admin())
            ->get(route('admin.media.show', [$event, 'search' => $otherEventPhoto->reference_number]))
            ->assertOk()
            ->assertDontSee('this-event.jpg')
            ->assertDontSee('other-event.jpg')
            ->assertSee('No photo matches "'.$otherEventPhoto->reference_number.'"', false);
    }

    public function test_search_text_that_is_not_an_id_reference_or_uuid_finds_nothing(): void
    {
        $event = $this->event();
        $this->photo($event, ['original_filename' => 'race.jpg']);

        $this->actingAs($this->admin())
            ->get(route('admin.media.show', [$event, 'search' => 'race%']))
            ->assertOk()
            ->assertDontSee('race.jpg')
            ->assertSee('No photo matches');
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
            'title' => 'Original Race', 'slug' => 'original-race-'.uniqid(), 'content' => '',
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
