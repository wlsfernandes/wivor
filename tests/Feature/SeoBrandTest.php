<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoBrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_use_the_wivor_brand_in_titles_and_share_metadata(): void
    {
        foreach (['events.listEvents', 'photographers', 'contact_us', 'privacy', 'terms', 'login'] as $routeName) {
            $response = $this->get(route($routeName))->assertOk();
            $title = $this->titleOf($response->getContent());

            $this->assertStringContainsString('WiVor', $title, "Title of {$routeName}");
            $this->assertStringNotContainsString('WivorPhotos', $title, "Title of {$routeName}");
        }
    }

    public function test_photographers_page_uses_the_specified_seo_title(): void
    {
        $this->get(route('photographers'))
            ->assertOk()
            ->assertSee('<title>Sell Event Photos Online | WiVor for Photographers</title>', false);
    }

    public function test_event_page_title_uses_the_wivor_brand(): void
    {
        $event = $this->publishedEvent('Peachtree Road Race');

        $this->get(route('events.show', ['event' => $event->slug]))
            ->assertOk()
            ->assertSee('<title>Peachtree Road Race Photos | WiVor</title>', false);
    }

    public function test_public_pages_no_longer_print_a_keywords_meta_tag(): void
    {
        $event = $this->publishedEvent('Keyword Free Race');

        $this->get(route('events.show', ['event' => $event->slug]))->assertOk()->assertDontSee('meta name="keywords"', false);
        $this->get(route('events.listEvents'))->assertOk()->assertDontSee('meta name="keywords"', false);
    }

    public function test_share_metadata_names_the_site_wivor(): void
    {
        $event = $this->publishedEvent('Share Name Race');

        $this->get(route('events.show', ['event' => $event->slug]))
            ->assertOk()
            ->assertSee('<meta property="og:site_name" content="WiVor">', false);
    }

    private function titleOf(string $html): string
    {
        preg_match('/<title>(.*?)<\/title>/s', $html, $matches);

        return trim($matches[1] ?? '');
    }

    private function publishedEvent(string $title): Event
    {
        return Event::create([
            'title' => $title,
            'slug' => Event::generateUniqueSlug($title),
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
    }
}
