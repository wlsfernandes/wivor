@extends('layouts.app')

@section('title', 'Frequently Asked Questions | WivorPhotos')
@section('meta-description', 'Find answers about locating event photos, purchasing and downloading images, and working with WivorPhotos as a photographer.')
@section('meta-keywords', 'WivorPhotos FAQ, find event photos, buy sports photos, download event photos, WivorPhotos photographers')

@section('content')
    <section class="cta-section home-3 centred">
        <div class="bg-layer parallax-bg" data-parallax='{"y": 100}'
            style="background-image: url({{ asset('assets/images/gallery/banner3.jpg') }});"></div>
        <div class="auto-container">
            <div class="inner-box">
                <h1>Frequently Asked Questions</h1>
                <p>Everything you need to find, purchase, publish, and download photos with WivorPhotos.</p>
            </div>
        </div>
    </section>

    <main class="contact-section sec-pad">
        <div class="auto-container">
            <div class="sec-title centred mb_55">
                <span class="sub-title calendar">How can we help?</span>
                <h2>Choose a topic</h2>
                <p>Open any question below to see the answer.</p>
            </div>

            <nav class="d-flex flex-wrap justify-content-center gap-2 mb-5" aria-label="FAQ topics">
                <a class="btn btn-outline-primary" href="#customers">Finding and buying photos</a>
                <a class="btn btn-outline-primary" href="#orders">Payments and downloads</a>
                <a class="btn btn-outline-primary" href="#photographers">For photographers</a>
            </nav>

            <div class="row justify-content-center">
                <div class="col-xl-9 col-lg-10">
                    <section id="customers" class="mb-5" aria-labelledby="customers-title">
                        <div class="sec-title mb_30">
                            <span class="sub-title">For customers</span>
                            <h2 id="customers-title" class="h3">Finding and buying photos</h2>
                        </div>

                        <details class="card border-0 shadow-sm mb-3" open>
                            <summary class="card-header bg-white p-4 fw-semibold">What is WivorPhotos?</summary>
                            <div class="card-body px-4 pb-4">
                                WivorPhotos is an event photography marketplace where athletes, families, and fans can
                                find professional sports and fitness photos and purchase the moments they want to keep.
                            </div>
                        </details>

                        <details class="card border-0 shadow-sm mb-3">
                            <summary class="card-header bg-white p-4 fw-semibold">How do I find my event?</summary>
                            <div class="card-body px-4 pb-4">
                                Open the event directory and search by event name, city, state, sport, or date. You can
                                combine the available filters to narrow the results.
                            </div>
                        </details>

                        <details class="card border-0 shadow-sm mb-3">
                            <summary class="card-header bg-white p-4 fw-semibold">Why are no photos showing for my event?
                            </summary>
                            <div class="card-body px-4 pb-4">
                                The photographer may still be processing or publishing the gallery, or the photos may
                                not be available for sale yet. The event page displays its current photo-availability
                                status.
                            </div>
                        </details>

                        <details class="card border-0 shadow-sm mb-3">
                            <summary class="card-header bg-white p-4 fw-semibold">How do I select photos?</summary>
                            <div class="card-body px-4 pb-4">
                                Open an event gallery and use “Add to selection” beneath any available photo. Your
                                selection page lets you review the images, remove photos, and see the subtotal before
                                checkout.
                            </div>
                        </details>

                        <details class="card border-0 shadow-sm mb-3">
                            <summary class="card-header bg-white p-4 fw-semibold">Can I combine photos from different
                                events?</summary>
                            <div class="card-body px-4 pb-4">
                                Not in the same order. Each selection contains photos from one event so its price and
                                availability can be checked correctly. Complete that order before selecting photos from
                                another event.
                            </div>
                        </details>

                        <details class="card border-0 shadow-sm mb-3">
                            <summary class="card-header bg-white p-4 fw-semibold">Do I need an account to purchase
                                photos?</summary>
                            <div class="card-body px-4 pb-4">
                                No. The current MVP supports guest selection, checkout, and secure delivery without a
                                WivorPhotos customer account.
                            </div>
                        </details>

                        <details class="card border-0 shadow-sm mb-3">
                            <summary class="card-header bg-white p-4 fw-semibold">How much does each photo cost?</summary>
                            <div class="card-body px-4 pb-4">
                                The event gallery displays its current per-photo price. Your selection page shows the
                                number of photos and complete subtotal before you continue to payment.
                            </div>
                        </details>
                    </section>

                    <section id="orders" class="mb-5" aria-labelledby="orders-title">
                        <div class="sec-title mb_30">
                            <span class="sub-title">For customers</span>
                            <h2 id="orders-title" class="h3">Payments and downloads</h2>
                        </div>

                        <details class="card border-0 shadow-sm mb-3">
                            <summary class="card-header bg-white p-4 fw-semibold">How does checkout work?</summary>
                            <div class="card-body px-4 pb-4">
                                After reviewing your selection, choose “Continue to Secure Checkout.” WivorPhotos sends
                                you to Stripe's hosted checkout page to complete payment. Photos unlock only after the
                                payment is confirmed.
                            </div>
                        </details>

                        <details class="card border-0 shadow-sm mb-3">
                            <summary class="card-header bg-white p-4 fw-semibold">What happens if I cancel checkout?
                            </summary>
                            <div class="card-body px-4 pb-4">
                                Your selected photos remain in the cart so you can review them and try checkout again.
                                An incomplete or cancelled payment does not unlock the original files.
                            </div>
                        </details>

                        <details class="card border-0 shadow-sm mb-3">
                            <summary class="card-header bg-white p-4 fw-semibold">When can I download my photos?</summary>
                            <div class="card-body px-4 pb-4">
                                Once Stripe confirms payment, the confirmation page provides access to your order. We
                                also email a receipt containing the protected order and download link.
                            </div>
                        </details>

                        <details class="card border-0 shadow-sm mb-3">
                            <summary class="card-header bg-white p-4 fw-semibold">How long are my downloads available?
                            </summary>
                            <div class="card-body px-4 pb-4">
                                Downloads are available for the period shown on your order page and receipt. Download
                                and safely back up your originals before that date.
                            </div>
                        </details>

                        <details class="card border-0 shadow-sm mb-3">
                            <summary class="card-header bg-white p-4 fw-semibold">Why does the gallery show protected
                                previews?</summary>
                            <div class="card-body px-4 pb-4">
                                Gallery previews help you identify the photos you want while protecting the
                                photographer's original work. The downloadable original becomes available after a
                                successful purchase.
                            </div>
                        </details>
                    </section>

                    <section id="photographers" aria-labelledby="photographers-title">
                        <div class="sec-title mb_30">
                            <span class="sub-title">Work with WivorPhotos</span>
                            <h2 id="photographers-title" class="h3">For photographers</h2>
                        </div>

                        <details class="card border-0 shadow-sm mb-3">
                            <summary class="card-header bg-white p-4 fw-semibold">How do I apply as a photographer?
                            </summary>
                            <div class="card-body px-4 pb-4">
                                Complete the photographer application with your contact details, location, portfolio,
                                equipment, and professional introduction. You must be at least 18 years old and verify
                                your email before the application can be reviewed.
                            </div>
                        </details>

                        <details class="card border-0 shadow-sm mb-3">
                            <summary class="card-header bg-white p-4 fw-semibold">What happens after I apply?</summary>
                            <div class="card-body px-4 pb-4">
                                The WivorPhotos team reviews your application. Approved and email-verified photographers
                                can sign in to the photographer dashboard to access events, assignments, uploads, and
                                sales information.
                            </div>
                        </details>

                        <details class="card border-0 shadow-sm mb-3">
                            <summary class="card-header bg-white p-4 fw-semibold">Can I upload photos to any event?
                            </summary>
                            <div class="card-body px-4 pb-4">
                                No. You must be an approved photographer with an approved assignment for that event.
                                Uploads must also be completed before the event's upload deadline.
                            </div>
                        </details>

                        <details class="card border-0 shadow-sm mb-3">
                            <summary class="card-header bg-white p-4 fw-semibold">What files can I upload?</summary>
                            <div class="card-body px-4 pb-4">
                                Upload JPG or JPEG images up to 40 MB each. The longest side must be at least 2,400
                                pixels, neither side may exceed 12,000 pixels, and images must use RGB or sRGB. Do not
                                add your own watermark, logo, border, or marketplace branding.
                            </div>
                        </details>

                        <details class="card border-0 shadow-sm mb-3">
                            <summary class="card-header bg-white p-4 fw-semibold">Who is responsible for image rights
                                and backups?</summary>
                            <div class="card-body px-4 pb-4">
                                Photographers must upload only photographs they created or are legally authorized to
                                sell. Photographers must also keep their own backup because WivorPhotos is a sales
                                platform, not permanent backup storage.
                            </div>
                        </details>

                        <details class="card border-0 shadow-sm mb-3">
                            <summary class="card-header bg-white p-4 fw-semibold">How do photos become available to
                                customers?</summary>
                            <div class="card-body px-4 pb-4">
                                WivorPhotos processes uploaded JPEGs and prepares protected gallery previews. When files
                                are ready, the photographer selects which photos to publish in the event gallery.
                            </div>
                        </details>

                        <details class="card border-0 shadow-sm mb-3">
                            <summary class="card-header bg-white p-4 fw-semibold">How do I track sales and earnings?
                            </summary>
                            <div class="card-body px-4 pb-4">
                                The photographer dashboard shows gross sales, WivorPhotos commission,
                                payment-processing fees, refunds, net earnings, pending payouts, and paid amounts.
                            </div>
                        </details>

                        <details class="card border-0 shadow-sm mb-3">
                            <summary class="card-header bg-white p-4 fw-semibold">Is Stripe payout setup required before
                                publishing?</summary>
                            <div class="card-body px-4 pb-4">
                                No. During the MVP, payout setup is optional and does not prevent photo publishing or
                                customer sales. Your dashboard still records earnings and payout status.
                            </div>
                        </details>
                    </section>

                    <div class="card border-0 bg-light mt-5">
                        <div class="card-body p-4 p-lg-5 text-center">
                            <h2 class="h4">Ready to get started?</h2>
                            <p>Browse event galleries or learn how to join WivorPhotos as a photographer.</p>
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <a class="theme-btn-one" href="{{ route('events.listEvents') }}">Find your event</a>
                                <a class="btn btn-outline-primary" href="{{ route('photographers') }}#how-it-works">For
                                    photographers</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
