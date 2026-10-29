@extends('layouts.master')
@section('title', 'Participants — '.$event->title)
@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h1 class="h3 mb-0">{{ $event->title }} participants</h1>
                <p class="text-muted mb-0">{{ number_format($participants->total()) }} in the CRM</p>
            </div>
            <a href="{{ route('admin.crm.imports.create', $event) }}" class="btn btn-success">Import participants</a>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead><tr><th>Bib</th><th>Name</th><th>Email</th><th>Division</th></tr></thead>
                    <tbody>
                        @forelse ($participants as $participant)
                            <tr>
                                <td>{{ $participant->bib_number }}</td>
                                <td>{{ $participant->full_name ?? trim($participant->first_name.' '.$participant->last_name) }}</td>
                                <td><a href="{{ route('admin.crm.contacts.show', $participant) }}">{{ $participant->email_normalized }}</a></td>
                                <td>{{ $participant->division }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center">No participants imported yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $participants->links() }}</div>
    </div>
@endsection
