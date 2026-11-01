<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
                'Find your race photos.', 'Relive the moment.',
                'Search your event, find yourself by bib number or selfie, and preview the photos before you buy.',
                'Search Events',
                'Not sure of the event name?',
                'How WiVor works',
                'Find your event', 'Find yourself', 'Choose your photos',
                'You capture the race. Help athletes find your work.',
                'Organizing a race?',
                'Know what you are buying',
                'Preview first', 'Buy only what you want', 'Your privacy',
                'Ready to find your photos?',
                'Search by event name, bib number or selfie.',
            ])
            ->assertSee("I'm a Photographer", false)
            ->assertSee('href="'.route('how-it-works').'"', false)
            ->assertSee('href="'.route('biometric-notice').'"', false);
    }

    public function test_home_page_hides_selfie_search_when_face_recognition_is_disabled(): void
    {
        config(['face_recognition.enabled' => false]);

        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee('Search your event, find yourself by bib number, and preview the photos before you buy.')
            ->assertDontSee('selfie');
    }

    public function test_home_page_sends_each_audience_to_its_existing_journey(): void
    {
        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee('<a class="wv-link" href="'.route('events.listEvents').'">Browse events</a>', false)
            ->assertSee('<a class="wv-btn wv-btn--primary" href="'.route('photographers').'#register_section">Apply as a Photographer</a>', false)
            ->assertSee('<a class="wv-link" href="'.route('organizers').'">Become a Partner</a>', false)
            ->assertSee('<a class="wv-btn wv-btn--primary" href="'.route('events.listEvents').'">Find My Photos</a>', false);
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
            ->assertSee('There is no sign-up fee. WiVor keeps a commission on each sale.');
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

    public function test_event_search_matches_title_city_and_sport_on_postgresql_regardless_of_letter_case(): void
    {
        $searchQuery = Event::query()->search('marathon')->toBase();
        $searchQuery->grammar = new PostgresGrammar(DB::connection());

        $this->assertSame(3, substr_count($searchQuery->toSql(), ' ilike '));
        $this->assertStringNotContainsString(' like ', $searchQuery->toSql());
    }

    public function test_event_search_finds_a_published_event_in_any_letter_case(): void
    {
        Event::create([
            'title' => 'Boston Marathon',
            'slug' => Event::generateUniqueSlug('Boston Marathon'),
            'content' => '',
            'status' => Event::STATUS_PUBLISHED,
            'published' => true,
            'published_at' => now(),
            'date_of_event' => now(),
            'price_cents' => 1000,
        ]);

        foreach (['marathon', 'MARATHON', 'Marathon'] as $searchText) {
            $this->get(route('events.listEvents', ['search' => $searchText]))
                ->assertOk()
                ->assertSee('Boston Marathon');
        }
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

    public function test_only_the_home_page_marks_the_hero_copy_for_its_entrance_animation(): void
    {
        $this->get(route('welcome'))->assertOk()->assertSee('class="wv-hero__intro"', false);
        $this->get(route('faq'))->assertOk()->assertDontSee('wv-hero__intro', false);
    }
}
