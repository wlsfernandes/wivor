@extends('layouts.app')

@section('title', 'Wivor | Home')

@section('meta-description', 'Discover comprehensive Hispanic theological education and Latino ministry training programs. Explore Bible institute certifications, leadership development, and theological resources for Hispanic pastors and church leaders. Empower your ministry with tailored courses and Spanish-language resources.')

@section('meta-keywords', 'somosWivor,Hispanic theological education, Latino ministry training, Bible institute certification, Hispanic church leadership, theological resources for Hispanics, Hispanic ministry programs, Latino religious education, Hispanic theology courses, Spanish theological resources, Hispanic pastoral training, leadership development, Latino church leaders resources, certification for Hispanic Bible institutes,Preaching, Compelling, Teologia, Teologica, Educacion, Education, Servicio, Comunidad, Hispano, Latino, Predicacion, Transformacion, America Latina, Caribe, Educadores, Scholars, Autores, Historiadores, Teologia Integral, Teologia Sistematica, Migración, Justicia Social, Adiestramiento, Formacion, Antioquia, Reflexión, Recursos, Libros, Storytelling, Colaboracion, En Conjunto')


<!-- Content here -->

@section('content')
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

    <section class="two-column-section" style="background-color: #ffffff; padding: 40px 0;">
        <div class="auto-container">
            <div class="row align-items-start">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2 style="text-align: center; margin-bottom: 20px;"><i class="bi bi-binoculars"></i>
                        @lang('messages.our_vision')</h2>
                    <p>@lang('messages.our_vision_p1').</p>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2 style="text-align: center; margin-bottom: 20px;"><i class="bi bi-compass"></i>
                        @lang('messages.our_mission')</h2>
                    <p>@lang('messages.our_vision_p1')</p>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-style-two">
        <div class="pattern-layer" style="background-image: url(assets/images/shape/shape-2.png);"></div>
        <div class="auto-container">
            <div class="inner-box">
                <img src="assets/images/logo/wivor_white.png" width="96px" alt="">
                <h2>@lang('messages.core_values')</h2>
            </div>
        </div>
    </section>
    <section class="one-column-section" style="background-color: #ffffff; padding: 40px 0; text-align: center;">
        <div class="auto-container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10 col-sm-12">
                    <h2 style="margin-bottom: 20px;">
                        <i class="bi bi-clipboard-check"></i> @lang('messages.values')
                    </h2>
                </div>
            </div>
        </div>
    </section>
    <section class="one-column-section" style="background-color: #ffffff; padding: 40px 0; text-align: center;">
        <div class="auto-container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10 col-sm-12" style="text-align: left;">
                    <h4 style="margin-bottom: 20px;">
                        <i class="bi bi-clipboard-check"></i>
                        @lang('messages.values_p1')
                    </h4>
                    <h4 style="margin-bottom: 20px;">
                        <i class="bi bi-clipboard-check"></i>
                        @lang('messages.values_p2')
                    </h4>
                    <h4 style="margin-bottom: 20px;">
                        <i class="bi bi-clipboard-check"></i>
                        @lang('messages.values_p3')
                    </h4>
                    <h4 style="margin-bottom: 20px;">
                        <i class="bi bi-clipboard-check"></i>
                        @lang('messages.values_p4')
                    </h4>
                    <h4 style="margin-bottom: 20px;">
                        <i class="bi bi-clipboard-check"></i>
                        @lang('messages.values_p5')
                    </h4>
                </div>
            </div>
        </div>
    </section>


    @include('partials.contact')


@endsection
