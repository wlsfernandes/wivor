<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\UserAndRoleSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminUserCredentialSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_created_user_receives_a_secure_password_setup_link_and_keeps_the_selected_role(): void
    {
        Notification::fake();

        [$admin, $employeeRole] = $this->adminAndEmployeeRole();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'New Employee',
                'email' => 'new-employee@example.com',
                'password' => 'request-supplied-password',
                'role_ids' => $employeeRole->id,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $user = User::where('email', 'new-employee@example.com')->firstOrFail();

        $this->assertFalse(Hash::check('request-supplied-password', $user->password));
        $this->assertTrue($user->roles()->whereKey($employeeRole->id)->exists());
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_created_user_can_choose_a_password_with_the_setup_link_and_then_authenticate(): void
    {
        Notification::fake();

        [$admin, $employeeRole] = $this->adminAndEmployeeRole();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Setup Employee',
            'email' => 'setup-employee@example.com',
            'role_ids' => $employeeRole->id,
        ]);

        $user = User::where('email', 'setup-employee@example.com')->firstOrFail();
        $token = null;

        Notification::assertSentTo(
            $user,
            ResetPassword::class,
            function (ResetPassword $notification) use (&$token): bool {
                $token = $notification->token;

                return true;
            }
        );

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertSessionHasNoErrors();

        $this->post(route('logout'));
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'new-secure-password',
        ]);

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_user_and_role_seeder_creates_the_admin_role_without_creating_a_user(): void
    {
        $this->seed(UserAndRoleSeeder::class);

        $this->assertDatabaseHas('roles', ['id' => 1, 'name' => 'admin']);
        $this->assertDatabaseCount('users', 0);
    }

    /** @return array{User, Role} */
    private function adminAndEmployeeRole(): array
    {
        $adminRole = Role::create(['name' => 'admin']);
        $employeeRole = Role::create(['name' => 'employee']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        return [$admin, $employeeRole];
    }
}
