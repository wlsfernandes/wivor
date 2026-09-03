@extends('layouts.master')
@section('title', 'Media operations')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h3">Media operations</h1>
    <p class="text-muted">Upload health, publication, and retention by event.</p>
    <div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Event</th><th>Total</th><th>Processing</th><th>Ready</th><th>Rejected</th><th>Published</th><th>Removed</th><th>Sales close</th></tr></thead>
        <tbody>@foreach($events as $event)<tr>
            <td><a href="{{ route('admin.media.show', $event) }}">{{ $event->title }}</a></td>
            <td>{{ $event->photos_count }}</td><td>{{ $event->processing_count }}</td><td>{{ $event->ready_count }}</td>
            <td>{{ $event->rejected_count }}</td><td>{{ $event->published_count }}</td><td>{{ $event->removed_count }}</td>
            <td>{{ $event->sales_close_at?->format('M j, Y') ?? 'Not published' }}</td>
        </tr>@endforeach</tbody>
    </table></div></div>
    <div class="mt-3">{{ $events->links() }}</div>
</div>
@endsection
