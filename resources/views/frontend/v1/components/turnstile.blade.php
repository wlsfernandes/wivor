{{-- Cloudflare Turnstile for one public form. $form names the form so a failed check only shows its error on that form. --}}
<input type="hidden" name="turnstile_form" value="{{ $form }}">
<div data-turnstile-widget data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
<div class="invalid-feedback @if ($errors->has('cf-turnstile-response') && old('turnstile_form') === $form) d-block @endif" data-turnstile-feedback role="alert">@if (old('turnstile_form') === $form){{ $errors->first('cf-turnstile-response') }}@endif</div>

@once
    <script>
        // Render every widget on the page; most visitors never see one (interaction-only).
        window.wivorRenderTurnstileWidgets = function () {
            // The async script can load before later forms on the page are parsed.
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', window.wivorRenderTurnstileWidgets);
                return;
            }

            document.querySelectorAll('[data-turnstile-widget]').forEach(function (container) {
                const form = container.closest('form');
                const feedback = form.querySelector('[data-turnstile-feedback]');

                function showMessage(message) {
                    feedback.textContent = message;
                    feedback.classList.add('d-block');
                }

                function clearMessage() {
                    feedback.textContent = '';
                    feedback.classList.remove('d-block');
                }

                turnstile.render(container, {
                    sitekey: container.dataset.sitekey,
                    size: 'normal',
                    appearance: 'interaction-only',
                    callback: clearMessage,
                    'expired-callback': function () {
                        showMessage('The security check expired. Please complete it again.');
                    },
                    'error-callback': function () {
                        showMessage('The security check could not be completed. Please refresh the page and try again.');
                    },
                });

                form.addEventListener('submit', function (event) {
                    const tokenInput = form.querySelector('input[name="cf-turnstile-response"]');

                    if (!tokenInput || tokenInput.value === '') {
                        event.preventDefault();
                        showMessage('Please complete the security check before submitting.');
                    }
                });
            });
        };
    </script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=wivorRenderTurnstileWidgets" async defer></script>
@endonce
