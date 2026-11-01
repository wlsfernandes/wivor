{{-- Event search. Submits to the event directory with the same "search" parameter as its filter form. --}}
<form class="wv-search" method="GET" action="{{ route('events.listEvents') }}" role="search">
    <div class="wv-search__field-wrap">
        <label class="wv-label" for="{{ $fieldId }}">Event, city or sport</label>
        <div class="position-relative">
            <i class="fas fa-search wv-search__icon" aria-hidden="true"></i>
            <input class="wv-input wv-search__field" id="{{ $fieldId }}" name="search" type="search"
                placeholder="Try a race name or city" value="{{ $searchValue ?? '' }}">
        </div>
    </div>
    <button class="wv-btn wv-btn--primary wv-search__submit" type="submit">Search Events</button>
</form>
