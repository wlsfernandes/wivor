@extends('layouts.master')
@section('title', 'Campaign — '.$campaign->title)
@section('content')
    <div class="container-fluid py-4">
        <a href="{{ route('admin.crm.campaigns.index') }}">← Email campaigns</a>
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mt-2 mb-3">
            <div>
                <h1 class="h3 mb-1">{{ $campaign->title }}</h1>
                <p class="text-muted mb-0">{{ \App\Models\CrmCampaign::CATEGORIES[$campaign->category] ?? $campaign->category }} · {{ ucfirst($campaign->status) }}
                    @if ($campaign->scheduled_at) · scheduled for {{ $campaign->scheduled_at->timezone(\App\Models\CrmCampaign::SCHEDULE_TIMEZONE)->format('M j, Y g:i A T') }} @endif</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.crm.campaigns.preview', $campaign) }}" class="btn btn-outline-secondary">Preview</a>
                @if ($campaign->isDraft())
                    <a href="{{ route('admin.crm.campaigns.edit', $campaign) }}" class="btn btn-outline-primary">Edit</a>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif
        @if (! $hasPostalAddress)
            <div class="alert alert-warning" role="alert">CRM_POSTAL_ADDRESS is not configured. Campaigns cannot be scheduled until it is set.</div>
        @endif
        @foreach ($contentErrors as $contentError)
            <div class="alert alert-warning" role="alert">{{ $contentError }}</div>
        @endforeach

        <div class="row mb-3">
            <div class="col-md-4"><div class="card"><div class="card-body">
                <p class="text-muted mb-1">{{ $campaign->isDraft() ? 'Eligible now' : 'Recipients in snapshot' }}</p>
                <h2 class="h4 mb-0">{{ number_format($counts['eligible']) }}</h2>
            </div></div></div>
            <div class="col-md-4"><div class="card"><div class="card-body">
                <p class="text-muted mb-1">Not eligible (no consent, opted out or suppressed)</p>
                <h2 class="h4 mb-0">{{ number_format($counts['ineligible']) }}</h2>
            </div></div></div>
            <div class="col-md-4"><div class="card"><div class="card-body">
                <p class="text-muted mb-1">Unique people in audience</p>
                <h2 class="h4 mb-0">{{ number_format($counts['candidates']) }}</h2>
            </div></div></div>
        </div>

        <div class="card mb-3"><div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Audience</dt>
                <dd class="col-sm-9">
                    @if ($campaign->audience_type === 'events')
                        {{ $campaign->events->pluck('title')->join(', ') ?: 'No events selected' }}
                    @else
                        Whole CRM directory
                    @endif
                </dd>
                <dt class="col-sm-3">Subject</dt><dd class="col-sm-9">{{ $campaign->subject }}</dd>
                <dt class="col-sm-3">Created by</dt><dd class="col-sm-9">{{ $campaign->author?->name ?? '—' }}</dd>
            </dl>
        </div></div>

        <div class="card"><div class="card-body d-flex flex-wrap gap-3 align-items-end">
            @if (in_array($campaign->status, ['draft', 'scheduled'], true))
                <form method="POST" action="{{ route('admin.crm.campaigns.test-send', $campaign) }}">
                    @csrf
                    <button class="btn btn-outline-primary">Send test to me</button>
                </form>
            @endif

            @if ($campaign->isDraft())
                <form method="POST" action="{{ route('admin.crm.campaigns.schedule', $campaign) }}" class="d-flex gap-2 align-items-end"
                    onsubmit="return confirm(@js('Schedule this campaign for '.number_format($counts['eligible']).' eligible recipients?'))">
                    @csrf
                    <div>
                        <label for="scheduled_at" class="form-label">Send at (Eastern time)</label>
                        <input type="datetime-local" id="scheduled_at" name="scheduled_at" class="form-control" required>
                    </div>
                    <button class="btn btn-success" @disabled(! $hasPostalAddress || $counts['eligible'] === 0 || $contentErrors !== [])>Schedule</button>
                </form>
            @endif

            @if ($campaign->status === 'scheduled')
                <form method="POST" action="{{ route('admin.crm.campaigns.unschedule', $campaign) }}">
                    @csrf
                    <button class="btn btn-outline-secondary">Move back to draft</button>
                </form>
            @endif

            @if (in_array($campaign->status, ['draft', 'scheduled', 'paused'], true))
                <form method="POST" action="{{ route('admin.crm.campaigns.cancel', $campaign) }}" onsubmit="return confirm('Cancel this campaign? This cannot be undone.')">
                    @csrf
                    <button class="btn btn-outline-danger">Cancel campaign</button>
                </form>
            @endif

            @if ($campaign->isDraft())
                <form method="POST" action="{{ route('admin.crm.campaigns.destroy', $campaign) }}" onsubmit="return confirm('Delete this draft?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-link text-danger">Delete draft</button>
                </form>
            @endif
        </div></div>
        <p class="text-muted small mt-2">Scheduled campaigns are not sent yet: the delivery engine arrives in the next phase.</p>
    </div>
@endsection
