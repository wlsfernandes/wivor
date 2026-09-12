<div class="scroll-to-top">
    <div>
        <div class="scroll-top-inner">
            <div class="scroll-bar">
                <div class="bar-inner"></div>
            </div>
            <div class="scroll-bar-text">Go To Top</div>
        </div>
    </div>
</div>

</div> <!-- close from header <div class="boxed_wrapper"> -->

<footer class="main-footer" style="margin-top: 250px;">
    <div class="auto-container">
        <div class="footer-top">
            <figure class="footer-logo"><a href="{{ url('/') }}"><img
                        src="{{ asset('assets/images/logo/wivor_white.png') }}" alt="Wivor Logo"></a></figure>
            <ul class="social-links">
                <li><a href="https://www.facebook.com/p/WiVor-Photos-61573081696201/" target="_blank"
                        rel="noopener noreferrer" aria-label="WivorPhotos on Facebook"><i class="fab fa-facebook-f"
                            aria-hidden="true"></i></a></li>
                <li><a href="https://www.instagram.com/wivor.photos/" target="_blank" rel="noopener noreferrer"
                        aria-label="WivorPhotos on Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                </li>
            </ul>
        </div>
        <nav aria-label="Footer navigation" class="py-4">
            <ul class="list-unstyled d-flex flex-wrap justify-content-center mb-0" style="gap: 12px 24px;">
                <li><a class="text-white" href="{{ route('privacy') }}">@lang('header.privacy_policy')</a></li>
                <li><a class="text-white" href="{{ route('terms') }}">@lang('header.terms_of_use')</a></li>
                <li><a class="text-white" href="{{ route('refund-policy') }}">@lang('header.refund_policy')</a></li>
                <li><a class="text-white" href="{{ route('photographer-terms') }}">Photographer Terms</a></li>
                <li><a class="text-white" href="{{ route('photo-removal.create') }}">@lang('header.photo_removal_request')</a></li>
                <li><a class="text-white" href="{{ route('contact_us') }}">@lang('header.contact_us')</a></li>
            </ul>
        </nav>
        <!--   <div class="widget-section">
            <div class="row clearfix">

                <div class="col-lg-3 col-md-6 col-sm-12 footer-column">
                    <div class="links-widget footer-widget ml_50">
                        <div class="widget-title">
                            <h3>Quick Link</h3>
                        </div>
                        <div class="widget-content">
                            <ul class="links-list clearfix">
                                <li><a href="{{ url('/') }}">About Us</a></li>
                                <li><a href="{{ url('/') }}">Services</a></li>
                                <li><a href="{{ url('/') }}">Case</a></li>
                                <li><a href="{{ url('/') }}">Pricing</a></li>
                                <li><a href="{{ url('/') }}">Contact Us</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 footer-column">
                    <div class="links-widget footer-widget ml_30">
                        <div class="widget-title">
                            <h3>Usefull Links</h3>
                        </div>
                        <div class="widget-content">
                            <ul class="links-list clearfix">
                                <li><a href="">Privacy Policy</a></li>
                                <li><a href="">Terms & Condition</a></li>
                                <li><a href="">Support</a></li>
                                <li><a href="">Disclaimer</a></li>
                                <li><a href="">Faq</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 footer-column">
                    <div class="contact-widget footer-widget ml_30">
                        <div class="widget-title">
                            <h3>Contact</h3>
                        </div>
                        <div class="widget-content">
                            <ul class="info-list clearfix">
                                <li><i class="icon-17"></i>160 Clairemont Ave. Suite 300 Decatur, GA 30030</li>
                                <li><i class="icon-18"></i><a href="mailto:{{ config('contact.email') }}">{{ config('contact.email') }}</a>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
        <div class="footer-bottom centred">
            <div class="copyright">
                <p><small>&copy; <span id="currentYear"></span> WivorPhotos. All rights reserved.</small>
                </p>
            </div>
        </div>
    </div>
</footer>
<!-- main-footer end -->

<script>
    // Get the current year
    document.getElementById("currentYear").textContent = new Date().getFullYear();
</script>

<!-- jQuery plugins -->
<script src="{{ asset('assets/js/jquery.js') }}"></script>
<script src="{{ asset('assets/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/owl.js') }}"></script>
<script src="{{ asset('assets/js/wow.js') }}"></script>
<script src="{{ asset('assets/js/validation.js') }}"></script>
<script src="{{ asset('assets/js/jquery.fancybox.js') }}"></script>
<script src="{{ asset('assets/js/appear.js') }}"></script>
<script src="{{ asset('assets/js/scrollbar.js') }}"></script>
<script src="{{ asset('assets/js/isotope.js') }}"></script>
<script src="{{ asset('assets/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('assets/js/parallax-scroll.js') }}"></script>
<script src="{{ asset('assets/js/jquery-ui.js') }}"></script>
<script src="{{ asset('assets/js/nav-tool.js') }}"></script>
<script src="{{ asset('assets/js/jquery.bootstrap-touchspin.js') }}"></script>
<script src="{{ asset('assets/js/bxslider.js') }}"></script>

<!-- main-js -->
<script src="{{ asset('assets/js/script.js') }}"></script>
