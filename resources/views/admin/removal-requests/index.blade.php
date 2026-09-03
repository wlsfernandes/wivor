@extends('layouts.master')
@section('title', 'Photo removal requests')
@section('content')
    <div class="container-fluid py-4">
        <h1 class="h3">Photo removal requests</h1>
        <p class="text-muted">Customer-submitted requests to review or remove a photo. This does not delete a sold image or
            its financial history.</p>
        <div class="card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Submitted</th>
                            <th>Photo</th>
                            <th>Event</th>
                            <th>Requester</th>
                            <th>Reason</th>
                            <th>Explanation</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $removalRequest)
                            <tr>
                                <td>{{ $removalRequest->created_at->format('M j, Y g:i A') }}</td>
                                <td>
                                    @if ($thumbnailUrls[$removalRequest->id] ?? null)
                                        <img src="{{ $thumbnailUrls[$removalRequest->id] }}" alt="Reported photo"
                                            style="width: 64px; height: 64px; object-fit: cover;" class="rounded">
                                    @else
                                        <span class="text-muted small">No longer available</span>
                                    @endif
                                </td>
                                <td>{{ $removalRequest->photo?->event?->title ?? 'Unknown event' }}</td>
                                <td>{{ $removalRequest->requester_name }}<br><span
                                        class="text-muted small">{{ $removalRequest->requester_email }}</span></td>
                                <td>{{ $removalRequest->reason_label }}</td>
                                <td class="small">{{ $removalRequest->explanation ?: '—' }}</td>
                                <td>{{ $removalRequest->status_label }}</td>
                                <td class="d-flex flex-column gap-1">
                                    @if ($removalRequest->status === 'pending')
                                        <form method="POST"
                                            action="{{ route('admin.removal-requests.resolve', $removalRequest) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-primary w-100" type="submit">Mark
                                                reviewed</button>
                                        </form>
                                    @endif
                                    @if ($removalRequest->photo && $removalRequest->photo->status === 'published')
                                        <form method="POST"
                                            action="{{ route('admin.media.unpublish', ['event' => $removalRequest->photo->event_id, 'photo' => $removalRequest->photo->uuid]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-danger w-100" type="submit">Unpublish
                                                photo</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-muted text-center py-4">No removal requests.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $requests->links() }}</div>
    </div>
@endsection
