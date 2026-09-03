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
                                    {{ $cartCount }} photo(s) selected &middot; ${{ number_format($cartSubtotalCents / 100, 2) }} &middot; View selection
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

                    <section class="card border-0 bg-light my-5" aria-labelledby="event-photos">
                        <div class="card-body py-4">
                            <h2 id="event-photos" class="h4">Event photos</h2>
                            @if ($photos->isEmpty())
                                <p class="text-muted mb-0">No photos are currently available.</p>
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
