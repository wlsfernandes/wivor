@extends('layouts.app')

@section('title', 'Photo Removal Request | WivorPhotos')
@section('meta-description', 'Submit a WivorPhotos photo removal request for administrative review.')

@section('content')
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="mb-3">Photo Removal Request</h1>
                <p>Use this form to identify a photograph and submit a request for administrative review. Submitting this form does not automatically remove or delete a photograph.</p>
                <p class="mb-4">If you need help locating the photo reference, contact <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>.</p>

                @error('request')
                    <div class="alert alert-danger" role="alert">{{ $message }}</div>
                @enderror

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <form method="POST" action="{{ route('photo-removal.store') }}">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="requester_name" class="form-label">Name</label>
                                    <input id="requester_name" name="requester_name" type="text" value="{{ old('requester_name') }}" class="form-control @error('requester_name') is-invalid @enderror" maxlength="255" required>
                                    @error('requester_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="requester_email" class="form-label">Email</label>
                                    <input id="requester_email" name="requester_email" type="email" value="{{ old('requester_email') }}" class="form-control @error('requester_email') is-invalid @enderror" maxlength="255" required>
                                    @error('requester_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="event" class="form-label">Event</label>
                                <input id="event" name="event" type="text" value="{{ old('event') }}" class="form-control @error('event') is-invalid @enderror" maxlength="255" required>
                                @error('event')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="photo_identifier" class="form-label">Photo URL or photo identifier</label>
                                <input id="photo_identifier" name="photo_identifier" type="text" value="{{ old('photo_identifier') }}" class="form-control @error('photo_identifier') is-invalid @enderror" maxlength="2048" aria-describedby="photo_identifier_help" required>
                                <div id="photo_identifier_help" class="form-text">Enter the full WivorPhotos photo URL, UUID, or eight-character reference shown on the photo page.</div>
                                @error('photo_identifier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason for request</label>
                                <select id="reason" name="reason" class="form-control @error('reason') is-invalid @enderror" required>
                                    <option value="">Select a reason</option>
                                    @foreach ($reasons as $value => $label)
                                        <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-4">
                                <label for="additional_notes" class="form-label">Additional notes</label>
                                <textarea id="additional_notes" name="additional_notes" class="form-control @error('additional_notes') is-invalid @enderror" rows="5" maxlength="2000">{{ old('additional_notes') }}</textarea>
                                @error('additional_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <button type="submit" class="theme-btn-one"><span>Submit for review</span></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
