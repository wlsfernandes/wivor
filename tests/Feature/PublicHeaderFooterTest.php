<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicHeaderFooterTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_shows_specified_navigation_and_find_my_photos_button(): void
    {
        $this->get(route('faq'))
            ->assertOk()
            ->assertSeeInOrder([
                'href="'.route('how-it-works').'"', 'How It Works',
                'href="'.route('athletes').'"', 'Athletes',
                'href="'.route('photographers').'"', 'Photographers',
                'href="'.route('organizers').'"', 'Organizers',
                'href="'.route('faq').'"', 'FAQ',
            ], false)
            ->assertSee('class="wv-btn wv-btn--primary wv-header__cta"', false)
            ->assertSee('src="'.asset('assets/images/logo/wivor.png').'"', false);
    }

    public function test_header_has_mobile_menu_toggle_and_keeps_cart_and_login(): void
    {
        $this->get(route('faq'))
            ->assertOk()
            ->assertSee('aria-controls="wv-primary-nav"', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('href="'.route('cart.show').'"', false)
            ->assertSee('href="'.route('login').'"', false)
            ->assertSee('src="'.asset('assets/js/wivor-nav.js').'"', false);
    }

    public function test_header_still_shows_flash_and_validation_messages(): void
    {
        $this->withSession(['success' => 'Saved successfully.', 'error' => 'Something failed.'])
            ->get(route('faq'))
            ->assertOk()
            ->assertSee('Saved successfully.')
            ->assertSee('Something failed.');
    }

    public function test_footer_has_four_link_columns_white_logo_and_copyright(): void
    {
        $response = $this->get(route('faq'))->assertOk();

        $response->assertSeeInOrder(['Platform', 'For You', 'Company', 'Legal'])
            ->assertSee('src="'.asset('assets/images/logo/wivor_white.png').'"', false)
            ->assertSee('© 2026 WiVor. All rights reserved.')
            ->assertSee('href="https://www.instagram.com/wivor.photos/"', false)
            ->assertSee('href="https://www.facebook.com/p/WiVor-Photos-61573081696201/"', false);

        foreach (['how-it-works', 'faq', 'athletes', 'photographers', 'organizers', 'about', 'contact_us', 'privacy', 'terms', 'biometric-notice'] as $routeName) {
            $response->assertSee('href="'.route($routeName).'"', false);
        }
    }
}
