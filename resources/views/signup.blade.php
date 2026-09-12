@extends('layouts.app')

@section('title', 'Guest Checkout | WivorPhotos')

@section('meta-description', 'No customer account is required to browse and purchase event photos from WivorPhotos.')
@section('meta-keywords', 'WivorPhotos guest checkout, sports event photos, athlete photos, event photography marketplace')

@section('content')
    <section class="contact-section sec-pad" style="margin-bottom:150px;" aria-labelledby="guest-checkout-title">
        <div class="auto-container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-lg-5 text-center">
                            <span class="sub-title calendar">Customer purchases</span>
                            <h1 id="guest-checkout-title" class="h2 mt-3">No customer account is required</h1>
                            <p class="lead text-muted mt-3">
                                Browse published event galleries, choose your photos, and complete your purchase as a guest.
                                Your confirmation page and email receipt provide protected access to purchased originals.
                            </p>
                            <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
                                <a class="theme-btn-one" href="{{ route('events.listEvents') }}">Browse Event Photos</a>
                                <a class="btn btn-outline-primary" href="{{ route('photographers') }}#register_section">
                                    Apply as a Photographer
                                </a>
                            </div>
                            <p class="small text-muted mt-4 mb-0">
                                Already an approved photographer? <a href="{{ route('login') }}">Sign in here</a>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
