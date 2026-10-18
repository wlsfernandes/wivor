<?php

namespace Tests\Feature;

use Tests\TestCase;

class CookieConsentTest extends TestCase
{
    private const CONSENT_COOKIE = 'wivor_cookie_consent';

    public function test_cookie_banner_shows_accept_and_reject_when_no_choice_was_made(): void
    {
        $this->get(route('faq'))
            ->assertOk()
            ->assertSee('id="wv-cookie-consent"', false)
            ->assertSeeInOrder(['We use essential cookies to run this site.', 'Accept', 'Reject'])
            ->assertSee('href="'.route('privacy').'"', false)
            ->assertSee('src="'.asset('assets/js/wivor-cookie-consent.js').'"', false);
    }

    public function test_cookie_banner_stays_hidden_after_visitor_accepted(): void
    {
        $this->withUnencryptedCookie(self::CONSENT_COOKIE, 'accepted')
            ->get(route('faq'))
            ->assertOk()
            ->assertDontSee('id="wv-cookie-consent"', false);
    }

    public function test_cookie_banner_stays_hidden_after_visitor_rejected(): void
    {
        $this->withUnencryptedCookie(self::CONSENT_COOKIE, 'rejected')
            ->get(route('faq'))
            ->assertOk()
            ->assertDontSee('id="wv-cookie-consent"', false);
    }

    public function test_cookie_banner_shows_again_when_cookie_value_is_unknown(): void
    {
        $this->withUnencryptedCookie(self::CONSENT_COOKIE, 'maybe')
            ->get(route('faq'))
            ->assertOk()
            ->assertSee('id="wv-cookie-consent"', false);
    }
}
