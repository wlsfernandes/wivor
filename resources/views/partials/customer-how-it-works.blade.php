<section id="how-wivor-works" class="about-style-two pt_120" aria-labelledby="customer-how-it-works-title">
    <div class="auto-container">
        <div class="row align-items-center clearfix">
            <div class="col-lg-6 col-md-12 col-sm-12 image-column">
                <div class="image-box mr_40">
                    <div class="image-shape"
                        style="background-image: url({{ asset('assets/images/shape/shape-1.png') }});"></div>
                    <figure class="image">
                        <img src="{{ asset('assets/images/gallery/marathon.jpg') }}"
                            alt="Athletes participating in a sports event" loading="lazy">
                    </figure>
                </div>
            </div>

            <div class="col-lg-6 col-md-12 col-sm-12 content-column">
                <div class="content_block_two">
                    <div class="content-box ml_40">
                        <div class="sec-title mb_40">
                            <span class="sub-title">For athletes, families, and fans</span>
                            <h2 id="customer-how-it-works-title">Find your moment in four simple steps</h2>
                        </div>

                        <ol class="list-unstyled mb_40">
                            <li class="d-flex gap-3 mb-4">
                                <span class="badge rounded-pill bg-dark align-self-start mt-1">1</span>
                                <div>
                                    <h3 class="h5 mb-2">Find your event</h3>
                                    <p class="mb-0">Search by event name, city, state, sport, or date to quickly reach
                                        the right gallery.</p>
                                </div>
                            </li>
                            <li class="d-flex gap-3 mb-4">
                                <span class="badge rounded-pill bg-dark align-self-start mt-1">2</span>
                                <div>
                                    <h3 class="h5 mb-2">Choose your photos</h3>
                                    <p class="mb-0">Browse protected previews and add your favorite moments to your
                                        selection. You can review the photos and subtotal before paying.</p>
                                </div>
                            </li>
                            <li class="d-flex gap-3 mb-4">
                                <span class="badge rounded-pill bg-dark align-self-start mt-1">3</span>
                                <div>
                                    <h3 class="h5 mb-2">Pay securely</h3>
                                    <p class="mb-0">Continue to Stripe's secure checkout to complete your purchase.
                                        Your photos unlock only after payment is confirmed.</p>
                                </div>
                            </li>
                            <li class="d-flex gap-3">
                                <span class="badge rounded-pill bg-dark align-self-start mt-1">4</span>
                                <div>
                                    <h3 class="h5 mb-2">Download the originals</h3>
                                    <p class="mb-0">Use the protected link on your confirmation page or in your email
                                        receipt to download the original photos while they are available.</p>
                                </div>
                            </li>
                        </ol>

                        <div class="btn-box">
                            <a href="{{ route('events.listEvents') }}" class="theme-btn-one">Find your event</a>
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
                        <h3 class="h5 mt-3">Flexible event search</h3>
                        <p class="mb-0">Use the details you remember—such as the location, sport, or date—to narrow
                            down the event list.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <i class="bi bi-cart-check fs-2 text-primary" aria-hidden="true"></i>
                        <h3 class="h5 mt-3">Review before checkout</h3>
                        <p class="mb-0">See the gallery's per-photo price, remove unwanted selections, and confirm
                            your
                            subtotal before continuing.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <i class="bi bi-shield-check fs-2 text-primary" aria-hidden="true"></i>
                        <h3 class="h5 mt-3">Protected delivery</h3>
                        <p class="mb-0">Payment confirmation controls access to the original files, and your receipt
                            keeps the order link close at hand.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
