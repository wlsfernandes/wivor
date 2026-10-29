@extends('layouts.master')
@section('title', 'CRM Contacts')
@section('content')
    <div class="container-fluid py-4">
        <h1 class="h3 mb-3">Contacts</h1>

        <form method="GET" action="{{ route('admin.crm.contacts.index') }}" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="search" name="search" class="form-control" placeholder="Search email or name" value="{{ $filters['search'] }}" aria-label="Search">
            </div>
            <div class="col-md-4">
                <select name="event_id" class="form-select" aria-label="Event">
                    <option value="">All events</option>
                    @foreach ($events as $event)
                        <option value="{{ $event->id }}" @selected((int) $filters['event_id'] === $event->id)>{{ $event->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><button type="submit" class="btn btn-primary">Filter</button></div>
        </form>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead><tr><th>Email</th><th>Name</th><th>Events</th><th>Added</th></tr></thead>
                    <tbody>
                        @forelse ($contacts as $contact)
                            <tr>
                                <td><a href="{{ route('admin.crm.contacts.show', $contact) }}">{{ $contact->email_normalized }}</a></td>
                                <td>{{ $contact->full_name ?? trim($contact->first_name.' '.$contact->last_name) }}</td>
                                <td>{{ $contact->events_count }}</td>
                                <td>{{ $contact->created_at->format('M j, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center">No contacts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $contacts->links() }}</div>
    </div>
@endsection
