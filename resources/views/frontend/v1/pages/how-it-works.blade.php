@extends('frontend.v1.layouts.app')

@php($isFaceSearchEnabled = (bool) config('face_recognition.enabled'))
@php($metaDescription = $isFaceSearchEnabled ? 'See how WiVor matches athletes with event photos using face and bib recognition, and how photographers get paid.' : 'See how WiVor matches athletes with event photos using bib number recognition, and how photographers get paid.')

@section('title', 'How WiVor Works | Photo Matching for Athletes')
@section('meta-description', $metaDescription)

@section('content')
    <section class="wv-section wv-section--dark">
        <div class="wv-container">
            <h1 class="wv-h1">Simple for athletes. Simple for photographers.</h1>
            <p class="wv-lead">Here's how WiVor turns event photos into memories, step by step.</p>
        </div>
    </section>

    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <div class="row g-5">
                <div class="col-12 col-lg-6">
                    <h2 class="wv-h2">For Athletes</h2>
                    <ol class="list-unstyled">
                        <li class="mb-4">
                            <div class="wv-step-number">1</div>
                            <h3 class="wv-h3">Find your event</h3>
                            <p class="wv-body">Search for your race or event on WiVor.</p>
                        </li>
                        <li class="mb-4">
                            <div class="wv-step-number">2</div>
                            <h3 class="wv-h3">Search for yourself</h3>
                            @if ($isFaceSearchEnabled)
                                <p class="wv-body">Enter your bib number or upload a selfie. We find the matches.</p>
                            @else
                                <p class="wv-body">Enter your bib number. We find the matches.</p>
                            @endif
                        </li>
                        <li class="mb-4">
                            <div class="wv-step-number">3</div>
                            <h3 class="wv-h3">Preview your photos</h3>
                            <p class="wv-body">Browse every photo of you for free.</p>
                        </li>
                        <li>
                            <div class="wv-step-number">4</div>
                            <h3 class="wv-h3">Buy what you love</h3>
                            <p class="wv-body">Choose your favorites, pay securely and download.</p>
                        </li>
                    </ol>
                </div>
                <div class="col-12 col-lg-6">
                    <h2 class="wv-h2">For Photographers</h2>
                    <ol class="list-unstyled">
                        <li class="mb-4">
                            <div class="wv-step-number">1</div>
                            <h3 class="wv-h3">Create a free account</h3>
                            <p class="wv-body">Sign up in minutes. No fees.</p>
                        </li>
                        <li class="mb-4">
                            <div class="wv-step-number">2</div>
                            <h3 class="wv-h3">Shoot the event</h3>
                            <p class="wv-body">Photograph athletes as usual.</p>
                        </li>
                        <li class="mb-4">
                            <div class="wv-step-number">3</div>
                            <h3 class="wv-h3">Upload your photos</h3>
                            @if ($isFaceSearchEnabled)
                                <p class="wv-body">WiVor reads faces and bib numbers and sorts photos for you.</p>
                            @else
                                <p class="wv-body">WiVor reads bib numbers and sorts photos for you.</p>
                            @endif
                        </li>
                        <li>
                            <div class="wv-step-number">4</div>
                            <h3 class="wv-h3">Get paid</h3>
                            <p class="wv-body">When an athlete buys, WiVor handles the payment and you get your share.</p>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    @if ($isFaceSearchEnabled)
        <section class="wv-section wv-section--light">
            <div class="wv-container">
                <h2 class="wv-h2">Two ways to find you</h2>
                <p class="wv-lead">Bib number recognition reads the number on your race bib. Face recognition matches your face to photos taken at the event. Together, they make sure you don't miss a shot.</p>
                <a class="wv-link" href="{{ route('biometric-notice') }}">Learn how we protect your data →</a>
            </div>
        </section>
    @endif

    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <div class="wv-button-row">
                <a href="{{ route('athletes') }}" class="wv-btn wv-btn--primary">Find My Photos</a>
                <a href="{{ route('photographers') }}#register_section" class="wv-btn wv-btn--outline">Join as a Photographer</a>
            </div>
        </div>
    </section>
@endsection
