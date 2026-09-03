<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Photo;
use App\Services\PhotographerUploadAccess;
use App\Services\PhotoStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhotoDeliveryController extends Controller
{
    public function photographerPreview(Request $request, Event $event, Photo $photo, PhotographerUploadAccess $access, PhotoStorage $storage): RedirectResponse
    {
        $access->assignment($request->user(), $event);
        abort_unless($photo->event_id === $event->id && $photo->photographer_id === $request->user()->photographer->id, 404);
        abort_unless($photo->thumbnail_key && in_array($photo->status, [Photo::STATUS_READY, Photo::STATUS_PUBLISHED], true), 404);
        return redirect()->away($storage->deliveryUrl($photo->thumbnail_key));
    }

    /** Display an indexable landing page for one published photograph. */
    public function gallery(Event $event, Photo $photo): View
    {
        $this->assertPublic($event, $photo);
        $photo->loadMissing(['event', 'photographer']);

        $canonicalUrl = route('events.photos.show', ['event' => $event->slug, 'photo' => $photo]);
        $imageUrl = route('events.photos.image', ['event' => $event->slug, 'photo' => $photo]);
        $photographerName = $photo->photographer->full_name;
        $people = $photo->public_people;
        $licenseUrl = filter_var(config('seo.image_license_url'), FILTER_VALIDATE_URL) ?: null;
        $subject = $people->isNotEmpty()
            ? 'Photo of '.$people->join(', ', ' and ')
            : $photo->display_title;
        $description = $photo->caption ?: "{$subject} at {$event->title} in {$event->location_label}, photographed by {$photographerName}.";
        $seoDescription = Str::limit($description, 160);

        $imageObject = array_filter([
            '@type' => 'ImageObject',
            '@id' => $canonicalUrl.'#image',
            'name' => $photo->display_title,
            'description' => $description,
            'caption' => $photo->caption,
            'contentUrl' => $imageUrl,
            'thumbnailUrl' => $imageUrl,
            'encodingFormat' => 'image/jpeg',
            'width' => $photo->preview_width,
            'height' => $photo->preview_height,
            'dateCreated' => $event->date_of_event?->toDateString(),
            'datePublished' => $photo->published_at?->toIso8601String(),
            'uploadDate' => $photo->uploaded_at?->toIso8601String(),
            'representativeOfPage' => true,
            'creator' => array_filter([
                '@type' => 'Person',
                'name' => $photographerName,
                'sameAs' => filter_var($photo->photographer->profile_url, FILTER_VALIDATE_URL) ?: null,
            ]),
            'creditText' => "{$photographerName} / ".config('seo.brand'),
            'copyrightNotice' => $photo->copyright_notice,
            'license' => $licenseUrl,
            'acquireLicensePage' => route('contact_us'),
            'contentLocation' => [
                '@type' => 'Place',
                'name' => collect([$event->venue_name, $event->location_label])->filter()->implode(', '),
                'address' => array_filter([
                    '@type' => 'PostalAddress',
                    'addressLocality' => $event->city,
                    'addressRegion' => $event->state,
                    'addressCountry' => $event->country_code,
                ]),
            ],
            'about' => collect([
                [
                    '@type' => 'SportsEvent',
                    'name' => $event->title,
                    'url' => route('events.show', ['event' => $event->slug]),
                    'startDate' => $event->starts_at?->toIso8601String() ?? $event->date_of_event?->toDateString(),
                ],
            ])->concat($people->map(fn (string $name): array => [
                '@type' => 'Person',
                'name' => $name,
            ]))->all(),
        ], fn ($value) => $value !== null && $value !== '');

        return view('photos.show', [
            'event' => $event,
            'photo' => $photo,
            'people' => $people,
            'photographerName' => $photographerName,
            'canonicalUrl' => $canonicalUrl,
            'imageUrl' => $imageUrl,
            'seoTitle' => $photo->display_title.' | '.config('seo.brand'),
            'seoDescription' => $seoDescription,
            'copyrightNotice' => $photo->copyright_notice,
            'licenseUrl' => $licenseUrl,
            'structuredData' => [
                '@context' => 'https://schema.org',
                '@graph' => [
                    [
                        '@type' => 'WebPage',
                        '@id' => $canonicalUrl.'#webpage',
                        'url' => $canonicalUrl,
                        'name' => $photo->display_title,
                        'description' => $description,
                        'mainEntity' => ['@id' => $canonicalUrl.'#image'],
                        'primaryImageOfPage' => ['@id' => $canonicalUrl.'#image'],
                    ],
                    $imageObject,
                ],
            ],
        ]);
    }

    /** Serve a stable, crawlable URL while the underlying object remains private. */
    public function image(Event $event, Photo $photo): StreamedResponse
    {
        $this->assertPublic($event, $photo);

        $disk = Storage::disk(config('photo_uploads.disk'));
        abort_unless($disk->exists($photo->preview_key), 404);
        $stream = $disk->readStream($photo->preview_key);
        abort_unless(is_resource($stream), 404);

        $etag = '"'.($photo->checksum ?: hash('sha256', $photo->uuid)).'-preview"';
        if (request()->header('If-None-Match') === $etag) {
            fclose($stream);

            return response()->stream(static fn () => null, 304, ['ETag' => $etag]);
        }

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="'.$photo->uuid.'.jpg"',
            'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=604800',
            'ETag' => $etag,
        ]);
    }

    private function assertPublic(Event $event, Photo $photo): void
    {
        abort_unless(
            $event->status === Event::STATUS_PUBLISHED
                && $photo->event_id === $event->id
                && $photo->status === Photo::STATUS_PUBLISHED
                && $photo->preview_key,
            404
        );
        abort_if($event->sales_close_at?->isPast(), 404);
    }
}
