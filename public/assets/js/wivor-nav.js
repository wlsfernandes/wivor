// Opens and closes the public header navigation on small screens.
(function () {
    var toggle = document.querySelector('.wv-header__toggle');
    var navigation = document.getElementById('wv-primary-nav');

    if (!toggle || !navigation) {
        return;
    }

    toggle.addEventListener('click', function () {
        var isOpen = navigation.classList.toggle('is-open');

        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        toggle.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
    });
})();
