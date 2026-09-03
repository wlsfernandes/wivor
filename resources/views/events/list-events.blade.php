@extends($layout)

@section('title', 'Find Your Event Photos | WivorPhotos')
@section('meta-description', 'Search WivorPhotos for professional sports and fitness event photos by event name, city, state, sport, or date.')
@section('meta-keywords', 'find event photos, sports event photography, race photos, marathon photos, cycling photos, fitness event photos, WivorPhotos')

@section('content')
    <main class="container py-5">
        <header class="text-center mb-5">
            <h1>Find Your Event Photos</h1>
            <p class="text-muted">Search upcoming and published sports events across the United States.</p>
        </header>

        <form class="card card-body mb-5" method="GET" action="{{ route('events.listEvents') }}">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label" for="search">Event, city, or sport</label>
                    <input class="form-control" id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search events">
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label" for="state">State</label>
                    <select class="form-select" id="state" name="state">
                        <option value="">All states</option>
                        @foreach ($filterOptions['states'] as $state)
                            <option value="{{ $state }}" @selected(($filters['state'] ?? '') === $state)>{{ $state }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label" for="city">City</label>
                    <select class="form-select" id="city" name="city">
                        <option value="">All cities</option>
                        @foreach ($filterOptions['cities'] as $city)
                            <option value="{{ $city }}" @selected(($filters['city'] ?? '') === $city)>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label" for="sport">Sport</label>
                    <select class="form-select" id="sport" name="sport">
                        <option value="">All sports</option>
                        @foreach ($filterOptions['sports'] as $sport)
                            <option value="{{ $sport }}" @selected(($filters['sport'] ?? '') === $sport)>{{ $sport }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-lg-2 d-grid">
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label" for="date_from">From date</label>
                    <input class="form-control" id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label" for="date_to">To date</label>
                    <input class="form-control" id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}">
                </div>
            </div>
        </form>

        <div class="row g-4">
            @forelse ($events as $event)
                <div class="col-md-6 col-lg-4">
                    <article class="card h-100 shadow-sm border-0">
                        <a href="{{ route('events.show', ['event' => $event->slug]) }}">
                            <img src="{{ $event->cover_url }}" class="card-img-top" alt="Cover for {{ $event->title }}" style="height: 240px; object-fit: cover;">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <span class="text-uppercase small fw-semibold text-primary">{{ $event->sport_label }}</span>
                            <h2 class="h5 mt-2"><a class="text-decoration-none" href="{{ route('events.show', ['event' => $event->slug]) }}">{{ $event->title }}</a></h2>
                            <p class="text-muted mb-2">{{ $event->date_label }}</p>
                            <p class="text-muted mb-3">{{ $event->location_label }}</p>
                            <p class="fw-semibold mt-auto mb-0">{{ $event->public_availability_label }}</p>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light border text-center py-5">
                        <h2 class="h5">No events match your search</h2>
                        <p class="text-muted mb-3">Try removing a filter or searching for a different event, city, or sport.</p>
                        <a class="btn btn-outline-primary" href="{{ route('events.listEvents') }}">Clear filters</a>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-5">{{ $events->links() }}</div>
    </main>
@endsection
