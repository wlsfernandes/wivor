@extends('layouts.app')

@section('title', 'Find Event Photos | WivorPhotos')

@section('meta-description', 'Find and purchase professional sports and fitness photos from events across the United States, or join WivorPhotos as a photographer.')

@section('meta-keywords', 'WivorPhotos, sports photography, event photos, race photos, athlete photos, fitness photography, find event photos, buy sports photos, sports photographers')

@section('styles')
<style>
    .home-search-hero {
        position: relative;
        padding: 96px 0 88px;
        background:
            linear-gradient(100deg, rgba(20, 20, 20, 0.9), rgba(20, 20, 20, 0.62)),
            url('{{ asset('assets/images/resource/banner_wivor.jpg') }}') center 42% / cover no-repeat;
    }

    .home-search-hero__intro {
        max-width: 820px;
        margin: 0 auto 32px;
        text-align: center;
    }

    .home-search-hero h1 {
        color: #fff;
        font-size: clamp(2.25rem, 5vw, 4.5rem);
        font-weight: 600;
        line-height: 1.08;
        text-shadow: 0 3px 14px rgba(0, 0, 0, 0.75);
    }

    .home-search-hero__intro p {
        max-width: 700px;
        margin: 20px auto 0;
        color: #fff;
        font-size: clamp(1rem, 2vw, 1.25rem);
        line-height: 1.6;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.9);
    }

    .home-event-search {
        max-width: 1120px;
        margin: 0 auto;
        padding: 28px;
        background: rgba(255, 255, 255, 0.97);
        border: 1px solid rgba(255, 255, 255, 0.72);
        border-radius: 16px;
        box-shadow: 0 18px 44px rgba(0, 0, 0, 0.28);
    }

    .home-event-search .form-label {
        color: #232323;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .home-event-search .form-control {
        min-height: 48px;
        border-color: #d8d8d8;
        color: #232323;
    }

    .home-event-search .form-control:focus {
        border-color: #ff6700;
        box-shadow: 0 0 0 0.2rem rgba(255, 103, 0, 0.2);
        outline: 2px solid transparent;
    }

    .home-search-submit {
        min-height: 50px;
        padding: 12px 28px;
        border: 1px solid #c93600;
        border-radius: 8px;
        background: linear-gradient(to right, #d94f00, #c93600);
        color: #fff;
        font-weight: 600;
    }

    .home-search-submit:hover,
    .home-search-submit:focus-visible {
        border-color: #a82e00;
        background: #a82e00;
        color: #fff;
    }

    .home-browse-link {
        color: #232323;
        font-weight: 600;
        text-decoration: underline;
        text-underline-offset: 4px;
    }

    .home-browse-link:hover,
    .home-browse-link:focus-visible {
        color: #d94f00;
    }

    .home-event-card {
        overflow: hidden;
        border-radius: 12px;
    }

    .home-event-card__image {
        width: 100%;
        height: 230px;
        object-fit: cover;
    }

    .home-event-card__sport {
        color: #c44500;
    }

    @media (max-width: 767.98px) {
        .home-search-hero {
            padding: 70px 0 64px;
        }

        .home-event-search {
            padding: 22px 18px;
        }

        .home-search-actions {
            align-items: stretch !important;
            flex-direction: column;
        }
    }
</style>
@endsection

@section('content')
    <section class="home-search-hero" aria-labelledby="home-search-title">
        <div class="auto-container">
            <div class="home-search-hero__intro">
                <h1 id="home-search-title">@lang('messages.home_find_title')</h1>
                <p>@lang('messages.home_find_support')</p>
            </div>

            <form class="home-event-search" method="GET" action="{{ route('events.listEvents') }}">
                <div class="row g-3">
                    <div class="col-lg-4">
                        <label class="form-label" for="home-search">@lang('messages.home_search_label')</label>
                        <input class="form-control" id="home-search" name="search" type="search"
                            placeholder="@lang('messages.home_search_placeholder')">
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label" for="home-city">@lang('messages.home_city')</label>
                        <input class="form-control" id="home-city" name="city" type="text" maxlength="120"
                            placeholder="@lang('messages.home_city_placeholder')">
                    </div>
                    <div class="col-md-3 col-lg-2">
                        <label class="form-label" for="home-state">@lang('messages.home_state')</label>
                        <input class="form-control text-uppercase" id="home-state" name="state" type="text"
                            maxlength="2" pattern="[A-Za-z]{2}" placeholder="@lang('messages.home_state_placeholder')"
                            title="@lang('messages.home_state_help')">
                    </div>
                    <div class="col-md-9 col-lg-3">
                        <label class="form-label" for="home-sport">@lang('messages.home_sport')</label>
                        <input class="form-control" id="home-sport" name="sport" type="text" maxlength="100"
                            placeholder="@lang('messages.home_sport_placeholder')">
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="home-date-from">@lang('messages.home_date_from')</label>
                        <input class="form-control" id="home-date-from" name="date_from" type="date">
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="home-date-to">@lang('messages.home_date_to')</label>
                        <input class="form-control" id="home-date-to" name="date_to" type="date">
                    </div>
                    <div class="col-lg-6 d-flex align-items-end gap-3 home-search-actions">
                        <button class="home-search-submit" type="submit">
                            <i class="bi bi-search me-2" aria-hidden="true"></i>@lang('messages.home_search_events')
                        </button>
                        <a class="home-browse-link py-2" href="{{ route('events.listEvents') }}">@lang('messages.home_browse_all_events')</a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    @include('partials.event', ['events' => $homepageEvents])
    @include('partials.customer-how-it-works')
    <section class="about-style-two pt_120">
        <div class="auto-container">
            <div class="row align-items-center clearfix">
                <div class="col-lg-6 col-md-12 col-sm-12 image-column">
                    <div class="image-box mr_40">
                        <div class="image-shape" style="background-image: url(assets/images/shape/shape-1.png);"></div>
                        <figure class="image"><img src="assets/images/gallery/wivor_who.png"
                                style="width: 550px; height: 504px;" alt=""></figure>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 col-sm-12 content-column">
                    <div class="content_block_two">
                        <div class="content-box ml_40">
                            <div class="sec-title mb_60">
                                <span class="sub-title">About WivorPhotos</span>
                                <h2>@lang('messages.who_we_are')</h2>
                            </div>
                            <div class="text mb_40">
                                <p>@lang('messages.who_we_are_p1')</p>
                            </div>
                            <div class="btn-box">
                                <a href="{{ route('about_us') }}" class="theme-btn-one">More About Us</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="about-style-two pt_120">
        <div class="auto-container">
            <div class="row align-items-center clearfix">
                <!-- Content Column (Moved to the left) -->
                <div class="col-lg-6 col-md-12 col-sm-12 content-column">
                    <div class="content_block_two">
                        <div class="content-box mr_40"> <!-- Changed ml_40 to mr_40 for spacing adjustment -->
                            <div class="sec-title mb_60">
                                <span class="sub-title">About WivorPhotos</span>
                                <h2>@lang('messages.what_we_do')</h2>
                            </div>
                            <div class="text mb_40">
                                <p>@lang('messages.what_we_do_p1')</p>
                                <p>@lang('messages.what_we_do_p2')</p>
                                <p>@lang('messages.what_we_do_p3')</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image Column (Moved to the right) -->
                <div class="col-lg-6 col-md-12 col-sm-12 image-column">
                    <div class="image-box ml_40"> <!-- Changed mr_40 to ml_40 for spacing adjustment -->
                        <div class="image-shape" style="background-image: url(assets/images/shape/shape-1.png);"></div>
                        <figure class="image"><img src="assets/images/gallery/wivor_photo_what.png"></figure>
                    </div>
                </div>
            </div>
        </div>
    </section>

   

    <section class=" cta-style-two" style="margin-top:48px;">
        <div class="pattern-layer"></div>
        <div class="auto-container">
            <div class="inner-box">
                <img src="assets/images/logo/wivor_white.png" width="96px" alt="">
            </div>
        </div>
    </section>

    @include('partials.cards')

    @include('partials.contact')


@endsection
