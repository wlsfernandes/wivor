@extends('layouts.master-without-nav')
@section('title')
    @lang('translation.Recover_Password')
@endsection
@section('content')
    <div class="account-pages my-5  pt-sm-5">
        <div class="container">
            <div class="row justify-content-center">

                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div>
                        <a href="{{ url('index') }}" class="mb-5 d-block auth-logo">
                            <img src="{{ asset('/assets/images/logo-dark.png') }}" alt="" height="22"
                                class="logo logo-dark">
                            <img src="{{ asset('/assets/images/logo-light.png') }}" alt="" height="22"
                                class="logo logo-light">
                        </a>
                        <div class="card">

                            <div class="card-body p-4">

                                <div class="text-center mt-2">
                                    <h5 class="text-primary">Reset Password</h5>
                                    <p class="text-muted">Reset Password with Minible.</p>
                                </div>
                                <div class="p-2 mt-4">
                                    <div class="alert alert-success text-center mb-4" role="alert">
                                        Enter your Email and instructions will be sent to you!
                                    </div>
                                    <form action="{{ url('index') }}">

                                        <div class="mb-3">
                                            <label class="form-label" for="useremail">Email</label>
                                            <input type="email" class="form-control" id="useremail"
                                                placeholder="Enter email">
                                        </div>

                                        <div class="mt-3 text-end">
                                            <button class="btn btn-primary w-sm waves-effect waves-light"
                                                type="submit">Reset</button>
                                        </div>


                                        <div class="mt-4 text-center">
                                            <p class="mb-0">Remember It ? <a href="auth-login"
                                                    class="fw-medium text-primary"> Signin </a></p>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                        <div class="mt-5 text-center">
                            <p>© <script>
                                    document.write(new Date().getFullYear())

                                </script> Minible. Crafted with <i class="mdi mdi-heart text-danger"></i> by Themesbrand</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
@endsection
