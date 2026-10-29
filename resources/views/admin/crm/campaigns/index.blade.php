@extends('layouts.master')
@section('title', 'Email Campaigns')
@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="h3 mb-0">Email campaigns</h1>
            <a href="{{ route('admin.crm.campaigns.create') }}" class="btn btn-success">New campaign</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead><tr><th>Title</th><th>Type</th><th>Audience</th><th>Status</th><th>Recipients</th><th>Scheduled</th></tr></thead>
                    <tbody>
                        @forelse ($campaigns as $campaign)
                            <tr>
                                <td><a href="{{ route('admin.crm.campaigns.show', $campaign) }}">{{ $campaign->title }}</a></td>
                                <td>{{ \App\Models\CrmCampaign::CATEGORIES[$campaign->category] ?? $campaign->category }}</td>
                                <td>{{ $campaign->audience_type === 'events' ? $campaign->events_count.' event(s)' : 'Directory' }}</td>
                                <td>{{ ucfirst($campaign->status) }}</td>
                                <td>{{ $campaign->isDraft() ? '—' : number_format($campaign->intended_count) }}</td>
                                <td>{{ $campaign->scheduled_at?->timezone(\App\Models\CrmCampaign::SCHEDULE_TIMEZONE)->format('M j, Y g:i A T') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-muted text-center">No campaigns yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $campaigns->links() }}</div>
    </div>
@endsection
