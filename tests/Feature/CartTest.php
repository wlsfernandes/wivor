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

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_and_remove_a_photo_from_the_cart(): void
    {
        [$event, $photo] = $this->publishedPhoto();

        $this->post(route('cart.items.store'), ['photo' => $photo->uuid])->assertRedirect();
        $this->get(route('cart.show'))
            ->assertOk()
            ->assertSee('1 photo(s) selected')
            ->assertSee('Subtotal: $10.00');

        $this->delete(route('cart.items.destroy', ['photo' => $photo->uuid]))->assertRedirect();
        $this->get(route('cart.show'))->assertSee('You have not selected any photos yet.');
    }

    public function test_cart_rejects_a_photo_from_a_different_photographer(): void
    {
        [$eventOne, $photoOne] = $this->publishedPhoto();
        [, $photoTwo] = $this->publishedPhoto();

        $this->post(route('cart.items.store'), ['photo' => $photoOne->uuid])->assertRedirect();
        $this->post(route('cart.items.store'), ['photo' => $photoTwo->uuid])
            ->assertSessionHasErrors('photo');

        $this->get(route('cart.show'))->assertSee('1 photo(s) selected');
    }

    public function test_cart_subtotal_reflects_the_current_event_price(): void
    {
        [$event, $photo] = $this->publishedPhoto();
        $this->post(route('cart.items.store'), ['photo' => $photo->uuid])->assertRedirect();

        $event->update(['price_cents' => 1500]);

        $this->get(route('cart.show'))->assertSee('Subtotal: $15.00');
    }

    /** @return array{Event, Photo} */
    private function publishedPhoto(array $eventOverrides = []): array
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $photographer = Photographer::create([
            'user_id' => $user->id,
            'first_name' => 'Alex',
            'last_name' => 'Rivera',
        ]);
        $photographer->forceFill(['status' => Photographer::STATUS_APPROVED])->save();

        $event = Event::create(array_merge([
            'title' => 'City Run '.uniqid(),
            'slug' => Event::generateUniqueSlug('City Run '.uniqid()),
            'sport' => 'Running',
            'content' => 'A city road race.',
            'status' => Event::STATUS_PUBLISHED,
            'published' => true,
            'published_at' => now()->subDay(),
            'date_of_event' => '2026-08-30',
            'sales_close_at' => now()->addMonth(),
            'price_cents' => 1000,
            'timezone' => 'America/New_York',
            'city' => 'Orlando',
            'state' => 'FL',
            'country_code' => 'US',
        ], $eventOverrides));

        $assignment = EventAssignment::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'status' => 'approved',
            'upload_deadline_at' => now()->addDays(3),
        ]);
        $batch = UploadBatch::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'assignment_id' => $assignment->id,
            'selected_count' => 1,
            'status' => 'completed',
        ]);
        $photo = Photo::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'assignment_id' => $assignment->id,
            'upload_batch_id' => $batch->id,
            'original_filename' => 'finish.jpg',
            'original_key' => 'photos/original-'.uniqid().'.jpg',
            'detected_mime' => 'image/jpeg',
            'width' => 2400,
            'height' => 1600,
            'checksum' => hash('sha256', uniqid('', true)),
            'status' => Photo::STATUS_PUBLISHED,
            'uploaded_at' => now()->subDay(),
            'published_at' => now()->subHours(12),
        ]);

        return [$event, $photo];
    }
}
