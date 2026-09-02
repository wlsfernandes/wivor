@extends($layout)

@section('title', 'Create Event')

@section('content')
    <div class="container py-4">
        <div class="mb-4">
            <h1 class="h3 mb-1">Create event</h1>
            <p class="text-muted mb-0">Add the event details now. Photos can be added in a future release.</p>
        </div>

        <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data">
            @csrf
            @include('events._form', ['submitLabel' => 'Create event'])
        </form>
    </div>
@endsection
