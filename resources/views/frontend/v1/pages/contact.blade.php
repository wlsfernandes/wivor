@extends('frontend.v1.layouts.app')

@section('title', 'Contact WivorPhotos')
@section('meta-description', 'Contact the WivorPhotos team for customer and photographer support.')

@section('content')
    <main>
        <section class="container pt-5 text-center">
            <h1>Contact WivorPhotos</h1>
            <p class="mb-0">Email us at <a href="mailto:{{ config('contact.email') }}">{{ config('contact.email') }}</a> or use the form below.</p>
        </section>

        @include('frontend.v1.components.contact')
    </main>
@endsection
