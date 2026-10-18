<?php

namespace Tests\Feature;

use Tests\TestCase;

class SearchIndexingTest extends TestCase
{
    private const NOINDEX_META = '<meta name="robots" content="noindex,nofollow">';

    public function test_marketing_pages_stay_indexable(): void
    {
        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="index,follow', false);
    }

    public function test_cart_and_login_pages_are_hidden_from_search(): void
    {
        $this->get(route('cart.show'))->assertOk()->assertSee(self::NOINDEX_META, false);
        $this->get(route('login'))->assertOk()->assertSee(self::NOINDEX_META, false);
    }

    public function test_robots_txt_blocks_athlete_photos_and_private_pages_and_lists_the_sitemap(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        foreach (['Disallow: /events/*/photos', 'Disallow: /orders/', 'Disallow: /cart', 'Disallow: /checkout/'] as $rule) {
            $this->assertStringContainsString($rule, $robots);
        }
        $this->assertStringContainsString('Sitemap: https://www.wivorphotos.com/sitemap.xml', $robots);
        $this->assertStringNotContainsString("Disallow: /\n", $robots);
    }
}
