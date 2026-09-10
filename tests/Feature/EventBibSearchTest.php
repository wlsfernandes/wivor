<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\UploadBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventBibSearchTest extends TestCase
{
    use RefreshDatabase;

    private Photographer $photographer;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->photographer = Photographer::create([
            'user_id' => $user->id,
            'first_name' => 'Gallery',
            'last_name' => 'Photographer',
        ]);
    }

    public function test_event_page_without_bib_keeps_the_normal_photo_listing(): void
    {
        $event = $this->event('Normal Gallery');
        $first = $this->photo($event);
        $second = $this->photo($event);

        $this->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Find your photos')
            ->assertSee('Event photos')
            ->assertSee(route('events.photos.show', [$event, $first]), false)
            ->assertSee(route('events.photos.show', [$event, $second]), false);
    }

    public function test_exact_bib_search_returns_multiple_matches_only_from_the_current_event(): void
    {
        $event = $this->event('Current Event');
        $otherEvent = $this->event('Other Event');
        $firstMatch = $this->photo($event, ['88333']);
        $secondMatch = $this->photo($event, ['88333', '456']);
        $differentBib = $this->photo($event, ['8833']);
        $otherEventMatch = $this->photo($otherEvent, ['88333']);

        $response = $this->get(route('events.show', [$event, 'bib' => '88333']));

        $response->assertOk()
            ->assertSee('Photos matching bib #88333')
            ->assertSee('value="88333"', false)
            ->assertSee('View all photos')
            ->assertSee(route('events.photos.show', [$event, $firstMatch]), false)
            ->assertSee(route('events.photos.show', [$event, $secondMatch]), false)
            ->assertDontSee(route('events.photos.show', [$event, $differentBib]), false)
            ->assertDontSee(route('events.photos.show', [$otherEvent, $otherEventMatch]), false);
    }

    public function test_photo_with_multiple_bibs_can_be_found_by_either_number(): void
    {
        $event = $this->event('Multiple Bibs');
        $photo = $this->photo($event, ['123', '456']);

        $this->get(route('events.show', [$event, 'bib' => '123']))
            ->assertOk()
            ->assertSee(route('events.photos.show', [$event, $photo]), false);
        $this->get(route('events.show', [$event, 'bib' => '456']))
            ->assertOk()
            ->assertSee(route('events.photos.show', [$event, $photo]), false);
    }

    public function test_unknown_and_invalid_bibs_are_handled_cleanly(): void
    {
        $event = $this->event('No Match');
        $this->photo($event, ['123']);

        $this->get(route('events.show', [$event, 'bib' => '999']))
            ->assertOk()
            ->assertSee('No photos were found for bib #999 yet.')
            ->assertSee('View all photos');

        $this->get(route('events.show', [$event, 'bib' => '123456']))
            ->assertRedirect()
            ->assertSessionHasErrors('bib');
    }

    public function test_photo_from_filtered_results_can_still_be_added_to_selection(): void
    {
        $event = $this->event('Sellable Search', true);
        $photo = $this->photo($event, ['88333']);

        $this->get(route('events.show', [$event, 'bib' => '88333']))
            ->assertOk()
            ->assertSee('Add to selection');

        $this->from(route('events.show', [$event, 'bib' => '88333']))
            ->post(route('cart.items.store'), ['photo' => $photo->uuid])
            ->assertRedirect(route('events.show', [$event, 'bib' => '88333']))
            ->assertSessionHasNoErrors();

        $this->get(route('events.show', [$event, 'bib' => '88333']))
            ->assertOk()
            ->assertSee('Remove from selection');
    }

    private function event(string $title, bool $sellable = false): Event
    {
        return Event::create([
            'title' => $title,
            'slug' => Event::generateUniqueSlug($title),
            'content' => '',
            'status' => Event::STATUS_PUBLISHED,
            'published' => true,
            'published_at' => now(),
            'date_of_event' => now(),
            'price_cents' => $sellable ? 1000 : 0,
            'sales_close_at' => $sellable ? now()->addDay() : null,
        ]);
    }

    /** @param list<string> $bibs */
    private function photo(Event $event, array $bibs = []): Photo
    {
        $assignment = EventAssignment::firstOrCreate([
            'event_id' => $event->id,
            'photographer_id' => $this->photographer->id,
        ], ['status' => 'approved']);
        $batch = UploadBatch::create([
            'event_id' => $event->id,
            'photographer_id' => $this->photographer->id,
            'assignment_id' => $assignment->id,
            'selected_count' => 1,
        ]);
        $photo = Photo::create([
            'event_id' => $event->id,
            'photographer_id' => $this->photographer->id,
            'assignment_id' => $assignment->id,
            'upload_batch_id' => $batch->id,
            'original_filename' => 'gallery.jpg',
            'original_key' => 'events/test/'.uniqid().'/original.jpg',
            'preview_key' => 'events/test/'.uniqid().'/preview.jpg',
            'thumbnail_key' => 'events/test/'.uniqid().'/thumbnail.jpg',
            'status' => Photo::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        foreach ($bibs as $bib) {
            $photo->bibNumbers()->create(['bib_number' => $bib, 'confidence' => 95]);
        }

        return $photo;
    }
}
