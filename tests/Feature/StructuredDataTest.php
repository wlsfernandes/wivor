<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StructuredDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_describes_the_wivor_organization_and_website(): void
    {
        $data = $this->structuredDataOf($this->get(route('welcome'))->assertOk()->getContent());
        $types = array_column($data, '@type');

        $this->assertContains('Organization', $types);
        $this->assertContains('WebSite', $types);

        $organization = $data[array_search('Organization', $types, true)];
        $this->assertSame('WiVor', $organization['name']);
        $this->assertSame(asset('assets/images/logo/wivor.png'), $organization['logo']);
        $this->assertContains('https://www.instagram.com/wivor.photos/', $organization['sameAs']);
    }

    public function test_faq_page_marks_up_every_visible_question_with_face_search_enabled(): void
    {
        config(['face_recognition.enabled' => true]);

        $data = $this->structuredDataOf($this->get(route('faq'))->assertOk()->getContent());
        $faq = $data[0];

        $this->assertSame('FAQPage', $faq['@type']);
        $this->assertCount(8, $faq['mainEntity']);
        $this->assertSame('Is WiVor really free?', $faq['mainEntity'][0]['name']);
        $this->assertSame('Question', $faq['mainEntity'][0]['@type']);
        $this->assertStringContainsString('Athletes only pay', $faq['mainEntity'][0]['acceptedAnswer']['text']);
    }

    public function test_faq_structured_data_leaves_out_face_search_when_it_is_disabled(): void
    {
        config(['face_recognition.enabled' => false]);

        $html = $this->get(route('faq'))->assertOk()->getContent();
        $faq = $this->structuredDataOf($html)[0];

        $this->assertCount(7, $faq['mainEntity']);
        $this->assertStringNotContainsString('selfie', json_encode($faq));
        $this->assertStringNotContainsString('face data', json_encode($faq));
    }

    public function test_event_page_describes_the_race_as_a_sports_event(): void
    {
        $event = $this->publishedEvent(['title' => 'Peachtree Road Race', 'venue_name' => 'Lenox Square']);

        $data = $this->structuredDataOf($this->get(route('events.show', ['event' => $event->slug]))->assertOk()->getContent());
        $sportsEvent = $data[array_search('SportsEvent', array_column($data, '@type'), true)];

        $this->assertSame('Peachtree Road Race', $sportsEvent['name']);
        $this->assertSame('2026-07-04', $sportsEvent['startDate']);
        $this->assertSame('Lenox Square', $sportsEvent['location']['name']);
        $this->assertSame('Atlanta', $sportsEvent['location']['address']['addressLocality']);
        $this->assertSame('GA', $sportsEvent['location']['address']['addressRegion']);
        $this->assertSame(route('events.show', ['event' => $event->slug]), $sportsEvent['url']);
        $this->assertSame($event->cover_url, $sportsEvent['image']);
    }

    public function test_event_without_a_date_has_no_sports_event_data(): void
    {
        $event = $this->publishedEvent(['title' => 'Undated Race', 'date_of_event' => null]);

        $html = $this->get(route('events.show', ['event' => $event->slug]))->assertOk()->getContent();

        $this->assertNotContains('SportsEvent', array_column($this->structuredDataOf($html), '@type'));
    }

    public function test_event_title_with_quotes_and_script_tags_keeps_structured_data_valid(): void
    {
        $event = $this->publishedEvent(['title' => 'The "Big" </script><script>alert(1)</script> Run']);

        $html = $this->get(route('events.show', ['event' => $event->slug]))->assertOk()->getContent();
        $data = $this->structuredDataOf($html);
        $sportsEvent = $data[array_search('SportsEvent', array_column($data, '@type'), true)];

        $this->assertSame('The "Big" </script><script>alert(1)</script> Run', $sportsEvent['name']);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    private function publishedEvent(array $overrides): Event
    {
        return Event::create(array_merge([
            'slug' => Event::generateUniqueSlug($overrides['title']),
            'sport' => 'Running',
            'published' => true,
            'status' => Event::STATUS_PUBLISHED,
            'published_at' => now(),
            'date_of_event' => '2026-07-04',
            'timezone' => 'America/New_York',
            'city' => 'Atlanta',
            'state' => 'GA',
            'country_code' => 'US',
            'content' => 'A community race.',
        ], $overrides));
    }

    /** @return list<array<string, mixed>> */
    private function structuredDataOf(string $html): array
    {
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);

        $items = [];
        foreach ($matches[1] as $json) {
            $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
            foreach (isset($decoded['@type']) ? [$decoded] : $decoded as $item) {
                $items[] = $item;
            }
        }

        return $items;
    }
}
