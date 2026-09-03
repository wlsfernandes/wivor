<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Photo;
use App\Models\PhotoRemovalRequest;
use App\Models\Photographer;
use App\Models\Role;
use App\Models\UploadBatch;
use App\Models\User;
use App\Services\PhotoStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhotoRemovalRequestTest extends TestCase
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

    public function test_customer_can_submit_a_removal_request_for_a_published_photo(): void
    {
        [$event, $photo] = $this->publishedPhoto();

        $this->get(route('photos.removal-requests.create', ['event' => $event->slug, 'photo' => $photo]))
            ->assertOk()
            ->assertSee($photo->reference_number);

        $this->post(route('photos.removal-requests.store', ['event' => $event->slug, 'photo' => $photo]), [
            'requester_name' => 'Jamie Customer',
            'requester_email' => 'jamie@example.com',
            'reason' => PhotoRemovalRequest::REASON_DEPICTS_ME,
            'explanation' => 'This is a photo of me and I did not consent.',
        ])->assertRedirect(route('events.photos.show', ['event' => $event->slug, 'photo' => $photo]));

        $this->assertDatabaseHas('photo_removal_requests', [
            'photo_id' => $photo->id,
            'requester_name' => 'Jamie Customer',
            'requester_email' => 'jamie@example.com',
            'reason' => PhotoRemovalRequest::REASON_DEPICTS_ME,
            'status' => PhotoRemovalRequest::STATUS_PENDING,
        ]);
    }

    public function test_removal_request_requires_name_email_and_reason(): void
    {
        [$event, $photo] = $this->publishedPhoto();

        $this->post(route('photos.removal-requests.store', ['event' => $event->slug, 'photo' => $photo]), [])
            ->assertSessionHasErrors(['requester_name', 'requester_email', 'reason']);
    }

    public function test_admin_can_view_and_resolve_removal_requests(): void
    {
        [, $photo] = $this->publishedPhoto();
        $removalRequest = PhotoRemovalRequest::create([
            'photo_id' => $photo->id,
            'requester_name' => 'Jamie Customer',
            'requester_email' => 'jamie@example.com',
            'reason' => PhotoRemovalRequest::REASON_COPYRIGHT,
            'explanation' => 'I own the copyright to this image.',
        ]);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

        $this->actingAs($admin)->get(route('admin.removal-requests.index'))
            ->assertOk()
            ->assertSee('Jamie Customer')
            ->assertSee('jamie@example.com');

        $this->actingAs($admin)->patch(route('admin.removal-requests.resolve', $removalRequest))
            ->assertRedirect();

        $removalRequest->refresh();
        $this->assertSame(PhotoRemovalRequest::STATUS_REVIEWED, $removalRequest->status);
        $this->assertSame($admin->id, $removalRequest->reviewed_by);
        $this->assertNotNull($removalRequest->reviewed_at);
    }

    public function test_non_admin_cannot_access_removal_requests_admin_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.removal-requests.index'))->assertForbidden();
    }

    /** @return array{Event, Photo} */
    private function publishedPhoto(): array
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $photographer = Photographer::create([
            'user_id' => $user->id,
            'first_name' => 'Alex',
            'last_name' => 'Rivera',
        ]);
        $photographer->forceFill(['status' => Photographer::STATUS_APPROVED])->save();

        $event = Event::create([
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
        $photo = Photo::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'assignment_id' => $assignment->id,
            'upload_batch_id' => $batch->id,
            'original_filename' => 'finish.jpg',
            'original_key' => 'photos/original-'.uniqid().'.jpg',
            'thumbnail_key' => 'photos/thumbnail-'.uniqid().'.jpg',
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
