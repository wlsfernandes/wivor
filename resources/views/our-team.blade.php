@extends('layouts.app')

@section('title', '#somosAETH | Our Team')

@section('meta-description', 'This is a brief description of the home page.')

@section('meta-keywords', 'home, welcome, introduction')

<style>
    /* FontAwesome for working BootSnippet :> */

    @import url('https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');

    #team {
        background: #eee !important;
    }

    .btn-primary:hover,
    .btn-primary:focus {
        background-color: #4a235a;
        border-color: #4a235a;
        box-shadow: none;
        outline: none;
    }

    .btn-primary {
        color: #fff;
        background-color: #4a235a;
        border-color: #4a235a;
    }

    section {
        padding: 60px 0;
    }

    section .section-title {
        text-align: center;
        color: #4a235a;
        margin-bottom: 50px;
        text-transform: uppercase;
    }

    #team .card {
        border: none;
        background: #ffffff;
    }

    .image-flip:hover .backside,
    .image-flip.hover .backside {
        -webkit-transform: rotateY(0deg);
        -moz-transform: rotateY(0deg);
        -o-transform: rotateY(0deg);
        -ms-transform: rotateY(0deg);
        transform: rotateY(0deg);
        border-radius: .25rem;
    }

    .image-flip:hover .frontside,
    .image-flip.hover .frontside {
        -webkit-transform: rotateY(180deg);
        -moz-transform: rotateY(180deg);
        -o-transform: rotateY(180deg);
        transform: rotateY(180deg);
    }

    .mainflip {
        -webkit-transition: 1s;
        -webkit-transform-style: preserve-3d;
        -ms-transition: 1s;
        -moz-transition: 1s;
        -moz-transform: perspective(1000px);
        -moz-transform-style: preserve-3d;
        -ms-transform-style: preserve-3d;
        transition: 1s;
        transform-style: preserve-3d;
        position: relative;
    }

    .frontside {
        position: relative;
        -webkit-transform: rotateY(0deg);
        -ms-transform: rotateY(0deg);
        z-index: 2;
        margin-bottom: 30px;
    }

    .backside {
        position: absolute;
        top: 0;
        left: 0;
        background: white;
        -webkit-transform: rotateY(-180deg);
        -moz-transform: rotateY(-180deg);
        -o-transform: rotateY(-180deg);
        -ms-transform: rotateY(-180deg);
        transform: rotateY(-180deg);
        -webkit-box-shadow: 5px 7px 9px -4px rgb(158, 158, 158);
        -moz-box-shadow: 5px 7px 9px -4px rgb(158, 158, 158);
        box-shadow: 5px 7px 9px -4px rgb(158, 158, 158);
    }

    .frontside,
    .backside {
        -webkit-backface-visibility: hidden;
        -moz-backface-visibility: hidden;
        -ms-backface-visibility: hidden;
        backface-visibility: hidden;
        -webkit-transition: 1s;
        -webkit-transform-style: preserve-3d;
        -moz-transition: 1s;
        -moz-transform-style: preserve-3d;
        -o-transition: 1s;
        -o-transform-style: preserve-3d;
        -ms-transition: 1s;
        -ms-transform-style: preserve-3d;
        transition: 1s;
        transform-style: preserve-3d;
    }

    .frontside .card,
    .backside .card {
        min-height: 312px;
    }

    .backside .card a {
        font-size: 18px;
        color: 330033 !important;
    }

    .frontside .card .card-title,
    .backside .card .card-title {
        color: 330033 !important;
    }

    .frontside .card .card-body img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
    }
</style>

<!-- Content here -->

@section('content')
    <section class="page-title centred">
        <div class="bg-layer" style="background-image: url(assets/images/gallery/team.jpg);"></div>
        <div class="auto-container">
            <div class="content-box">
                <h1>@lang('messages.our_team')</h1>
            </div>
        </div>
    </section>


    <section class="team-page-section centred">
        <div class="auto-container">
            <div class="auto-container">
                <div class="sec-title centred mb_55" style="text-align: center;">
                    <h2 style="color:#4a235a;">@lang('team.staff')</h2>
                </div>
            </div>
            <!-- ******************************************* row ******************* -->
            <div class="row clearfix">
                <div class="col-lg-4 col-md-6 col-sm-12 team-block">
                    <div class="team-block-two">
                        <div class="inner-box">
                            <figure class="image-box">
                                <a href="{{ route('junior')}}"><img src="assets/images/team/wilson-fernandes-junior.png"
                                        alt=""></a>
                            </figure>
                            <div class="lower-content">
                                <h3><a href="{{ route('junior')}}" style="color:#4a235a;"> Wilson Fernandes Junior</a></h3>
                                <span class="designation">@lang('team.junior.p1')</span>
                            </div>
                        </div>
                    </div>
                </div>



            </div>

            <!-- ******************************************* row ******************* -->



        </div>


    </section>


@endsection
