@extends('layouts.master')
@section('title', 'Media — '.$event->title)
@section('content')
<div class="container-fluid py-4">
    <a href="{{ route('admin.media.index') }}">← Media operations</a>
    <div class="d-flex justify-content-between align-items-start mt-2 mb-4"><div><h1 class="h3">{{ $event->title }}</h1><p class="text-muted">{{ $event->date_label }} · {{ $event->location_label }}</p></div>
        <form method="POST" action="{{ route('admin.media.close', $event) }}" onsubmit="return confirm('Close this gallery now?')">@csrf<button class="btn btn-outline-danger">Close gallery</button></form>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card mb-4"><div class="card-header"><h2 class="h5 mb-0">Photographer upload deadlines</h2></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Photographer</th><th>Status</th><th>Deadline</th><th>Extend</th></tr></thead><tbody>
        @foreach($assignments as $assignment)<tr><td>{{ $assignment->photographer->first_name }} {{ $assignment->photographer->last_name }}</td><td>{{ ucfirst($assignment->status) }}</td><td>{{ $event->uploadDeadlineFor($assignment->photographer)->format('M j, Y g:i A') }}</td><td>
            <form class="d-flex gap-2" method="POST" action="{{ route('admin.media.deadline', [$event, $assignment->photographer]) }}">@csrf @method('PATCH')<input class="form-control form-control-sm" type="datetime-local" name="upload_deadline_at" required><button class="btn btn-sm btn-primary">Save</button></form>
        </td></tr>@endforeach
    </tbody></table></div></div>

    <div class="card mb-4"><div class="card-header"><h2 class="h5 mb-0">Retention holds</h2></div><div class="card-body">
        <form class="row g-2 mb-3" method="POST" action="{{ route('admin.media.holds.store', $event) }}">@csrf
            <div class="col-md-3"><input class="form-control" type="number" name="photo_id" placeholder="Photo database ID (optional)"></div><div class="col-md-5"><input class="form-control" name="reason" placeholder="Required hold reason" required></div><div class="col-md-3"><input class="form-control" type="datetime-local" name="review_at"></div><div class="col-md-1"><button class="btn btn-warning">Hold</button></div>
        </form>
        @forelse($holds as $hold)<div class="d-flex gap-2 align-items-center border-top py-2"><span class="flex-grow-1">{{ $hold->photo_id ? 'Photo #'.$hold->photo_id : 'Entire event' }} — {{ $hold->reason }}</span><form method="POST" action="{{ route('admin.media.holds.release', [$event, $hold]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-secondary">Release</button></form></div>@empty<p class="text-muted mb-0">No active holds.</p>@endforelse
    </div></div>

    @if($deletionFailures->isNotEmpty())<div class="alert alert-danger"><strong>Recent deletion failures:</strong> {{ $deletionFailures->count() }} attempt(s). Eligible records are retried by the daily task.</div>@endif
    <div class="card"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>ID</th><th>Photographer</th><th>File</th><th>Status</th><th>Reason</th><th>Actions</th></tr></thead><tbody>
        @foreach($photos as $photo)<tr><td>#{{ $photo->id }}</td><td>{{ $photo->photographer->first_name }} {{ $photo->photographer->last_name }}</td><td>{{ $photo->original_filename }}</td><td>{{ ucfirst($photo->status) }}</td><td>{{ $photo->rejection_reason ?? $photo->deletion_reason }}</td><td><div class="d-flex gap-2">
            @if($photo->rejection_code === 'processing_failed')<form method="POST" action="{{ route('admin.media.retry', [$event, $photo]) }}">@csrf<button class="btn btn-sm btn-outline-primary">Retry</button></form>@endif
            @if($photo->status === 'published')<form method="POST" action="{{ route('admin.media.unpublish', [$event, $photo]) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning">Unpublish</button></form>@endif
            @if($photo->status !== 'removed')<form class="d-flex gap-1" method="POST" action="{{ route('admin.media.remove', [$event, $photo]) }}" onsubmit="return confirm('Permanently remove this media?')">@csrf @method('DELETE')<input class="form-control form-control-sm" name="reason" placeholder="Removal reason" required><button class="btn btn-sm btn-danger">Remove</button></form>@endif
        </div></td></tr>@endforeach
    </tbody></table></div></div><div class="mt-3">{{ $photos->links() }}</div>
</div>
@endsection
