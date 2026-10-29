<?php

namespace Tests\Feature;

use App\Models\CrmContact;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_crm_dashboard_counts(): void
    {
        $event = Event::create(['title' => 'River Run', 'slug' => 'river-run', 'content' => 'CRM fixture.']);
        $first = CrmContact::create(['email_normalized' => 'a@example.com', 'email_original' => 'a@example.com']);
        CrmContact::create(['email_normalized' => 'b@example.com', 'email_original' => 'b@example.com']);
        $first->events()->attach($event, ['bib_number' => '1']);

        $this->actingAs($this->admin())
            ->get(route('admin.crm.dashboard'))
            ->assertOk()
            ->assertViewHas('summary', [
                'contacts' => 2,
                'linkedEvents' => 1,
                'imports' => 0,
            ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.crm.dashboard'))->assertRedirect(route('login'));
    }

    public function test_non_admin_user_is_forbidden(): void
    {
        /** @var User $photographer */
        $photographer = User::factory()->createOne();
        $photographer->roles()->attach(Role::create(['name' => 'photographer']));

        $this->actingAs($photographer)
            ->get(route('admin.crm.dashboard'))
            ->assertForbidden();
    }

    public function test_crm_menu_link_is_shown_to_admins(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertSee(route('admin.crm.dashboard'), false);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin']));

        return $admin;
    }
}
