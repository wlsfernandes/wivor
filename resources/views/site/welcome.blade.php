@extends('layouts.app')

@section('title', 'Wivor | Home')

@section('meta-description', 'Discover comprehensive Hispanic theological education and Latino ministry training programs. Explore Bible institute certifications, leadership development, and theological resources for Hispanic pastors and church leaders. Empower your ministry with tailored courses and Spanish-language resources.')

@section('meta-keywords', 'somosWivor,Hispanic theological education, Latino ministry training, Bible institute certification, Hispanic church leadership, theological resources for Hispanics, Hispanic ministry programs, Latino religious education, Hispanic theology courses, Spanish theological resources, Hispanic pastoral training, leadership development, Latino church leaders resources, certification for Hispanic Bible institutes,Preaching, Compelling, Teologia, Teologica, Educacion, Education, Servicio, Comunidad, Hispano, Latino, Predicacion, Transformacion, America Latina, Caribe, Educadores, Scholars, Autores, Historiadores, Teologia Integral, Teologia Sistematica, Migración, Justicia Social, Adiestramiento, Formacion, Antioquia, Reflexión, Recursos, Libros, Storytelling, Colaboracion, En Conjunto')


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
                        <img class="d-block w-100" src="assets/images/resource/banner_wivor.jpg" alt="Wivor">
                        <div class="auto-container"
                            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 2; text-align: center;">
                            <div class="content-box">
                                <h1 style="font-size:72px;color: #FFFFFF"><b>WiVor</b></h1>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src="assets/images/resource/wivor_banner2.png" alt="Wivor">
                        <div class="auto-container"
                            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 2; text-align: center;">
                            <div class="content-box">
                                <h1 style="font-size:48px;color:#000000"><b>WiVor</b></h1>

                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src="assets/images/gallery/banner3.jpg" alt="Second slide">
                        <div class="auto-container"
                            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 2; text-align: center;">
                            <div class="content-box">
                                <h1 style="font-size:48px;color:#000000"><b>@lang('header.Wivor_values')</b></h1>
                                <h5 style="font-size:24px;color:#000000">@lang('header.values_p1')</h5>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src="assets/images/gallery/banner4.jpg" alt="Second slide">
                        <div class="auto-container"
                            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 2; text-align: center;">
                            <div class="content-box">
                                <h1 style="font-size:48px;color:#000000"><b>@lang('header.Wivor_values')</b></h1>
                                <h5 style="font-size:24px;color:#000000">@lang('header.values_p1')</h5>
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
