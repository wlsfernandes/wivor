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

<footer class="wv-footer">
    <div class="wv-container">
        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <a href="{{ route('welcome') }}" class="wv-footer__logo">
                    <img src="{{ asset('assets/images/logo/wivor_white.png') }}" alt="WiVor home">
                </a>
                <ul class="wv-footer__social">
                    <li><a href="https://www.instagram.com/wivor.photos/" target="_blank" rel="noopener noreferrer"
                            aria-label="WiVor on Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a></li>
                    <li><a href="https://www.facebook.com/p/WiVor-Photos-61573081696201/" target="_blank"
                            rel="noopener noreferrer" aria-label="WiVor on Facebook"><i class="fab fa-facebook-f"
                                aria-hidden="true"></i></a></li>
                </ul>
            </div>
            <nav class="col-6 col-lg-2" aria-labelledby="wv-footer-platform">
                <h2 id="wv-footer-platform" class="wv-footer__title">Platform</h2>
                <ul>
                    <li><a href="{{ route('how-it-works') }}">How It Works</a></li>
                    <li><a href="{{ route('faq') }}">FAQ</a></li>
                </ul>
            </nav>
            <nav class="col-6 col-lg-2" aria-labelledby="wv-footer-for-you">
                <h2 id="wv-footer-for-you" class="wv-footer__title">For You</h2>
                <ul>
                    <li><a href="{{ route('athletes') }}">Athletes</a></li>
                    <li><a href="{{ route('photographers') }}">Photographers</a></li>
                    <li><a href="{{ route('organizers') }}">Organizers</a></li>
                </ul>
            </nav>
            <nav class="col-6 col-lg-2" aria-labelledby="wv-footer-company">
                <h2 id="wv-footer-company" class="wv-footer__title">Company</h2>
                <ul>
                    <li><a href="{{ route('about') }}">About</a></li>
                    <li><a href="{{ route('contact_us') }}">Contact</a></li>
                </ul>
            </nav>
            <nav class="col-6 col-lg-2" aria-labelledby="wv-footer-legal">
                <h2 id="wv-footer-legal" class="wv-footer__title">Legal</h2>
                <ul>
                    <li><a href="{{ route('privacy') }}">Privacy</a></li>
                    <li><a href="{{ route('terms') }}">Terms</a></li>
                    <li><a href="{{ route('biometric-notice') }}">Biometric Notice</a></li>
                    <li><a href="{{ route('refund-policy') }}">Refund Policy</a></li>
                    <li><a href="{{ route('photographer-terms') }}">Photographer Terms</a></li>
                    <li><a href="{{ route('photo-removal.create') }}">Photo Removal Request</a></li>
                </ul>
            </nav>
        </div>
        <p class="wv-footer__copyright">© 2026 WiVor. All rights reserved.</p>
    </div>
</footer>

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
<script src="{{ asset('assets/js/wivor-nav.js') }}"></script>

@include('frontend.v1.components.cookie-consent')
