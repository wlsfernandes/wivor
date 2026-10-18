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
    {{-- 4.1 Hero: text left, athlete photo right (one column on mobile). --}}
    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <div class="row g-4 align-items-center">
                <div class="col-12 col-lg-6">
                    <h1 class="wv-h1">Your best moments. Found in seconds.</h1>
                    @if ($isFaceSearchEnabled)
                        <p class="wv-lead">WiVor uses face and bib number recognition to match you with the photos photographers took of you at your event. Browse for free. Pay only for the ones you love.</p>
                    @else
                        <p class="wv-lead">WiVor uses bib number recognition to match you with the photos photographers took of you at your event. Browse for free. Pay only for the ones you love.</p>
                    @endif
                    <div class="wv-button-row">
                        <a href="{{ route('athletes') }}" class="wv-btn wv-btn--primary">Find My Photos</a>
                        <a href="{{ route('photographers') }}" class="wv-btn wv-btn--outline">I'm a Photographer</a>
                    </div>
                    <p class="wv-small">Free for photographers and event organizers. Athletes pay only for the photos they buy.</p>
                </div>
                <div class="col-12 col-lg-6">
                    <img class="wv-hero__image" src="{{ asset('assets/images/site/hero-finish-line.webp') }}"
                        alt="Runner raising both arms while breaking the finish line tape" width="1192" height="600"
                        fetchpriority="high">
                </div>
            </div>
        </div>
    </section>

    {{-- 4.2 Benefits strip --}}
    <section class="wv-section wv-section--dark">
        <div class="wv-container">
            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <i class="fas fa-user-plus wv-icon" aria-hidden="true"></i>
                    <h2 class="wv-h3">Free to join</h2>
                    <p class="wv-body mb-0">No sign-up fees for photographers, organizers or athletes.</p>
                </div>
                <div class="col-12 col-md-4">
                    <i class="fas fa-search wv-icon" aria-hidden="true"></i>
                    <h2 class="wv-h3">Smart matching</h2>
                    @if ($isFaceSearchEnabled)
                        <p class="wv-body mb-0">Face and bib number recognition finds your photos automatically.</p>
                    @else
                        <p class="wv-body mb-0">Bib number recognition finds your photos automatically.</p>
                    @endif
                </div>
                <div class="col-12 col-md-4">
                    <i class="fas fa-heart wv-icon" aria-hidden="true"></i>
                    <h2 class="wv-h3">Pay only if you love it</h2>
                    <p class="wv-body mb-0">See your photos first. Buy only the ones you want.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 4.3 How it works summary --}}
    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <h2 class="wv-h2">How WiVor works</h2>
            <ol class="row g-4 list-unstyled">
                <li class="col-12 col-md-4">
                    <div class="wv-step-number">1</div>
                    <h3 class="wv-h3">Photographers shoot</h3>
                    <p class="wv-body">Photographers capture athletes at races and events and upload their photos to WiVor.</p>
                </li>
                <li class="col-12 col-md-4">
                    <div class="wv-step-number">2</div>
                    <h3 class="wv-h3">We match you</h3>
                    @if ($isFaceSearchEnabled)
                        <p class="wv-body">Our platform identifies athletes using face recognition and bib numbers.</p>
                    @else
                        <p class="wv-body">Our platform identifies athletes using bib numbers.</p>
                    @endif
                </li>
                <li class="col-12 col-md-4">
                    <div class="wv-step-number">3</div>
                    <h3 class="wv-h3">You choose</h3>
                    <p class="wv-body">Find your photos, pick your favorites and download them in full quality.</p>
                </li>
            </ol>
            <a class="wv-link" href="{{ route('how-it-works') }}">See the full process →</a>
        </div>
    </section>

    {{-- 4.4 Three audiences --}}
    <section class="wv-section wv-section--light">
        <div class="wv-container">
            <h2 class="wv-h2">Made for everyone at the event</h2>
            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <div class="wv-card">
                        <img class="wv-card__image" src="{{ asset('assets/images/site/athletes-bib-runners.webp') }}"
                            alt="Two runners with race bibs celebrating on the course" width="800" height="800"
                            loading="lazy">
                        <h3 class="wv-h3">Athletes</h3>
                        <p class="wv-body">Find every photo of you, without scrolling through thousands of images.</p>
                        <a class="wv-link" href="{{ route('athletes') }}">Find my photos →</a>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="wv-card">
                        <img class="wv-card__image" src="{{ asset('assets/images/site/photographers-telephoto-lens.webp') }}"
                            alt="Photographer with a telephoto lens shooting an event crowd" width="900" height="452"
                            loading="lazy">
                        <h3 class="wv-h3">Photographers</h3>
                        <p class="wv-body">Upload your work, reach more athletes and let WiVor handle payments.</p>
                        <a class="wv-link" href="{{ route('photographers') }}#register_section">Join as a photographer →</a>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="wv-card">
                        <img class="wv-card__image" src="{{ asset('assets/images/site/organizers-race-crowd.webp') }}"
                            alt="Crowd of runners on the road at the start of a race" width="900" height="372"
                            loading="lazy">
                        <h3 class="wv-h3">Event Organizers</h3>
                        <p class="wv-body">Offer your athletes professional photos with zero cost and zero extra work.</p>
                        <a class="wv-link" href="{{ route('organizers') }}">Partner with us →</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 4.5 Why WiVor --}}
    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <div class="row g-5">
                <div class="col-12 col-lg-5">
                    <h2 class="wv-h2">Why athletes and photographers choose WiVor</h2>
                </div>
                <div class="col-12 col-lg-7">
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <i class="fas fa-images wv-icon" aria-hidden="true"></i>
                            <h3 class="wv-h3">No more endless searching</h3>
                            <p class="wv-body mb-0">Skip the folders and galleries. Your photos come to you.</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <i class="fas fa-balance-scale wv-icon" aria-hidden="true"></i>
                            <h3 class="wv-h3">Fair for photographers</h3>
                            <p class="wv-body mb-0">Photographers get paid when athletes buy. WiVor handles checkout and payments.</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <i class="fas fa-shield-alt wv-icon" aria-hidden="true"></i>
                            <h3 class="wv-h3">No risk for athletes</h3>
                            <p class="wv-body mb-0">Viewing is free. You only pay for photos you choose to buy.</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <i class="fas fa-medal wv-icon" aria-hidden="true"></i>
                            <h3 class="wv-h3">Built for events</h3>
                            <p class="wv-body mb-0">From local run clubs to triathlons, WiVor works for any event with bib numbers.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 4.6 Social proof: hidden until real testimonials and partner logo permission exist (spec 4.6). --}}

    {{-- 4.7 Final CTA --}}
    <section class="wv-section wv-section--orange">
        <div class="wv-container">
            <h2 class="wv-h2">Ready to find your photos?</h2>
            @if ($isFaceSearchEnabled)
                <p class="wv-lead">Search by your name, bib number or a selfie.</p>
            @else
                <p class="wv-lead">Search by event name or bib number.</p>
            @endif
            <div class="wv-button-row">
                <a href="{{ route('athletes') }}" class="wv-btn wv-btn--inverse">Find My Photos</a>
            </div>
        </div>
    </section>
@endsection
