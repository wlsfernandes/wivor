@extends($layout)

@section('title', 'Report or Request Removal | WivorPhotos')

@section('content')
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <h1 class="h3">Report or Request Removal</h1>
                <p class="text-muted">Photo reference: {{ $photo->reference_number }}</p>

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">Please correct the highlighted fields and try again.</div>
                @endif

                <form method="POST"
                    action="{{ route('photos.removal-requests.store', ['event' => $event->slug, 'photo' => $photo]) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="requester_name">Your name</label>
                        <input class="form-control @error('requester_name') is-invalid @enderror" id="requester_name"
                            name="requester_name" type="text" value="{{ old('requester_name') }}" required>
                        @error('requester_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="requester_email">Your email</label>
                        <input class="form-control @error('requester_email') is-invalid @enderror" id="requester_email"
                            name="requester_email" type="email" value="{{ old('requester_email') }}" required>
                        @error('requester_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="reason">Reason</label>
                        <select class="form-select @error('reason') is-invalid @enderror" id="reason" name="reason"
                            required>
                            <option value="">Select a reason</option>
                            @foreach ($reasons as $value => $label)
                                <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="explanation">Short explanation (optional)</label>
                        <textarea class="form-control @error('explanation') is-invalid @enderror" id="explanation" name="explanation"
                            rows="4">{{ old('explanation') }}</textarea>
                        @error('explanation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button class="btn btn-primary" type="submit">Submit request</button>
                    <a class="btn btn-outline-secondary"
                        href="{{ route('events.photos.show', ['event' => $event->slug, 'photo' => $photo]) }}">Cancel</a>
                </form>
            </div>
        </div>
    </main>
@endsection
