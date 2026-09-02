<?php

namespace Tests\Feature;

use App\Mail\PhotographerAccountStatusChanged;
use App\Mail\PhotographerApplicationApproved;
use App\Mail\PhotographerApplicationReceived;
use App\Models\Photographer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PhotographerApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_photographer_registers_with_their_own_password_and_starts_pending(): void
    {
        Mail::fake();
        Notification::fake();

        $response = $this->post(route('registerPhotographer'), $this->validApplication());

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();

        $user = User::where('email', 'alex@example.com')->firstOrFail();
        $photographer = $user->photographer;

        $this->assertTrue(Hash::check('secure-pass', $user->password));
        $this->assertTrue($user->hasRole('photographer'));
        $this->assertSame(Photographer::STATUS_PENDING, $photographer->status);
        $this->assertNotNull($photographer->age_confirmed_at);
        $this->assertNotNull($photographer->terms_accepted_at);
        Notification::assertSentTo($user, VerifyEmail::class);
        Mail::assertSent(PhotographerApplicationReceived::class, fn ($mail) => $mail->hasTo($user->email));
    }

    public function test_registration_requires_the_complete_application_and_consents(): void
    {
        $this->post(route('registerPhotographer'), [])
            ->assertSessionHasErrors([
                'first_name',
                'last_name',
                'email',
                'password',
                'phone',
                'city',
                'state',
                'profile_url',
                'camera_model',
                'about',
                'is_adult',
                'accepts_terms',
            ]);
    }

    public function test_only_approved_and_verified_photographers_can_open_the_dashboard(): void
    {
        [$pendingUser, $pendingPhotographer] = $this->createPhotographer(Photographer::STATUS_PENDING);

        $this->actingAs($pendingUser)
            ->get(route('photographer.dashboard'))
            ->assertForbidden();

        $pendingPhotographer->forceFill(['status' => Photographer::STATUS_APPROVED])->save();
        $this->actingAs($pendingUser)
            ->get(route('photographer.dashboard'))
            ->assertOk();

        $pendingUser->forceFill(['email_verified_at' => null])->save();
        $this->actingAs($pendingUser)
            ->get(route('photographer.dashboard'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_pending_photographer_can_view_application_status(): void
    {
        [$user] = $this->createPhotographer(Photographer::STATUS_PENDING);

        $this->actingAs($user)
            ->get(route('photographer.application-status'))
            ->assertOk()
            ->assertSee('Pending review')
            ->assertSee('application is under review');
    }

    public function test_admin_can_approve_a_photographer_and_sends_welcome_email(): void
    {
        Mail::fake();
        [$admin] = $this->createAdmin();
        [$photographerUser, $photographer] = $this->createPhotographer(Photographer::STATUS_PENDING);

        $this->actingAs($admin)
            ->patch(route('admin.photographers.approve', $photographer))
            ->assertRedirect();

        $photographer->refresh();
        $this->assertSame(Photographer::STATUS_APPROVED, $photographer->status);
        $this->assertSame($admin->id, $photographer->reviewed_by);
        $this->assertNotNull($photographer->reviewed_at);
        Mail::assertSent(PhotographerApplicationApproved::class, fn ($mail) => $mail->hasTo($photographerUser->email));
    }

    public function test_admin_can_review_the_application_details(): void
    {
        [$admin] = $this->createAdmin();
        [, $photographer] = $this->createPhotographer(Photographer::STATUS_PENDING);

        $this->actingAs($admin)
            ->get(route('photographers.list'))
            ->assertOk()
            ->assertSee('Test Photographer')
            ->assertSee('Pending review');

        $this->actingAs($admin)
            ->get(route('admin.photographers.show', $photographer))
            ->assertOk()
            ->assertSee('Sony A1')
            ->assertSee('Open portfolio')
            ->assertSee('Approve photographer');
    }

    public function test_admin_can_decline_and_suspend_with_internal_reasons(): void
    {
        Mail::fake();
        [$admin] = $this->createAdmin();
        [$declinedUser, $pending] = $this->createPhotographer(Photographer::STATUS_PENDING);

        $this->actingAs($admin)
            ->patch(route('admin.photographers.decline', $pending), ['reason' => 'Portfolio does not meet pilot needs.'])
            ->assertRedirect();

        $this->assertSame(Photographer::STATUS_DECLINED, $pending->refresh()->status);
        $this->assertSame('Portfolio does not meet pilot needs.', $pending->status_reason);
        Mail::assertSent(PhotographerAccountStatusChanged::class, fn ($mail) => $mail->hasTo($declinedUser->email));

        [, $approved] = $this->createPhotographer(Photographer::STATUS_APPROVED);
        $this->actingAs($admin)
            ->patch(route('admin.photographers.suspend', $approved), ['reason' => 'Marketplace policy review.'])
            ->assertRedirect();

        $this->assertSame(Photographer::STATUS_SUSPENDED, $approved->refresh()->status);
    }

    public function test_admin_can_restore_a_suspended_photographer(): void
    {
        Mail::fake();
        [$admin] = $this->createAdmin();
        [, $photographer] = $this->createPhotographer(Photographer::STATUS_SUSPENDED);
        $photographer->forceFill(['status_reason' => 'Prior review'])->save();

        $this->actingAs($admin)
            ->patch(route('admin.photographers.restore', $photographer))
            ->assertRedirect();

        $photographer->refresh();
        $this->assertSame(Photographer::STATUS_APPROVED, $photographer->status);
        $this->assertNull($photographer->status_reason);
    }

    public function test_non_admin_cannot_review_photographers(): void
    {
        [$user] = $this->createPhotographer(Photographer::STATUS_APPROVED);
        [, $photographer] = $this->createPhotographer(Photographer::STATUS_PENDING);

        $this->actingAs($user)
            ->patch(route('admin.photographers.approve', $photographer))
            ->assertForbidden();
    }

    /** @return array<string, mixed> */
    private function validApplication(): array
    {
        return [
            'first_name' => 'Alex',
            'last_name' => 'Morgan',
            'email' => 'alex@example.com',
            'password' => 'secure-pass',
            'password_confirmation' => 'secure-pass',
            'phone' => '407-555-0100',
            'city' => 'Orlando',
            'state' => 'FL',
            'profile_url' => 'https://instagram.com/alexphotos',
            'camera_model' => 'Canon R6 with 70-200mm lens',
            'about' => 'Professional sports and community event photographer.',
            'is_adult' => '1',
            'accepts_terms' => '1',
        ];
    }

    /** @return array{User, Photographer} */
    private function createPhotographer(string $status): array
    {
        $role = Role::firstOrCreate(['name' => 'photographer']);
        $user = User::factory()->create();
        $user->roles()->attach($role);
        $photographer = Photographer::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'Photographer',
            'phone' => '407-555-0100',
            'city' => 'Orlando',
            'state' => 'FL',
            'profile_url' => 'https://example.com/portfolio',
            'camera_model' => 'Sony A1',
            'about' => 'Sports photographer.',
        ]);
        $photographer->forceFill(['status' => $status])->save();

        return [$user, $photographer];
    }

    /** @return array{User, Role} */
    private function createAdmin(): array
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return [$user, $role];
    }
}
