@extends('frontend.v1.layouts.app')

@php($isFaceSearchEnabled = (bool) config('face_recognition.enabled'))

@section('title', 'About | WiVor')
@section('meta-description', 'WiVor is an online platform that connects photographers and athletes.')

@section('content')
    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <h1 class="wv-h1">Every athlete deserves their photos.</h1>
            <p class="wv-lead">WiVor is an online platform that connects photographers and athletes. Photographers capture the moment. We make sure the right athlete finds the right photo, using {{ $isFaceSearchEnabled ? 'face and bib number recognition' : 'bib number recognition' }}, and we take care of payments along the way. We believe event photos should be easy to find, fair for photographers, and free of risk for athletes.</p>
        </div>
    </section>

    <section class="wv-section wv-section--light">
        <div class="wv-container">
            <h2 class="wv-h2">Our values</h2>
            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <div class="wv-card"><h3 class="wv-h3 mb-0">Simple</h3></div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="wv-card"><h3 class="wv-h3 mb-0">Fair</h3></div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="wv-card"><h3 class="wv-h3 mb-0">Professional</h3></div>
                </div>
            </div>
        </div>
    </section>

    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <a href="{{ route('contact_us') }}" class="wv-btn wv-btn--primary">Get in touch</a>
        </div>
    </section>
@endsection
