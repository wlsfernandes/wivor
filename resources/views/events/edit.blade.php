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
    </div>
@endsection
