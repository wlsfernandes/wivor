<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\WebsitePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsitePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_all_website_policy_editors_from_the_admin_menu(): void
    {
        $admin = $this->createAdmin();

        foreach (WebsitePolicy::TITLES as $policy => $title) {
            $this->actingAs($admin)
                ->get(route('admin.website.policies.edit', ['policy' => $policy]))
                ->assertOk()
                ->assertSee('Website')
                ->assertSee('Privacy Policy')
                ->assertSee('Terms of Use')
                ->assertSee('Refund Policy')
                ->assertSee($title)
                ->assertSee('name="content"', false);
        }
    }

    public function test_admin_can_save_policy_text_and_the_public_page_displays_it_safely(): void
    {
        $admin = $this->createAdmin();
        $content = "Your privacy matters.\n\n<script>alert('unsafe')</script>";

        $this->actingAs($admin)
            ->put(route('admin.website.policies.update', ['policy' => WebsitePolicy::PRIVACY]), [
                'content' => $content,
            ])
            ->assertRedirect(route('admin.website.policies.edit', ['policy' => WebsitePolicy::PRIVACY]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('website_policies', [
            'slug' => WebsitePolicy::PRIVACY,
            'content' => $content,
        ]);

        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee('Your privacy matters.')
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee("<script>alert('unsafe')</script>", false);
    }

    public function test_policy_text_is_required(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->put(route('admin.website.policies.update', ['policy' => WebsitePolicy::TERMS]), [
                'content' => '',
            ])
            ->assertSessionHasErrors('content');

        $this->assertDatabaseCount('website_policies', 0);
    }

    public function test_public_policy_page_keeps_its_placeholder_until_content_is_saved(): void
    {
        $this->get(route('refund-policy'))
            ->assertOk()
            ->assertSee('Policy content pending')
            ->assertSee('WivorPhotos is preparing its full Refund Policy.');
    }

    public function test_non_admin_cannot_manage_website_policies(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.website.policies.edit', ['policy' => WebsitePolicy::REFUND]))
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('admin.website.policies.update', ['policy' => WebsitePolicy::REFUND]), [
                'content' => 'Refund terms.',
            ])
            ->assertForbidden();
    }

    public function test_unknown_policy_cannot_be_managed(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.website.policies.edit', ['policy' => 'unknown']))
            ->assertNotFound();
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

        return $admin;
    }
}
