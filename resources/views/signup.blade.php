@extends('layouts.app')

@section('title', 'Wivor | Home')

@section('meta-description', 'Create a WivorPhotos account to discover professional sports and fitness event photos and access marketplace features.')

@section('meta-keywords', 'WivorPhotos sign up, create WivorPhotos account, sports event photos, athlete photos, fitness photography marketplace')


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


    <section id="signup_section" class="contact-section sec-pad" style="margin-bottom:150px;">
        <div class="auto-container">
            <div class="sec-title centred mb_55">
                <span class="sub-title calendar">@lang('messages.register_now')</span>
            </div>
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 form-column">
                    <div class="form-inner">
                        <form method="post" action="{{route('registerUser')}}" id="register-form"
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
                               
                                <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                    <input type="email" name="email" class="form-control"
                                        placeholder="@lang('messages.your_email')" required autocomplete="off">
                                </div>

                                <!-- Centered Submit Button -->
                                <div class="col-lg-12 col-md-12 col-sm-12 form-group text-center">
                                    <button class="theme-btn-one btn btn-primary" type="submit" name="submit-form">
                                        <i class="bi bi-person-plus-fill"></i> <span>@lang('messages.register_now')</span>
                                    </button>
                                    <p style="margin-top:50px;">@lang('header.by_click')</p>
                                    
                                    <p><input class="form-check-input" type="checkbox" id="consentCheckbox" required> @lang('header.authorization')</p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>




@endsection
