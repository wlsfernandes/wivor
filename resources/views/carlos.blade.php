@extends('layouts.app')

@section('title', 'Carlos | Co-Founder of Wivor')

@section('meta-description', 'Meet Carlos, co-founder of Wivor, engineer, business strategist, athlete, and advocate for professional sports photography.')

@section('meta-keywords', 'Carlos, Wivor co-founder, WivorPhotos, sports photography, mechanical engineer, Brazilian Jiu-Jitsu')

@section('content')
    <section class="team-details">
        <div class="auto-container">
            <div class="team-details-content">
                <div class="row clearfix">
                    <div class="col-lg-3 col-md-12 col-sm-12 image-column">
                        <figure class="image-box">
                            <img src="{{ asset('assets/images/team/carlos.png') }}" alt="Carlos">
                        </figure>
                    </div>
                    <div class="col-lg-9 col-md-12 col-sm-12 content-column">
                        <div class="content-box">
                            <h2>Carlos</h2>
                            <p><b>@lang('team.carlos.p1')</b></p>
                            <p>@lang('team.carlos.p2')</p>
                            <p>@lang('team.carlos.p3')</p>
                            <p>@lang('team.carlos.p4')</p>
                            <p>@lang('team.carlos.p5')</p>

                            <ul class="info-list clearfix">
                                <li><span>Email:</span> <a href="mailto:contact@wivor">contact@wivor</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
