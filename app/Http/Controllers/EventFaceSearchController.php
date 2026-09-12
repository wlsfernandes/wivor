<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Photo;
use App\Services\CartService;
use App\Services\FaceRecognitionService;
use Aws\Exception\AwsException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class EventFaceSearchController extends Controller
{
    public function __construct(
        private readonly FaceRecognitionService $faceRecognition,
        private readonly CartService $cart,
    ) {
    }

    public function __invoke(Request $request, Event $event): View|RedirectResponse
    {
        abort_unless(config('face_recognition.enabled') && $event->status === Event::STATUS_PUBLISHED, 404);

        if ($event->sales_close_at?->isPast()) {
            return redirect()->route('events.show', ['event' => $event->slug])
                ->withErrors(['selfie' => 'Face search is unavailable because this event gallery is closed.']);
        }

        $validated = $request->validate([
            'selfie' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
            'face_search_consent' => ['accepted'],
        ], [
            'selfie.required' => 'Choose a JPEG or PNG selfie to search this event.',
            'selfie.image' => 'The selfie must be a valid JPEG or PNG image.',
            'selfie.mimes' => 'The selfie must be a JPEG or PNG image.',
            'selfie.max' => 'The selfie must be 10 MB or smaller.',
            'face_search_consent.accepted' => 'You must agree before using face search.',
        ]);

        try {
            $imageBytes = file_get_contents($validated['selfie']->getRealPath());

            if ($imageBytes === false) {
                throw new RuntimeException('The temporary upload could not be read.');
            }

            $result = $this->faceRecognition->searchEventByImage(
                $event,
                $imageBytes,
                (float) config('face_recognition.similarity_threshold'),
                (int) config('face_recognition.max_results'),
            );

            $matches = $result['face_detected']
                ? $this->resolvePublishedMatches($event, $result['matches'])
                : collect();
            $status = ! $result['face_detected']
                ? 'no_face'
                : ($matches->isEmpty() ? 'no_matches' : 'matches');

            Log::info('Customer face search completed.', [
                'event_uuid' => $event->uuid,
                'outcome' => $status,
                'matching_photos' => $matches->count(),
            ]);
        } catch (Throwable $exception) {
            $matches = collect();
            $status = 'unavailable';

            Log::warning('Customer face search failed.', [
                'event_uuid' => $event->uuid,
                'error_category' => $exception instanceof AwsException
                    ? ($exception->getAwsErrorCode() ?: 'aws_error')
                    : class_basename($exception),
            ]);
        }

        return view('events.post-show', $this->viewData($event, $matches, $status));
    }

    /**
     * @param  list<array{photo_uuid: string, similarity: float}>  $candidates
     * @return Collection<int, Photo>
     */
    private function resolvePublishedMatches(Event $event, array $candidates): Collection
    {
        $photoUuids = collect($candidates)->pluck('photo_uuid')->unique()->values();
        $photos = $event->photos()
            ->with('photographer')
            ->where('status', Photo::STATUS_PUBLISHED)
            ->whereIn('uuid', $photoUuids)
            ->get()
            ->keyBy('uuid');

        return $photoUuids
            ->map(fn (string $uuid): ?Photo => $photos->get($uuid))
            ->filter()
            ->take((int) config('face_recognition.max_results'))
            ->values();
    }

    /** @return array<string, mixed> */
    private function viewData(Event $event, Collection $matches, string $status): array
    {
        $photosLiveLabel = $event->photos_live_at
            ? $event->photos_live_at->timezone($event->timezone)->format('F j, Y \a\t g:i A T')
            : null;
        $availabilityMessage = match (true) {
            $event->photos()->where('status', Photo::STATUS_PUBLISHED)->exists() => 'Browse the photographs currently published for this event.',
            $event->public_availability_label === 'Photos coming soon' => "Photos for this event are not available yet. Please return after {$photosLiveLabel}.",
            default => 'Event photography is being prepared. Please check back soon.',
        };

        $photos = $status === 'matches'
            ? new LengthAwarePaginator(
                $matches,
                $matches->count(),
                (int) config('face_recognition.max_results'),
                1,
                ['path' => route('events.show', ['event' => $event->slug])],
            )
            : $event->photos()
                ->with('photographer')
                ->where('status', Photo::STATUS_PUBLISHED)
                ->latest('published_at')
                ->paginate(48)
                ->withPath(route('events.show', ['event' => $event->slug]));
        $cartEvent = $this->cart->event();
        $cartCount = $this->cart->count();

        return [
            'event' => $event,
            'seoTitle' => "{$event->title} Photos | WivorPhotos",
            'seoDescription' => "Find professional photos from {$event->title} in {$event->location_label}.",
            'canonicalUrl' => route('events.show', ['event' => $event->slug]),
            'availabilityMessage' => $availabilityMessage,
            'bibNumber' => null,
            'photos' => $photos,
            'faceSearchStatus' => $status,
            'faceSearchCount' => $matches->count(),
            'cartPhotoUuids' => $cartEvent?->is($event) ? $this->cart->photos()->pluck('uuid') : collect(),
            'cartCount' => $cartCount,
            'cartSelectionLabel' => $cartCount.' '.($cartCount === 1 ? 'photo' : 'photos').' selected · $'.number_format($this->cart->subtotalCents() / 100, 2).' · View selection',
        ];
    }
}
