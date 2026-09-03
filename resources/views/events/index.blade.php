@extends($layout)

@section('title', 'Manage Events')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Manage events</h1>
                <p class="text-muted mb-0">Create, publish, and archive your sports events.</p>
            </div>
            <a class="btn btn-primary" href="{{ route('events.create') }}">
                <i class="fas fa-plus" aria-hidden="true"></i> Create event
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        <form class="card card-body mb-4" method="GET" action="{{ route('events.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label" for="search">Search</label>
                    <input class="form-control" id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Event, city, or sport">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All statuses</option>
                        @foreach ($statusFilters as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="event_date">Event date</label>
                    <input class="form-control" id="event_date" name="event_date" type="date" value="{{ $filters['event_date'] ?? '' }}">
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-outline-primary" type="submit">Filter events</button>
                </div>
            </div>
        </form>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Event</th>
                            <th scope="col">Sport</th>
                            <th scope="col">Date</th>
                            <th scope="col">Location</th>
                            <th scope="col">Photos live</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($events as $event)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $event->cover_url }}" alt="" class="rounded"
                                            style="width: 72px; height: 52px; object-fit: cover;">
                                        <span class="fw-semibold">{{ $event->title }}</span>
                                    </div>
                                </td>
                                <td>{{ $event->sport_label }}</td>
                                <td>{{ $event->date_label }}</td>
                                <td>{{ $event->location_label }}</td>
                                <td>{{ $event->photos_live_label }}</td>
                                <td><span class="badge bg-secondary">{{ $event->status_label }}</span></td>
                                <td>
                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                        @if ($event->is_published)
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('events.show', ['event' => $event->slug]) }}" target="_blank" rel="noopener noreferrer">View public page</a>
                                        @endif
                                        @if (auth()->user()->hasRole('admin'))
                                            <a class="btn btn-sm btn-outline-dark" href="{{ route('admin.media.show', $event) }}">Media</a>
                                        @endif
                                        @if (! auth()->user()->hasRole('admin') && ($event->pivot?->status ?? null) === 'approved' && ! $event->is_archived && $event->uploadDeadlineFor(auth()->user()->photographer)->isFuture())
                                            <a class="btn btn-sm btn-primary" href="{{ route('photographer.uploads.show', $event) }}">Upload photos</a>
                                        @endif
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('events.edit', ['event' => $event->id]) }}">Edit</a>
                                        @if (! $event->is_archived)
                                            <form method="POST" action="{{ route('events.publish', ['event' => $event->id]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-success" type="submit">
                                                    {{ $event->publish_action_label }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('events.destroy', ['event' => $event->id]) }}" onsubmit="return confirm('Archive this event?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Archive</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-5">No events match the current filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">{{ $events->links() }}</div>
    </div>
@endsection
