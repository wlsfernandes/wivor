<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoCanonicalAndShareTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketing_pages_have_a_canonical_address_built_from_the_app_url(): void
    {
        $baseUrl = rtrim(config('app.url'), '/');

        $this->get(route('welcome'))->assertOk()->assertSee('<link rel="canonical" href="'.$baseUrl.'/">', false);
        $this->get(route('faq'))->assertOk()->assertSee('<link rel="canonical" href="'.$baseUrl.'/faq">', false);
    }

    public function test_canonical_address_ignores_tracking_parameters_from_social_links(): void
    {
        $baseUrl = rtrim(config('app.url'), '/');

        $this->get('/faq?utm_source=instagram&fbclid=abc123')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.$baseUrl.'/faq">', false)
            ->assertDontSee('utm_source=instagram"', false);
    }

    public function test_canonical_address_uses_the_same_host_as_the_sitemap(): void
    {
        config(['app.url' => 'https://wivorphotos.com']);

        $this->get('https://www.wivorphotos.com/faq')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://www.wivorphotos.com/faq">', false);

        $this->get('https://www.wivorphotos.com/sitemaps/events-1.xml')
            ->assertOk()
            ->assertSee('<loc>https://www.wivorphotos.com/faq</loc>', false);
    }

    public function test_marketing_pages_have_a_share_preview_with_the_default_image(): void
    {
        $this->get(route('how-it-works'))
            ->assertOk()
            ->assertSee('<meta property="og:title" content="How WiVor Works | Photo Matching for Athletes">', false)
            ->assertSee('<meta property="og:image" content="'.asset('assets/images/site/og-default.jpg').'">', false)
            ->assertSee('<meta property="og:site_name" content="WiVor">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
    }

    public function test_event_page_keeps_its_own_cover_as_share_image(): void
    {
        $event = Event::create([
            'title' => 'Cover Share Race',
            'slug' => Event::generateUniqueSlug('Cover Share Race'),
            'sport' => 'Running',
            'published' => true,
            'status' => Event::STATUS_PUBLISHED,
            'published_at' => now(),
            'date_of_event' => '2026-10-10',
            'timezone' => 'America/New_York',
            'city' => 'Atlanta',
            'state' => 'GA',
            'country_code' => 'US',
            'content' => 'A community race.',
        ]);

        $this->get(route('events.show', ['event' => $event->slug]))
            ->assertOk()
            ->assertSee('<meta property="og:image" content="'.$event->cover_url.'">', false)
            ->assertDontSee('og-default.jpg', false)
            ->assertSee('<link rel="canonical" href="'.route('events.show', ['event' => $event->slug]).'">', false);
    }

    public function test_default_share_image_exists_with_the_recommended_size(): void
    {
        $path = public_path('assets/images/site/og-default.jpg');

        $this->assertFileExists($path);
        [$width, $height] = getimagesize($path);
        $this->assertSame([1200, 630], [$width, $height]);
    }
}
