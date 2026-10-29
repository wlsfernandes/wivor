@extends('layouts.master')
@section('title', 'Import #'.$import->id)
@section('content')
    <div class="container-fluid py-4">
        <a href="{{ route('admin.crm.imports.index') }}">← CSV imports</a>
        <h1 class="h3 mt-2 mb-1">Import #{{ $import->id }}</h1>
        <p class="text-muted">{{ $import->original_filename }} for <a href="{{ route('admin.crm.events.participants', $import->event) }}">{{ $import->event->title }}</a></p>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9">{{ ucfirst($import->status) }} @if ($import->failure_message)<span class="text-danger">— {{ $import->failure_message }}</span>@endif</dd>
                    <dt class="col-sm-3">Rows in file</dt><dd class="col-sm-9">{{ number_format($import->total_rows) }}</dd>
                    <dt class="col-sm-3">Imported</dt><dd class="col-sm-9">{{ number_format($import->imported_rows) }}</dd>
                    <dt class="col-sm-3">Duplicates skipped</dt><dd class="col-sm-9">{{ number_format($import->skipped_rows) }}</dd>
                    <dt class="col-sm-3">Rows with errors</dt><dd class="col-sm-9">{{ number_format($import->failed_rows) }}</dd>
                    <dt class="col-sm-3">Data source</dt><dd class="col-sm-9">{{ $import->data_source }}</dd>
                    <dt class="col-sm-3">Organizer</dt><dd class="col-sm-9">{{ $import->organizer_name ?? '—' }}</dd>
                    <dt class="col-sm-3">Consent</dt>
                    <dd class="col-sm-9">
                        {{ ucfirst($import->consent_status) }}
                        @if ($import->consent_category)
                            — {{ $import->consent_category }} emails, {{ $import->consent_scope === 'event' ? 'this event only' : 'WiVor' }}
                        @endif
                    </dd>
                    <dt class="col-sm-3">Uploaded by</dt><dd class="col-sm-9">{{ $import->operator?->name ?? '—' }}, {{ $import->created_at->format('M j, Y g:i A') }}</dd>
                    @if ($import->files_purged_at)
                        <dt class="col-sm-3">Files deleted</dt><dd class="col-sm-9">{{ $import->files_purged_at->format('M j, Y') }} (retention period)</dd>
                    @endif
                </dl>
            </div>
        </div>

        <div class="d-flex gap-2">
            @if ($import->status === 'pending')
                <a href="{{ route('admin.crm.imports.mapping.edit', $import) }}" class="btn btn-primary">Map columns</a>
            @endif
            @if ($import->status === 'failed' && ! $import->files_purged_at)
                <form method="POST" action="{{ route('admin.crm.imports.retry', $import) }}">@csrf<button class="btn btn-warning">Retry import</button></form>
            @endif
            @if ($hasErrorReport)
                <a href="{{ route('admin.crm.imports.errors', $import) }}" class="btn btn-outline-secondary">Download error report</a>
            @endif
        </div>
    </div>
@endsection

@if (in_array($import->status, ['queued', 'running'], true))
    @section('script-bottom')
        <script>setTimeout(() => window.location.reload(), 5000);</script>
    @endsection
@endif
