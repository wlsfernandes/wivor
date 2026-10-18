<?php

namespace Tests\Feature;

use Tests\TestCase;

class StructuredDataTest extends TestCase
{
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
