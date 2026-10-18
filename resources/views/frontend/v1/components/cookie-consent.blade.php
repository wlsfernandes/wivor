{{-- Cookie consent (spec section 12). Hidden once the visitor accepted or rejected; the choice lives in the wivor_cookie_consent cookie. --}}
@if (! in_array(request()->cookie('wivor_cookie_consent'), ['accepted', 'rejected'], true))
    <div id="wv-cookie-consent" class="wv-cookie" role="region" aria-label="Cookie consent">
        <p class="wv-cookie__text">
            We use essential cookies to run this site. With your permission, we may also use analytics cookies.
            <a href="{{ route('privacy') }}">Privacy Policy</a>
        </p>
        <div class="wv-cookie__actions">
            <button type="button" class="wv-btn wv-btn--primary wv-cookie__button" data-wv-cookie-choice="accepted">Accept</button>
            <button type="button" class="wv-btn wv-cookie__button wv-cookie__button--reject" data-wv-cookie-choice="rejected">Reject</button>
        </div>
    </div>
    <script src="{{ asset('assets/js/wivor-cookie-consent.js') }}"></script>
@endif
