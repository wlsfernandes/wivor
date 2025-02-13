<section class="event-section bg-color-1" style="background-color:#f5f1fb">
    <div class="auto-container">
        <div class="sec-title mb_55 centred">
            <span class="sub-title">@lang('messages.our_blog')</span>
            <h2 style="color:#4a235a ">@lang('messages.articles_news')</h2>
        </div>
        <div class="single-item-carousel owl-carousel owl-theme owl-dots-none nav-style-one">
            @foreach($articles as $article)
                <div class="events-block-one">
                    <div class="inner-box">
                        <figure class="image-box"><img src="{{ $article->image_url }}" alt="{{ $article->title_en }}"></figure>
                        <div class="content-box">
                            <h3>
                                @if(App::getLocale() == 'es')
                                    {{ $article->title_es }}
                                @elseif(App::getLocale() == 'pt')
                                    {{ $article->title_pt }}
                                @else
                                    {{ $article->title_en }}
                                @endif
                            </h3>
                            <p class="text-muted"><i>
                                    {{-- Display summary based on the current locale and limit to 90 characters --}}
                                    @if(App::getLocale() == 'es')
                                        {{ Str::limit($article->summary_es, 120, '...') }}
                                    @elseif(App::getLocale() == 'pt')
                                        {{ Str::limit($article->summary_pt, 120, '...') }}
                                    @else
                                        {{ Str::limit($article->summary_en, 120, '...') }} {{-- Default to English if no locale
                                        match --}}
                                    @endif
                                </i></>
                            <div class="btn-box">
                                <a href="{{ route('post.show', $article->slug) }}" class="btn btn-primary"
                                    style="background: linear-gradient(to right,#4a235a, #a569bd, #e8daef); border-color: #4a235a; color: #fff;">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> @lang('messages.read_more')
                                </a>


                            </div>
                            <ul class="lower-box">
                                <li class="comment"><i class="icon-38"></i>{{$article->date}}</li>
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