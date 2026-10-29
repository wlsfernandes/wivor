@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<input type="hidden" name="crm_template_id" value="{{ old('crm_template_id', $campaign->crm_template_id) }}">

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="title" class="form-label">Internal title</label>
        <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror"
            value="{{ old('title', $campaign->title) }}" maxlength="255" required>
    </div>
    <div class="col-md-6 mb-3">
        <label for="category" class="form-label">Type</label>
        <select id="category" name="category" class="form-select @error('category') is-invalid @enderror">
            @foreach ($categories as $category => $label)
                <option value="{{ $category }}" @selected(old('category', $campaign->category) === $category)>{{ $label }}</option>
            @endforeach
        </select>
        <div class="form-text">Event notifications go to the selected events' participants unless they opted out. Promotions and newsletters go only to contacts with recorded marketing consent.</div>
    </div>
</div>

<fieldset class="border rounded p-3 mb-3">
    <legend class="fs-6 w-auto px-2 mb-0">Audience</legend>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="audience_type" id="audience_events" value="events" @checked(old('audience_type', $campaign->audience_type) === 'events')>
        <label class="form-check-label" for="audience_events">Participants of selected events</label>
    </div>
    <div class="form-check mb-2">
        <input class="form-check-input" type="radio" name="audience_type" id="audience_directory" value="directory" @checked(old('audience_type', $campaign->audience_type) === 'directory')>
        <label class="form-check-label" for="audience_directory">Whole CRM directory (promotions and newsletters only)</label>
    </div>
    <label for="event_ids" class="form-label">Events</label>
    <select id="event_ids" name="event_ids[]" class="form-select @error('event_ids') is-invalid @enderror" multiple size="8">
        @foreach ($events as $event)
            <option value="{{ $event->id }}" @selected(in_array($event->id, array_map('intval', old('event_ids', $selectedEventIds)), true))>{{ $event->title }} — {{ $event->date_label }}</option>
        @endforeach
    </select>
    <div class="form-text">Hold Ctrl (Cmd on Mac) to select several. A person in several selected events receives one copy.</div>
</fieldset>

@include('admin.crm._content-fields', ['content' => $campaign])
