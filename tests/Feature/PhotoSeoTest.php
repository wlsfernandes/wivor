<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\Role;
use App\Models\UploadBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_photo_has_an_indexable_page_with_credit_people_and_social_metadata(): void
    {
        [, , $event, $photo] = $this->photoFixture();

        $response = $this->get(route('events.photos.show', [
            'event' => $event->slug,
            'photo' => $photo,
        ]));

        $response->assertOk()
            ->assertSee('<meta name="robots" content="index,follow,max-image-preview:large', false)
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('<script type="application/ld+json">', false)
            ->assertSee('"@type":"ImageObject"', false)
            ->assertSee('"name":"Alex Rivera"', false)
            ->assertSee('"name":"Jordan Silva"', false)
            ->assertSee('People in this photo')
            ->assertSee('Photographer:</strong> Alex Rivera', false)
            ->assertDontSee('meta name="keywords"', false);
        $this->assertSame(1, substr_count(strtolower($response->getContent()), '<head>'));
        $this->assertSame(1, substr_count(strtolower($response->getContent()), '<body>'));
    }

    public function test_photo_image_has_a_stable_public_url_and_cache_headers(): void
    {
        [, , $event, $photo] = $this->photoFixture();

        $response = $this->get(route('events.photos.image', [
            'event' => $event->slug,
            'photo' => $photo,
        ]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');
        $this->assertStringContainsString('max-age=86400', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
        $this->assertSame('jpeg preview', $response->streamedContent());
    }

    public function test_unpublished_and_expired_photos_are_not_public(): void
    {
        [, , $event, $photo] = $this->photoFixture(['status' => Photo::STATUS_READY]);

        $this->get(route('events.photos.show', ['event' => $event->slug, 'photo' => $photo]))
            ->assertNotFound();

        $photo->update(['status' => Photo::STATUS_PUBLISHED]);
        $event->update(['sales_close_at' => now()->subMinute()]);

        $this->get(route('events.photos.show', ['event' => $event->slug, 'photo' => $photo]))
            ->assertNotFound();
    }

    public function test_image_sitemap_lists_the_photo_landing_page_and_stable_jpeg(): void
    {
        [, , $event, $photo] = $this->photoFixture();

        $this->get(route('sitemap.index'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('sitemap.photos', ['page' => 1]), false);

        $this->get(route('sitemap.photos', ['page' => 1]))
            ->assertOk()
            ->assertSee(route('events.photos.show', ['event' => $event->slug, 'photo' => $photo]), false)
            ->assertSee(route('events.photos.image', ['event' => $event->slug, 'photo' => $photo]), false)
            ->assertSee('<image:image>', false);
    }

    /** @return array{User, Photographer, Event, Photo} */
    private function photoFixture(array $photoOverrides = []): array
    {
        Storage::fake('photo-seo');
        config(['photo_uploads.disk' => 'photo-seo']);

        $role = Role::firstOrCreate(['name' => 'photographer']);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->attach($role);
        $photographer = Photographer::create([
            'user_id' => $user->id,
            'first_name' => 'Alex',
            'last_name' => 'Rivera',
            'profile_url' => 'https://example.com/alex-rivera',
        ]);
        $photographer->forceFill(['status' => Photographer::STATUS_APPROVED])->save();

        $event = Event::create([
            'title' => 'City Run',
            'slug' => 'city-run-seo',
            'sport' => 'Running',
            'content' => 'A city road race.',
            'status' => Event::STATUS_PUBLISHED,
            'published' => true,
            'published_at' => now()->subDay(),
            'date_of_event' => '2026-08-30',
            'starts_at' => '2026-08-30 09:00:00',
            'sales_close_at' => now()->addMonth(),
            'timezone' => 'America/New_York',
            'venue_name' => 'Riverside Park',
            'city' => 'Orlando',
            'state' => 'FL',
            'country_code' => 'US',
        ]);
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
        $photo = Photo::create(array_merge([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'assignment_id' => $assignment->id,
            'upload_batch_id' => $batch->id,
            'original_filename' => 'finish.jpg',
            'title' => 'Runner at the City Run finish line',
            'alt_text' => 'Jordan Silva raises both arms after finishing the City Run.',
            'caption' => 'Jordan Silva celebrates at the finish line in Riverside Park.',
            'copyright_notice' => '© 2026 Alex Rivera',
            'people' => ['Jordan Silva'],
            'people_publication_confirmed_at' => now(),
            'original_key' => 'photos/original.jpg',
            'preview_key' => 'photos/preview.jpg',
            'thumbnail_key' => 'photos/thumbnail.jpg',
            'detected_mime' => 'image/jpeg',
            'width' => 2400,
            'height' => 1600,
            'checksum' => hash('sha256', 'jpeg preview'),
            'status' => Photo::STATUS_PUBLISHED,
            'uploaded_at' => now()->subDay(),
            'published_at' => now()->subHours(12),
        ], $photoOverrides));
        Storage::disk('photo-seo')->put($photo->preview_key, 'jpeg preview');

        return [$user, $photographer, $event, $photo];
    }
}
