<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\WebsitePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BiometricNoticePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_biometric_notice_shows_fallback_content_when_no_admin_text_exists(): void
    {
        $this->get('/biometric-notice')
            ->assertOk()
            ->assertSee('Biometric Notice')
            ->assertSee('WivorPhotos does not intentionally save the search selfie')
            ->assertSee('href="'.route('privacy').'"', false)
            ->assertDontSee('Policy content pending');
    }

    public function test_biometric_notice_shows_admin_saved_content_instead_of_fallback(): void
    {
        WebsitePolicy::create([
            'slug' => WebsitePolicy::BIOMETRIC_NOTICE,
            'content' => 'Lawyer approved biometric text.',
        ]);

        $this->get(route('biometric-notice'))
            ->assertOk()
            ->assertSee('Lawyer approved biometric text.')
            ->assertDontSee('WivorPhotos does not intentionally save the search selfie');
    }

    public function test_admin_can_open_the_biometric_notice_editor(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

        $this->actingAs($admin)
            ->get(route('admin.website.policies.edit', ['policy' => WebsitePolicy::BIOMETRIC_NOTICE]))
            ->assertOk()
            ->assertSee('Biometric Notice')
            ->assertSee('name="content"', false);
    }
}
