@extends('layouts.master')
@section('title', 'New Email Campaign')
@section('content')
    <div class="container-fluid py-4">
        <a href="{{ route('admin.crm.campaigns.index') }}">← Email campaigns</a>
        <h1 class="h3 mt-2 mb-3">New email campaign</h1>

        @if ($templates->isNotEmpty())
            <form method="GET" action="{{ route('admin.crm.campaigns.create') }}" class="d-flex gap-2 mb-3">
                @foreach ($selectedEventIds as $eventId)
                    <input type="hidden" name="event_id" value="{{ $eventId }}">
                @endforeach
                <select name="template" class="form-select w-auto" aria-label="Template">
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}" @selected($campaign->crm_template_id === $template->id)>{{ $template->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline-secondary">Start from template</button>
            </form>
        @endif

        <div class="card"><div class="card-body">
            <form method="POST" action="{{ route('admin.crm.campaigns.store') }}">
                @csrf
                @include('admin.crm.campaigns._form')
                <button type="submit" class="btn btn-primary">Save draft</button>
            </form>
        </div></div>
    </div>
@endsection
@section('script-bottom')
    @include('admin.crm._editor-script')
@endsection
