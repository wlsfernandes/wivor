@extends($layout)

@section('title', 'Edit Event')

@section('content')
    <div class="container py-4">
        <div class="mb-4">
            <h1 class="h3 mb-1">Edit event</h1>
            <p class="text-muted mb-0">Update {{ $event->title }} and its public availability.</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('events.update', ['event' => $event->id]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('events._form', ['submitLabel' => 'Save changes'])
        </form>

        @can('access-admin')
            <div class="card mt-4">
                <div class="card-header"><h2 class="h5 mb-0">Assigned photographers</h2></div>
                <div class="card-body">
                    @if ($event->photographers->isNotEmpty())
                        <p class="mb-3">{{ $event->photographers->pluck('full_name')->join(', ') }}</p>
                    @endif

                    <form class="row g-2 align-items-end" method="POST" action="{{ route('admin.events.photographers.assign', $event) }}">
                        @csrf
                        <div class="col-md-8">
                            <label class="form-label" for="photographer_id">Approved photographer</label>
                            <select class="form-select @error('photographer_id') is-invalid @enderror" id="photographer_id" name="photographer_id" required>
                                <option value="">Select photographer</option>
                                @foreach ($approvedPhotographers as $photographer)
                                    <option value="{{ $photographer->id }}" @selected(old('photographer_id') == $photographer->id)>{{ $photographer->full_name }}</option>
                                @endforeach
                            </select>
                            @error('photographer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary" type="submit">Assign &amp; approve</button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    </div>
@endsection
