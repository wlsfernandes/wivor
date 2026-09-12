@extends('layouts.app')

@section('title', 'Terms of Use | WivorPhotos')
@section('meta-description', 'Terms for browsing WivorPhotos event galleries and purchasing digital event photographs.')

@section('content')
    <main class="container py-5">
        <div class="row justify-content-center">
            <article class="col-lg-8">
                <h1 class="mb-3">Terms of Use</h1>
                <p class="text-muted fw-semibold">Last updated: September 12, 2026</p>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body p-4 p-md-5">
                        <p>These Terms govern customer and visitor use of the WivorPhotos event-photography marketplace. Photographer participation is governed separately by the <a href="{{ route('photographer-terms') }}">Photographer Terms</a>.</p>

                        <h2 class="h4 mt-4">Event galleries and guest access</h2>
                        <p>Visitors can browse published event galleries and purchase available photographs as guests; WivorPhotos does not currently provide customer order-history accounts. Gallery previews may be watermarked. Events, photographs, prices, and sale windows may change or become unavailable before checkout.</p>

                        <h2 class="h4 mt-4">Search tools</h2>
                        <p>Bib-number and optional selfie search are assistive tools. Recognition results may be incomplete, inaccurate, or unavailable and are not a guarantee that every photograph of a person will be found. Visitors should confirm each selected photograph before purchasing. Selfie search requires a separate consent and is described in the <a href="{{ route('privacy') }}">Privacy Policy</a>.</p>

                        <h2 class="h4 mt-4">Purchases and payment</h2>
                        <p>The cart displays the event's per-photo price, selected-photo count, and subtotal. Checkout is hosted and payment is processed by Stripe. An order is complete only after payment is confirmed. WivorPhotos currently sells digital event photographs only; it does not offer physical prints, video products, or subscriptions through this checkout.</p>

                        <h2 class="h4 mt-4">Digital delivery and permitted use</h2>
                        <p>A completed order provides access to the purchased high-resolution JPEG files through a protected, token-gated order page. The receipt and order page identify the download-access period. Access is not permanent, so customers should download and back up purchased files before it expires.</p>
                        <p>A purchase provides the described download access but does not transfer the photographer's ownership of the photograph. The current checkout does not offer selectable commercial licenses or resale rights. Customers should <a href="{{ route('contact_us') }}">contact WivorPhotos</a> before relying on a purchase for commercial licensing.</p>

                        <h2 class="h4 mt-4">Refunds and disputes</h2>
                        <p>Refund requests are reviewed through support and are not handled by an automated in-app refund tool. Eligibility and processing are described in the <a href="{{ route('refund-policy') }}">Refund Policy</a>. A full refund or payment dispute may revoke future download access.</p>

                        <h2 class="h4 mt-4">Availability and removal</h2>
                        <p>WivorPhotos may close galleries, unpublish or remove photographs, enforce retention schedules, address rights or safety concerns, and suspend access needed to protect the service. Anyone seeking review of a photograph can use the <a href="{{ route('photo-removal.create') }}">Photo Removal Request</a> process. Submitting a request begins review and does not itself guarantee a particular outcome.</p>

                        <h2 class="h4 mt-4">Acceptable use</h2>
                        <p>Visitors must not misuse download links, interfere with the service, attempt unauthorized access, upload unlawful material, or use gallery or search features to harass, identify, or harm another person.</p>

                        <h2 class="h4 mt-4">Service limitations</h2>
                        <p>Internet, storage, payment, email, and recognition services can experience delays or failures. WivorPhotos does not promise uninterrupted availability or perfect search results. Nothing in these Terms limits rights that cannot lawfully be limited.</p>

                        <h2 class="h4 mt-4">Contact and updates</h2>
                        <p>Questions about an order or these Terms can be sent through the <a href="{{ route('contact_us') }}">contact page</a>. WivorPhotos may update these Terms as the service changes; the date above identifies the current published version.</p>
                    </div>
                </div>
            </article>
        </div>
    </main>
@endsection
