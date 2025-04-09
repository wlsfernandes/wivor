@extends('layouts.app-sidebar')

@section('title', 'Wivor | Home')

@section('meta-description', 'Discover comprehensive Hispanic theological education and Latino ministry training programs...')
@section('meta-keywords', 'somosWivor')

@section('content')
<div class="container-fluid px-3">
    <div class="row g-3">
        @foreach($events as $event)
            <div class="col-12 col-sm-6 col-md-4 col-xl-3 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <a href="{{ route('event.show', $event->slug) }}">
                        <img src="{{ $event->image_url }}"
                             srcset="{{ $event->image_url }} 1x, {{ $event->image_url_2x ?? $event->image_url }} 2x"
                             class="card-img-top"
                             alt="{{ $event->title_en }}"
                             style="height: 220px; object-fit: cover;">
                    </a>
                    <div class="card-body d-flex flex-column p-3">
                        <h6 class="card-title mb-2" style="font-size: 1rem;">
                            @switch(App::getLocale())
                                @case('es') {{ $event->title_es }} @break
                                @case('pt') {{ $event->title_pt }} @break
                                @default {{ $event->title_en }}
                            @endswitch
                        </h6>
                        <p class="card-text text-muted small flex-grow-1 mb-2" style="font-size: 0.875rem;">
                            <i>{{ $event->plain_summary }}</i>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('post.show', $event->slug) }}" class="btn btn-sm btn-gradient">
                                <i class="bi bi-box-arrow-in-right me-1"></i> @lang('messages.read_more')
                            </a>
                            <small class="text-muted">{{ $event->date }}</small>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination (unchanged) --}}
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
