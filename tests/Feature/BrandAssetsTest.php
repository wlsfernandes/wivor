<?php

namespace Tests\Feature;

use Tests\TestCase;

class BrandAssetsTest extends TestCase
{
    public function test_public_pages_load_manrope_inter_and_the_brand_stylesheet(): void
    {
        $this->get(route('faq'))
            ->assertOk()
            ->assertSee('family=Manrope', false)
            ->assertSee('family=Inter', false)
            ->assertDontSee('family=Alata', false)
            ->assertSee('href="'.asset('assets/css/wivor-brand.css').'"', false);
    }

    public function test_brand_stylesheet_defines_every_specified_color_and_no_retired_color(): void
    {
        $stylesheet = file_get_contents(public_path('assets/css/wivor-brand.css'));

        $specifiedColors = [
            '#151b22', '#28323d', '#34404c', '#596675', '#f7f8f6', '#ffffff', '#f1f3f3', '#e1e6e7',
            '#c64526', '#a5381c', '#fff0ea', '#f7f8fa', '#dfe4e8', '#1d7755', '#1174b8', '#b42318',
        ];

        foreach ($specifiedColors as $color) {
            $this->assertStringContainsString($color, $stylesheet);
        }

        foreach (['#ff5722', '#ff7e55', '#303841', "'Alata'", '--wv-'] as $retiredValue) {
            $this->assertStringNotContainsString($retiredValue, $stylesheet);
        }
    }

    public function test_portal_shares_the_accent_and_admin_layout_does_not_load_the_public_brand(): void
    {
        $portalLayout = file_get_contents(resource_path('views/portal/layouts/app-sidebar.blade.php'));
        $adminHead = file_get_contents(resource_path('views/layouts/head.blade.php'));

        $this->assertStringContainsString('--photographer-accent: #c64526', $portalLayout);
        $this->assertStringNotContainsString('wivor-brand', $adminHead);
    }

    public function test_brand_stylesheet_provides_shared_form_badge_focus_and_motion_rules(): void
    {
        $stylesheet = file_get_contents(public_path('assets/css/wivor-brand.css'));

        foreach (['.wv-input', '.wv-label', '.wv-help', '.wv-search', '.wv-badge--available', '.wv-badge--upcoming', '.wv-badge--past'] as $selector) {
            $this->assertStringContainsString($selector, $stylesheet);
        }

        $this->assertStringContainsString('outline: 3px solid var(--wivor-focus)', $stylesheet);
        $this->assertStringContainsString('prefers-reduced-motion: reduce', $stylesheet);
    }

    public function test_link_underlines_override_the_template_rule_that_removes_them(): void
    {
        $stylesheet = file_get_contents(public_path('assets/css/wivor-brand.css'));

        $this->assertDoesNotMatchRegularExpression('/text-decoration: underline;/', $stylesheet);
        $this->assertStringContainsString('text-decoration: underline !important', $stylesheet);
    }

    public function test_brand_stylesheet_shows_keyboard_focus_on_header_footer_and_faq_controls(): void
    {
        $stylesheet = file_get_contents(public_path('assets/css/wivor-brand.css'));

        foreach (['.wv-header__nav a:focus-visible', '.wv-header__toggle:focus-visible', '.wv-footer a:focus-visible', '.wv-faq summary:focus-visible'] as $selector) {
            $this->assertStringContainsString($selector, $stylesheet);
        }
    }
}
