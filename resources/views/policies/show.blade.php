@extends('layouts.app')

@section('title', $title . ' | WivorPhotos')
@section('meta-description', $metaDescription)

@section('content')
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="mb-3">{{ $title }}</h1>
                <p class="text-muted fw-semibold">Last updated: {{ $lastUpdated ?? 'To be published' }}</p>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body p-4 p-md-5">
                        <div style="white-space: pre-wrap; overflow-wrap: anywhere;">{{ $content }}</div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
