// Saves the visitor's cookie choice for one year and hides the consent banner.
(function () {
    var CONSENT_COOKIE_NAME = 'wivor_cookie_consent';
    var ONE_YEAR_IN_SECONDS = 60 * 60 * 24 * 365;
    var banner = document.getElementById('wv-cookie-consent');

    if (!banner) {
        return;
    }

    banner.querySelectorAll('[data-wv-cookie-choice]').forEach(function (button) {
        button.addEventListener('click', function () {
            var choice = button.getAttribute('data-wv-cookie-choice');
            var secureFlag = window.location.protocol === 'https:' ? '; Secure' : '';

            document.cookie = CONSENT_COOKIE_NAME + '=' + choice + '; Max-Age=' + ONE_YEAR_IN_SECONDS + '; Path=/; SameSite=Lax' + secureFlag;
            banner.remove();
        });
    });
})();
