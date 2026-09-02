@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        Please correct the highlighted fields and try again.
    </div>
@endif

<div class="card mb-4">
    <div class="card-header"><h2 class="h5 mb-0">Event</h2></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label" for="title">Event title</label>
                <input class="form-control @error('title') is-invalid @enderror" id="title" name="title"
                    type="text" value="{{ old('title', $formValues['title']) }}" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="sport">Sport</label>
                <input class="form-control @error('sport') is-invalid @enderror" id="sport" name="sport"
                    type="text" value="{{ old('sport', $formValues['sport']) }}" placeholder="Running" required>
                @error('sport')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="content">Description</label>
                <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content"
                    rows="5">{{ old('content', $formValues['content']) }}</textarea>
                @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="summary">Short summary</label>
                <textarea class="form-control @error('summary') is-invalid @enderror" id="summary" name="summary"
                    rows="2">{{ old('summary', $formValues['summary']) }}</textarea>
                <div class="form-text">Used in event previews and search results.</div>
                @error('summary')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h2 class="h5 mb-0">Date and time</h2></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="date_of_event">Event date</label>
                <input class="form-control @error('date_of_event') is-invalid @enderror" id="date_of_event"
                    name="date_of_event" type="date" value="{{ old('date_of_event', $formValues['date_of_event']) }}" required>
                <div class="form-text">The day the sports event takes place.</div>
                @error('date_of_event')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="starts_at">Start time</label>
                <input class="form-control @error('starts_at') is-invalid @enderror" id="starts_at" name="starts_at"
                    type="datetime-local" value="{{ old('starts_at', $formValues['starts_at']) }}">
                @error('starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="photos_live_at">Photos live</label>
                <input class="form-control @error('photos_live_at') is-invalid @enderror" id="photos_live_at"
                    name="photos_live_at" type="datetime-local" value="{{ old('photos_live_at', $formValues['photos_live_at']) }}">
                <div class="form-text">When customers should expect photos to become available.</div>
                @error('photos_live_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="timezone">Timezone</label>
                <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone" required>
                    @foreach ($timezones as $value => $label)
                        <option value="{{ $value }}" @selected(old('timezone', $formValues['timezone']) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('timezone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h2 class="h5 mb-0">Location</h2></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="venue_name">Venue</label>
                <input class="form-control @error('venue_name') is-invalid @enderror" id="venue_name" name="venue_name"
                    type="text" value="{{ old('venue_name', $formValues['venue_name']) }}">
                @error('venue_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="city">City</label>
                <input class="form-control @error('city') is-invalid @enderror" id="city" name="city"
                    type="text" value="{{ old('city', $formValues['city']) }}" required>
                @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label class="form-label" for="state">State</label>
                <input class="form-control text-uppercase @error('state') is-invalid @enderror" id="state" name="state"
                    type="text" maxlength="2" value="{{ old('state', $formValues['state']) }}" placeholder="FL" required>
                @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h2 class="h5 mb-0">Publishing</h2></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="image_url">Cover image</label>
                <input class="form-control @error('image_url') is-invalid @enderror" id="image_url" name="image_url"
                    type="file" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">Optional JPEG, PNG, or WebP up to 10 MB.</div>
                @error('image_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if ($formValues['cover_url'])
                    <img class="img-thumbnail mt-3" src="{{ $formValues['cover_url'] }}" alt="Current event cover" style="max-height: 180px;">
                @endif
            </div>
            <div class="col-md-6">
                <label class="form-label" for="status">Status</label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $formValues['status']) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap gap-2">
    <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="btn btn-outline-secondary" href="{{ route('events.index') }}">Cancel</a>
</div>
