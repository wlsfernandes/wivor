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

@section('styles')
    <style>
        .event-summary__image {
            aspect-ratio: 4 / 3;
            max-height: 360px;
            object-fit: cover;
        }

        .event-availability-dot {
            height: .55rem;
            width: .55rem;
        }

        .photo-search-card__icon {
            align-items: center;
            background: rgba(var(--bs-primary-rgb), .1);
            border-radius: 50%;
            color: var(--bs-primary);
            display: inline-flex;
            font-size: 1.5rem;
            height: 3rem;
            justify-content: center;
            width: 3rem;
        }

        .photo-search-card {
            border: 1px solid rgba(33, 37, 41, .08);
            border-radius: 1rem;
            transition: box-shadow .2s ease, transform .2s ease;
        }

        .photo-search-card:hover {
            box-shadow: 0 1rem 2rem rgba(33, 37, 41, .12) !important;
            transform: translateY(-3px);
        }

        .photo-search-card--face {
            background: linear-gradient(180deg, rgba(var(--bs-primary-rgb), .08), #fff 42%);
            border: 2px solid rgba(var(--bs-primary-rgb), .55);
        }

        .photo-search-card__icon--face {
            overflow: hidden;
            position: relative;
        }

        .photo-search-card__icon--face::after {
            animation: face-scan 2.2s ease-in-out infinite;
            background: var(--bs-primary);
            box-shadow: 0 0 8px var(--bs-primary);
            content: '';
            height: 2px;
            left: .55rem;
            opacity: .85;
            position: absolute;
            right: .55rem;
            top: .75rem;
        }

        .photo-search-card__icon--bib {
            animation: bib-pulse 2s ease-in-out infinite;
        }

        @keyframes face-scan {
            0%, 100% { transform: translateY(0); opacity: .35; }
            50% { transform: translateY(1.45rem); opacity: 1; }
        }

        @keyframes bib-pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(var(--bs-primary-rgb), .28); }
            50% { box-shadow: 0 0 0 .55rem rgba(var(--bs-primary-rgb), 0); }
        }

        .photo-search-card .btn,
        .photo-search-card .form-control {
            min-height: 46px;
        }

        @media (prefers-reduced-motion: reduce) {
            .photo-search-card,
            .photo-search-card__icon--bib,
            .photo-search-card__icon--face::after {
                animation: none;
                transition: none;
            }
        }
    </style>
@endsection

