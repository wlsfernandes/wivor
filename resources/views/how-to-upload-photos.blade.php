@extends('layouts.app')

@section('title', 'How to Upload Photos | WivorPhotos')
@section('meta-description', 'A simple guide for approved WivorPhotos photographers to find an event and upload JPEG photos.')

@section('content')
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <header class="text-center mb-5">
                    <h1>How to Upload Photos</h1>
                    <p class="lead text-muted mb-0">Upload your event photos in a few simple steps.</p>
                </header>

                <ol class="list-group list-group-numbered shadow-sm mb-4">
                    <li class="list-group-item p-4">
                        <h2 class="h5">Go to Login</h2>
                        <p class="mb-2">Sign in with your approved photographer account.</p>
                        <a href="{{ route('login') }}">Open Login</a>
                    </li>
                    <li class="list-group-item p-4">
                        <h2 class="h5">Search your events</h2>
                        <p class="mb-2">Open My Events and search by event name, city, or sport.</p>
                        <a href="{{ route('events.index') }}">Search Events</a>
                    </li>
                    <li class="list-group-item p-4">
                        <h2 class="h5">Open the uploader</h2>
                        <p class="mb-0">Find the event you are assigned to and click <strong>Upload photos</strong>.</p>
                    </li>
                    <li class="list-group-item p-4">
                        <h2 class="h5">Review the photo recommendations</h2>
                        <ul class="mb-0">
                            <li>Upload JPG or JPEG files only.</li>
                            <li>Each photo must be 40 MB or smaller.</li>
                            <li>The longest side must be at least 2,400 pixels, and neither side may exceed 12,000 pixels.</li>
                            <li>Use RGB/sRGB; CMYK is not accepted.</li>
                            <li>Do not add your own watermark, logo, or border.</li>
                            <li>Upload only photos you have the right to sell, and keep your own backup.</li>
                        </ul>
                    </li>
                    <li class="list-group-item p-4">
                        <h2 class="h5">Drop JPEGs and add photos</h2>
                        <p class="mb-0">Confirm the rights-and-backup notice, drop your JPEG photos into <strong>Drop JPEG photos here</strong>, then click <strong>Add Photos</strong>.</p>
                    </li>
                </ol>

                <div class="alert alert-info mb-0" role="note">
                    After processing finishes, review the results and click <strong>Publish Ready Photos</strong> when you are ready to make them available in the event gallery.
                </div>
            </div>
        </div>
    </main>
@endsection
