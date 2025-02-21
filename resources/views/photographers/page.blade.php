@extends('layouts.app')

@section('title', 'Wivor | Home')

@section('meta-description', 'Discover comprehensive Hispanic theological education and Latino ministry training programs. Explore Bible institute certifications, leadership development, and theological resources for Hispanic pastors and church leaders. Empower your ministry with tailored courses and Spanish-language resources.')

@section('meta-keywords', 'somosWivor,Hispanic theological education, Latino ministry training, Bible institute certification, Hispanic church leadership, theological resources for Hispanics, Hispanic ministry programs, Latino religious education, Hispanic theology courses, Spanish theological resources, Hispanic pastoral training, leadership development, Latino church leaders resources, certification for Hispanic Bible institutes,Preaching, Compelling, Teologia, Teologica, Educacion, Education, Servicio, Comunidad, Hispano, Latino, Predicacion, Transformacion, America Latina, Caribe, Educadores, Scholars, Autores, Historiadores, Teologia Integral, Teologia Sistematica, Migración, Justicia Social, Adiestramiento, Formacion, Antioquia, Reflexión, Recursos, Libros, Storytelling, Colaboracion, En Conjunto')


<!-- Content here -->
<style>
    input[type="password"] {
        border: 1px solid #ced4da;
        padding: 10px;
        width: 100%;
        background: #F7F5F1;
        position: relative;
        display: block;
        width: 100%;
        height: 60px;
        background: #F7F5F1;
        border-radius: 5px;
        border: 1px solid #F7F5F1;
        font-size: 16px;
        font-family: 'Poppins', sans-serif;
        color: #6E6E6E;
        padding: 10px 30px;
        transition: all 500ms ease;
    }

    /* Optional: Add focus effect for better UI */
    input[type="password"]:focus {
        border-color: 1px solid #F7F5F1;
        outline: none;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    }
</style>
@section('content')

    <!-- cta-section -->
    <section class="cta-section home-3 centred">
        <div class="bg-layer parallax-bg" data-parallax='{"y": 100}'
            style="background-image: url(assets/images/gallery/wivor-photographer.png);"></div>
        <div class="auto-container">
            <div class="inner-box">
                <h2>WiVor</h2>
                <h3>@lang('photographers.photo_plataform')</h3>
                <a href="#register_section" class="theme-btn-one"><span>@lang('photographers.start_here')</span></a>
            </div>
        </div>
    </section>

    @include('partials.cards')


    <section id="register_section" class="contact-section sec-pad">
        <div class="auto-container">
            <div class="sec-title centred mb_55">
                <span class="sub-title calendar">@lang('messages.register_now')</span>
            </div>
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 form-column">
                    <div class="form-inner">
                        <form method="post" action="{{ route('registerPhotographer') }}" id="register-form"
                            autocomplete="off">
                            @csrf
                            <div class="row clearfix">
                                <!-- Name, Last Name, Phone -->
                                <div class="col-lg-4 col-md-4 col-sm-12 form-group">
                                    <input type="text" name="first_name" class="form-control"
                                        placeholder="@lang('messages.your_name')" required autocomplete="off">
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 form-group">
                                    <input type="text" name="last_name" class="form-control"
                                        placeholder="@lang('messages.last_name')" required autocomplete="off">
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 form-group">
                                    <input type="text" name="phone" class="form-control"
                                        placeholder="@lang('messages.your_phone')" required autocomplete="off">
                                </div>
                                <div class="col-lg-5 col-md-5 col-sm-12 form-group">
                                    <input type="text" name="address" class="form-control"
                                        placeholder="@lang('messages.address')" required autocomplete="off">
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-12 form-group">
                                    <input type="text" name="city" class="form-control" placeholder="@lang('messages.city')"
                                        required autocomplete="off">
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-12 form-group">
                                    <input type="text" name="state" class="form-control"
                                        placeholder="@lang('messages.state')" required autocomplete="off">
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-12 form-group">
                                    <input type="text" name="zipcode" class="form-control"
                                        placeholder="@lang('messages.zipcode')" required autocomplete="off">
                                </div>

                                <!-- Camera Model, Portfolio URL, Username -->
                                <div class="col-lg-4 col-md-4 col-sm-12 form-group">
                                    <input type="text" name="camera_model" class="form-control"
                                        placeholder="@lang('messages.camera_model')" required autocomplete="off">
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 form-group">
                                    <input type="text" name="profile_url" class="form-control"
                                        placeholder="@lang('messages.profile_url')" required autocomplete="off">
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 form-group">
                                    <input type="email" name="email" class="form-control"
                                        placeholder="@lang('messages.your_email')" required autocomplete="off">
                                </div>
                                <!-- About You -->
                                <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                    <textarea name="about" class="form-control"
                                        placeholder="@lang('messages.about_you')"></textarea>
                                </div>

                                <!-- Centered Submit Button -->
                                <div class="col-lg-12 col-md-12 col-sm-12 form-group text-center">
                                    <button class="theme-btn-one btn btn-primary" type="submit" name="submit-form">
                                        <i class="bi bi-person-plus-fill"></i> <span>@lang('messages.register_now')</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-section sec-pad">


        @include('partials.contact')


@endsection
