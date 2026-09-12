<footer class="footer">
    <div class="container-fluid">
    <div class="footer-bottom centred">
            <div class="copyright">
                <p><small>&copy; <span id="currentYear"></span> WivorPhotos. All rights reserved.</small>
                </p>
            </div>
        </div>
    </div>
</footer>



<script>
    // Get the current year
    document.getElementById("currentYear").textContent = new Date().getFullYear();
</script>
<script src="https://www.google.com/recaptcha/api.js?render={{ env('RECAPTCHA_SITE_KEY') }}"></script>
<script>
    grecaptcha.ready(function () {
        grecaptcha.execute('{{ env('RECAPTCHA_SITE_KEY') }}', { action: 'contact' }).then(function (token) {
            if (document.getElementById('recaptcha-token')) {
                document.getElementById('recaptcha-token').value = token;
            }
        });
    });
</script>
