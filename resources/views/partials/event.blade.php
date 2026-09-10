<section id="available-events" class="py-5" style="background-color: #f7f7f7;" aria-labelledby="available-events-title">
    <div class="container">
        <div class="sec-title mb_40 text-center">
            <span class="sub-title">@lang('messages.home_events_eyebrow')</span>
            <h2 id="available-events-title">@lang('messages.home_recent_events')</h2>
            <p class="mt-3 text-muted">@lang('messages.home_recent_events_support')</p>
        </div>
        <div class="row g-4">
            @forelse ($events as $event)
                <div class="col-md-6 col-lg-4">
                    <article class="card h-100 shadow-sm border-0 home-event-card">
                        <a href="{{ route('events.show', $event->slug) }}">
                            <img src="{{ $event->cover_url }}" class="card-img-top home-event-card__image"
                                alt="Cover for {{ $event->title }}" loading="lazy">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <span class="home-event-card__sport text-uppercase small fw-semibold">{{ $event->sport_label }}</span>
                            <h3 class="h5 card-title mt-2">
                                <a class="text-dark" href="{{ route('events.show', $event->slug) }}">{{ $event->title }}</a>
                            </h3>
                            <p class="card-text text-muted mb-2">{{ $event->date_label }}</p>
                            <p class="card-text text-muted mb-3">{{ $event->location_label }}</p>
                            <p class="fw-semibold mt-auto mb-3">{{ $event->public_availability_label }}</p>
                            <a href="{{ route('events.show', $event->slug) }}" class="btn btn-outline-dark align-self-start">
                                @lang('messages.home_view_event')
                            </a>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="bg-white border rounded text-center p-5">
                        <h3 class="h5">@lang('messages.home_no_events_title')</h3>
                        <p class="text-muted mt-2 mb-0">@lang('messages.home_no_events_support')</p>
                    </div>
                </div>
            @endforelse
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('events.listEvents') }}" class="theme-btn-one">@lang('messages.home_browse_all_events')</a>
        </div>
    </div>
</section>
