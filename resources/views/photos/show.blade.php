@extends('layouts.app')

@section('title', $seoTitle)
@section('meta-description', $seoDescription)
@section('meta-keywords',
    e(
    collect([$photo->display_title, $event->title, $event->sport ? $event->sport . ' photography' : null, $event->city ?
    $event->city . ' sports photos' : null, $photographerName, 'sports event photo',
    'WivorPhotos'])->merge($people)->filter()->unique()->implode(', '),
    ))
@section('author', $photographerName)
@section('canonical', $canonicalUrl)
@section('og-type', 'article')
@section('og-image', $imageUrl)
@section('og-image-type', 'image/jpeg')
@section('og-image-width', $photo->preview_width)
@section('og-image-height', $photo->preview_height)
@section('og-image-alt', $photo->display_alt_text)

@section('structured-data')
    <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection

@section('content')
    <main class="container py-5">
        <article class="row justify-content-center">
            <div class="col-xl-10">
                <nav aria-label="Breadcrumb" class="mb-3">
                    <a href="{{ route('events.show', ['event' => $event->slug]) }}">{{ $event->title }}</a>
                    <span aria-hidden="true"> / </span>
                    <span>Photo</span>
                </nav>

                <figure class="mb-4">
                    <img src="{{ $imageUrl }}" alt="{{ $photo->display_alt_text }}" class="img-fluid rounded w-100"
                        @if ($photo->preview_width) width="{{ $photo->preview_width }}" @endif
                        @if ($photo->preview_height) height="{{ $photo->preview_height }}" @endif fetchpriority="high">
                    @if ($photo->caption)
                        <figcaption class="text-muted mt-2">{{ $photo->caption }}</figcaption>
                    @endif
                </figure>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <h1 class="h2">{{ $photo->display_title }}</h1>
                        <p class="lead text-muted">{{ $event->date_label }} · {{ $event->location_label }}</p>

                        @if ($people->isNotEmpty())
                            <section aria-labelledby="people-in-photo">
                                <h2 id="people-in-photo" class="h5">People in this photo</h2>
                                <p>{{ $people->join(', ', ' and ') }}</p>
                            </section>
                        @endif
                    </div>

                    <aside class="col-lg-4">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h2 class="h5">Photo credit</h2>
                                <p class="mb-2"><strong>Photographer:</strong> {{ $photographerName }}</p>
                                @if ($copyrightNotice)
                                    <p class="small text-muted mb-3">{{ $copyrightNotice }}</p>
                                @endif
                                @if ($licenseUrl)
                                    <p class="mb-2"><a href="{{ $licenseUrl }}">Image license terms</a></p>
                                @endif
                                <a href="{{ route('contact_us') }}">Contact WivorPhotos about licensing</a>
                                <p class="small text-muted mt-3 mb-1">Reference: {{ $photo->reference_number }}</p>
                                <a class="small"
                                    href="{{ route('photos.removal-requests.create', ['event' => $event->slug, 'photo' => $photo]) }}">Report
                                    or Request Removal</a>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </article>
    </main>
@endsection
