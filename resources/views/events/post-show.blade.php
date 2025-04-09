@extends('layouts.app')

@section('title')
    {{ $event->title }}
@endsection

@section('meta-description')
    {{ $event->summary ?? '' }}
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-12 box-shadow">

                    {{-- Title & Date --}}
                    <div class="row">
                        <div class="col-12 text-center">
                            <h1 style="color:#4a235a">
                                {{ $event->title }}
                            </h1>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-end pe-3">
                                <small class="text-muted">{{ $event->date_of_publication }}</small>
                            </div>
                        </div>
                    </div>

                    {{-- Image --}}
                    <img class="card-img-top" style="max-height:600px" src="{{ $event->image_url }}"
                        alt="{{ $event->title }}">

                    {{-- Content --}}

                    <div class="card-body">
                        <h3 style="color:#4a235a"><i>
                                {{ $event->content ?? '' }}
                            </i></h3>

                        <p class="card-text flex-grow-1 text-muted" style="margin-top:24px;">
                            <small style="color: #6c757d;">
                                {{ $event->summary }}
                            </small>
                        </p>


                        {{-- External Link Button --}}
                        @if($event->external_link)
                            <div class="d-flex justify-content-center mt-5">
                                <a href="{{ $event->external_link }}" target="_blank" class="btn btn-danger btn-md">
                                    {{ $event->external_link_button ?? 'Click here to access' }}
                                </a>
                            </div>
                        @endif

                        {{-- Download Buttons --}}
                        @if($event->file_url)
                            <div class="d-flex justify-content-center mt-4">
                                <a href="{{ $event->file_url }}" target="_blank" class="btn btn-sm btn-gradient">
                                    {{ $event->button_text_en ?? 'Download English here' }}
                                </a>
                            </div>
                        @endif

                        @if($event->file_url_es)
                            <div class="d-flex justify-content-center mt-4">
                                <a href="{{ $event->file_url_es }}" target="_blank" class="btn btn-sm btn-gradient">
                                    {{ $event->button_text_es ?? 'Descargar Español aquí' }}
                                </a>
                            </div>
                        @endif

                    </div> <!-- end card-body -->
                </div> <!-- end card -->
            </div>
        </div>
    </div>
@endsection