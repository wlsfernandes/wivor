@extends('layouts.app')

@section('title', 'Photographer and Admin Login | WivorPhotos')

@section('meta-description', 'Photographers and administrators can sign in to manage WivorPhotos events, uploads, sales, and marketplace operations.')
@section('meta-keywords', 'WivorPhotos login, photographer login, event photo marketplace')

@section('content')
    <section class="h-100 gradient-form" style="background-color: #eee;">
        <div class="container py-5 h-100" style="padding:0px;">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-xl-10">
                    <div class="card rounded-3 text-black">
                        <div class="row g-0">
                            <!-- Login Form -->
                            <div class="col-lg-6">
                                <div class="card-body p-md-5 mx-md-4">
                                    @if (session('success'))
                                        <div class="alert alert-success">
                                            {{ session('success') }}
                                        </div>
                                    @endif

                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            {!! $errors->first() !!}
                                        </div>
                                    @endif
                                    <div class="text-center">
                                        <img src="{{ asset('assets/images/logo/wivor.png') }}" alt="logo">
                                    </div>

                                    <form id="loginForm" method="POST" action="{{ route('login') }}">
                                        @csrf
                                        <p style="margin-top:30px">Login</p>

                                        <div data-mdb-input-init class="form-outline mb-4">
                                            <input type="email" id="form2Example11" class="form-control" name="email"
                                                placeholder="Phone number or email address" required />
                                            <label class="form-label" for="form2Example11">Username</label>
                                        </div>

                                        <div data-mdb-input-init class="form-outline mb-4">
                                            <input type="password" id="form2Example22" class="form-control" name="password"
                                                required />
                                            <label class="form-label" for="form2Example22">Password</label>
                                        </div>

                                        <div class="text-center pt-1 mb-5 pb-1">
                                            <button id="loginButton" data-mdb-button-init data-mdb-ripple-init
                                                class="btn btn-primary btn-block w-100 fa-lg gradient-custom-2 mb-3" type="submit"
                                                style="border-color: #ff3d00;
                                                       background: linear-gradient(to right, #ff6700, #ff3d00, #1a1a1a);
                                                       background: -webkit-linear-gradient(to right, #ff6700, #ff3d00, #1a1a1a);">
                                                Log in
                                            </button>
                                            <a class="text-muted" href="{{ route('password.request') }}">Forgot password?</a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Customer and photographer guidance -->
                            <div class="col-lg-6 d-flex align-items-center gradient-custom-2">
                                <div class="text-white px-3 py-4 p-md-5 mx-md-4">
                                    <h2 class="mb-3" style="color:#1a1a1a">Looking for your event photos?</h2>
                                    <p class="small mb-0" style="color:#1a1a1a; margin-top:25px;">
                                        No customer account is required. Browse published event galleries, select your
                                        photos, and check out as a guest.
                                    </p>
                                    <div class="text-center pt-1" style="margin-top:25px;">
                                        <a class="btn btn-success btn-block w-100 fa-lg gradient-custom-2 mb-3"
                                            href="{{ route('events.listEvents') }}">Browse Event Photos</a>
                                    </div>

                                    <h3 class="h5" style="color:#1a1a1a; margin-top:25px;">Photographers</h3>
                                    <p class="small" style="color:#1a1a1a;">
                                        Approved photographers sign in here. New photographers can submit an application.
                                    </p>
                                    <a class="btn btn-outline-dark w-100" href="{{ route('photographers') }}#register_section">
                                        Apply as a Photographer
                                    </a>
                                </div>
                            </div>
                            <!-- End customer and photographer guidance -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
