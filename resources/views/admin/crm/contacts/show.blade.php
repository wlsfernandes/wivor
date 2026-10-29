@extends('layouts.master')
@section('title', 'Contact — '.$contact->email_normalized)
@section('content')
    <div class="container-fluid py-4">
        <a href="{{ route('admin.crm.contacts.index') }}">← Contacts</a>
        <h1 class="h3 mt-2 mb-1">{{ $contact->full_name ?? (trim($contact->first_name.' '.$contact->last_name) ?: $contact->email_normalized) }}</h1>
        <p class="text-muted">{{ $contact->email_original }} @if ($contact->phone) · {{ $contact->phone }} @endif · added {{ $contact->created_at->format('M j, Y') }}@if ($contact->first_source) from {{ str_replace('_', ' ', $contact->first_source) }}@endif</p>

        <div class="card mb-3">
            <div class="card-header"><h2 class="h5 mb-0">Events</h2></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Event</th><th>Bib</th><th>Division</th><th>Other fields</th><th>Import</th></tr></thead>
                    <tbody>
                        @forelse ($contact->events as $event)
                            <tr>
                                <td><a href="{{ route('admin.crm.events.participants', $event) }}">{{ $event->title }}</a></td>
                                <td>{{ $event->participation->bib_number }}</td>
                                <td>{{ $event->participation->division }}</td>
                                <td>
                                    @foreach ($event->participation->extra_fields ?? [] as $name => $value)
                                        <div><span class="text-muted">{{ $name }}:</span> {{ $value }}</div>
                                    @endforeach
                                </td>
                                <td>
                                    @if ($event->participation->crm_import_id)
                                        <a href="{{ route('admin.crm.imports.show', $event->participation->crm_import_id) }}">#{{ $event->participation->crm_import_id }}</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted">Not linked to any event.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h2 class="h5 mb-0">Consent</h2></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Category</th><th>Scope</th><th>Status</th><th>Source</th><th>Evidence</th><th>Recorded</th><th>Revoked</th></tr></thead>
                    <tbody>
                        @forelse ($contact->consents as $consent)
                            <tr>
                                <td>{{ ucfirst($consent->category) }}</td>
                                <td>{{ $consent->scopedEvent?->title ?? 'WiVor' }}</td>
                                <td>{{ ucfirst($consent->status) }}</td>
                                <td>{{ $consent->source }}</td>
                                <td>{{ $consent->evidence }}</td>
                                <td>{{ $consent->recorded_at->format('M j, Y') }}</td>
                                <td>{{ $consent->revoked_at?->format('M j, Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-muted">No consent recorded. Marketing consent is unknown.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2 class="h5 mb-0">Suppressions</h2></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Reason</th><th>Category</th><th>Since</th></tr></thead>
                    <tbody>
                        @forelse ($contact->suppressions as $suppression)
                            <tr>
                                <td>{{ ucfirst($suppression->reason) }}</td>
                                <td>{{ ucfirst($suppression->category) }}</td>
                                <td>{{ $suppression->created_at->format('M j, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted">No suppressions.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
