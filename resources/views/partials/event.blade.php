<section class="event-section bg-color-1" style="background-color:#f5f1fb">
    <div class="auto-container">
        <div class="sec-title mb_55 centred">
            <span class="sub-title">@lang('messages.our_blog')</span>
        </div>
        <div class="single-item-carousel owl-carousel owl-theme owl-dots-none nav-style-one">
            @foreach($events as $event)
                <div class="events-block-one">
                    <div class="inner-box">
                        <figure class="image-box"><img src="{{ $event->image_url }}" alt="{{ $event->title_en }}">
                        </figure>
                        <div class="content-box">
                            <h3>
                                @if(App::getLocale() == 'es')
                                    {{ $event->title_es }}
                                @elseif(App::getLocale() == 'pt')
                                    {{ $event->title_pt }}
                                @else
                                    {{ $event->title_en }}
                                @endif
                            </h3>
                            <p class="text-muted"><i>
                                    {{-- Display summary based on the current locale and limit to 90 characters --}}
                                    @if(App::getLocale() == 'es')
                                        {{ Str::limit($event->summary_es, 120, '...') }}
                                    @elseif(App::getLocale() == 'pt')
                                        {{ Str::limit($event->summary_pt, 120, '...') }}
                                    @else
                                        {{ Str::limit($event->summary_en, 120, '...') }} {{-- Default to English if no locale
                                        match --}}
                                    @endif
                                </i></>
                            <div class="btn-box">
                                <a href="{{ route('posts.show', $event->slug) }}" class="btn btn-primary"
                                    style="background: linear-gradient(to right,#4a235a, #a569bd, #e8daef); border-color: #4a235a; color: #fff;">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> @lang('messages.read_more')
                                </a>


                            </div>
                            <ul class="lower-box">
                                <li class="comment"><i class="icon-38"></i>{{$event->date}}</li>
                                <li class="admin"><i class="bi bi-box-arrow-in-right me-2"></i> @lang('messages.read_more')
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
