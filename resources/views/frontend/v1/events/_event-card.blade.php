@php($eventUrl = route('events.show', ['event' => $event->slug]))
<article class="wv-event-card">
    <a class="wv-event-card__media" href="{{ $eventUrl }}" tabindex="-1" aria-hidden="true">
        <img src="{{ $event->cover_url }}" alt="" loading="lazy">
    </a>
    <div class="wv-event-card__body">
        <p class="wv-event-card__sport">{{ $event->sport_label }}</p>
        <h3 class="wv-event-card__title"><a href="{{ $eventUrl }}">{{ $event->title }}</a></h3>
        <p class="wv-event-card__meta">{{ $event->date_label }}<br>{{ $event->location_label }}</p>
        @if ($isLive ?? false)
            <a class="wv-btn wv-btn--primary wv-event-card__action" href="{{ $eventUrl }}">Find your photos</a>
        @else
            <p class="wv-event-card__status">{{ $event->public_availability_label }}</p>
        @endif
    </div>
</article>
