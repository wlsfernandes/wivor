@extends('layouts.app')

@section('title', 'Wivor | Home')

@section('meta-description', 'Find and purchase professional sports and fitness photos from events across the United States, or join WivorPhotos as a photographer.')

@section('meta-keywords', 'WivorPhotos, sports photography, event photos, race photos, athlete photos, fitness photography, find event photos, buy sports photos, sports photographers')

<style>
    .home-banner-copy {
        position: absolute;
        top: 50%;
        left: 50%;
        z-index: 2;
        width: min(90%, 900px);
        padding: 0 15px;
        text-align: center;
        transform: translate(-50%, -50%);
    }

    .home-banner-copy h1 {
        color: #fff;
        font-size: clamp(2rem, 4.5vw, 4.5rem);
        line-height: 1.1;
        text-shadow: 0 3px 14px rgba(0, 0, 0, 0.75);
    }

    .home-banner-copy p {
        max-width: 760px;
        margin: 18px auto 0;
        color: #fff;
        font-size: clamp(1rem, 2vw, 1.5rem);
        line-height: 1.5;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.9);
    }

    .home-banner-copy.dark h1,
    .home-banner-copy.dark p {
        color: #1f1f1f;
        text-shadow: 0 2px 12px rgba(255, 255, 255, 0.9);
    }
</style>

<!-- Content here -->

@section('content')
    <section>
        <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
                <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
                <li data-target="#carouselExampleIndicators" data-slide-to="3"></li>

            </ol>
            <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img class="d-block w-100" src="assets/images/resource/banner_wivor.jpg"
                            alt="Sports photographer preparing to capture an event">
                        <div class="home-banner-copy">
                            <div class="content-box">
                                <h1><b>@lang('messages.banner_1_title')</b></h1>
                                <p>@lang('messages.banner_1_subtitle')</p>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src="assets/images/resource/wivor_banner2.png"
                            alt="Photographer carrying a camera and tripod">
                        <div class="home-banner-copy dark">
                            <div class="content-box">
                                <h1><b>@lang('messages.banner_2_title')</b></h1>
                                <p>@lang('messages.banner_2_subtitle')</p>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src="assets/images/gallery/banner3.jpg"
                            alt="Baseball players gathered beside the field">
                        <div class="home-banner-copy dark">
                            <div class="content-box">
                                <h1><b>@lang('messages.banner_3_title')</b></h1>
                                <p>@lang('messages.banner_3_subtitle')</p>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src="assets/images/gallery/banner4.jpg"
                            alt="Surfer riding a wave">
                        <div class="home-banner-copy dark">
                            <div class="content-box">
                                <h1><b>@lang('messages.banner_4_title')</b></h1>
                                <p>@lang('messages.banner_4_subtitle')</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
    </section>
    <section class="cta-style-two">
        <div class="pattern-layer"></div>
        <div class="auto-container">
            <div class="inner-box">
            </div>
        </div>
    </section>
    @include('partials.customer-how-it-works')
    @include('partials.event', ['events' => $events])
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
                                <span class="sub-title">About WiVor</span>
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
                                <span class="sub-title">About WiVor</span>
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
