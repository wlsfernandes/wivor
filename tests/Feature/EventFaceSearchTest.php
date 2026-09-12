<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\UploadBatch;
use App\Models\User;
use App\Services\FaceRecognitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class EventFaceSearchTest extends TestCase
{
    use RefreshDatabase;

    private Photographer $photographer;

    protected function setUp(): void
    {
        parent::setUp();

        config(['face_recognition.enabled' => true]);
        Storage::fake('media');
        config(['photo_uploads.disk' => 'media']);
        $user = User::factory()->create();
        $this->photographer = Photographer::create([
            'user_id' => $user->id,
            'first_name' => 'Face',
            'last_name' => 'Search',
        ]);
    }

    public function test_enabled_feature_displays_selfie_search_on_a_public_event(): void
    {
        $event = $this->event();

        $this->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Find me with a selfie')
            ->assertSee('Amazon Rekognition')
            ->assertSee('WivorPhotos does not save your selfie.')
            ->assertSee(route('events.face-search', $event), false)
            ->assertSee('accept="image/jpeg,image/png"', false)
            ->assertSee('name="face_search_consent"', false);
    }

    public function test_disabled_feature_hides_ui_and_rejects_direct_posts(): void
    {
        config(['face_recognition.enabled' => false]);
        $event = $this->event();
        $service = $this->createMock(FaceRecognitionService::class);
        $service->expects($this->never())->method('searchEventByImage');
        $this->app->instance(FaceRecognitionService::class, $service);

        $this->get(route('events.show', $event))
            ->assertOk()
            ->assertDontSee('Find me with a selfie');

        $this->post(route('events.face-search', $event), [
            'selfie' => $this->selfie(),
            'face_search_consent' => '1',
        ])->assertNotFound();
    }

    public function test_consent_is_required_before_service_is_called(): void
    {
        $event = $this->event();
        $this->mockServiceNeverCalled();

        $this->from(route('events.show', $event))
            ->post(route('events.face-search', $event), ['selfie' => $this->selfie()])
            ->assertRedirect(route('events.show', $event))
            ->assertSessionHasErrors('face_search_consent');
    }

    public function test_selfie_file_is_required_before_service_is_called(): void
    {
        $event = $this->event();
        $this->mockServiceNeverCalled();

        $this->from(route('events.show', $event))
            ->post(route('events.face-search', $event), ['face_search_consent' => '1'])
            ->assertRedirect(route('events.show', $event))
            ->assertSessionHasErrors('selfie');
    }

    public function test_non_image_upload_is_rejected_before_service_is_called(): void
    {
        $event = $this->event();
        $this->mockServiceNeverCalled();

        $this->from(route('events.show', $event))
            ->post(route('events.face-search', $event), [
                'selfie' => UploadedFile::fake()->create('document.pdf', 5, 'application/pdf'),
                'face_search_consent' => '1',
            ])
            ->assertRedirect(route('events.show', $event))
            ->assertSessionHasErrors('selfie');
    }

    public function test_successful_search_shows_only_published_matches_from_the_selected_event(): void
    {
        $event = $this->event(true);
        $otherEvent = $this->event();
        $matchingPhoto = $this->photo($event, Photo::STATUS_PUBLISHED);
        $unpublishedPhoto = $this->photo($event, Photo::STATUS_READY);
        $otherEventPhoto = $this->photo($otherEvent, Photo::STATUS_PUBLISHED);
        $upload = $this->selfie();
        $imageBytes = file_get_contents($upload->getRealPath());
        $service = $this->createMock(FaceRecognitionService::class);
        $service->expects($this->once())
            ->method('searchEventByImage')
            ->with(
                $this->callback(fn (Event $candidate): bool => $candidate->is($event)),
                $imageBytes,
                90.0,
                50,
            )
            ->willReturn([
                'collection_id' => 'wivor-event-'.$event->uuid,
                'face_detected' => true,
                'matches' => [
                    ['photo_uuid' => $otherEventPhoto->uuid, 'similarity' => 99.9],
                    ['photo_uuid' => $unpublishedPhoto->uuid, 'similarity' => 99.8],
                    ['photo_uuid' => $matchingPhoto->uuid, 'similarity' => 97.2],
                ],
            ]);
        $this->app->instance(FaceRecognitionService::class, $service);

        $this->get(route('events.show', $event))->assertOk();
        $response = $this->post(route('events.face-search', $event), [
            'selfie' => $upload,
            'face_search_consent' => '1',
        ]);

        $response->assertOk()
            ->assertSee('We found 1 photo that may include you.')
            ->assertSee('Possible face matches')
            ->assertSee(route('events.photos.show', [$event, $matchingPhoto]), false)
            ->assertDontSee(route('events.photos.show', [$event, $unpublishedPhoto]), false)
            ->assertDontSee(route('events.photos.show', [$otherEvent, $otherEventPhoto]), false)
            ->assertDontSee('97.2')
            ->assertSee('Add to selection');

        $this->post(route('cart.items.store'), ['photo' => $matchingPhoto->uuid])
            ->assertRedirect(route('events.show', $event))
            ->assertSessionHasNoErrors();
        $this->assertTrue(session()->has('wivor_cart'));
    }

    public function test_no_match_response_is_friendly_and_keeps_normal_gallery_available(): void
    {
        $event = $this->event();
        $photo = $this->photo($event, Photo::STATUS_PUBLISHED);
        $this->mockResult($event, true, []);

        $this->postFaceSearch($event)
            ->assertOk()
            ->assertSee("We couldn't find a strong match in this event.", false)
            ->assertSee('search using your bib number')
            ->assertSee('Event photos')
            ->assertSee(route('events.photos.show', [$event, $photo]), false);
    }

    public function test_no_face_response_is_friendly(): void
    {
        $event = $this->event();
        $this->mockResult($event, false, []);

        $this->postFaceSearch($event)
            ->assertOk()
            ->assertSee("We couldn't clearly detect a face in that photo.", false)
            ->assertSee('one person clearly');
    }

    public function test_aws_failure_is_friendly_and_normal_event_features_remain_available(): void
    {
        $event = $this->event();
        $photo = $this->photo($event, Photo::STATUS_PUBLISHED);
        $service = $this->createMock(FaceRecognitionService::class);
        $service->method('searchEventByImage')->willThrowException(new RuntimeException('Secret AWS details.'));
        $this->app->instance(FaceRecognitionService::class, $service);

        $this->postFaceSearch($event)
            ->assertOk()
            ->assertSee('Face search is temporarily unavailable.')
            ->assertSee('Bib number')
            ->assertSee(route('events.photos.show', [$event, $photo]), false)
            ->assertDontSee('Secret AWS details.');
    }

    public function test_selfie_is_not_written_to_storage_database_session_or_a_queue(): void
    {
        Queue::fake();
        $event = $this->event();
        $photo = $this->photo($event, Photo::STATUS_PUBLISHED);
        $this->mockResult($event, true, [
            ['photo_uuid' => $photo->uuid, 'similarity' => 98.4],
        ]);
        $photoCount = Photo::count();

        $this->postFaceSearch($event)->assertOk();

        $this->assertSame([], Storage::disk('media')->allFiles());
        $this->assertSame($photoCount, Photo::count());
        $this->assertFalse(session()->has('selfie'));
        $this->assertFalse(session()->has('face_search_results'));
        Queue::assertNothingPushed();
    }

    public function test_draft_event_cannot_be_face_searched(): void
    {
        $event = $this->event();
        $event->update(['status' => Event::STATUS_DRAFT, 'published' => false]);
        $this->mockServiceNeverCalled();

        $this->postFaceSearch($event)->assertNotFound();
    }

    private function mockResult(Event $event, bool $faceDetected, array $matches): void
    {
        $service = $this->createMock(FaceRecognitionService::class);
        $service->expects($this->once())
            ->method('searchEventByImage')
            ->with(
                $this->callback(fn (Event $candidate): bool => $candidate->is($event)),
                $this->callback(fn ($bytes): bool => is_string($bytes) && $bytes !== ''),
                90.0,
                50,
            )
            ->willReturn([
                'collection_id' => 'wivor-event-'.$event->uuid,
                'face_detected' => $faceDetected,
                'matches' => $matches,
            ]);
        $this->app->instance(FaceRecognitionService::class, $service);
    }

    private function mockServiceNeverCalled(): void
    {
        $service = $this->createMock(FaceRecognitionService::class);
        $service->expects($this->never())->method('searchEventByImage');
        $this->app->instance(FaceRecognitionService::class, $service);
    }

    private function postFaceSearch(Event $event)
    {
        return $this->post(route('events.face-search', $event), [
            'selfie' => $this->selfie(),
            'face_search_consent' => '1',
        ]);
    }

    private function selfie(): UploadedFile
    {
        return UploadedFile::fake()->image('selfie.png', 20, 20);
    }

    private function event(bool $sellable = false): Event
    {
        return Event::create([
            'title' => 'Customer Face Search '.uniqid(),
            'slug' => 'customer-face-search-'.uniqid(),
            'content' => '',
            'status' => Event::STATUS_PUBLISHED,
            'published' => true,
            'published_at' => now(),
            'date_of_event' => now(),
            'price_cents' => $sellable ? 1000 : 0,
            'sales_close_at' => $sellable ? now()->addDay() : null,
        ]);
    }

    private function photo(Event $event, string $status): Photo
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

        return Photo::create([
            'event_id' => $event->id,
            'photographer_id' => $this->photographer->id,
            'assignment_id' => $assignment->id,
            'upload_batch_id' => $batch->id,
            'original_filename' => 'face-result.jpg',
            'original_key' => 'events/test/face-result/original.jpg',
            'preview_key' => 'events/test/face-result/preview.jpg',
            'thumbnail_key' => 'events/test/face-result/thumbnail.jpg',
            'status' => $status,
            'published_at' => $status === Photo::STATUS_PUBLISHED ? now() : null,
        ]);
    }
}
