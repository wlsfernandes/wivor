@extends('layouts.app')

@section('title')
    {{
        App::getLocale() === 'es' ? $post->title_es :
        (App::getLocale() === 'pt' ? $post->title_pt : $post->title_en)
    }}
@endsection

@section('meta-description')
    {{
        App::getLocale() === 'es' ? $post->summary_es :
        (App::getLocale() === 'pt' ? $post->summary_pt : $post->summary_en)
    }}
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
                            @switch(App::getLocale())
                                @case('es')
                                    {{ $post->title_es ?? '' }}
                                    @break
                                @case('pt')
                                    {{ $post->title_pt ?? '' }}
                                    @break
                                @default
                                    {{ $post->title_en ?? '' }}
                            @endswitch
                        </h1>
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-end pe-3">
                            <small class="text-muted">{{ $post->date }}</small>
                        </div>
                    </div>
                </div>

                {{-- Image --}}
                <img class="card-img-top" style="max-height:600px"
                     src="{{ $post->image_url }}"
                     alt="{{ $post->title_en }}">

                {{-- Content --}}
       
                <div class="card-body">
                <h3 style="color:#4a235a"><i>
                        @switch(App::getLocale())
                            @case('es')
                                {{ $post->content_es ?? '' }}
                                @break
                            @case('pt')
                                {{ $post->content_pt ?? '' }}
                                @break
                            @default
                                {{ $post->content_en ?? '' }}
                        @endswitch
                        </i></h3>

                    <p class="card-text flex-grow-1 text-muted" style="margin-top:24px;">
    <small style="color: #6c757d;">
        @switch(App::getLocale())
            @case('es')
                {!! $post->summary_es ?? '' !!}
                @break
            @case('pt')
                {!! $post->summary_pt ?? '' !!}
                @break
            @default
                {!! $post->summary_en ?? '' !!}
        @endswitch
    </small>
</p>


                    {{-- External Link Button --}}
                    @if($post->external_link)
                        <div class="d-flex justify-content-center mt-5">
                            <a href="{{ $post->external_link }}" target="_blank" class="btn btn-danger btn-md">
                                {{ $post->external_link_button ?? 'Click here to access' }}
                            </a>
                        </div>
                    @endif

                    {{-- Download Buttons --}}
                    @if($post->file_url)
                        <div class="d-flex justify-content-center mt-4">
                            <a href="{{ $post->file_url }}" target="_blank" class="btn btn-sm btn-gradient">
                                {{ $post->button_text_en ?? 'Download English here' }}
                            </a>
                        </div>
                    @endif

                    @if($post->file_url_es)
                        <div class="d-flex justify-content-center mt-4">
                            <a href="{{ $post->file_url_es }}" target="_blank" class="btn btn-sm btn-gradient">
                                {{ $post->button_text_es ?? 'Descargar Español aquí' }}
                            </a>
                        </div>
                    @endif

                </div> <!-- end card-body -->
            </div> <!-- end card -->
        </div>
    </div>
</div>
@endsection
