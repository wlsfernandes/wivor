@extends('layouts.app')

@section('title', 'Wivor | Home')

@section('meta-description', 'Discover comprehensive Hispanic theological education and Latino ministry training programs. Explore Bible institute certifications, leadership development, and theological resources for Hispanic pastors and church leaders. Empower your ministry with tailored courses and Spanish-language resources.')

@section('meta-keywords', 'somosWivor')
@section('content')
    <div class="container my-5">
        <div class="sec-title mb_55 centred">
            <a href="{{ route('post') }}"> <span class="sub-title"><b>@lang('messages.our_blog')</b></span>
                <h4 style="color:#4a235a ">@lang('messages.articles_news')</h4>
            </a>
        </div>

        <div class="row g-4">
            @foreach($posts as $post)
                <div class="col-md-6 col-lg-4" style="margin-bottom:48px;">
                    <div class="card h-100 shadow-sm border-0">
                        <a href="{{ route('post.show', $post->slug) }}">
                            <img src="{{ $post->image_url }}"
                                srcset="{{ $post->image_url }} 1x, {{ $post->image_url_2x ?? $post->image_url }} 2x"
                                class="card-img-top" alt="{{ $post->title_en }}"
                                style="height: 400px; object-fit: cover;object-position: left top;">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                @if(App::getLocale() == 'es')
                                    {{ $post->title_es }}
                                @elseif(App::getLocale() == 'pt')
                                    {{ $post->title_pt }}
                                @else
                                    {{ $post->title_en }}
                                @endif
                            </h5>
                            <p class="card-text text-muted small flex-grow-1">
                                <i>{{ $post->plain_summary }}</i>
                            </p>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <a href="{{ route('post.show', $post->slug) }}" class="btn btn-sm btn-gradient">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> @lang('messages.read_more')
                                </a>
                                <small class="text-muted">{{ $post->date }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ✅ Custom Pagination --}}
        <div class="pagination-wrapper centred mt-5">
            <ul class="pagination clearfix">
                @if ($posts->onFirstPage())
                    <li><a href="#" aria-disabled="true"><i class="icon-56"></i></a></li>
                @else
                    <li><a href="{{ $posts->previousPageUrl() }}"><i class="icon-56"></i></a></li>
                @endif

                @foreach ($posts->getUrlRange(1, $posts->lastPage()) as $page => $url)
                    <li>
                        <a href="{{ $url }}" class="{{ $posts->currentPage() === $page ? 'current' : '' }}">
                            {{ $page }}
                        </a>
                    </li>
                @endforeach

                @if ($posts->hasMorePages())
                    <li><a href="{{ $posts->nextPageUrl() }}"><i class="icon-55"></i></a></li>
                @else
                    <li><a href="#" aria-disabled="true"><i class="icon-55"></i></a></li>
                @endif
            </ul>
        </div>
    </div>

    {{-- CTA --}}
    <section class="cta-style-two mt-5">
        <div class="pattern-layer" style="background-image: url(assets/images/shape/shape-2.png);"></div>
        <div class="auto-container">
            <div class="inner-box">
                <h2>
                    <i class="bi bi-book" style="font-size: 1.5rem; color: #fff;"></i>
                    @lang('messages.resources_grow'):
                </h2>
            </div>
        </div>
    </section>
@endsection