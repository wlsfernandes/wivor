<?php

namespace Tests\Feature;

use Tests\TestCase;

class AboutPageTest extends TestCase
{
    public function test_about_page_shows_mission_values_and_contact_call_to_action(): void
    {
        config(['face_recognition.enabled' => true]);

        $this->get('/about')
            ->assertOk()
            ->assertSee('Every athlete deserves their photos.')
            ->assertSee('using face and bib number recognition, and we take care of payments along the way.')
            ->assertSeeInOrder(['Our values', 'Simple', 'Fair', 'Professional'])
            ->assertSee('href="'.route('contact_us').'"', false)
            ->assertSee('Get in touch');
    }

    public function test_about_page_omits_face_recognition_when_face_search_is_disabled(): void
    {
        config(['face_recognition.enabled' => false]);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('using bib number recognition, and we take care of payments along the way.')
            ->assertDontSee('face recognition')
            ->assertDontSee('face and bib');
    }

    public function test_old_about_us_address_permanently_redirects_to_about(): void
    {
        $this->get('/about-us')->assertStatus(301)->assertRedirect('/about');
    }
}
