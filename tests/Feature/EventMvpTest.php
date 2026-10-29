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

    public function test_event_form_tells_photographers_not_to_use_a_recognizable_athlete_as_cover(): void
    {
        [$owner, $ownerPhotographer] = $this->createPhotographerUser();
        $event = $this->createEvent();
        $event->photographers()->attach($ownerPhotographer);

        $this->actingAs($owner)
            ->get(route('events.edit', ['event' => $event->id]))
            ->assertOk()
            ->assertSee('Use a course, start line, banner or crowd photo. Do not use a photo where an athlete can be recognized: the cover can appear in Google search.');
    }

    public function test_public_directory_filter_selects_stay_native_instead_of_nice_select_widgets(): void
    {
        $response = $this->get(route('events.listEvents'))->assertOk();

        foreach (['state', 'city', 'sport'] as $filterName) {
            $response->assertSee('<select class="form-select ignore" id="'.$filterName.'" name="'.$filterName.'">', false);
        }
    }

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

    public function test_public_directory_groups_events_into_live_upcoming_and_past(): void
    {
        $published = ['status' => Event::STATUS_PUBLISHED, 'published' => true];
        $this->createEvent($published + ['title' => 'Live Photos Run', 'date_of_event' => now()->subDays(3), 'photos_live_at' => now()->subDay()]);
        $this->createEvent($published + ['title' => 'Next Month Ride', 'date_of_event' => now()->addMonth()]);
        $this->createEvent($published + ['title' => 'Undated Swim', 'date_of_event' => null]);
        $this->createEvent($published + ['title' => 'Old Trail Race', 'date_of_event' => now()->subMonth()]);
        $this->createEvent($published + ['title' => 'Closed Sales Race', 'date_of_event' => now()->subMonths(2), 'photos_live_at' => now()->subMonth(), 'sales_close_at' => now()->subDay()]);

        $this->get(route('events.listEvents'))
            ->assertOk()
            ->assertSeeInOrder([
                'Photos are live', 'Live Photos Run', 'Find your photos',
                'Upcoming events', 'Next Month Ride', 'Undated Swim',
                'Past events', 'Old Trail Race', 'Closed Sales Race',
            ]);
    }

    public function test_public_directory_omits_empty_groups(): void
    {
        $this->createEvent(['status' => Event::STATUS_PUBLISHED, 'published' => true, 'title' => 'Old Trail Race', 'date_of_event' => now()->subMonth()]);

        $this->get(route('events.listEvents'))
            ->assertOk()
            ->assertSee('Past events')
            ->assertDontSee('Photos are live')
            ->assertDontSee('Upcoming events')
            ->assertDontSee('No events match your search');
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

    public function test_assigned_non_owner_can_upload_but_cannot_manage_the_event(): void
    {
        [$owner, $ownerPhotographer] = $this->createPhotographerUser();
        [$assignedUser, $assignedPhotographer] = $this->createPhotographerUser();
        $event = $this->createEvent([
            'title' => 'Owner Only Event',
            'status' => Event::STATUS_PUBLISHED,
            'published' => true,
            'published_at' => now(),
        ]);
        $event->photographers()->attach($ownerPhotographer, ['status' => 'approved']);
        $event->photographers()->attach($assignedPhotographer, ['status' => 'approved']);

        $editUrl = route('events.edit', ['event' => $event->id]);
        $publishUrl = route('events.publish', ['event' => $event->id]);
        $archiveUrl = route('events.destroy', ['event' => $event->id]);

        $this->actingAs($owner)
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee('href="'.$editUrl.'"', false)
            ->assertSee('action="'.$publishUrl.'"', false)
            ->assertSee('action="'.$archiveUrl.'"', false);

        $this->actingAs($assignedUser)
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee('href="'.route('photographer.uploads.show', $event).'"', false)
            ->assertDontSee('href="'.$editUrl.'"', false)
            ->assertDontSee('action="'.$publishUrl.'"', false)
            ->assertDontSee('action="'.$archiveUrl.'"', false);

        $this->actingAs($assignedUser)
            ->get($editUrl)
            ->assertForbidden();

        $this->actingAs($assignedUser)
            ->put(route('events.update', ['event' => $event->id]), $this->validEventPayload([
                'title' => 'Unauthorized Edit',
            ]))
            ->assertForbidden();

        $this->actingAs($assignedUser)
            ->patch($publishUrl)
            ->assertForbidden();

        $this->actingAs($assignedUser)
            ->delete($archiveUrl)
            ->assertForbidden();

        $event->refresh();
        $this->assertSame('Owner Only Event', $event->title);
        $this->assertSame(Event::STATUS_PUBLISHED, $event->status);
        $this->assertTrue($event->published);
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

    public function test_owner_can_turn_live_photos_off_and_on_while_event_stays_public(): void
    {
        [$owner, $photographer] = $this->createPhotographerUser();
        $event = $this->createEvent(['status' => Event::STATUS_PUBLISHED, 'published' => true, 'photos_live_at' => now()->subDay()]);
        $event->photographers()->attach($photographer);

        $this->actingAs($owner)
            ->patch(route('events.photos-live', ['event' => $event->id]))
            ->assertRedirect()
            ->assertSessionHas('success', 'Photos are no longer live.');

        $event->refresh();
        $this->assertNull($event->photos_live_at);
        $this->assertSame(Event::STATUS_PUBLISHED, $event->status);

        $this->actingAs($owner)
            ->patch(route('events.photos-live', ['event' => $event->id]))
            ->assertSessionHas('success', 'Photos are live.');

        $this->assertTrue($event->refresh()->has_live_photos);
    }

    public function test_turning_photos_live_on_replaces_a_future_schedule_with_now(): void
    {
        $event = $this->createEvent(['photos_live_at' => now()->addWeek()]);

        $this->actingAs($this->createAdminUser())
            ->patch(route('events.photos-live', ['event' => $event->id]))
            ->assertRedirect();

        $this->assertTrue($event->refresh()->has_live_photos);
    }

    public function test_unassigned_user_cannot_toggle_live_photos(): void
    {
        [$stranger] = $this->createPhotographerUser();
        $event = $this->createEvent(['photos_live_at' => now()->subDay()]);

        $this->actingAs($stranger)
            ->patch(route('events.photos-live', ['event' => $event->id]))
            ->assertForbidden();

        $this->assertNotNull($event->refresh()->photos_live_at);
    }

    public function test_manage_events_shows_a_live_photos_switch(): void
    {
        $event = $this->createEvent(['title' => 'Switch Race', 'photos_live_at' => now()->subDay()]);

        $this->actingAs($this->createAdminUser())
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee('action="'.route('events.photos-live', ['event' => $event->id]).'"', false)
            ->assertSee('role="switch"', false);
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
            ->assertSessionHasErrors(['title', 'sport', 'date_of_event', 'starts_at', 'city', 'state']);

        $this->actingAs($owner)
            ->post(route('events.store'), $this->validEventPayload(['title' => 'Owner Event']))
            ->assertRedirect();

        $event = Event::where('title', 'Owner Event')->firstOrFail();
        $this->assertTrue($event->photographers()->whereKey($photographer->id)->exists());
        $this->assertSame(Event::STATUS_DRAFT, $event->status);
    }

    public function test_admin_can_override_and_clear_the_event_commission(): void
    {
        $admin = $this->createAdminUser();
        $event = $this->createEvent(['price_cents' => 1000]);

        $this->actingAs($admin)
            ->get(route('events.edit', ['event' => $event->id]))
            ->assertSee('name="commission_percentage"', false)
            ->assertDontSee('name="packet_discount_percentage"', false);

        $this->actingAs($admin)
            ->put(route('events.update', ['event' => $event->id]), $this->validEventPayload(['commission_percentage' => '15']))
            ->assertRedirect(route('events.index'));
        $this->assertEquals(15, $event->refresh()->commission_percentage);

        $this->actingAs($admin)
            ->put(route('events.update', ['event' => $event->id]), $this->validEventPayload(['commission_percentage' => '']))
            ->assertRedirect(route('events.index'));
        $this->assertNull($event->refresh()->commission_percentage);
    }

    public function test_admin_commission_override_accepts_zero_to_one_hundred_percent_only(): void
    {
        $admin = $this->createAdminUser();
        $event = $this->createEvent(['price_cents' => 1000]);

        $this->actingAs($admin)
            ->put(route('events.update', ['event' => $event->id]), $this->validEventPayload(['commission_percentage' => '101']))
            ->assertSessionHasErrors('commission_percentage');

        $this->actingAs($admin)
            ->put(route('events.update', ['event' => $event->id]), $this->validEventPayload(['commission_percentage' => '100']))
            ->assertSessionHasNoErrors();
        $this->assertEquals(100, $event->refresh()->commission_percentage);
    }

    public function test_admin_can_set_the_commission_when_creating_an_event(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin)
            ->post(route('events.store'), $this->validEventPayload([
                'title' => 'Admin Priced Event',
                'commission_percentage' => '12.5',
            ]))
            ->assertRedirect();

        $this->assertEquals(12.5, Event::where('title', 'Admin Priced Event')->firstOrFail()->commission_percentage);
    }

    public function test_photographer_sees_the_effective_commission_read_only(): void
    {
        config(['commission.percentage' => 20]);
        [$owner, $photographer] = $this->createPhotographerUser();
        $event = $this->createEvent(['price_cents' => 1000, 'commission_percentage' => 15]);
        $event->photographers()->attach($photographer);

        $this->actingAs($owner)
            ->get(route('events.edit', ['event' => $event->id]))
            ->assertSee('WivorPhotos commission: 15%, set by WivorPhotos.');
    }

    public function test_photographer_cannot_see_or_set_the_event_commission(): void
    {
        [$owner, $photographer] = $this->createPhotographerUser();
        $event = $this->createEvent(['price_cents' => 1000]);
        $event->photographers()->attach($photographer);

        $this->actingAs($owner)
            ->get(route('events.edit', ['event' => $event->id]))
            ->assertDontSee('name="commission_percentage"', false);

        $this->actingAs($owner)
            ->put(route('events.update', ['event' => $event->id]), $this->validEventPayload(['commission_percentage' => '1']))
            ->assertSessionHasErrors('commission_percentage');

        $this->assertNull($event->refresh()->commission_percentage);
    }

    public function test_photographer_creates_an_event_with_package_prices_saved_in_cents(): void
    {
        [$owner] = $this->createPhotographerUser();

        $this->actingAs($owner)
            ->post(route('events.store'), $this->validEventPayload([
                'title' => 'Package Event',
                'price' => '17.00',
                'package_prices' => ['2' => '15.00', '3' => '', '4' => '', '5' => '12.50', '10' => '10'],
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $event = Event::where('title', 'Package Event')->firstOrFail();
        $this->assertSame(1700, $event->price_cents);
        $this->assertSame([2 => 1500, 5 => 1250, 10 => 1000], $event->priceTiers->pluck('price_cents', 'min_photos')->all());
    }

    public function test_event_form_shows_one_row_per_package_size_with_saved_prices(): void
    {
        [$owner, $photographer] = $this->createPhotographerUser();
        $event = $this->createEvent(['price_cents' => 1700]);
        $event->photographers()->attach($photographer);
        $event->priceTiers()->create(['min_photos' => 2, 'price_cents' => 1500]);

        $this->actingAs($owner)
            ->get(route('events.edit', ['event' => $event->id]))
            ->assertOk()
            ->assertSeeInOrder(['1 photo', '2 photos', '3 photos', '4 photos', '5 photos', '10 or more photos'])
            ->assertSee('name="package_prices[2]"', false)
            ->assertSee('value="15.00"', false)
            ->assertSee('name="package_prices[10]"', false);
    }

    public function test_updating_an_event_replaces_and_clears_its_package_prices(): void
    {
        [$owner, $photographer] = $this->createPhotographerUser();
        $event = $this->createEvent(['price_cents' => 1700]);
        $event->photographers()->attach($photographer);
        $event->priceTiers()->create(['min_photos' => 2, 'price_cents' => 1500]);
        $event->priceTiers()->create(['min_photos' => 5, 'price_cents' => 1200]);

        $this->actingAs($owner)
            ->put(route('events.update', ['event' => $event->id]), $this->validEventPayload([
                'price' => '17.00',
                'package_prices' => ['2' => '', '3' => '14.00', '4' => '', '5' => '11.00', '10' => ''],
            ]))
            ->assertRedirect(route('events.index'));
        $this->assertSame([3 => 1400, 5 => 1100], $event->priceTiers()->pluck('price_cents', 'min_photos')->all());

        $this->actingAs($owner)
            ->put(route('events.update', ['event' => $event->id]), $this->validEventPayload(['price' => '17.00']))
            ->assertRedirect(route('events.index'));
        $this->assertSame(0, $event->priceTiers()->count());
    }

    public function test_package_price_cannot_be_higher_than_a_smaller_order(): void
    {
        [$owner] = $this->createPhotographerUser();

        $this->actingAs($owner)
            ->post(route('events.store'), $this->validEventPayload([
                'price' => '15.00',
                'package_prices' => ['2' => '16.00'],
            ]))
            ->assertSessionHasErrors(['package_prices.2' => 'The 2+ photo price cannot be higher than the 1-photo price.']);

        // Empty sizes are skipped: 5 photos is compared with 3 photos.
        $this->actingAs($owner)
            ->post(route('events.store'), $this->validEventPayload([
                'price' => '15.00',
                'package_prices' => ['3' => '12.00', '4' => '', '5' => '12.01'],
            ]))
            ->assertSessionHasErrors(['package_prices.5' => 'The 5+ photo price cannot be higher than the 3+ photo price.']);

        $this->assertSame(0, Event::count());
    }

    public function test_package_price_may_equal_the_smaller_order_price(): void
    {
        [$owner] = $this->createPhotographerUser();

        $this->actingAs($owner)
            ->post(route('events.store'), $this->validEventPayload([
                'price' => '15.00',
                'package_prices' => ['2' => '15.00', '3' => '15.00'],
            ]))
            ->assertSessionHasNoErrors();
    }

    public function test_package_prices_reject_amounts_stripe_cannot_charge_and_unknown_sizes(): void
    {
        [$owner] = $this->createPhotographerUser();

        $this->actingAs($owner)
            ->post(route('events.store'), $this->validEventPayload([
                'package_prices' => ['2' => '0.49', '3' => 'abc'],
            ]))
            ->assertSessionHasErrors(['package_prices.2', 'package_prices.3']);

        $this->actingAs($owner)
            ->post(route('events.store'), $this->validEventPayload([
                'package_prices' => ['7' => '5.00'],
            ]))
            ->assertSessionHasErrors('package_prices');

        $this->assertSame(0, Event::count());
    }

    public function test_prices_with_more_than_two_decimals_are_rejected_instead_of_rounded(): void
    {
        [$owner] = $this->createPhotographerUser();

        $this->actingAs($owner)
            ->post(route('events.store'), $this->validEventPayload([
                'price' => '15.005',
                'package_prices' => ['2' => '12.999'],
            ]))
            ->assertSessionHasErrors(['price', 'package_prices.2']);

        $this->actingAs($owner)
            ->post(route('events.store'), $this->validEventPayload([
                'price' => '15',
                'package_prices' => ['2' => '12.5', '3' => '12.25'],
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Event::count());
    }

    public function test_photographer_cannot_change_package_prices_of_an_event_they_do_not_manage(): void
    {
        [$outsider] = $this->createPhotographerUser();
        $event = $this->createEvent(['price_cents' => 1700]);
        $event->priceTiers()->create(['min_photos' => 2, 'price_cents' => 1500]);

        $this->actingAs($outsider)
            ->put(route('events.update', ['event' => $event->id]), $this->validEventPayload([
                'price' => '17.00',
                'package_prices' => ['2' => '1.00'],
            ]))
            ->assertForbidden();

        $this->assertSame([2 => 1500], $event->priceTiers()->pluck('price_cents', 'min_photos')->all());
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

    private function createAdminUser(): User
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

        return $admin;
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
            'starts_at' => '2026-10-10T08:00',
            'timezone' => 'America/New_York',
            'city' => 'Orlando',
            'state' => 'FL',
            'country_code' => 'US',
            'price' => '10.00',
            'status' => Event::STATUS_DRAFT,
        ], $overrides);
    }
}
