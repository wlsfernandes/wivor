@extends('layouts.app-sidebar')

@section('title', 'Photographer Application | WivorPhotos')
@section('workspace-title', 'Application status')

@section('content')
    <div class="py-2 py-md-4">
        <div class="row justify-content-center">
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <p class="text-uppercase text-muted small fw-semibold mb-2">Photographer application</p>
                        <h1 class="h2 mb-3">{{ $statusLabel }}</h1>

                        @if ($showVerificationResend)
                            <div class="alert alert-warning" role="alert">
                                {{ $statusMessage }}
                            </div>
                            <form method="POST" action="{{ route('verification.resend') }}">
                                @csrf
                                <button class="btn btn-primary" type="submit">Resend verification email</button>
                            </form>
                        @else
                            <p class="mb-0">{{ $statusMessage }}</p>
                        @endif

                        @if ($canOpenDashboard)
                            <a class="btn btn-primary mt-3" href="{{ route('photographer.dashboard') }}">Open dashboard</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
