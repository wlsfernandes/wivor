@extends('layouts.app')

@section('title', '#somosAETH | Our team')

@section('meta-description', 'AETH - Team')

@section('meta-keywords', 'aeth, team')


<!-- Content here -->

@section('content')
    <section class="team-details">
        <div class="auto-container">
            <div class="team-details-content">
                <div class="row clearfix">
                    <div class="col-lg-3 col-md-12 col-sm-12 image-column">
                        <figure class="image-box"><img src="assets/images/team/wilson-fernandes-junior.png" alt=""></figure>
                    </div>
                    <div class="col-lg-9 col-md-12 col-sm-12 content-column">
                        <div class="content-box">
                            <h2>Wilson Fernandes Junior</h2>
                            <p><b>@lang('team.junior.p1')</b></p>
                            <p>@lang('team.junior.p2')</p>
                            <p>@lang('team.junior.p3')</p>
                            <p>@lang('team.junior.p4')</p>

                            <ul class="info-list clearfix">
                                <li><span>Email:</span> <a
                                        href="mailto:wfernandes@wivorphotos.com">wfernandes@wivorphotos.com</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection
