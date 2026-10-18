@extends('frontend.v1.layouts.app')

@php($isFaceSearchEnabled = (bool) config('face_recognition.enabled'))

@section('title', 'FAQ | WiVor')
@section('meta-description', 'Answers about finding, previewing and buying your event photos on WiVor, and how photographers and event organizers work with WiVor.')

@section('content')
    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <h1 class="wv-h1">Frequently Asked Questions</h1>

            <div class="wv-faq">
                <details>
                    <summary>Is WiVor really free?</summary>
                    <div class="wv-body">Yes. It's free for photographers, event organizers and athletes. Athletes only pay if they like their photos and choose to buy them.</div>
                </details>

                <details>
                    <summary>How does WiVor find my photos?</summary>
                    @if ($isFaceSearchEnabled)
                        <div class="wv-body">We use bib number recognition and face recognition. Enter your bib number, upload a selfie, or both.</div>
                    @else
                        <div class="wv-body">We use bib number recognition. Enter your bib number.</div>
                    @endif
                </details>

                <details>
                    <summary>Do I have to buy photos?</summary>
                    <div class="wv-body">No. You can browse your photos for free and buy only the ones you want.</div>
                </details>

                <details>
                    <summary>How do I receive my photos?</summary>
                    <div class="wv-body">After purchase, you can download high-resolution files right away.</div>
                </details>

                <details>
                    <summary>What if I can't find my photos?</summary>
                    <div class="wv-body">Photographers may still be uploading. Check back in a day or two, or contact us at <a class="wv-link" href="mailto:{{ config('contact.email') }}">{{ config('contact.email') }}</a>.</div>
                </details>

                @if ($isFaceSearchEnabled)
                    <details>
                        <summary>How is my face data used and protected?</summary>
                        <div class="wv-body">Your selfie is used only to match you with your event photos. Read our <a class="wv-link" href="{{ route('biometric-notice') }}">Biometric Notice</a>.</div>
                    </details>
                @endif

                <details>
                    <summary>How do photographers get paid?</summary>
                    <div class="wv-body">WiVor handles payments. When an athlete buys a photo, the photographer receives their share.</div>
                </details>

                <details>
                    <summary>How can my event partner with WiVor?</summary>
                    <div class="wv-body">Fill out the form on our <a class="wv-link" href="{{ url('/organizers') }}">Organizers page</a> and we'll get in touch.</div>
                </details>
            </div>
        </div>
    </section>
@endsection
