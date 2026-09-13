@extends('layouts.app')

@section('title', $seoTitle)
@section('meta-description', $seoDescription)
@section('meta-keywords', e(collect([
    $event->title,
    $event->sport ? $event->sport . ' photos' : null,
    $event->city ? $event->city . ' event photos' : null,
    'sports event photos',
    'WivorPhotos',
])->filter()->unique()->implode(', ')))
@section('canonical', $canonicalUrl)
@section('og-image', $event->cover_url)

@section('styles')
    <style>
        .event-gallery__cover {
            aspect-ratio: 4 / 3;
            max-height: 240px;
            object-fit: cover;
        }
    </style>
@endsection

@section('content')
    <main class="container py-5">
        <nav class="mb-4" aria-label="Breadcrumb">
            <a class="text-decoration-none" href="{{ route('events.show', ['event' => $event->slug]) }}">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Back to event
            </a>
        </nav>

        <header class="card border-0 bg-light overflow-hidden mb-4">
            <div class="row g-0 align-items-center">
                <div class="col-md-4 col-lg-3">
                    <img class="event-gallery__cover img-fluid w-100" src="{{ $event->cover_url }}" alt="Cover for {{ $event->title }}">
                </div>
                <div class="col-md-8 col-lg-9">
                    <div class="card-body p-4">
                        <span class="text-uppercase small fw-semibold text-primary">{{ $event->sport_label }}</span>
                        <h1 class="h2 mt-2 mb-3">{{ $event->title }} photos</h1>
                        <p class="text-muted mb-2">
                            <i class="bi bi-calendar-event me-1" aria-hidden="true"></i> {{ $event->date_label }}
                            <span class="mx-2" aria-hidden="true">·</span>
                            <i class="bi bi-geo-alt me-1" aria-hidden="true"></i> {{ $event->location_label }}
                        </p>
                        <p class="h4 text-primary mb-0">
                            {{ number_format($availablePhotoCount) }} available {{ $availablePhotoCount === 1 ? 'photo' : 'photos' }}
                        </p>
                    </div>
                </div>
            </div>
        </header>

        @if ($event->isSellable())
            <div class="alert alert-light border d-flex flex-wrap justify-content-between align-items-center gap-2" role="status">
                <span>{{ $event->price_label }} per photo</span>
                @if ($cartCount > 0)
                    <a class="btn btn-sm btn-primary" href="{{ route('cart.show') }}">{{ $cartSelectionLabel }}</a>
                @endif
            </div>
        @endif

        @if (($faceSearchStatus ?? null) === 'matches')
            <div class="alert alert-success" role="status">
                We found {{ $faceSearchCount }} {{ $faceSearchCount === 1 ? 'photo' : 'photos' }} that may include you.
            </div>
        @elseif (($faceSearchStatus ?? null) === 'no_matches')
            <div class="alert alert-info" role="status">
                <strong>We couldn't find a strong match in this event.</strong><br>
                Try another clear selfie or search using your bib number from the event page.
            </div>
        @elseif (($faceSearchStatus ?? null) === 'no_face')
            <div class="alert alert-info" role="status">
                <strong>We couldn't clearly detect a face in that photo.</strong><br>
                Try a photo showing one person clearly, facing the camera when possible.
            </div>
        @elseif (($faceSearchStatus ?? null) === 'unavailable')
            <div class="alert alert-warning" role="status">
                <strong>Face search is temporarily unavailable.</strong><br>
                You can still browse this gallery or return to the event page for Bib number search.
            </div>
        @endif

        <section class="card border-0 bg-light" aria-labelledby="event-photos">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h2 id="event-photos" class="h4 mb-1">
                            {{ ($faceSearchStatus ?? null) === 'matches' ? 'Possible face matches' : ($bibNumber ? 'Photos matching bib #'.$bibNumber : 'Event photos') }}
                        </h2>
                        <p class="text-muted mb-0">
                            @if ($bibNumber || ($faceSearchStatus ?? null) === 'matches')
                                {{ number_format($photos->total()) }} {{ $photos->total() === 1 ? 'match' : 'matches' }} ·
                            @endif
                            {{ number_format($availablePhotoCount) }} event {{ $availablePhotoCount === 1 ? 'photo' : 'photos' }}
                        </p>
                    </div>
                    @if ($bibNumber || ($faceSearchStatus ?? null) === 'matches')
                        <a href="{{ route('events.photos.index', ['event' => $event->slug]) }}">View all photos</a>
                    @endif
                </div>

                @if ($photos->isEmpty())
                    @if ($bibNumber)
                        <p class="text-muted mt-3 mb-0">No photos were found for bib #{{ $bibNumber }} yet.</p>
                    @else
                        <p class="text-muted mt-3 mb-0">No photos are currently available.</p>
                    @endif
                @else
                    <div class="row g-3 mt-1">
                        @foreach ($photos as $photo)
                            <div class="col-6 col-md-4">
                                <a href="{{ route('events.photos.show', ['event' => $event->slug, 'photo' => $photo]) }}">
                                    <img class="img-fluid rounded w-100" style="aspect-ratio: 1 / 1; object-fit: cover;" src="{{ route('events.photos.image', ['event' => $event->slug, 'photo' => $photo]) }}" alt="{{ $photo->display_alt_text }}" loading="lazy">
                                </a>
                                @if ($event->isSellable())
                                    @if ($cartPhotoUuids->contains($photo->uuid))
                                        <form class="mt-1" method="POST" action="{{ route('cart.items.destroy', ['photo' => $photo->uuid]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-secondary w-100" type="submit">Remove from selection</button>
                                        </form>
                                    @else
                                        <form class="mt-1" method="POST" action="{{ route('cart.items.store') }}">
                                            @csrf
                                            <input type="hidden" name="photo" value="{{ $photo->uuid }}">
                                            <button class="btn btn-sm btn-primary w-100" type="submit">Add to selection</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">{{ $photos->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
                @endif
            </div>
        </section>
    </main>
@endsection