@section('content')
    <main class="container py-5">
        <article>
            <header class="card border-0 bg-light overflow-hidden mb-4">
                <div class="row g-0 align-items-stretch">
                    <div class="col-lg-5">
                        <img src="{{ $event->cover_url }}" alt="Cover for {{ $event->title }}" class="event-summary__image img-fluid w-100 h-100">
                    </div>
                    <div class="col-lg-7 d-flex align-items-center">
                        <div class="card-body p-4 p-lg-5">
                            <span class="text-uppercase small fw-semibold text-primary">{{ $event->sport_label }}</span>
                            <h1 class="mt-2 mb-4">{{ $event->title }}</h1>
                            <p class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-calendar-event text-primary" aria-hidden="true"></i>
                                <span>{{ $event->date_label }}</span>
                            </p>
                            <p class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-geo-alt text-primary" aria-hidden="true"></i>
                                <span>{{ $event->location_label }}</span>
                            </p>
                            @if ($event->venue_name)
                                <p class="small text-muted ms-4 mb-3">{{ $event->venue_name }}</p>
                            @endif
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-3 small" role="status">
                                <span class="event-availability-dot d-inline-block rounded-circle {{ $availablePhotoCount > 0 ? 'bg-success' : 'bg-secondary' }}" aria-hidden="true"></span>
                                <span class="fw-semibold">
                                    <span class="visually-hidden">{{ $event->public_availability_label }}: </span>
                                    {{ number_format($availablePhotoCount) }} {{ $availablePhotoCount === 1 ? 'photo' : 'photos' }} available
                                </span>
                                @if ($event->isSellable())
                                    <span class="text-muted" aria-hidden="true">·</span>
                                    <span>{{ $event->price_label }} per photo</span>
                                @endif
                                @if ($cartCount > 0)
                                    <span class="text-muted" aria-hidden="true">·</span>
                                    <a href="{{ route('cart.show') }}">{{ $cartSelectionLabel }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
            @endif

            <section class="my-5" aria-labelledby="photo-search-heading">
                <div class="text-center mb-4">
                    <h2 id="photo-search-heading" class="h3">Find your photos</h2>
                    <p class="text-muted mb-0">Choose the easiest way to find your event photos.</p>
                </div>

                <div class="row g-4">
                    @if (config('face_recognition.enabled') && ! $event->sales_close_at?->isPast())
                        <div class="col-12 col-lg-4 order-1 order-lg-2">
                            <div class="photo-search-card photo-search-card--face card h-100 shadow">
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                                        <span class="photo-search-card__icon photo-search-card__icon--face" aria-hidden="true"><i class="bi bi-person-bounding-box"></i></span>
                                        <span class="badge rounded-pill bg-primary">AI face search</span>
                                    </div>
                                    <h3 class="h5">Find yourself with a selfie</h3>
                                    <p class="text-muted">Upload one clear selfie to find your event photos.</p>
                                    <p class="small text-muted">
                                        Your selfie is processed by Amazon Rekognition only for this search. WivorPhotos does not save your selfie.
                                    </p>

                                    <form class="mt-auto" method="POST" action="{{ route('events.face-search', ['event' => $event->slug]) }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold" for="selfie">Upload your selfie</label>
                                            <input class="form-control" id="selfie" name="selfie" type="file" accept="image/jpeg,image/png" required>
                                            <div class="form-text">JPEG or PNG, up to 5 MB.</div>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" id="face_search_consent" name="face_search_consent" type="checkbox" value="1" required>
                                            <label class="form-check-label small" for="face_search_consent">
                                                I agree to use this photo only to search this event, as described in the <a href="{{ route('privacy') }}">Privacy Policy</a>.
                                            </label>
                                        </div>
                                        <button class="btn btn-primary w-100" type="submit">Find my photos</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="col-12 col-lg-4 order-2 order-lg-1">
                        <div class="photo-search-card card h-100 shadow-sm">
                            <div class="card-body p-4 d-flex flex-column">
                                <span class="photo-search-card__icon photo-search-card__icon--bib mb-3" aria-hidden="true"><i class="bi bi-person-vcard"></i></span>
                                <h3 class="h5">Find photos by bib number</h3>
                                <p class="text-muted">Enter the number shown on your race bib.</p>
                                <form class="mt-auto" method="GET" action="{{ route('events.photos.index', ['event' => $event->slug]) }}">
                                    <div class="mb-3">
                                        <label class="form-label" for="bib">Bib number</label>
                                        <input class="form-control" id="bib" name="bib" type="text" inputmode="numeric" pattern="[0-9]{1,5}" maxlength="5" value="{{ old('bib', $bibNumber) }}" placeholder="Enter your bib number">
                                    </div>
                                    <button class="btn btn-primary w-100" type="submit">Find photos</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4 order-3">
                        <div class="photo-search-card card h-100 shadow-sm">
                            <div class="card-body p-4 d-flex flex-column">
                                <span class="photo-search-card__icon mb-3" aria-hidden="true"><i class="bi bi-images"></i></span>
                                <h3 class="h5">Browse all photos</h3>
                                <p class="text-muted">Explore every available photo from this event.</p>
                                <a class="btn btn-primary w-100 mt-auto" href="{{ route('events.photos.index', ['event' => $event->slug]) }}">See all photos</a>
                            </div>
                        </div>
                    </div>
                </div>

            </section>

            @if ($event->content)
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <section class="my-5" aria-labelledby="event-description">
                            <h2 id="event-description" class="h4">About this event</h2>
                            <p class="text-muted">{{ $event->content }}</p>
                        </section>
                    </div>
                </div>
            @endif
        </article>
    </main>
@endsection
