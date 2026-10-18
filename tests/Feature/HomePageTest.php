<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_home_page_shows_specified_sections_in_order_with_face_search_enabled(): void
    {
        config(['face_recognition.enabled' => true]);

        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee('<title>WiVor | Find and Buy Your Race Photos</title>', false)
            ->assertSeeInOrder([
                'Your best moments. Found in seconds.',
                'WiVor uses face and bib number recognition to match you with the photos photographers took of you at your event.',
                'Free for athletes, photographers and event organizers.',
                'Free to join', 'Smart matching', 'Pay only if you love it',
                'How WiVor works',
                'Photographers shoot', 'We match you', 'You choose',
                'Made for everyone at the event',
                'Athletes', 'Photographers', 'Event Organizers',
                'Why athletes and photographers choose WiVor',
                'No more endless searching', 'Fair for photographers', 'No risk for athletes', 'Built for events',
                'Ready to find your photos?',
                'Search by your name, bib number or a selfie.',
            ])
            ->assertSee("I'm a Photographer", false)
            ->assertSee('href="'.route('athletes').'"', false)
            ->assertSee('href="'.route('how-it-works').'"', false)
            ->assertSee('href="'.route('organizers').'"', false)
            ->assertSee('href="'.route('photographers').'#register_section"', false);
    }

    public function test_home_page_hides_unverified_testimonials_and_partner_logos(): void
    {
        $this->get(route('welcome'))
            ->assertOk()
            ->assertDontSee('What our community says')
            ->assertDontSee('Trusted by')
            ->assertDontSee('[Quote]');
    }
}
