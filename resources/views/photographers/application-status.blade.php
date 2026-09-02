@extends('layouts.app')

@section('title', 'Photographer Application | WivorPhotos')

@section('content')
    <section class="contact-section sec-pad">
        <div class="auto-container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <p class="text-uppercase text-muted mb-2">Photographer application</p>
                            <h1 class="h2 mb-3">{{ $statusLabel }}</h1>

                            @if ($showVerificationResend)
                                <div class="alert alert-warning" role="alert">
                                    {{ $statusMessage }}
                                </div>
                                <form method="POST" action="{{ route('verification.resend') }}">
                                    @csrf
                                    <button class="theme-btn-one" type="submit"><span>Resend verification email</span></button>
                                </form>
                            @else
                                <p>{{ $statusMessage }}</p>
                            @endif

                            @if ($canOpenDashboard)
                                <a class="theme-btn-one" href="{{ route('photographer.dashboard') }}"><span>Open dashboard</span></a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
