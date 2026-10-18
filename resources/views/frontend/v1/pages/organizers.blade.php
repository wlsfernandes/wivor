@extends('frontend.v1.layouts.app')

@section('title', 'Event Photography Partner | WiVor for Organizers')
@section('meta-description', 'Give your athletes professional photos at no cost to you. Partner with WiVor for your next race.')

@section('content')
    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <h1 class="wv-h1">Give your athletes the photos they'll remember.</h1>
            <p class="wv-lead">Partner with WiVor and offer professional event photos with no cost to you and no extra work for your team.</p>
            <div class="wv-button-row">
                {{-- Points to Contact until the partnership form ships (marketing site plan 3). --}}
                <a href="{{ route('contact_us') }}" class="wv-btn wv-btn--primary">Become a Partner</a>
            </div>
        </div>
    </section>

    <section class="wv-section wv-section--light">
        <div class="wv-container">
            <div class="row g-4">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="wv-card">
                        <i class="fas fa-hand-holding-usd wv-icon" aria-hidden="true"></i>
                        <h3 class="wv-h3">Zero cost</h3>
                        <p class="wv-body mb-0">WiVor is free for organizers.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="wv-card">
                        <i class="fas fa-flag-checkered wv-icon" aria-hidden="true"></i>
                        <h3 class="wv-h3">No extra work</h3>
                        <p class="wv-body mb-0">We connect photographers and athletes. You focus on the race.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="wv-card">
                        <i class="fas fa-running wv-icon" aria-hidden="true"></i>
                        <h3 class="wv-h3">Better athlete experience</h3>
                        <p class="wv-body mb-0">Athletes get their photos fast and share them, which promotes your event.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="wv-card">
                        <i class="fas fa-bullhorn wv-icon" aria-hidden="true"></i>
                        <h3 class="wv-h3">Free marketing</h3>
                        <p class="wv-body mb-0">Every shared photo shows your event to new participants.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <ol class="row g-4 list-unstyled mb-0">
                <li class="col-12 col-md-4">
                    <div class="wv-step-number">1</div>
                    <h3 class="wv-h3">Tell us about your event</h3>
                </li>
                <li class="col-12 col-md-4">
                    <div class="wv-step-number">2</div>
                    <h3 class="wv-h3">We coordinate photographers</h3>
                </li>
                <li class="col-12 col-md-4">
                    <div class="wv-step-number">3</div>
                    <h3 class="wv-h3">Athletes find and buy their photos</h3>
                </li>
            </ol>
        </div>
    </section>
@endsection
