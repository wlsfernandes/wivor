<?php

namespace Tests\Feature;

use App\Mail\CrmCampaignMail;
use App\Models\CrmCampaign;
use App\Models\CrmContact;
use App\Models\CrmTemplate;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CrmCampaignHttpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['crm.postal_address' => '1 Finish Line Rd, Atlanta, GA 30301']);
    }

    public function test_admin_creates_an_event_notification_with_sanitized_body(): void
    {
        $event = $this->event();

        $response = $this->actingAs($this->admin())->post(route('admin.crm.campaigns.store'), $this->payload($event, [
            'body_html' => '<p>Hi {{first_name}}</p><script>steal()</script>',
        ]));

        $campaign = CrmCampaign::sole();
        $response->assertRedirect(route('admin.crm.campaigns.show', $campaign));
        $this->assertSame('<p>Hi {{first_name}}</p>', $campaign->body_html);
        $this->assertSame(CrmCampaign::STATUS_DRAFT, $campaign->status);
        $this->assertSame([$event->id], $campaign->events()->pluck('events.id')->all());
    }

    public function test_event_notifications_cannot_target_the_whole_directory(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.crm.campaigns.store'), $this->payload($this->event(), ['audience_type' => CrmCampaign::AUDIENCE_DIRECTORY]))
            ->assertSessionHasErrors('audience_type');
    }

    public function test_unknown_placeholders_are_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.crm.campaigns.store'), $this->payload($this->event(), ['subject' => 'Your {{password}}']))
            ->assertSessionHasErrors(['content' => '{{password}} is not an available placeholder.']);
    }

    public function test_body_links_must_be_absolute_or_placeholders(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.crm.campaigns.store'), $this->payload($this->event(), ['body_html' => '<p><a href="/events">Events</a></p>']))
            ->assertSessionHasErrors(['content' => 'The link "/events" must be a full https:// address or a placeholder such as {{event_url}}.']);
    }

    public function test_campaign_show_page_reports_eligible_audience(): void
    {
        $event = $this->event();
        $this->participant('ana@example.com', $event);
        $campaign = $this->draft($event);

        $this->actingAs($this->admin())
            ->get(route('admin.crm.campaigns.show', $campaign))
            ->assertOk()
            ->assertViewHas('counts', ['candidates' => 1, 'eligible' => 1, 'ineligible' => 0]);
    }

    public function test_preview_renders_sandboxed_desktop_and_mobile_frames(): void
    {
        $campaign = $this->draft($this->event());

        $this->actingAs($this->admin(name: 'Wilson Admin'))
            ->get(route('admin.crm.campaigns.preview', $campaign))
            ->assertOk()
            ->assertSee('sandbox=""', false)
            ->assertSee('width: 375px', false)
            ->assertSee('Hi Wilson', false);
    }

    public function test_test_send_goes_only_to_the_signed_in_admin(): void
    {
        Mail::fake();
        $event = $this->event();
        $this->participant('ana@example.com', $event);
        $campaign = $this->draft($event);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.crm.campaigns.test-send', $campaign))
            ->assertRedirect(route('admin.crm.campaigns.show', $campaign))
            ->assertSessionHas('success');

        Mail::assertQueued(CrmCampaignMail::class, fn (CrmCampaignMail $mail): bool => $mail->hasTo($admin->email)
            && str_starts_with($mail->email->subject, '[TEST] '));
        Mail::assertQueuedCount(1);
        $this->assertSame(0, $campaign->recipients()->count());
        $this->assertSame(CrmCampaign::STATUS_DRAFT, $campaign->refresh()->status);
    }

    public function test_admin_schedules_unschedules_and_cancels(): void
    {
        $event = $this->event();
        $this->participant('ana@example.com', $event);
        $campaign = $this->draft($event);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.crm.campaigns.schedule', $campaign), ['scheduled_at' => now()->addDay()->format('Y-m-d\TH:i')])
            ->assertRedirect(route('admin.crm.campaigns.show', $campaign));
        $this->assertSame(CrmCampaign::STATUS_SCHEDULED, $campaign->refresh()->status);
        $this->assertSame(1, $campaign->recipients()->count());

        $this->actingAs($admin)->post(route('admin.crm.campaigns.unschedule', $campaign));
        $this->assertSame(CrmCampaign::STATUS_DRAFT, $campaign->refresh()->status);

        $this->actingAs($admin)->post(route('admin.crm.campaigns.cancel', $campaign));
        $this->assertSame(CrmCampaign::STATUS_CANCELLED, $campaign->refresh()->status);
    }

    public function test_send_time_is_entered_and_shown_in_eastern_time(): void
    {
        $event = $this->event();
        $this->participant('ana@example.com', $event);
        $campaign = $this->draft($event);
        $admin = $this->admin();
        $nextJanuary = now('America/New_York')->addYear()->startOfYear()->addDays(14)->format('Y-m-d');

        $this->actingAs($admin)
            ->get(route('admin.crm.campaigns.show', $campaign))
            ->assertSee('Send at (Eastern time)', false);

        $this->actingAs($admin)
            ->post(route('admin.crm.campaigns.schedule', $campaign), ['scheduled_at' => $nextJanuary.'T09:00'])
            ->assertRedirect(route('admin.crm.campaigns.show', $campaign));

        $this->assertSame($nextJanuary.' 14:00:00', $campaign->refresh()->scheduled_at->utc()->format('Y-m-d H:i:s'));
        $this->actingAs($admin)
            ->get(route('admin.crm.campaigns.show', $campaign))
            ->assertSee('9:00 AM EST');
    }

    public function test_scheduling_without_postal_address_shows_the_reason(): void
    {
        config(['crm.postal_address' => null]);
        $event = $this->event();
        $this->participant('ana@example.com', $event);
        $campaign = $this->draft($event);

        $this->actingAs($this->admin())
            ->post(route('admin.crm.campaigns.schedule', $campaign), ['scheduled_at' => now()->addDay()->format('Y-m-d\TH:i')])
            ->assertSessionHasErrors(['campaign' => 'Set CRM_POSTAL_ADDRESS in the environment before scheduling campaigns.']);
    }

    public function test_scheduled_campaign_cannot_be_edited(): void
    {
        $event = $this->event();
        $this->participant('ana@example.com', $event);
        $campaign = $this->draft($event);
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.crm.campaigns.schedule', $campaign), ['scheduled_at' => now()->addDay()->format('Y-m-d\TH:i')]);

        $this->actingAs($admin)
            ->put(route('admin.crm.campaigns.update', $campaign), $this->payload($event, ['subject' => 'Changed']))
            ->assertSessionHasErrors('campaign');

        $this->assertSame('Your photos are live', $campaign->refresh()->subject);
    }

    public function test_template_can_be_created_and_used_to_start_a_campaign(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.crm.templates.store'), [
            'name' => 'Photos live',
            'subject' => 'Your {{event_name}} photos are live',
            'body_html' => '<p>Hi {{first_name}}</p><img src="javascript:x">',
            'cta_label' => 'See photos',
            'cta_url' => '{{photo_gallery_url}}',
        ])->assertRedirect(route('admin.crm.templates.index'));

        $template = CrmTemplate::sole();
        $this->assertSame('<p>Hi {{first_name}}</p><img />', $template->body_html);

        $this->actingAs($admin)
            ->get(route('admin.crm.campaigns.create', ['template' => $template->id]))
            ->assertOk()
            ->assertSee('Your {{event_name}} photos are live', false);
    }

    public function test_event_pages_link_to_emailing_participants(): void
    {
        $event = $this->event();

        $this->actingAs($this->admin())
            ->get(route('admin.media.show', $event))
            ->assertSee(route('admin.crm.campaigns.create', ['event_id' => $event->id]), false);
    }

    public function test_non_admin_cannot_reach_campaign_or_template_routes(): void
    {
        $campaign = $this->draft($this->event());
        $template = CrmTemplate::create(['name' => 'T', 'subject' => 'S', 'body_html' => '<p>B</p>']);
        /** @var User $user */
        $user = User::factory()->createOne();

        $this->actingAs($user);
        $this->get(route('admin.crm.campaigns.index'))->assertForbidden();
        $this->get(route('admin.crm.campaigns.create'))->assertForbidden();
        $this->post(route('admin.crm.campaigns.store'), [])->assertForbidden();
        $this->get(route('admin.crm.campaigns.show', $campaign))->assertForbidden();
        $this->get(route('admin.crm.campaigns.edit', $campaign))->assertForbidden();
        $this->put(route('admin.crm.campaigns.update', $campaign), [])->assertForbidden();
        $this->delete(route('admin.crm.campaigns.destroy', $campaign))->assertForbidden();
        $this->get(route('admin.crm.campaigns.preview', $campaign))->assertForbidden();
        $this->post(route('admin.crm.campaigns.test-send', $campaign))->assertForbidden();
        $this->post(route('admin.crm.campaigns.schedule', $campaign))->assertForbidden();
        $this->post(route('admin.crm.campaigns.unschedule', $campaign))->assertForbidden();
        $this->post(route('admin.crm.campaigns.cancel', $campaign))->assertForbidden();
        $this->get(route('admin.crm.templates.index'))->assertForbidden();
        $this->post(route('admin.crm.templates.store'), [])->assertForbidden();
        $this->get(route('admin.crm.templates.edit', $template))->assertForbidden();
        $this->put(route('admin.crm.templates.update', $template), [])->assertForbidden();
        $this->delete(route('admin.crm.templates.destroy', $template))->assertForbidden();
    }

    public function test_campaign_mail_has_plain_text_alternative_and_configured_sender(): void
    {
        config(['crm.from_address' => 'news@wivorphotos.com', 'crm.from_name' => 'WiVor News']);
        $campaign = $this->draft($this->event());
        $mail = new CrmCampaignMail(app(\App\Services\CrmCampaignRenderer::class)->render($campaign, ['first_name' => 'Ana'], null));

        $mail->assertFrom('news@wivorphotos.com', 'WiVor News');
        $mail->assertHasSubject('Your photos are live');
        $mail->assertSeeInText('Hi Ana');
        $mail->assertSeeInHtml('Hi Ana');
    }

    public function test_admin_can_open_campaign_and_template_pages(): void
    {
        $campaign = $this->draft($this->event());
        $template = CrmTemplate::create(['name' => 'Photos live', 'subject' => 'S', 'body_html' => '<p>B</p>']);
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.crm.campaigns.index'))->assertOk()->assertSee('River Run photos live');
        $this->actingAs($admin)->get(route('admin.crm.campaigns.edit', $campaign))->assertOk()->assertSee('assets/libs/quill/quill.min.js', false);
        $this->actingAs($admin)->get(route('admin.crm.templates.index'))->assertOk()->assertSee('Photos live');
        $this->actingAs($admin)->get(route('admin.crm.templates.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.crm.templates.edit', $template))->assertOk();
    }

    public function test_transactional_emails_use_the_configured_postal_address(): void
    {
        config(['crm.postal_address' => "382 Scarlet Rose Way\nHoschton, GA 30548"]);

        $html = view('emails.contact', ['data' => ['username' => 'Ana', 'email' => 'ana@example.com', 'phone' => '1', 'message' => 'Hi']])->render();

        $this->assertStringContainsString('382 Scarlet Rose Way<br />', $html);
        $this->assertStringContainsString('Hoschton, GA 30548', $html);
        $this->assertStringNotContainsString('Decatur', $html);
    }


    /** @param array<string, mixed> $overrides */
    private function payload(Event $event, array $overrides = []): array
    {
        return array_merge([
            'title' => 'River Run photos live',
            'category' => CrmCampaign::CATEGORY_EVENT_NOTIFICATION,
            'audience_type' => CrmCampaign::AUDIENCE_EVENTS,
            'event_ids' => [$event->id],
            'subject' => 'Your photos are live',
            'preview_text' => 'Find yourself in the gallery',
            'body_html' => '<p>Hi {{first_name}}</p>',
        ], $overrides);
    }

    private function draft(Event $event): CrmCampaign
    {
        $campaign = CrmCampaign::create([
            'title' => 'River Run photos live',
            'category' => CrmCampaign::CATEGORY_EVENT_NOTIFICATION,
            'audience_type' => CrmCampaign::AUDIENCE_EVENTS,
            'subject' => 'Your photos are live',
            'body_html' => '<p>Hi {{first_name}}</p>',
        ]);
        $campaign->events()->attach($event);

        return $campaign->refresh();
    }

    private function participant(string $email, Event $event): CrmContact
    {
        $contact = CrmContact::create(['email_normalized' => $email, 'email_original' => $email]);
        $contact->events()->attach($event);

        return $contact;
    }

    private function event(): Event
    {
        return Event::create([
            'title' => 'River Run',
            'slug' => Event::generateUniqueSlug('River Run'),
            'sport' => 'Running',
            'status' => Event::STATUS_DRAFT,
            'date_of_event' => '2026-10-10',
            'timezone' => 'America/New_York',
            'city' => 'Orlando',
            'state' => 'FL',
            'country_code' => 'US',
            'content' => 'CRM fixture.',
        ]);
    }

    private function admin(string $name = 'Admin User'): User
    {
        /** @var User $admin */
        $admin = User::factory()->createOne(['name' => $name]);
        $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

        return $admin;
    }
}
