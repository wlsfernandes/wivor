@extends('layouts.app')

@section('title', $seoTitle)
@section('meta-description', $seoDescription)
@section('meta-keywords', e(collect([
    $event->title,
    $event->sport ? $event->sport . ' photos' : null,
    $event->city ? $event->city . ' event photos' : null,
    $event->state ? $event->state . ' sports photography' : null,
    'sports event photos',
    'WivorPhotos',
])->filter()->unique()->implode(', ')))
@section('canonical', $canonicalUrl)
@section('og-image', $event->cover_url)

@section('content')
    <main class="container py-5">
        <article>
            <img src="{{ $event->cover_url }}" alt="Cover for {{ $event->title }}" class="img-fluid rounded w-100 mb-4" style="max-height: 520px; object-fit: cover;">

            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <span class="text-uppercase small fw-semibold text-primary">{{ $event->sport_label }}</span>
                    <h1 class="mt-2">{{ $event->title }}</h1>
                    <p class="lead text-muted">{{ $event->date_label }} · {{ $event->location_label }}</p>
                    @if ($event->venue_name)
                        <p class="text-muted">{{ $event->venue_name }}</p>
                    @endif

                    <div class="alert alert-info my-4" role="status">
                        <h2 class="h5">{{ $event->public_availability_label }}</h2>
                        <p class="mb-0">{{ $availabilityMessage }}</p>
                    </div>

                    @if ($event->isSellable())
                        <div class="alert alert-light border d-flex flex-wrap justify-content-between align-items-center gap-2" role="status">
                            <span>{{ $event->price_label }} per photo</span>
                            @if ($cartCount > 0)
                                <a class="btn btn-sm btn-primary" href="{{ route('cart.show') }}">
                                    {{ $cartSelectionLabel }}
                                </a>
                            @endif
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                    @endif

                    @if ($event->content)
                        <section class="my-5" aria-labelledby="event-description">
                            <h2 id="event-description" class="h4">About this event</h2>
                            <p class="text-muted">{{ $event->content }}</p>
                        </section>
                    @endif

                    <section class="card border-0 bg-light my-5" aria-labelledby="bib-search-heading">
                        <div class="card-body py-4">
                            <h2 id="bib-search-heading" class="h4">Find your photos</h2>
                            <form class="row g-2 align-items-end" method="GET" action="{{ route('events.show', ['event' => $event->slug]) }}">
                                <div class="col-sm-8">
                                    <label class="form-label" for="bib">Bib number</label>
                                    <input class="form-control" id="bib" name="bib" type="text" inputmode="numeric" pattern="[0-9]{1,5}" maxlength="5" value="{{ old('bib', $bibNumber) }}" placeholder="Enter your bib number">
                                </div>
                                <div class="col-sm-4">
                                    <button class="btn btn-primary w-100" type="submit">Search</button>
                                </div>
                            </form>

                            @if (config('face_recognition.enabled') && ! $event->sales_close_at?->isPast())
                                <div class="d-flex align-items-center gap-3 my-4" aria-hidden="true">
                                    <hr class="flex-grow-1 my-0">
                                    <span class="small text-muted text-uppercase">or</span>
                                    <hr class="flex-grow-1 my-0">
                                </div>

                                <h3 class="h5">Find me with a selfie</h3>
                                <p class="text-muted mb-2">For best results, upload a photo showing one person clearly.</p>
                                <p class="small text-muted">
                                    Your selfie is used only to search this event and is processed by Amazon Rekognition for face matching. WivorPhotos does not save your selfie.
                                </p>

                                <form method="POST" action="{{ route('events.face-search', ['event' => $event->slug]) }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label" for="selfie">JPEG or PNG selfie</label>
                                        <input class="form-control" id="selfie" name="selfie" type="file" accept="image/jpeg,image/png" required>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" id="face_search_consent" name="face_search_consent" type="checkbox" value="1" required>
                                        <label class="form-check-label" for="face_search_consent">
                                            I agree to use this photo only to search for matching photos in this event.
                                        </label>
                                    </div>
                                    <button class="btn btn-outline-primary" type="submit">Find my photos</button>
                                </form>

                                @if (($faceSearchStatus ?? null) === 'matches')
                                    <div class="alert alert-success mt-4 mb-0" role="status">
                                        We found {{ $faceSearchCount }} {{ $faceSearchCount === 1 ? 'photo' : 'photos' }} that may include you.
                                    </div>
                                @elseif (($faceSearchStatus ?? null) === 'no_matches')
                                    <div class="alert alert-info mt-4 mb-0" role="status">
                                        <strong>We couldn't find a strong match in this event.</strong><br>
                                        Try another clear photo of yourself, or search using your bib number.
                                    </div>
                                @elseif (($faceSearchStatus ?? null) === 'no_face')
                                    <div class="alert alert-info mt-4 mb-0" role="status">
                                        <strong>We couldn't clearly detect a face in that photo.</strong><br>
                                        Try a photo showing one person clearly, facing the camera when possible.
                                    </div>
                                @elseif (($faceSearchStatus ?? null) === 'unavailable')
                                    <div class="alert alert-warning mt-4 mb-0" role="status">
                                        <strong>Face search is temporarily unavailable.</strong><br>
                                        You can still browse the event or search by bib number.
                                    </div>
                                @endif
                            @endif
                        </div>
                    </section>

                    <section class="card border-0 bg-light my-5" aria-labelledby="event-photos">
                        <div class="card-body py-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <h2 id="event-photos" class="h4 mb-0">
                                    {{ ($faceSearchStatus ?? null) === 'matches' ? 'Possible face matches' : ($bibNumber ? 'Photos matching bib #'.$bibNumber : 'Event photos') }}
                                </h2>
                                @if ($bibNumber || ($faceSearchStatus ?? null) === 'matches')
                                    <a href="{{ route('events.show', ['event' => $event->slug]) }}">View all photos</a>
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
                                <div class="mt-4">{{ $photos->links() }}</div>
                            @endif
                        </div>
                    </section>

                </div>
            </div>
        </article>
    </main>
@endsection
