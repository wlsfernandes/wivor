<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTurnstileTest extends TestCase
{
    private const TURNSTILE_VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    private const TURNSTILE_FAILED_MESSAGE = 'Security check failed or expired. Please complete it again and resubmit.';

    public function test_contact_page_renders_one_turnstile_widget_and_loads_the_script_once(): void
    {
        config(['services.turnstile.site_key' => 'test-site-key']);

        $html = $this->get(route('contact_us'))
            ->assertOk()
            ->assertSee('data-sitekey="test-site-key"', false)
            ->getContent();

        $this->assertSame(1, substr_count($html, '<div data-turnstile-widget'));
        $this->assertSame(1, substr_count($html, 'challenges.cloudflare.com/turnstile/v0/api.js'));
        $this->assertStringNotContainsString('g-recaptcha-response', $html);
    }

    public function test_photographer_page_renders_both_widgets_but_loads_the_script_once(): void
    {
        $html = $this->get(route('photographers'))->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, '<div data-turnstile-widget'));
        $this->assertSame(1, substr_count($html, 'challenges.cloudflare.com/turnstile/v0/api.js'));
    }

    public function test_contact_message_is_sent_after_cloudflare_confirms_the_token(): void
    {
        Mail::fake();
        Http::fake([self::TURNSTILE_VERIFY_URL => Http::response(['success' => true])]);

        $this->from(route('contact_us'))
            ->post(route('contact.send'), $this->validMessage())
            ->assertRedirect(route('contact_us'))
            ->assertSessionHasNoErrors();

        Mail::assertSent(ContactMessage::class);
        Http::assertSent(fn (Request $request): bool => $request['response'] === 'turnstile-token');
    }

    public function test_contact_message_without_a_token_is_rejected_without_calling_cloudflare(): void
    {
        Mail::fake();
        Http::fake();
        $message = $this->validMessage();
        unset($message['cf-turnstile-response']);

        $this->from(route('contact_us'))
            ->post(route('contact.send'), $message)
            ->assertSessionHasErrors(['cf-turnstile-response' => 'Please complete the security check before submitting.']);

        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    public function test_contact_message_with_an_invalid_or_reused_token_is_rejected(): void
    {
        Http::fake([self::TURNSTILE_VERIFY_URL => Http::response(['success' => false, 'error-codes' => ['timeout-or-duplicate']])]);

        $this->assertContactMessageRejectedByTurnstile();
    }

    public function test_contact_message_is_rejected_when_cloudflare_is_unreachable(): void
    {
        Http::fake(fn () => throw new ConnectionException('Connection timed out'));

        $this->assertContactMessageRejectedByTurnstile();
    }

    public function test_contact_message_keeps_the_users_input_after_a_failed_security_check(): void
    {
        Http::fake([self::TURNSTILE_VERIFY_URL => Http::response(['success' => false])]);

        $this->from(route('contact_us'))
            ->post(route('contact.send'), $this->validMessage())
            ->assertSessionHasInput('message', 'I need help finding my purchase.');
    }

    public function test_failed_contact_check_on_the_photographer_page_shows_only_under_the_contact_form(): void
    {
        Http::fake([self::TURNSTILE_VERIFY_URL => Http::response(['success' => false])]);

        $this->from(route('photographers'))
            ->post(route('contact.send'), $this->validMessage() + ['turnstile_form' => 'contact']);

        $html = $this->get(route('photographers'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, self::TURNSTILE_FAILED_MESSAGE.'</div>'));
        $this->assertGreaterThan(
            strpos($html, 'action="'.route('registerPhotographer').'"'),
            strpos($html, self::TURNSTILE_FAILED_MESSAGE.'</div>'),
        );
    }

    public function test_contact_form_is_throttled_after_five_messages_per_minute_from_one_ip(): void
    {
        Mail::fake();
        Http::fake([self::TURNSTILE_VERIFY_URL => Http::response(['success' => true])]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('contact.send'), $this->validMessage())->assertSessionHasNoErrors();
        }

        $this->post(route('contact.send'), $this->validMessage())->assertTooManyRequests();
        Mail::assertSentCount(5);
    }

    public function test_contact_form_throttle_is_counted_per_ip(): void
    {
        Mail::fake();
        Http::fake([self::TURNSTILE_VERIFY_URL => Http::response(['success' => true])]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('contact.send'), $this->validMessage());
        }

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->post(route('contact.send'), $this->validMessage())
            ->assertSessionHasNoErrors();
    }

    /** @return array<string, string> */
    private function validMessage(): array
    {
        return [
            'username' => 'Jamie Customer',
            'email' => 'jamie@example.test',
            'phone' => '4045551212',
            'message' => 'I need help finding my purchase.',
            'cf-turnstile-response' => 'turnstile-token',
        ];
    }

    private function assertContactMessageRejectedByTurnstile(): void
    {
        Mail::fake();

        $this->from(route('contact_us'))
            ->post(route('contact.send'), $this->validMessage())
            ->assertRedirect(route('contact_us'))
            ->assertSessionHasErrors(['cf-turnstile-response' => self::TURNSTILE_FAILED_MESSAGE]);

        Mail::assertNothingSent();
    }
}
