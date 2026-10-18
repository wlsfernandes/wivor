<?php

namespace Tests\Feature;

use App\Models\Photographer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendTruthAlignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_directs_customers_to_guest_browsing_and_keeps_photographer_access(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('action="'.route('login').'"', false)
            ->assertSee('No customer account is required')
            ->assertSee('href="'.route('events.listEvents').'"', false)
            ->assertSee('href="'.route('photographers').'#register_section"', false)
            ->assertDontSee('multiple shipping address')
            ->assertDontSee('action="'.route('signUp').'"', false);
    }

    public function test_legacy_customer_signup_page_explains_guest_checkout_without_exposing_registration(): void
    {
        $this->get(route('signUp'))
            ->assertOk()
            ->assertSee('No customer account is required')
            ->assertSee('Browse Event Photos')
            ->assertSee('Apply as a Photographer')
            ->assertDontSee('action="'.route('registerUser').'"', false);
    }

    public function test_homepage_shows_bib_only_matching_without_face_or_video_marketing_when_face_search_is_disabled(): void
    {
        config(['face_recognition.enabled' => false]);

        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee('WiVor uses bib number recognition to match you with the photos photographers took of you at your event.')
            ->assertSee('Bib number recognition finds your photos automatically.')
            ->assertSee('Our platform identifies athletes using bib numbers.')
            ->assertSee('Search by event name or bib number.')
            ->assertDontSee('Search by your name')
            ->assertDontSee('selfie')
            ->assertDontSee('face recognition')
            ->assertDontSee('Video Editing')
            ->assertDontSee('video marketplace');
    }

    public function test_photographer_application_and_truthful_payout_language_remain_visible(): void
    {
        $this->get(route('photographers'))
            ->assertOk()
            ->assertSee('action="'.route('registerPhotographer').'"', false)
            ->assertSee('Sales, commission, earnings, and payout-status reporting.')
            ->assertSee('complete Stripe account setup')
            ->assertDontSee('automatically paid');
    }

    public function test_authenticated_footer_uses_wivorphotos_branding(): void
    {
        $adminRole = Role::create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('WivorPhotos. All rights reserved.')
            ->assertDontSee('devpromaster');
    }

    public function test_photographer_dashboard_distinguishes_payout_readiness_from_automatic_transfers(): void
    {
        $role = Role::create(['name' => 'photographer']);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->attach($role);
        $photographer = Photographer::create([
            'user_id' => $user->id,
            'first_name' => 'Taylor',
            'last_name' => 'Photographer',
        ]);
        $photographer->forceFill([
            'status' => Photographer::STATUS_APPROVED,
            'stripe_onboarding_status' => Photographer::STRIPE_READY,
        ])->save();

        $this->actingAs($user)
            ->get(route('photographer.dashboard'))
            ->assertOk()
            ->assertSee('WivorPhotos tracks transfers separately.')
            ->assertSee('do not automatically send a transfer after each sale.');
    }

    public function test_authenticated_legacy_view_does_not_advertise_video_services(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/apagar')
            ->assertOk()
            ->assertDontSee('Video Editing');
    }
}
