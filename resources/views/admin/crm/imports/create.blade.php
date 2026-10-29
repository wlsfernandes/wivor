@extends('layouts.master')
@section('title', 'Import Participants — '.$event->title)
@section('content')
    <div class="container-fluid py-4">
        <a href="{{ route('admin.crm.events.participants', $event) }}">← {{ $event->title }} participants</a>
        <h1 class="h3 mt-2 mb-3">Import participants</h1>
        <p class="text-muted">Upload a CSV of participants for <strong>{{ $event->title }}</strong>. You choose which columns to import on the next step.</p>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.crm.imports.store', $event) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="file" class="form-label">CSV file</label>
                        <input type="file" id="file" name="file" accept=".csv,.txt,text/csv" class="form-control @error('file') is-invalid @enderror" required>
                        <div class="form-text">Up to 5 MB. Comma, semicolon, tab or pipe separated. The first row must contain column names.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="data_source" class="form-label">Data source</label>
                            <input type="text" id="data_source" name="data_source" class="form-control @error('data_source') is-invalid @enderror"
                                value="{{ old('data_source') }}" maxlength="255" placeholder="e.g. Registration platform export" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="organizer_name" class="form-label">Organizer</label>
                            <input type="text" id="organizer_name" name="organizer_name" class="form-control @error('organizer_name') is-invalid @enderror"
                                value="{{ old('organizer_name') }}" maxlength="255">
                        </div>
                    </div>

                    <fieldset class="border rounded p-3 mb-3">
                        <legend class="fs-6 w-auto px-2 mb-0">Consent</legend>
                        <p class="text-muted small">Importing a contact does not give consent. Choose "granted" only when you hold written evidence, such as the registration form wording people agreed to.</p>
                        <div class="mb-3">
                            <label for="consent_status" class="form-label">Consent status</label>
                            <select id="consent_status" name="consent_status" class="form-select @error('consent_status') is-invalid @enderror">
                                <option value="unknown" @selected(old('consent_status', 'unknown') === 'unknown')>Unknown — no consent recorded</option>
                                <option value="granted" @selected(old('consent_status') === 'granted')>Granted — I have evidence</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="consent_category" class="form-label">Consent covers</label>
                                <select id="consent_category" name="consent_category" class="form-select @error('consent_category') is-invalid @enderror">
                                    <option value="">—</option>
                                    @foreach ($consentCategories as $category)
                                        <option value="{{ $category }}" @selected(old('consent_category') === $category)>{{ ucfirst($category) }} emails</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="consent_scope" class="form-label">Consent given to</label>
                                <select id="consent_scope" name="consent_scope" class="form-select @error('consent_scope') is-invalid @enderror">
                                    <option value="">—</option>
                                    <option value="event" @selected(old('consent_scope') === 'event')>This event only</option>
                                    <option value="wivor" @selected(old('consent_scope') === 'wivor')>WiVor</option>
                                </select>
                            </div>
                        </div>
                        <label for="consent_evidence" class="form-label">Evidence</label>
                        <textarea id="consent_evidence" name="consent_evidence" rows="3" maxlength="2000" class="form-control @error('consent_evidence') is-invalid @enderror">{{ old('consent_evidence') }}</textarea>
                    </fieldset>

                    <button type="submit" class="btn btn-primary">Upload and map columns</button>
                </form>
            </div>
        </div>
    </div>
@endsection
