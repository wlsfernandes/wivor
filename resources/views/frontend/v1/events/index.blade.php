@extends($layout)

@section('title', 'Find Your Event Photos | WiVor')
@section('meta-description', 'Search WivorPhotos for professional sports and fitness event photos by event name, city, state, sport, or date.')

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
                    <select class="form-select ignore" id="state" name="state">
                        <option value="">All states</option>
                        @foreach ($filterOptions['states'] as $state)
                            <option value="{{ $state }}" @selected(($filters['state'] ?? '') === $state)>{{ $state }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label" for="city">City</label>
                    <select class="form-select ignore" id="city" name="city">
                        <option value="">All cities</option>
                        @foreach ($filterOptions['cities'] as $city)
                            <option value="{{ $city }}" @selected(($filters['city'] ?? '') === $city)>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label" for="sport">Sport</label>
                    <select class="form-select ignore" id="sport" name="sport">
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

        @if ($liveEvents->isEmpty() && $upcomingEvents->isEmpty() && $pastEvents->isEmpty())
            <div class="alert alert-light border text-center py-5">
                <h2 class="h5">No events match your search</h2>
                <p class="text-muted mb-3">Try removing a filter or searching for a different event, city, or sport.</p>
                <a class="btn btn-outline-primary" href="{{ route('events.listEvents') }}">Clear filters</a>
            </div>
        @endif

        @if ($liveEvents->isNotEmpty())
            <section class="wv-events-group wv-events-group--live" aria-labelledby="live-events-heading">
                <header class="wv-events-group__head">
                    <h2 class="wv-events-group__title" id="live-events-heading"><span class="wv-live-dot" aria-hidden="true"></span>Photos are live</h2>
                    <p class="wv-events-group__count">{{ trans_choice('{1} 1 event|[2,*] :count events', $liveEvents->count()) }}</p>
                </header>
                <p class="wv-events-group__note">Open an event to find and buy your photos.</p>
                <div class="wv-events-grid">
                    @foreach ($liveEvents as $event)
                        @include('frontend.v1.events._event-card', ['event' => $event, 'isLive' => true])
                    @endforeach
                </div>
            </section>
        @endif

        @if ($upcomingEvents->isNotEmpty())
            <section class="wv-events-group" aria-labelledby="upcoming-events-heading">
                <header class="wv-events-group__head">
                    <h2 class="wv-events-group__title" id="upcoming-events-heading">Upcoming events</h2>
                    <p class="wv-events-group__count">{{ trans_choice('{1} 1 event|[2,*] :count events', $upcomingEvents->count()) }}</p>
                </header>
                <div class="wv-events-grid">
                    @foreach ($upcomingEvents as $event)
                        @include('frontend.v1.events._event-card', ['event' => $event])
                    @endforeach
                </div>
            </section>
        @endif

        @if ($pastEvents->isNotEmpty())
            <section class="wv-events-group wv-events-group--past" aria-labelledby="past-events-heading">
                <header class="wv-events-group__head">
                    <h2 class="wv-events-group__title" id="past-events-heading">Past events</h2>
                    <p class="wv-events-group__count">{{ trans_choice('{1} 1 event|[2,*] :count events', $pastEvents->total()) }}</p>
                </header>
                <div class="wv-events-grid">
                    @foreach ($pastEvents as $event)
                        @include('frontend.v1.events._event-card', ['event' => $event])
                    @endforeach
                </div>
                <div class="mt-5">{{ $pastEvents->links() }}</div>
            </section>
        @endif
    </main>
@endsection
