@extends('layouts.master')

@section('title', 'Review Photographer')

@section('content')
    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <a href="{{ route('photographers.list') }}" class="d-inline-block mb-2">&larr; All photographers</a>
                            <h1 class="h4 mb-1">{{ $photographer->first_name }} {{ $photographer->last_name }}</h1>
                            <p class="text-muted mb-0">{{ $statusLabel }}</p>
                        </div>
                    </div>

                    <dl class="row mb-0">
                        <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $photographer->user->email }}</dd>
                        <dt class="col-sm-4">Email verification</dt><dd class="col-sm-8">{{ $emailVerifiedLabel }}</dd>
                        <dt class="col-sm-4">Phone</dt><dd class="col-sm-8">{{ $photographer->phone }}</dd>
                        <dt class="col-sm-4">Location</dt><dd class="col-sm-8">{{ $photographer->city }}, {{ $photographer->state }}</dd>
                        <dt class="col-sm-4">Portfolio</dt>
                        <dd class="col-sm-8"><a href="{{ $photographer->profile_url }}" target="_blank" rel="noopener noreferrer">Open portfolio</a></dd>
                        <dt class="col-sm-4">Camera/equipment</dt><dd class="col-sm-8">{{ $photographer->camera_model }}</dd>
                        <dt class="col-sm-4">Introduction</dt><dd class="col-sm-8 text-break">{{ $photographer->about }}</dd>
                        <dt class="col-sm-4">Registered</dt><dd class="col-sm-8">{{ $registeredAtLabel }}</dd>
                        <dt class="col-sm-4">Stripe onboarding</dt><dd class="col-sm-8">{{ $stripeOnboardingLabel }}</dd>
                        @if ($reviewedAtLabel)
                            <dt class="col-sm-4">Last reviewed</dt><dd class="col-sm-8">{{ $reviewedAtLabel }} by {{ $reviewerName }}</dd>
                        @endif
                        @if ($photographer->status_reason)
                            <dt class="col-sm-4">Internal reason</dt><dd class="col-sm-8">{{ $photographer->status_reason }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="h5">Review actions</h2>

                    @if ($canApprove)
                        <form method="POST" action="{{ route('admin.photographers.approve', $photographer) }}" class="mb-4">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-success w-100" type="submit">Approve photographer</button>
                        </form>
                    @endif

                    @if ($canRestore)
                        <form method="POST" action="{{ route('admin.photographers.restore', $photographer) }}" class="mb-4">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-success w-100" type="submit">Restore access</button>
                        </form>
                    @endif

                    @if ($canDecline || $canSuspend)
                        <form method="POST" action="{{ $canDecline ? route('admin.photographers.decline', $photographer) : route('admin.photographers.suspend', $photographer) }}">
                            @csrf
                            @method('PATCH')
                            <div class="mb-3">
                                <label for="reason" class="form-label">Internal reason</label>
                                <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="4" required>{{ old('reason') }}</textarea>
                                <div class="form-text">This note is not included in the photographer email.</div>
                                @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <button class="btn btn-danger w-100" type="submit">{{ $canDecline ? 'Decline application' : 'Suspend access' }}</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
