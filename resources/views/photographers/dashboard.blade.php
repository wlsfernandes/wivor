@extends('layouts.app-sidebar')

@section('title', 'Photographer Dashboard | WivorPhotos')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <p class="text-uppercase text-muted mb-1">Photographer dashboard</p>
                <h1 class="h3 mb-0">Welcome, {{ $photographerName }}</h1>
            </div>
        </div>

        @if (! $payoutSetupComplete)
            <div class="alert alert-warning" role="alert">
                <h2 class="h5 alert-heading">Complete payout setup</h2>
                <p class="mb-0">Stripe onboarding must be complete before WivorPhotos can send payouts. Payout setup will be enabled with the marketplace payment workflow.</p>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5">Your account is approved</h2>
                <p class="text-muted mb-0">Available events, assignments, uploads, sales, and payouts will appear here as the remaining MVP workflows are enabled.</p>
            </div>
        </div>
    </div>
@endsection
