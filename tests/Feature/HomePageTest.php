<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_specified_sections_in_order_with_face_search_enabled(): void
    {
        config(['face_recognition.enabled' => true]);

        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee('<title>WiVor | Find and Buy Your Race Photos</title>', false)
            ->assertSeeInOrder([
                'Your race. Your moment.',
                'Find your event, search by bib number or selfie, then preview and buy the photos you want.',
                'Search Events',
                'How WiVor works',
                'Find your event', 'Find yourself', 'Choose your photos',
                'For photographers', 'For event organizers',
                'Know what you are buying',
                'Preview first', 'Buy only what you want', 'Your privacy',
                'Ready to find your photos?',
                'Search by event name, bib number or selfie.',
            ])
            ->assertSee("I'm a Photographer", false)
            ->assertSee('href="'.route('athletes').'"', false)
            ->assertSee('href="'.route('how-it-works').'"', false)
            ->assertSee('href="'.route('organizers').'"', false)
            ->assertSee('href="'.route('photographers').'#register_section"', false)
            ->assertSee('href="'.route('biometric-notice').'"', false);
    }

    public function test_home_page_has_one_h1_and_a_working_event_search_form(): void
    {
        $response = $this->get(route('welcome'))->assertOk();

        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
        $response->assertSee('action="'.route('events.listEvents').'"', false)
            ->assertSee('method="GET"', false)
            ->assertSee('name="search"', false)
            ->assertSee('<label', false);
    }

    public function test_home_page_does_not_claim_photographers_use_wivor_for_free(): void
    {
        $this->get(route('welcome'))
            ->assertOk()
            ->assertDontSee('Free for photographers')
            ->assertDontSee('zero cost')
            ->assertSee('WiVor keeps a commission on each sale.');
    }

    public function test_home_page_meta_description_does_not_claim_wivor_is_free_to_use(): void
    {
        foreach ([true, false] as $isFaceSearchEnabled) {
            config(['face_recognition.enabled' => $isFaceSearchEnabled]);

            $this->get(route('welcome'))
                ->assertOk()
                ->assertDontSee('Free to use')
                ->assertSee('Free to browse.');
        }
    }

    public function test_empty_event_search_opens_the_full_event_list(): void
    {
        $this->get(route('events.listEvents', ['search' => '']))->assertOk();
    }

    public function test_event_search_escapes_markup_in_the_search_text(): void
    {
        $markup = '<script>alert(1)</script>';

        $this->get(route('events.listEvents', ['search' => $markup]))
            ->assertOk()
            ->assertDontSee($markup, false);
    }

    public function test_home_page_shows_hero_and_audience_photos_with_descriptive_alt_text(): void
    {
        $photos = [
            'hero-finish-line.webp' => 'Runner with a race bib approaching cheering spectators on a seaside promenade',
            'athletes-bib-runners.webp' => 'Two runners with race bibs celebrating on the course',
            'photographers-telephoto-lens.webp' => 'Photographer with a telephoto lens shooting an event crowd',
            'organizers-race-crowd.webp' => 'Crowd of runners on the road at the start of a race',
        ];

        $response = $this->get(route('welcome'))->assertOk();

        foreach ($photos as $fileName => $altText) {
            $this->assertFileExists(public_path('assets/images/site/'.$fileName));
            $response->assertSee('src="'.asset('assets/images/site/'.$fileName).'"', false)
                ->assertSee('alt="'.$altText.'"', false);
        }
        $response->assertSee('loading="lazy"', false);
    }

    public function test_home_page_hides_unverified_testimonials_and_partner_logos(): void
    {
        $this->get(route('welcome'))
            ->assertOk()
            ->assertDontSee('What our community says')
            ->assertDontSee('Trusted by')
            ->assertDontSee('Free for athletes')
            ->assertDontSee('[Quote]');
    }
}
