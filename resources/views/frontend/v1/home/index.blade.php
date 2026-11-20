@extends('frontend.v1.layouts.app')

@php
    $isFaceSearchEnabled = (bool) config('face_recognition.enabled');
    $metaDescription = $isFaceSearchEnabled ? 'WiVor connects photographers and athletes. Find your event photos with face and bib recognition. Free to browse. Pay only for the photos you love.' : 'WiVor connects photographers and athletes. Find your event photos with bib number recognition. Free to browse. Pay only for the photos you love.';
@endphp

@section('title', 'WiVor | Find and Buy Your Race Photos')
@section('meta-description', $metaDescription)

@section('structured-data')
    @php
        $organizationStructuredData = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => config('seo.brand'),
                'url' => url('/').'/',
                'logo' => asset('assets/images/logo/wivor.png'),
                'email' => config('contact.email'),
                'sameAs' => [
                    'https://www.instagram.com/wivor.photos/',
                    'https://www.facebook.com/p/WiVor-Photos-61573081696201/',
                ],
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => config('seo.brand'),
                'url' => url('/').'/',
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($organizationStructuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection

@section('styles')
    {{-- Display face for the homepage only; other public pages keep Manrope + Inter (docs/frontend/00-frontend-guide.md). --}}
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800&display=swap" rel="stylesheet">
@endsection

@section('content')
    <div class="wv-home">
        {{-- Hero: race photo with the real event search. Copy and search sit on the dark side of the photo. --}}
        <section class="wv-home-hero" aria-labelledby="home-hero-title">
            <div class="wv-home-hero__media">
                {{-- Replace with a <picture> and a larger source when a higher-resolution original is available. --}}
                <img src="{{ asset('assets/images/site/hero-finish-line.webp') }}"
                    alt="Runner with a race bib approaching cheering spectators on a seaside promenade" width="1200" height="494"
                    fetchpriority="high" decoding="async">
            </div>
            <div class="wv-container wv-home-hero__inner">
                <div class="wv-hero__intro wv-home-hero__intro">
                    <h1 id="home-hero-title" class="wv-home-display wv-home-hero__title"><span>Your race.</span> <span>Your moment.</span> <span>Your photos.</span></h1>
                    @if ($isFaceSearchEnabled)
                        <p class="wv-home-hero__lead">Find your event, then find yourself in it by bib number or optional selfie. Preview every photo before you buy.</p>
                    @else
                        <p class="wv-home-hero__lead">Find your event, then find yourself in it by bib number. Preview every photo before you buy.</p>
                    @endif
                    @include('frontend.v1.components.event-search', ['fieldId' => 'home-event-search'])
                    <p class="wv-home-hero__secondary"><a class="wv-home-text-link" href="{{ route('how-it-works') }}">See how it works</a></p>
                </div>
            </div>
        </section>

        {{-- How it works: three stages, numbered because they happen in this order. --}}
        <section class="wv-home-section wv-home-section--white" aria-labelledby="home-steps-title">
            <div class="wv-container">
                <h2 id="home-steps-title" class="wv-home-display wv-home-h2">From finish line to favorite photo.</h2>
                <p class="wv-home-lead">Find your event. Find yourself. Choose the moments you want to keep.</p>
                <ol class="wv-home-steps">
                    <li>
                        <h3 class="wv-home-h3">Find your event</h3>
                        <p class="wv-home-body">Search by race name, city or sport.</p>
                    </li>
                    <li>
                        <h3 class="wv-home-h3">Find yourself</h3>
                        @if ($isFaceSearchEnabled)
                            <p class="wv-home-body">Inside the event, search by bib number or optional selfie, or browse every photo.</p>
                        @else
                            <p class="wv-home-body">Inside the event, search by bib number, or browse every photo.</p>
                        @endif
                    </li>
                    <li>
                        <h3 class="wv-home-h3">Keep your moments</h3>
                        <p class="wv-home-body">Preview your photos, buy the ones you want and download them in full quality.</p>
                    </li>
                </ol>
                <a class="wv-home-text-link" href="{{ route('how-it-works') }}">See the full process</a>
            </div>
        </section>

        {{-- Event directory: stands in for event cards until the homepage receives event data. --}}
        <section class="wv-home-section wv-home-section--light" aria-labelledby="home-events-title">
            <div class="wv-container wv-home-events">
                <div>
                    <h2 id="home-events-title" class="wv-home-display wv-home-h2">Your next great moment starts here.</h2>
                    <p class="wv-home-lead">Explore events and find the photos that tell your story.</p>
                </div>
                <div class="wv-button-row">
                    <a class="wv-btn wv-btn--outline wv-home-cta" href="{{ route('events.listEvents') }}">Browse all events</a>
                </div>
            </div>
        </section>

        {{-- Story: the feeling after the race, carried by the photo. --}}
        <section class="wv-home-story" aria-labelledby="home-story-title">
            <div class="wv-home-story__grid">
                <img class="wv-home-story__image" src="{{ asset('assets/images/site/athletes-bib-runners.webp') }}"
                    alt="Two runners with race bibs celebrating on the course" width="800" height="800" loading="lazy" decoding="async">
                <div class="wv-container wv-home-story__copy">
                    <h2 id="home-story-title" class="wv-home-display wv-home-h2">The race ends. The feeling stays.</h2>
                    <p class="wv-home-body">The effort, the celebration, the people cheering you on. Those moments deserve more than a passing memory.</p>
                    <a class="wv-home-text-link" href="{{ route('events.listEvents') }}">Find your event</a>
                </div>
            </div>
        </section>

        {{-- Photographers: second audience, own section and own action. --}}
        <section class="wv-home-section wv-home-section--white" aria-labelledby="home-photographers-title">
            <div class="wv-container wv-home-split">
                <img class="wv-home-split__image" src="{{ asset('assets/images/site/photographers-telephoto-lens.webp') }}"
                    alt="Photographer with a telephoto lens shooting an event crowd" width="900" height="452" loading="lazy" decoding="async">
                <div>
                    <p class="wv-home-audience">For photographers</p>
                    <h2 id="home-photographers-title" class="wv-home-display wv-home-h2">Capture the action. Reach the athletes.</h2>
                    <p class="wv-home-body">Upload your event photos, help athletes find your work and sell through WiVor. There is no sign-up fee. WiVor keeps a commission on each sale.</p>
                    <div class="wv-button-row">
                        <a class="wv-btn wv-btn--primary wv-home-cta" href="{{ route('photographers') }}">Explore photographer opportunities</a>
                    </div>
                </div>
            </div>
        </section>

        {{-- Organizers: third audience, a quieter panel. --}}
        <section class="wv-home-section wv-home-section--white wv-home-section--organizers" aria-labelledby="home-organizers-title">
            <div class="wv-container">
                <div class="wv-home-panel">
                    <img class="wv-home-panel__image" src="{{ asset('assets/images/site/organizers-race-crowd.webp') }}"
                        alt="Feet of runners in a crowded race start" width="900" height="372" loading="lazy" decoding="async">
                    <div class="wv-home-panel__copy">
                        <p class="wv-home-audience">For event organizers</p>
                        <h2 id="home-organizers-title" class="wv-home-display wv-home-h2">Give athletes more to remember.</h2>
                        <p class="wv-home-body">Give your athletes professional photos of their race. WiVor works with photographers to cover your event.</p>
                        <div class="wv-button-row">
                            <a class="wv-btn wv-btn--outline wv-home-cta" href="{{ route('organizers') }}">Partner with WiVor</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Purchase facts and privacy. --}}
        <section class="wv-home-section wv-home-section--light" aria-labelledby="home-facts-title">
            <div class="wv-container">
                <h2 id="home-facts-title" class="wv-home-display wv-home-h2">Know what you are buying.</h2>
                <div class="wv-home-facts">
                    <div>
                        <h3 class="wv-home-h3">Preview before you buy</h3>
                        <p class="wv-home-body">See your photos before you pay.</p>
                    </div>
                    <div>
                        <h3 class="wv-home-h3">Choose the photos you love</h3>
                        <p class="wv-home-body">You pay only for the photos you choose.</p>
                    </div>
                    <div>
                        @if ($isFaceSearchEnabled)
                            <h3 class="wv-home-h3">Selfie search is optional</h3>
                            <p class="wv-home-body">It needs your consent. Read the <a href="{{ route('biometric-notice') }}">Biometric Notice</a> and our <a href="{{ route('privacy') }}">Privacy Policy</a>.</p>
                        @else
                            <h3 class="wv-home-h3">Your privacy</h3>
                            <p class="wv-home-body">Read how WiVor handles your data in our <a href="{{ route('privacy') }}">Privacy Policy</a>.</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- Closing action. --}}
        <section class="wv-home-closing" aria-labelledby="home-closing-title">
            <div class="wv-container">
                <h2 id="home-closing-title" class="wv-home-display">Ready to relive your race?</h2>
                <p class="wv-home-lead">Your favorite moments may be waiting. Start by finding your event.</p>
                <div class="wv-button-row">
                    <a class="wv-btn wv-home-cta wv-home-cta--ink" href="{{ route('events.listEvents') }}">Find My Photos</a>
                </div>
            </div>
        </section>
    </div>
@endsection
