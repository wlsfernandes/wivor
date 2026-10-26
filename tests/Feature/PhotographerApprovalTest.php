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
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PhotographerApprovalTest extends TestCase
{
    use RefreshDatabase;

    private const TURNSTILE_VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function test_photographer_registration_form_links_to_distinct_photographer_terms(): void
    {
        $this->get(route('photographers'))
            ->assertOk()
            ->assertSee('href="'.route('photographer-terms').'"', false)
            ->assertSee('WivorPhotos Photographer Terms');
    }

    public function test_photographer_registers_with_their_own_password_and_starts_pending(): void
    {
        Mail::fake();
        Notification::fake();
        $this->fakeTurnstileResponse(['success' => true]);

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
                'cf-turnstile-response',
            ]);
    }

    public function test_photographer_registration_form_renders_the_turnstile_widget(): void
    {
        config(['services.turnstile.site_key' => 'test-site-key']);

        $this->get(route('photographers'))
            ->assertOk()
            ->assertSee('data-turnstile-widget', false)
            ->assertSee('data-sitekey="test-site-key"', false)
            ->assertSee('https://challenges.cloudflare.com/turnstile/v0/api.js', false);
    }

    public function test_registration_sends_the_secret_token_and_ip_to_cloudflare(): void
    {
        Mail::fake();
        Notification::fake();
        config(['services.turnstile.secret_key' => 'test-secret-key']);
        $this->fakeTurnstileResponse(['success' => true]);

        $this->post(route('registerPhotographer'), $this->validApplication())
            ->assertRedirect(route('verification.notice'));

        Http::assertSent(fn (Request $request): bool => $request->url() === self::TURNSTILE_VERIFY_URL
            && $request['secret'] === 'test-secret-key'
            && $request['response'] === 'turnstile-token'
            && $request['remoteip'] === '127.0.0.1');
    }

    public function test_registration_without_a_turnstile_token_is_rejected_without_calling_cloudflare(): void
    {
        Http::fake();
        $application = $this->validApplication();
        unset($application['cf-turnstile-response']);

        $this->post(route('registerPhotographer'), $application)
            ->assertSessionHasErrors(['cf-turnstile-response' => 'Please complete the security check before submitting.']);

        Http::assertNothingSent();
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'alex@example.com']);
    }

    public function test_registration_with_an_invalid_turnstile_token_is_rejected(): void
    {
        $this->fakeTurnstileResponse(['success' => false, 'error-codes' => ['invalid-input-response']]);

        $this->assertRegistrationRejectedByTurnstile();
    }

    public function test_registration_with_an_expired_or_reused_turnstile_token_is_rejected(): void
    {
        $this->fakeTurnstileResponse(['success' => false, 'error-codes' => ['timeout-or-duplicate']]);

        $this->assertRegistrationRejectedByTurnstile();
    }

    public function test_registration_is_rejected_when_cloudflare_is_unreachable(): void
    {
        Http::fake(fn () => throw new ConnectionException('Connection timed out'));

        $this->assertRegistrationRejectedByTurnstile();
    }

    public function test_registration_is_rejected_when_cloudflare_returns_a_server_error(): void
    {
        $this->fakeTurnstileResponse(['success' => true], 500);

        $this->assertRegistrationRejectedByTurnstile();
    }

    public function test_registration_is_rejected_when_cloudflare_returns_an_unexpected_body(): void
    {
        Http::fake([self::TURNSTILE_VERIFY_URL => Http::response('<html>bad gateway</html>', 200)]);

        $this->assertRegistrationRejectedByTurnstile();
    }

    public function test_registration_is_rejected_when_the_turnstile_secret_is_not_configured(): void
    {
        config(['services.turnstile.secret_key' => null]);
        Http::fake();

        $this->assertRegistrationRejectedByTurnstile();
        Http::assertNothingSent();
    }

    public function test_registration_is_throttled_after_five_attempts_per_minute_from_one_ip(): void
    {
        Http::fake();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('registerPhotographer'), [])->assertSessionHasErrors('email');
        }

        $this->post(route('registerPhotographer'), [])->assertTooManyRequests();
    }

    public function test_registration_throttle_is_counted_per_ip(): void
    {
        Http::fake();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('registerPhotographer'), []);
        }

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->post(route('registerPhotographer'), [])
            ->assertSessionHasErrors('email');
    }

    public function test_invalid_application_fields_still_report_their_own_errors_with_a_valid_token(): void
    {
        $this->fakeTurnstileResponse(['success' => true]);
        $application = array_merge($this->validApplication(), ['email' => 'not-an-email', 'state' => 'Florida']);

        $this->post(route('registerPhotographer'), $application)
            ->assertSessionHasErrors(['email', 'state'])
            ->assertSessionDoesntHaveErrors('cf-turnstile-response');
    }

    public function test_only_approved_and_verified_photographers_can_open_the_dashboard(): void
    {
        [$pendingUser, $pendingPhotographer] = $this->createPhotographer(Photographer::STATUS_PENDING);

        $this->actingAs($pendingUser)
            ->get(route('photographer.dashboard'))
            ->assertForbidden();

        $pendingPhotographer->forceFill(['status' => Photographer::STATUS_APPROVED])->save();
        $dashboard = $this->actingAs($pendingUser)
            ->get(route('photographer.dashboard'))
            ->assertOk()
            ->assertSee('id="photographer-sidebar"', false)
            ->assertSee('data-photographer-sidebar-toggle', false)
            ->assertDontSee('My events')
            ->assertSee('href="'.route('events.index').'"', false)
            ->assertSee('href="'.route('how-to-upload-photos').'"', false)
            ->assertSee('href="'.route('photographer-terms').'"', false)
            ->assertSee('Upload Photos');

        $dashboard->assertSeeInOrder([
            'Browse galleries',
            'How to Upload Photos',
        ]);

        $this->get(route('how-to-upload-photos'))
            ->assertOk()
            ->assertSee('id="photographer-sidebar"', false)
            ->assertSee('class="active"', false);

        $pendingUser->forceFill(['email_verified_at' => null])->save();
        $this->actingAs($pendingUser)
            ->get(route('photographer.dashboard'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_photographer_login_ignores_a_stale_admin_intended_url(): void
    {
        [$user] = $this->createPhotographer(Photographer::STATUS_APPROVED);

        $this->withSession(['url.intended' => url('/index')])
            ->post(route('login'), [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('photographer.dashboard'));
    }

    public function test_photographer_login_still_honors_a_valid_intended_url(): void
    {
        [$user] = $this->createPhotographer(Photographer::STATUS_APPROVED);

        $this->withSession(['url.intended' => route('events.index')])
            ->post(route('login'), [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('events.index'));
    }

    public function test_admin_login_still_honors_an_intended_url(): void
    {
        [$admin] = $this->createAdmin();

        $this->withSession(['url.intended' => route('users.index')])
            ->post(route('login'), [
                'email' => $admin->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('users.index'));
    }

    public function test_pending_photographer_can_view_application_status(): void
    {
        [$user] = $this->createPhotographer(Photographer::STATUS_PENDING);

        $this->actingAs($user)
            ->get(route('photographer.application-status'))
            ->assertOk()
            ->assertSee('Pending review')
            ->assertSee('application is under review')
            ->assertSee('id="photographer-sidebar"', false)
            ->assertSee('Application status')
            ->assertDontSee('My events');
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

    public function test_admin_list_shows_an_active_red_trash_icon_only_for_pending_applications(): void
    {
        [$admin] = $this->createAdmin();
        [, $pendingPhotographer] = $this->createPhotographer(Photographer::STATUS_PENDING);
        [, $approvedPhotographer] = $this->createPhotographer(Photographer::STATUS_APPROVED);

        $this->actingAs($admin)
            ->get(route('photographers.list'))
            ->assertOk()
            ->assertSee('action="'.route('admin.photographers.destroy', $pendingPhotographer).'"', false)
            ->assertDontSee('action="'.route('admin.photographers.destroy', $approvedPhotographer).'"', false)
            ->assertSee('Only pending applications can be deleted');
    }

    public function test_admin_can_delete_a_pending_application_and_its_user_account(): void
    {
        [$admin] = $this->createAdmin();
        [$user, $photographer] = $this->createPhotographer(Photographer::STATUS_PENDING);

        $this->actingAs($admin)
            ->delete(route('admin.photographers.destroy', $photographer))
            ->assertRedirect(route('photographers.list'))
            ->assertSessionHas('success', 'Photographer application deleted.');

        $this->assertDatabaseMissing('photographers', ['id' => $photographer->id]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('role_user', ['user_id' => $user->id]);
    }

    public function test_admin_cannot_delete_an_application_that_is_not_pending(): void
    {
        [$admin] = $this->createAdmin();

        foreach ([Photographer::STATUS_APPROVED, Photographer::STATUS_DECLINED, Photographer::STATUS_SUSPENDED] as $status) {
            [$user, $photographer] = $this->createPhotographer($status);

            $this->actingAs($admin)
                ->delete(route('admin.photographers.destroy', $photographer))
                ->assertStatus(422);

            $this->assertDatabaseHas('photographers', ['id' => $photographer->id]);
            $this->assertDatabaseHas('users', ['id' => $user->id]);
        }
    }

    public function test_non_admin_cannot_delete_a_pending_application(): void
    {
        [$user] = $this->createPhotographer(Photographer::STATUS_APPROVED);
        [, $photographer] = $this->createPhotographer(Photographer::STATUS_PENDING);

        $this->actingAs($user)
            ->delete(route('admin.photographers.destroy', $photographer))
            ->assertForbidden();

        $this->assertDatabaseHas('photographers', ['id' => $photographer->id]);
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
            'cf-turnstile-response' => 'turnstile-token',
        ];
    }

    /** @param array<string, mixed> $body */
    private function fakeTurnstileResponse(array $body, int $status = 200): void
    {
        Http::fake([self::TURNSTILE_VERIFY_URL => Http::response($body, $status)]);
    }

    private function assertRegistrationRejectedByTurnstile(): void
    {
        Mail::fake();

        $this->post(route('registerPhotographer'), $this->validApplication())
            ->assertSessionHasErrors(['cf-turnstile-response' => 'Security check failed or expired. Please complete it again and resubmit.']);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'alex@example.com']);
        Mail::assertNothingSent();
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
