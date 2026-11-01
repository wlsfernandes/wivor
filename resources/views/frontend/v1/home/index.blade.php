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

@section('content')
    {{-- Hero: dark text panel beside the race photo; copy and search come first on mobile. --}}
    <section class="wv-section wv-section--dark wv-hero">
        <div class="wv-container wv-hero__grid">
            <div class="wv-hero__intro">
                <h1 class="wv-h1 wv-hero__title"><span>Find your race photos.</span> <span>Relive the moment.</span></h1>
                @if ($isFaceSearchEnabled)
                    <p class="wv-lead">Search your event, find yourself by bib number or selfie, and preview the photos before you buy.</p>
                @else
                    <p class="wv-lead">Search your event, find yourself by bib number, and preview the photos before you buy.</p>
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

    {{-- Event directory: stands in for live gallery proof until the homepage has event data. --}}
    <section class="wv-section wv-section--light wv-band">
        <div class="wv-container wv-band__row">
            <div>
                <h2 class="wv-h3">Not sure of the event name?</h2>
                <p class="wv-body">See the full list of events on WiVor.</p>
            </div>
            <a class="wv-link" href="{{ route('events.listEvents') }}">Browse events</a>
        </div>
    </section>

    {{-- How it works: three steps beside an athlete photo. --}}
    <section class="wv-section wv-section--white">
        <div class="wv-container wv-split">
            <div>
                <h2 class="wv-h2">How WiVor works</h2>
                <p class="wv-lead">A race goes by fast. Your photos keep the moments that mattered.</p>
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

    {{-- Photographers: full-width panel, the mirror of the hero. --}}
    <section class="wv-section wv-section--dark wv-photographers">
        <img class="wv-photographers__image" src="{{ asset('assets/images/site/photographers-telephoto-lens.webp') }}"
            alt="Photographer with a telephoto lens shooting an event crowd" width="900" height="452" loading="lazy">
        <div class="wv-container wv-photographers__copy">
            <h2 class="wv-h2">You capture the race. Help athletes find your work.</h2>
            <p class="wv-body">Upload your event photos, let athletes find them and sell through WiVor. There is no sign-up fee. WiVor keeps a commission on each sale.</p>
            <div class="wv-button-row">
                <a class="wv-btn wv-btn--primary" href="{{ route('photographers') }}#register_section">Apply as a Photographer</a>
            </div>
        </div>
    </section>

    {{-- Organizers: a short path, below the athlete and photographer journeys. --}}
    <section class="wv-section wv-section--light wv-band">
        <div class="wv-container wv-band__row">
            <div>
                <h2 class="wv-h3">Organizing a race?</h2>
                <p class="wv-body">Give your athletes professional photos of their race. WiVor works with photographers to cover your event.</p>
            </div>
            <a class="wv-link" href="{{ route('organizers') }}">Become a Partner</a>
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
                <a class="wv-btn wv-btn--primary" href="{{ route('events.listEvents') }}">Find My Photos</a>
            </div>
        </div>
    </section>
@endsection
