<section id="how-wivor-works" class="about-style-two pt_120" aria-labelledby="customer-how-it-works-title">
    <div class="auto-container">
        <div class="row align-items-center clearfix">
            <div class="col-lg-6 col-md-12 col-sm-12 image-column">
                <div class="image-box mr_40">
                    <div class="image-shape"
                        style="background-image: url({{ asset('assets/images/shape/shape-1.png') }});"></div>
                    <figure class="image">
                        <img src="{{ asset('assets/images/gallery/marathon.jpg') }}"
                            alt="{{ __('messages.home_how_image_alt') }}" loading="lazy">
                    </figure>
                </div>
            </div>

            <div class="col-lg-6 col-md-12 col-sm-12 content-column">
                <div class="content_block_two">
                    <div class="content-box ml_40">
                        <div class="sec-title mb_40">
                            <span class="sub-title">@lang('messages.home_how_eyebrow')</span>
                            <h2 id="customer-how-it-works-title">@lang('messages.home_how_title')</h2>
                        </div>

                        <ol class="list-unstyled mb_40">
                            <li class="d-flex gap-3 mb-4">
                                <span class="badge rounded-pill bg-dark align-self-start mt-1">1</span>
                                <div>
                                    <h3 class="h5 mb-2">@lang('messages.home_how_step_1_title')</h3>
                                    <p class="mb-0">@lang('messages.home_how_step_1_text')</p>
                                </div>
                            </li>
                            <li class="d-flex gap-3 mb-4">
                                <span class="badge rounded-pill bg-dark align-self-start mt-1">2</span>
                                <div>
                                    <h3 class="h5 mb-2">@lang('messages.home_how_step_2_title')</h3>
                                    <p class="mb-0">@lang('messages.home_how_step_2_text')</p>
                                </div>
                            </li>
                            <li class="d-flex gap-3 mb-4">
                                <span class="badge rounded-pill bg-dark align-self-start mt-1">3</span>
                                <div>
                                    <h3 class="h5 mb-2">@lang('messages.home_how_step_3_title')</h3>
                                    <p class="mb-0">@lang('messages.home_how_step_3_text')</p>
                                </div>
                            </li>
                            <li class="d-flex gap-3">
                                <span class="badge rounded-pill bg-dark align-self-start mt-1">4</span>
                                <div>
                                    <h3 class="h5 mb-2">@lang('messages.home_how_step_4_title')</h3>
                                    <p class="mb-0">@lang('messages.home_how_step_4_text')</p>
                                </div>
                            </li>
                        </ol>

                        <div class="btn-box">
                            <a href="{{ route('events.listEvents') }}" class="theme-btn-one">@lang('messages.home_how_cta')</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 pt_80">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <i class="bi bi-search fs-2 text-primary" aria-hidden="true"></i>
                        <h3 class="h5 mt-3">@lang('messages.home_how_search_title')</h3>
                        <p class="mb-0">@lang('messages.home_how_search_text')</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <i class="bi bi-cart-check fs-2 text-primary" aria-hidden="true"></i>
                        <h3 class="h5 mt-3">@lang('messages.home_how_review_title')</h3>
                        <p class="mb-0">@lang('messages.home_how_review_text')</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <i class="bi bi-shield-check fs-2 text-primary" aria-hidden="true"></i>
                        <h3 class="h5 mt-3">@lang('messages.home_how_delivery_title')</h3>
                        <p class="mb-0">@lang('messages.home_how_delivery_text')</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
