@extends('frontend.v1.layouts.app')

@section('title', 'Biometric Notice | WiVor')
@section('meta-description', 'How WiVor uses face matching to help athletes find their event photos.')

@section('content')
    <main class="container py-5">
        <div class="row justify-content-center">
            <article class="col-lg-8">
                <h1 class="mb-3">Biometric Notice</h1>
                <p class="text-muted fw-semibold">Last updated: October 18, 2026</p>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body p-4 p-md-5">
                        <p>This notice explains how face matching is used on WiVor. It adds to our <a href="{{ route('privacy') }}">Privacy Policy</a>.</p>

                        <h2 class="h4 mt-4">Face matching in event photographs</h2>
                        <p>When face search is enabled, published event photographs may be processed by Amazon Rekognition. Facial features found in those photographs may be represented as face metadata in an event-specific Rekognition collection and associated with the photograph's WivorPhotos identifier. This is separate from the voluntary selfie-search flow.</p>

                        <h2 class="h4 mt-4">Selfie search</h2>
                        <p>A visitor who chooses selfie search uploads a JPEG or PNG image with consent. WivorPhotos reads that temporary request upload into memory and sends the image bytes to Amazon Rekognition to search the selected event collection. WivorPhotos does not intentionally save the search selfie to Amazon S3, its database, the visitor's session, or a queue payload. The temporary request ends after the search response. Amazon processes the image as WivorPhotos' service provider under Amazon's applicable service terms.</p>

                        <h2 class="h4 mt-4">Deletion</h2>
                        <p>When a photograph is unpublished, removed, or expires through the current retention process, WivorPhotos queues deletion of its associated face metadata from the event collection. Operational retries or provider availability can affect when that cleanup completes.</p>

                        <h2 class="h4 mt-4">Your choices</h2>
                        <p class="mb-0">Selfie search is optional; visitors can browse galleries or use bib-number search instead. A person shown in a photograph can use the <a href="{{ route('photo-removal.create') }}">Photo Removal Request</a> process. For privacy questions or requests, <a href="{{ route('contact_us') }}">contact WivorPhotos</a>.</p>
                    </div>
                </div>
            </article>
        </div>
    </main>
@endsection
