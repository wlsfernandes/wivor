@extends('frontend.v1.layouts.app')

@php
    $isFaceSearchEnabled = (bool) config('face_recognition.enabled');
    $metaDescription = $isFaceSearchEnabled ? 'WiVor connects photographers and athletes. Find your event photos with face and bib recognition. Free to use. Pay only for the photos you love.' : 'WiVor connects photographers and athletes. Find your event photos with bib number recognition. Free to use. Pay only for the photos you love.';
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

@section('content')
    {{-- Hero: dark text panel beside the race photo; copy and search come first on mobile. --}}
    <section class="wv-section wv-section--dark wv-hero">
        <div class="wv-container wv-hero__grid">
            <div>
                <h1 class="wv-h1 wv-hero__title">Your race. Your moment.</h1>
                @if ($isFaceSearchEnabled)
                    <p class="wv-lead">Find your event, search by bib number or selfie, then preview and buy the photos you want.</p>
                @else
                    <p class="wv-lead">Find your event, search by bib number, then preview and buy the photos you want.</p>
                @endif
                @include('frontend.v1.components.event-search', ['fieldId' => 'home-event-search'])
                <p class="wv-small"><a class="wv-link" href="{{ route('photographers') }}">I'm a Photographer</a></p>
            </div>
            <div class="wv-hero__media">
                <img src="{{ asset('assets/images/site/hero-finish-line.webp') }}"
                    alt="Runner with a race bib approaching cheering spectators on a seaside promenade" width="1200" height="494"
                    fetchpriority="high">
            </div>
        </div>
    </section>

    {{-- How it works: three steps beside an athlete photo. --}}
    <section class="wv-section wv-section--white">
        <div class="wv-container wv-split">
            <div>
                <h2 class="wv-h2">How WiVor works</h2>
                <ol class="wv-steps">
                    <li>
                        <h3 class="wv-h3">Find your event</h3>
                        <p class="wv-body">Search by event name, city or sport.</p>
                    </li>
                    <li>
                        <h3 class="wv-h3">Find yourself</h3>
                        @if ($isFaceSearchEnabled)
                            <p class="wv-body">Search this event by bib number or selfie, or browse every photo.</p>
                        @else
                            <p class="wv-body">Search this event by bib number, or browse every photo.</p>
                        @endif
                    </li>
                    <li>
                        <h3 class="wv-h3">Choose your photos</h3>
                        <p class="wv-body">Preview them, buy the ones you want and download them in full quality.</p>
                    </li>
                </ol>
                <a class="wv-link" href="{{ route('how-it-works') }}">See the full process</a>
            </div>
            <img class="wv-split__image" src="{{ asset('assets/images/site/athletes-bib-runners.webp') }}"
                alt="Two runners with race bibs celebrating on the course" width="800" height="800" loading="lazy">
        </div>
    </section>

    {{-- Photographers and organizers. --}}
    <section class="wv-section wv-section--light">
        <div class="wv-container">
            <div class="row g-4">
                <div class="col-12 col-md-6">
                    <div class="wv-card">
                        <img class="wv-card__image" src="{{ asset('assets/images/site/photographers-telephoto-lens.webp') }}"
                            alt="Photographer with a telephoto lens shooting an event crowd" width="900" height="452"
                            loading="lazy">
                        <h2 class="wv-h3">For photographers</h2>
                        <p class="wv-body">Upload your event photos and sell them to athletes. There is no sign-up fee. WiVor keeps a commission on each sale.</p>
                        <a class="wv-link" href="{{ route('photographers') }}#register_section">Apply as a Photographer</a>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="wv-card">
                        <img class="wv-card__image" src="{{ asset('assets/images/site/organizers-race-crowd.webp') }}"
                            alt="Crowd of runners on the road at the start of a race" width="900" height="372"
                            loading="lazy">
                        <h2 class="wv-h3">For event organizers</h2>
                        <p class="wv-body">Give your athletes professional photos of their race. WiVor works with photographers to cover your event.</p>
                        <a class="wv-link" href="{{ route('organizers') }}">Become a Partner</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Practical reassurance. --}}
    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <h2 class="wv-h2">Know what you are buying</h2>
            <div class="wv-reassurance">
                <div>
                    <h3 class="wv-h3">Preview first</h3>
                    <p class="wv-body">See your photos before you pay.</p>
                </div>
                <div>
                    <h3 class="wv-h3">Buy only what you want</h3>
                    <p class="wv-body">You pay only for the photos you choose.</p>
                </div>
                <div>
                    <h3 class="wv-h3">Your privacy</h3>
                    @if ($isFaceSearchEnabled)
                        <p class="wv-body">Selfie search is optional and needs your consent. Read the <a class="wv-link" href="{{ route('biometric-notice') }}">Biometric Notice</a>.</p>
                    @else
                        <p class="wv-body">Read how WiVor handles your data in our <a class="wv-link" href="{{ route('privacy') }}">Privacy Policy</a>.</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Closing action. --}}
    <section class="wv-section wv-section--dark">
        <div class="wv-container">
            <h2 class="wv-h2">Ready to find your photos?</h2>
            @if ($isFaceSearchEnabled)
                <p class="wv-lead">Search by event name, bib number or selfie.</p>
            @else
                <p class="wv-lead">Search by event name or bib number.</p>
            @endif
            <div class="wv-button-row">
                <a href="{{ route('athletes') }}" class="wv-btn wv-btn--primary">Find My Photos</a>
            </div>
        </div>
    </section>
@endsection
