<?php

namespace Tests\Feature;

use Tests\TestCase;

class OrganizersPageTest extends TestCase
{
    public function test_organizers_page_shows_benefits_steps_and_partner_call_to_action(): void
    {
        $this->get('/organizers')
            ->assertOk()
            ->assertSee('<title>Event Photography Partner | WiVor for Organizers</title>', false)
            ->assertSee("Give your athletes the photos they'll remember.", false)
            ->assertSee('Partner with WiVor and offer professional event photos with no cost to you and no extra work for your team.')
            ->assertSeeInOrder(['Zero cost', 'No extra work', 'Better athlete experience', 'Free marketing'])
            ->assertSeeInOrder(['Tell us about your event', 'We coordinate photographers', 'Athletes find and buy their photos'])
            ->assertSee('Become a Partner')
            ->assertSee('href="'.route('contact_us').'"', false)
            ->assertDontSee('Georgia Multiports');
    }

    public function test_faq_partnership_answer_links_to_the_organizers_page(): void
    {
        $this->get(route('faq'))
            ->assertOk()
            ->assertSee('href="'.route('organizers').'"', false);
    }
}
