<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicPolicyPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_pages_have_real_mvp_aligned_fallback_content_without_database_rows(): void
    {
        $pages = [
            'privacy' => [
                'heading' => 'Privacy Policy',
                'content' => 'WivorPhotos does not intentionally save the search selfie',
            ],
            'terms' => [
                'heading' => 'Terms of Use',
                'content' => 'purchase available photographs as guests',
            ],
            'refund-policy' => [
                'heading' => 'Refund Policy',
                'content' => 'does not provide an automated in-app refund-request',
            ],
            'photographer-terms' => [
                'heading' => 'Photographer Terms',
                'content' => 'it does not currently promise an automatic transfer after each sale',
            ],
        ];

        foreach ($pages as $routeName => $content) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee($content['heading'])
                ->assertSee($content['content'])
                ->assertSee('Last updated: September 12, 2026')
                ->assertDontSee('Policy content pending');
        }
    }

    public function test_public_footer_contains_policy_removal_and_contact_links(): void
    {
        $response = $this->get(route('privacy'))->assertOk();

        foreach (['privacy', 'terms', 'refund-policy', 'photographer-terms', 'photo-removal.create', 'contact_us'] as $routeName) {
            $response->assertSee('href="'.route($routeName).'"', false);
        }
    }

    public function test_public_header_keeps_the_customer_navigation_simple_and_searches_events(): void
    {
        $this->get(route('privacy'))
            ->assertOk()
            ->assertSeeInOrder([
                'Home',
                'Find Events',
                'Purchases &amp; Downloads',
                'For Photographers',
                'Login',
            ], false)
            ->assertSee('action="'.route('events.listEvents').'"', false)
            ->assertSee('name="search"', false);
    }

    public function test_contact_page_uses_configured_support_address_and_existing_form(): void
    {
        config(['contact.email' => 'support@example.test']);

        $this->get(route('contact_us'))
            ->assertOk()
            ->assertSee('Contact WivorPhotos')
            ->assertSee('support@example.test')
            ->assertSee('action="'.route('contact.send').'"', false);
    }

    public function test_contact_form_sends_to_configured_support_address(): void
    {
        Mail::fake();
        config(['contact.email' => 'support@example.test']);

        $this->from(route('contact_us'))->post(route('contact.send'), [
            'username' => 'Jamie Customer',
            'email' => 'jamie@example.test',
            'phone' => '4045551212',
            'message' => 'I need help finding my purchase.',
        ])->assertRedirect(route('contact_us'));

        Mail::assertSent(ContactMessage::class, fn (ContactMessage $message): bool => $message->hasTo('support@example.test'));
    }
}
