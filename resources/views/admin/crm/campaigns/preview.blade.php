@extends('layouts.master')
@section('title', 'Preview — '.$campaign->title)
@section('content')
    <div class="container-fluid py-4">
        <a href="{{ route('admin.crm.campaigns.show', $campaign) }}">← {{ $campaign->title }}</a>
        <h1 class="h3 mt-2 mb-1">Preview</h1>
        <p class="text-muted">Subject: <strong>{{ $email->subject }}</strong>. Personalized with sample values from your account.</p>

        <div class="d-flex flex-wrap gap-4 align-items-start">
            <div>
                <h2 class="h6">Mobile</h2>
                {{-- sandbox="" blocks scripts, forms and navigation inside the preview. --}}
                <iframe sandbox="" title="Mobile preview" srcdoc="{{ $email->html }}" style="width: 375px; height: 720px; border: 1px solid #ddd; background: #fff;"></iframe>
            </div>
            <div class="flex-grow-1">
                <h2 class="h6">Desktop</h2>
                <iframe sandbox="" title="Desktop preview" srcdoc="{{ $email->html }}" style="width: 100%; max-width: 700px; height: 720px; border: 1px solid #ddd; background: #fff;"></iframe>
            </div>
        </div>

        <h2 class="h6 mt-4">Plain-text version</h2>
        <pre class="border rounded p-3 bg-light" style="white-space: pre-wrap;">{{ $email->text }}</pre>
    </div>
@endsection
