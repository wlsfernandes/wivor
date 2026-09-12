@extends($layout)

@section('title', 'How to Upload Photos | WivorPhotos')
@section('workspace-title', 'How to Upload Photos')
@section('meta-description', 'A simple guide for approved WivorPhotos photographers to find an event and upload JPEG photos.')

@section('content')
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <header class="text-center mb-5">
                    <h1>How to Upload Photos</h1>
                    <p class="lead text-muted mb-0">Follow these six steps to add photos to an event gallery.</p>
                </header>

                <ol class="list-group list-group-numbered shadow-sm">
                    <li class="list-group-item p-4">
                        <h2 class="h5">Go to Login</h2>
                        <p>Click <strong>Login</strong> and sign in with your approved photographer account.</p>
                        <a href="{{ route('login') }}">Open Login</a>
                        <img class="img-fluid rounded border mt-3" src="{{ asset('assets/images/manual/1.png') }}"
                            alt="WivorPhotos home page showing the Login button" loading="lazy">
                    </li>

                    <li class="list-group-item p-4">
                        <h2 class="h5">Search your events</h2>
                        <p>Open <strong>Upload Photos</strong>, then search by event name, city, or sport. Find your assigned event and click <strong>Upload photos</strong>.</p>
                        <a href="{{ route('events.index') }}">Search Events</a>
                        <img class="img-fluid rounded border mt-3" src="{{ asset('assets/images/manual/2.png') }}"
                            alt="My Events page showing event search and the Upload photos button" loading="lazy">
                    </li>

                    <li class="list-group-item p-4">
                        <h2 class="h5">Review the photo recommendations</h2>
                        <p>Read the requirements and confirm that you own or control the photo rights and have kept your own backup.</p>
                        <ul>
                            <li>JPG or JPEG only, up to 40 MB per photo.</li>
                            <li>The longest side must be at least 2,400 pixels; neither side may exceed 12,000 pixels.</li>
                            <li>Use RGB/sRGB, with no photographer watermark, logo, or border.</li>
                        </ul>
                        <img class="img-fluid rounded border mt-3" src="{{ asset('assets/images/manual/3.png') }}"
                            alt="Photo requirements and the Drop JPEG photos here area" loading="lazy">
                    </li>

                    <li class="list-group-item p-4">
                        <h2 class="h5">Drop JPEGs and click Add Photos</h2>
                        <p>Drop your JPEG files into <strong>Drop JPEG photos here</strong>, or click the area to choose files. Check the queued files, then click <strong>Add Photos</strong>.</p>
                        <img class="img-fluid rounded border mt-3" src="{{ asset('assets/images/manual/4.png') }}"
                            alt="A queued JPEG beside the Add Photos button" loading="lazy">
                    </li>

                    <li class="list-group-item p-4">
                        <h2 class="h5">Review the processed photos</h2>
                        <p>Wait for processing to finish. Review any rejection messages, then choose each ready photo or use <strong>Select all ready</strong>.</p>
                        <img class="img-fluid rounded border mt-3" src="{{ asset('assets/images/manual/5.png') }}"
                            alt="Review photos area with Select all ready checked" loading="lazy">
                    </li>

                    <li class="list-group-item p-4">
                        <h2 class="h5">Publish ready photos</h2>
                        <p class="mb-0">Click <strong>Publish Ready Photos</strong> to make the selected photos available in the event gallery.</p>
                        <img class="img-fluid rounded border mt-3" src="{{ asset('assets/images/manual/6.png') }}"
                            alt="Publish Ready Photos button" loading="lazy">
                    </li>
                </ol>
            </div>
        </div>
    </main>
@endsection
