<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_sitemap_page_lists_every_marketing_page(): void
    {
        $response = $this->get(route('sitemap.events', ['page' => 1]))->assertOk();

        foreach (['welcome', 'events.listEvents', 'how-it-works', 'photographers', 'organizers', 'faq', 'about', 'contact_us'] as $routeName) {
            $response->assertSee('<loc>'.route($routeName).'</loc>', false);
        }
    }

    public function test_sitemap_does_not_list_redirects_or_private_pages(): void
    {
        $this->get(route('sitemap.events', ['page' => 1]))
            ->assertOk()
            ->assertDontSee('<loc>'.route('athletes').'</loc>', false)
            ->assertDontSee('<loc>'.route('cart.show').'</loc>', false)
            ->assertDontSee('<loc>'.route('login').'</loc>', false);
    }
}
