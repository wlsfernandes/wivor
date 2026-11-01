// Opens and closes the public header navigation on small screens.
(function () {
    var toggle = document.querySelector('.wv-header__toggle');
    var navigation = document.getElementById('wv-primary-nav');

    if (!toggle || !navigation) {
        return;
    }

    function setMenuOpen(isOpen) {
        navigation.classList.toggle('is-open', isOpen);
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        toggle.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
    }

    toggle.addEventListener('click', function () {
        setMenuOpen(!navigation.classList.contains('is-open'));
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && navigation.classList.contains('is-open')) {
            setMenuOpen(false);
            toggle.focus();
        }
    });
})();
