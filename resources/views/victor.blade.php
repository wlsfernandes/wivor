@extends('layouts.app')

@section('title', 'Victor | Co-Founder of Wivor')

@section('meta-description', 'Meet Victor, co-founder of Wivor, CPA, sports enthusiast, and advocate for professional sports photography.')

@section('meta-keywords', 'Victor, Wivor co-founder, WivorPhotos, sports photography, CPA, Brazilian Jiu-Jitsu')

@section('content')
    <section class="team-details">
        <div class="auto-container">
            <div class="team-details-content">
                <div class="row clearfix">
                    <div class="col-lg-3 col-md-12 col-sm-12 image-column">
                        <figure class="image-box">
                            <img src="{{ asset('assets/images/team/victor.png') }}" alt="Victor">
                        </figure>
                    </div>
                    <div class="col-lg-9 col-md-12 col-sm-12 content-column">
                        <div class="content-box">
                            <h2>Victor</h2>
                            <p><b>@lang('team.victor.p1')</b></p>
                            <p>@lang('team.victor.p2')</p>
                            <p>@lang('team.victor.p3')</p>
                            <p>@lang('team.victor.p4')</p>
                            <p>@lang('team.victor.p5')</p>

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
