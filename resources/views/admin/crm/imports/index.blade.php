@extends('layouts.master')
@section('title', 'CSV Imports')
@section('content')
    <div class="container-fluid py-4">
        <h1 class="h3 mb-3">CSV imports</h1>
        <p class="text-muted">Start a new import from an event's admin page.</p>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead><tr><th>#</th><th>Event</th><th>File</th><th>Status</th><th>Imported</th><th>Errors</th><th>Uploaded</th></tr></thead>
                    <tbody>
                        @forelse ($imports as $import)
                            <tr>
                                <td><a href="{{ route('admin.crm.imports.show', $import) }}">{{ $import->id }}</a></td>
                                <td>{{ $import->event->title }}</td>
                                <td>{{ $import->original_filename }}</td>
                                <td>{{ ucfirst($import->status) }}</td>
                                <td>{{ number_format($import->imported_rows) }}</td>
                                <td>{{ number_format($import->failed_rows) }}</td>
                                <td>{{ $import->created_at->format('M j, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-muted text-center">No imports yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $imports->links() }}</div>
    </div>
@endsection
