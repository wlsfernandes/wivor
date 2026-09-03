@extends('layouts.app')

@section('title', 'Apply as a Photographer | WivorPhotos')

@section('meta-description', 'Apply to become a WivorPhotos photographer, cover sports and fitness events, showcase your work, and sell photos directly to athletes.')
@section('meta-keywords', 'become a sports photographer, sell sports photos, event photography jobs, photographer marketplace, fitness photography, WivorPhotos photographers')

@section('content')
    <section class="cta-section home-3 centred">
        <div class="bg-layer parallax-bg" data-parallax='{"y": 100}'
            style="background-image: url({{ asset('assets/images/gallery/wivor-photographer.png') }});"></div>
        <div class="auto-container">
            <div class="inner-box">
                <h1>Photograph sports with WivorPhotos</h1>
                <p>Apply to cover events and sell the moments you capture.</p>
                <a href="#register_section" class="theme-btn-one"><span>Start your application</span></a>
            </div>
        </div>
    </section>

    @include('partials.cards')

    <section id="register_section" class="contact-section sec-pad">
        <div class="auto-container">
            <div class="sec-title centred mb_55">
                <span class="sub-title calendar">Photographer application</span>
                <h2>Create your account</h2>
                <p>You will verify your email before our team reviews your application.</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-10 form-column">
                    <div class="form-inner">
                        <form method="POST" action="{{ route('registerPhotographer') }}" autocomplete="on">
                            @csrf
                            <div class="row clearfix">
                                <div class="col-md-6 form-group">
                                    <label for="first_name">First name</label>
                                    <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" class="form-control @error('first_name') is-invalid @enderror" autocomplete="given-name" required>
                                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="last_name">Last name</label>
                                    <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" class="form-control @error('last_name') is-invalid @enderror" autocomplete="family-name" required>
                                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="email">Email</label>
                                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" autocomplete="email" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="phone">Phone</label>
                                    <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" autocomplete="tel" required>
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="city">City</label>
                                    <input id="city" type="text" name="city" value="{{ old('city') }}" class="form-control @error('city') is-invalid @enderror" autocomplete="address-level2" required>
                                    @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="state">State abbreviation</label>
                                    <input id="state" type="text" name="state" value="{{ old('state') }}" maxlength="2" class="form-control text-uppercase @error('state') is-invalid @enderror" autocomplete="address-level1" required>
                                    @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 form-group">
                                    <label for="profile_url">Portfolio or Instagram URL</label>
                                    <input id="profile_url" type="url" name="profile_url" value="{{ old('profile_url') }}" class="form-control @error('profile_url') is-invalid @enderror" placeholder="https://" required>
                                    @error('profile_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 form-group">
                                    <label for="camera_model">Camera model or equipment</label>
                                    <input id="camera_model" type="text" name="camera_model" value="{{ old('camera_model') }}" class="form-control @error('camera_model') is-invalid @enderror" required>
                                    @error('camera_model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 form-group">
                                    <label for="about">Short professional introduction</label>
                                    <textarea id="about" name="about" rows="5" class="form-control @error('about') is-invalid @enderror" required>{{ old('about') }}</textarea>
                                    @error('about')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="password">Password</label>
                                    <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" required>
                                    <small class="form-text text-muted">Use at least 8 characters.</small>
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="password_confirmation">Confirm password</label>
                                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                                </div>
                                <div class="col-12 form-group">
                                    <div class="form-check mb-3">
                                        <input id="is_adult" class="form-check-input @error('is_adult') is-invalid @enderror" type="checkbox" name="is_adult" value="1" @checked(old('is_adult')) required>
                                        <label class="form-check-label" for="is_adult">I confirm that I am at least 18 years old.</label>
                                        @error('is_adult')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-check">
                                        <input id="accepts_terms" class="form-check-input @error('accepts_terms') is-invalid @enderror" type="checkbox" name="accepts_terms" value="1" @checked(old('accepts_terms')) required>
                                        <label class="form-check-label" for="accepts_terms">I accept the WivorPhotos photographer terms.</label>
                                        @error('accepts_terms')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 form-group text-center">
                                    <button class="theme-btn-one" type="submit"><span>Submit application</span></button>
                                    <p class="mt-3 mb-0">We do not collect banking details or identity documents in this form.</p>
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
    </section>
@endsection
