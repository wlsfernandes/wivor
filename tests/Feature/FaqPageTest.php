<?php

namespace Tests\Feature;

use Tests\TestCase;

class FaqPageTest extends TestCase
{
    public function test_faq_lists_the_eight_specified_questions_in_order_with_face_search_enabled(): void
    {
        config(['face_recognition.enabled' => true]);

        $this->get(route('faq'))
            ->assertOk()
            ->assertSeeInOrder([
                'Is WiVor really free?',
                'How does WiVor find my photos?',
                'Do I have to buy photos?',
                'How do I receive my photos?',
                "What if I can't find my photos?",
                'How is my face data used and protected?',
                'How do photographers get paid?',
                'How can my event partner with WiVor?',
            ], false)
            ->assertSee('Enter your bib number, upload a selfie, or both.')
            ->assertSee('href="'.route('biometric-notice').'"', false);
    }

    public function test_faq_hides_face_data_question_when_face_search_is_disabled(): void
    {
        config(['face_recognition.enabled' => false]);

        $this->get(route('faq'))
            ->assertOk()
            ->assertSee('We use bib number recognition. Enter your bib number.')
            ->assertDontSee('How is my face data used and protected?')
            ->assertDontSee('selfie');
    }

    public function test_faq_uses_configured_support_email_and_shows_no_unfilled_placeholders(): void
    {
        config(['contact.email' => 'help@example.test']);

        $this->get(route('faq'))
            ->assertOk()
            ->assertSee('href="mailto:help@example.test"', false)
            ->assertDontSee('[support email]')
            ->assertDontSee('[Confirm')
            ->assertDontSee('[Insert');
    }
}
