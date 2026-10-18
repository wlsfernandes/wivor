<?php

namespace Tests\Feature;

use Tests\TestCase;

class HowItWorksPageTest extends TestCase
{
    public function test_how_it_works_shows_athlete_and_photographer_steps_with_face_search_enabled(): void
    {
        config(['face_recognition.enabled' => true]);

        $this->get('/how-it-works')
            ->assertOk()
            ->assertSee('<title>How WiVor Works | Photo Matching for Athletes</title>', false)
            ->assertSee('Simple for athletes. Simple for photographers.')
            ->assertSee("Here's how WiVor turns event photos into memories, step by step.", false)
            ->assertSeeInOrder([
                'For Athletes',
                'Find your event', 'Search for yourself', 'Enter your bib number or upload a selfie. We find the matches.',
                'Preview your photos', 'Buy what you love',
                'For Photographers',
                'Create a free account', 'Shoot the event',
                'WiVor reads faces and bib numbers and sorts photos for you.', 'Get paid',
            ])
            ->assertSee('Two ways to find you')
            ->assertSee('href="'.route('biometric-notice').'"', false)
            ->assertSee('href="'.route('athletes').'"', false)
            ->assertSee('href="'.route('photographers').'#register_section"', false);
    }

    public function test_how_it_works_hides_face_search_claims_when_face_search_is_disabled(): void
    {
        config(['face_recognition.enabled' => false]);

        $this->get(route('how-it-works'))
            ->assertOk()
            ->assertSee('Enter your bib number. We find the matches.')
            ->assertSee('WiVor reads bib numbers and sorts photos for you.')
            ->assertDontSee('selfie')
            ->assertDontSee('Face recognition')
            ->assertDontSee('Two ways to find you');
    }

    public function test_athletes_link_temporarily_sends_visitors_to_the_event_directory(): void
    {
        $this->get('/athletes')->assertRedirect(route('events.listEvents'));
    }
}
