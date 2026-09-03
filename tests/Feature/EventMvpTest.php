<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Photographer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventMvpTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_directory_filters_published_events(): void
    {
        $matchingEvent = $this->createEvent([
            'title' => 'Boston Fall Run',
            'sport' => 'Running',
            'city' => 'Boston',
            'state' => 'MA',
            'status' => Event::STATUS_PUBLISHED,
            'published' => true,
        ]);
        $this->createEvent([
            'title' => 'Austin Cycling Day',
            'sport' => 'Cycling',
            'city' => 'Austin',
            'state' => 'TX',
            'status' => Event::STATUS_PUBLISHED,
            'published' => true,
        ]);
        $this->createEvent(['title' => 'Boston Draft Event']);

        $response = $this->get(route('events.listEvents', [
            'search' => 'Boston',
            'state' => 'MA',
            'sport' => 'Running',
        ]));

        $response->assertOk()
            ->assertSee($matchingEvent->title)
            ->assertDontSee('Austin Cycling Day')
            ->assertDontSee('Boston Draft Event');
    }

    public function test_draft_event_is_not_public_but_published_event_is(): void
    {
        $event = $this->createEvent(['title' => 'Private Draft']);

        $this->get(route('events.show', ['event' => $event->slug]))->assertNotFound();

        $event->update([
            'status' => Event::STATUS_PUBLISHED,
            'published' => true,
            'published_at' => now(),
        ]);

        $this->get(route('events.show', ['event' => $event->slug]))
            ->assertOk()
            ->assertSee('Private Draft')
            ->assertSee('Event details available');
    }

    public function test_public_event_reports_scheduled_photo_availability(): void
    {
        $event = $this->createEvent([
            'title' => 'Scheduled Photos',
            'status' => Event::STATUS_PUBLISHED,
            'published' => true,
            'photos_live_at' => now()->addDay(),
        ]);

        $this->get(route('events.show', ['event' => $event->slug]))
            ->assertOk()
            ->assertSee('Photos coming soon');

        $event->update(['photos_live_at' => now()->subDay()]);

        $this->get(route('events.show', ['event' => $event->slug]))
            ->assertOk()
            ->assertSee('Photos are live');
    }

    public function test_only_assigned_photographer_or_admin_can_edit_an_event(): void
    {
        [$owner, $ownerPhotographer] = $this->createPhotographerUser();
        [$otherUser] = $this->createPhotographerUser();
        $event = $this->createEvent();
        $event->photographers()->attach($ownerPhotographer);

        $this->actingAs($owner)
            ->get(route('events.edit', ['event' => $event->id]))
            ->assertOk();

        $this->actingAs($otherUser)
            ->get(route('events.edit', ['event' => $event->id]))
            ->assertForbidden();

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        $this->actingAs($admin)
            ->get(route('events.edit', ['event' => $event->id]))
            ->assertOk();
    }

    public function test_owner_can_publish_and_archive_an_event(): void
    {
        [$owner, $photographer] = $this->createPhotographerUser();
        $event = $this->createEvent(['title' => 'Lifecycle Event']);
        $event->photographers()->attach($photographer);

        $this->actingAs($owner)
            ->patch(route('events.publish', ['event' => $event->id]))
            ->assertRedirect();

        $event->refresh();
        $this->assertSame(Event::STATUS_PUBLISHED, $event->status);
        $this->assertTrue($event->published);
        $this->get(route('events.show', ['event' => $event->slug]))->assertOk();

        $this->actingAs($owner)
            ->delete(route('events.destroy', ['event' => $event->id]))
            ->assertRedirect(route('events.index'));

        $event->refresh();
        $this->assertSame(Event::STATUS_ARCHIVED, $event->status);
        $this->assertFalse($event->published);
        $this->get(route('events.show', ['event' => $event->slug]))->assertNotFound();
    }

    public function test_admin_can_assign_and_approve_a_photographer_for_an_event(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);
        [, $photographer] = $this->createPhotographerUser();
        $event = $this->createEvent();

        $this->actingAs($admin)
            ->post(route('admin.events.photographers.assign', $event), [
                'photographer_id' => $photographer->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('event_photographer', [
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'status' => 'approved',
        ]);
    }

    public function test_event_creation_requires_the_mvp_fields_and_assigns_owner(): void
    {
        [$owner, $photographer] = $this->createPhotographerUser();

        $this->actingAs($owner)
            ->post(route('events.store'), [])
            ->assertSessionHasErrors(['title', 'sport', 'date_of_event', 'city', 'state']);

        $this->actingAs($owner)
            ->post(route('events.store'), $this->validEventPayload(['title' => 'Owner Event']))
            ->assertRedirect();

        $event = Event::where('title', 'Owner Event')->firstOrFail();
        $this->assertTrue($event->photographers()->whereKey($photographer->id)->exists());
        $this->assertSame(Event::STATUS_DRAFT, $event->status);
    }

    public function test_owner_can_replace_a_managed_cover_image(): void
    {
        Storage::fake('public');
        config(['filesystems.event_covers' => 'public']);

        [$owner, $photographer] = $this->createPhotographerUser();
        Storage::disk('public')->put('events/covers/old.jpg', 'old image');
        $event = $this->createEvent(['image_url' => 'events/covers/old.jpg']);
        $event->photographers()->attach($photographer);

        $payload = $this->validEventPayload([
            'title' => $event->title,
            'image_url' => UploadedFile::fake()->image('new-cover.jpg', 1200, 800),
        ]);

        $this->actingAs($owner)
            ->put(route('events.update', ['event' => $event->id]), $payload)
            ->assertRedirect(route('events.index'));

        $event->refresh();
        Storage::disk('public')->assertMissing('events/covers/old.jpg');
        Storage::disk('public')->assertExists($event->image_url);
    }

    /** @return array{User, Photographer} */
    private function createPhotographerUser(): array
    {
        $role = Role::firstOrCreate(['name' => 'photographer']);
        $user = User::factory()->create();
        $user->roles()->attach($role);
        $photographer = Photographer::create([
            'user_id' => $user->id,
            'first_name' => 'Event',
            'last_name' => 'Photographer',
        ]);
        $photographer->forceFill(['status' => Photographer::STATUS_APPROVED])->save();

        return [$user, $photographer];
    }

    private function createEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'title' => 'Test Race',
            'slug' => Event::generateUniqueSlug($overrides['title'] ?? 'Test Race'),
            'sport' => 'Running',
            'published' => false,
            'status' => Event::STATUS_DRAFT,
            'date_of_event' => '2026-10-10',
            'timezone' => 'America/New_York',
            'city' => 'Orlando',
            'state' => 'FL',
            'country_code' => 'US',
            'content' => 'A community sports event.',
        ], $overrides));
    }

    private function validEventPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Test Race',
            'sport' => 'Running',
            'content' => 'A community sports event.',
            'summary' => 'Event summary.',
            'date_of_event' => '2026-10-10',
            'timezone' => 'America/New_York',
            'city' => 'Orlando',
            'state' => 'FL',
            'country_code' => 'US',
            'price' => '10.00',
            'status' => Event::STATUS_DRAFT,
        ], $overrides);
    }
}
