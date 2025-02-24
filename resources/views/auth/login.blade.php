@extends('layouts.app')

@section('title', '#somosAETH | Portal')

@section('meta-description', 'This is a brief description of the home page.')

@section('meta-keywords', 'Wivor')

@section('content')
    <style>
        .gradient-custom-2 {
            border-color: #ff3d00;
            background: linear-gradient(to right, #ff6700, #ff3d00, #1a1a1a);
            background: -webkit-linear-gradient(to right, #ff6700, #ff3d00, #1a1a1a);
        }


        @media (min-width: 769px) {
            .gradient-custom-2 {
                border-top-right-radius: .3rem;
                border-bottom-right-radius: .3rem;
            }
        }
    </style>

    <section class="h-100 gradient-form" style="background-color: #eee;">
        <div class="container py-5 h-100" style="padding:0px;">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-xl-10">
                    <div class="card rounded-3 text-black">
                        <div class="row g-0">
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


                                    <form method="POST" action="{{ route('login') }}">
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
                                            <button data-mdb-button-init data-mdb-ripple-init
                                                class="btn btn-primary btn-block fa-lg gradient-custom-2 mb-3" type="submit"
                                                style="  border-color: #ff3d00;
                background: linear-gradient(to right, #ff6700, #ff3d00, #1a1a1a);
                background: -webkit-linear-gradient(to right, #ff6700, #ff3d00, #1a1a1a);">Log in</button>
                                            <a class="text-muted" href="{{ route('password.request') }}">Forgot
                                                password?</a>
                                        </div>

                                    </form>

                                </div>
                            </div>
                            <div class="col-lg-6 d-flex align-items-center gradient-custom-2">
                                <div class="text-white px-3 py-4 p-md-5 mx-md-4">
                                    <h4 class="mb-4" style="color:#fff">WiVor</h4>
                                    <p class="small mb-0" style="color:#1a1a1a">Lorem ipsum dolor sit amet, consectetur
                                        adipisicing elit, sed do
                                        eiusmod
                                        tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
                                        nostrud
                                        exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
