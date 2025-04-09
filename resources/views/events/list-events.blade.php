@extends('layouts.app')

@section('title', '#somosAETH | Events')

@section('meta-description', 'This is a brief description of the blog page.')

@section('meta-keywords', 'blog, posts, news')

@section('content')
    <div class="container my-5">
        <div class="sec-title mb_55 centred">
            <a href="#"> <span class="sub-title"><b>WiVor @lang('messages.events')</b></span>
            </a>
        </div>

        <div class="row g-4">
            @foreach($events as $event)
                <div class="col-md-6 col-lg-4" style="margin-bottom:48px;">
                    <div class="card h-100 shadow-sm border-0">
                        <a href="{{ route('events.show', $event->slug) }}">
                            <img src="{{ $event->image_url }}"
                                srcset="{{ $event->image_url }} 1x, {{ $event->image_url_2x ?? $event->image_url }} 2x"
                                class="card-img-top" alt="{{ $event->title_en }}"
                                style="height: 400px; object-fit: cover;object-position: left top;">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                              
                                    {{ $event->title }}
                             
                            </h5>
                            <p class="card-text text-muted small flex-grow-1">
                                <i>{{ $event->summary }}</i>
                            </p>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <a href="{{ route('events.show', $event->slug) }}" class="btn btn-sm btn-gradient">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> @lang('messages.read_more')
                                </a>
                                <small class="text-muted">{{ $event->date_of_publication }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ✅ Custom Pagination --}}
        <div class="pagination-wrapper centred mt-5">
            <ul class="pagination clearfix">
                @if ($events->onFirstPage())
                    <li><a href="#" aria-disabled="true"><i class="icon-56"></i></a></li>
                @else
                    <li><a href="{{ $events->previousPageUrl() }}"><i class="icon-56"></i></a></li>
                @endif

                @foreach ($events->getUrlRange(1, $events->lastPage()) as $page => $url)
                    <li>
                        <a href="{{ $url }}" class="{{ $events->currentPage() === $page ? 'current' : '' }}">
                            {{ $page }}
                        </a>
                    </li>
                @endforeach

                @if ($events->hasMorePages())
                    <li><a href="{{ $events->nextPageUrl() }}"><i class="icon-55"></i></a></li>
                @else
                    <li><a href="#" aria-disabled="true"><i class="icon-55"></i></a></li>
                @endif
            </ul>
        </div>
    </div>

   
@endsection