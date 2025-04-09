<section class="py-5" style="background-color:#f5f1fb">
    <div class="container">
        <div class="sec-title mb_55 centred">
            <a href="#"> <span class="sub-title"><b>@lang('messages.events')</b></span>
            </a>
        </div>
        <div class="row g-4">
            @foreach($events->take(3) as $event)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0 hover-shadow transition">
                        <a href="{{ route('events.show', $event->slug) }}">
                            <img src="{{ $event->image_url }}" class="card-img-top" alt="{{ $event->title_en }}">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                    {{ $event->title}}
                            </h5>
                            <p class="card-text text-muted small flex-grow-1">
                                <i>{{ $event->summary }}</i>
                            </p>

                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <a href="{{ route('events.show', $event->slug) }}" class="btn btn-sm btn-gradient">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> @lang('messages.read_more')
                                </a>
                                <small class="text-muted">{{ $event->date_of_publication ?? '' }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>