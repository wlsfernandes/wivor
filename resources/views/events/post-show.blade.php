@extends('layouts.app')

@section('title', $seoTitle)
@section('meta-description', $seoDescription)
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

                    @if ($event->content)
                        <section class="my-5" aria-labelledby="event-description">
                            <h2 id="event-description" class="h4">About this event</h2>
                            <p class="text-muted">{{ $event->content }}</p>
                        </section>
                    @endif

                    <section class="card border-0 bg-light my-5" aria-labelledby="event-photos">
                        <div class="card-body text-center py-5">
                            <h2 id="event-photos" class="h4">Event photos</h2>
                            <p class="text-muted mb-0">Photo browsing will be available in the next WivorPhotos release.</p>
                        </div>
                    </section>
                </div>
            </div>
        </article>
    </main>
@endsection
