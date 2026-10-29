<?php

namespace Tests\Feature;

use App\Models\CrmCampaign;
use App\Models\CrmContact;
use App\Models\CrmContactConsent;
use App\Models\CrmSuppression;
use App\Models\Event;
use App\Services\CrmAudienceBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmAudienceBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_notification_reaches_participants_with_unknown_consent(): void
    {
        $event = $this->event('River Run');
        $this->participant('ana@example.com', $event);
        $this->contact('outsider@example.com');

        $this->assertSame(['ana@example.com'], $this->eligibleEmails($this->campaign(CrmCampaign::CATEGORY_EVENT_NOTIFICATION, [$event])));
    }

    public function test_participant_in_several_selected_events_is_counted_once(): void
    {
        $riverRun = $this->event('River Run');
        $cityMarathon = $this->event('City Marathon');
        $ana = $this->participant('ana@example.com', $riverRun);
        $ana->events()->attach($cityMarathon);

        $counts = app(CrmAudienceBuilder::class)->counts($this->campaign(CrmCampaign::CATEGORY_EVENT_NOTIFICATION, [$riverRun, $cityMarathon]));

        $this->assertSame(['candidates' => 1, 'eligible' => 1, 'ineligible' => 0], $counts);
    }

    public function test_global_suppression_blocks_event_notifications(): void
    {
        $event = $this->event('River Run');
        $this->participant('ana@example.com', $event);
        $this->suppress('ana@example.com', CrmSuppression::CATEGORY_ALL);

        $this->assertSame([], $this->eligibleEmails($this->campaign(CrmCampaign::CATEGORY_EVENT_NOTIFICATION, [$event])));
    }

    public function test_operational_opt_out_blocks_event_notifications(): void
    {
        $event = $this->event('River Run');
        $this->participant('ana@example.com', $event);
        $this->suppress('ana@example.com', CrmContactConsent::CATEGORY_OPERATIONAL);

        $this->assertSame([], $this->eligibleEmails($this->campaign(CrmCampaign::CATEGORY_EVENT_NOTIFICATION, [$event])));
    }

    public function test_suppression_for_another_event_does_not_block_this_event(): void
    {
        $riverRun = $this->event('River Run');
        $cityMarathon = $this->event('City Marathon');
        $this->participant('ana@example.com', $riverRun);
        $this->suppress('ana@example.com', CrmContactConsent::CATEGORY_OPERATIONAL, $cityMarathon);

        $this->assertSame(['ana@example.com'], $this->eligibleEmails($this->campaign(CrmCampaign::CATEGORY_EVENT_NOTIFICATION, [$riverRun])));
    }

    public function test_marketing_requires_recorded_consent(): void
    {
        $event = $this->event('River Run');
        $this->participant('unknown@example.com', $event);
        $this->grantMarketing($this->participant('granted@example.com', $event));

        $this->assertSame(['granted@example.com'], $this->eligibleEmails($this->campaign(CrmCampaign::CATEGORY_PROMOTIONAL, [$event])));
    }

    public function test_revoked_marketing_consent_blocks_even_with_an_older_grant(): void
    {
        $contact = $this->contact('ana@example.com');
        $this->grantMarketing($contact);
        $contact->consents()->create([
            'category' => CrmContactConsent::CATEGORY_MARKETING,
            'status' => CrmContactConsent::STATUS_REVOKED,
            'source' => 'Preference page',
            'recorded_at' => now(),
            'revoked_at' => now(),
        ]);

        $this->assertSame([], $this->eligibleEmails($this->campaign(CrmCampaign::CATEGORY_NEWSLETTER, [])));
    }

    public function test_marketing_unsubscribe_blocks_newsletter(): void
    {
        $this->grantMarketing($this->contact('ana@example.com'));
        $this->suppress('ana@example.com', CrmContactConsent::CATEGORY_MARKETING);

        $this->assertSame([], $this->eligibleEmails($this->campaign(CrmCampaign::CATEGORY_NEWSLETTER, [])));
    }

    public function test_event_scoped_consent_does_not_cover_directory_newsletters(): void
    {
        $event = $this->event('River Run');
        $this->grantMarketing($this->contact('wivor@example.com'));
        $this->grantMarketing($this->participant('event-only@example.com', $event), $event);

        $this->assertSame(['wivor@example.com'], $this->eligibleEmails($this->campaign(CrmCampaign::CATEGORY_NEWSLETTER, [])));
        $this->assertSame(['event-only@example.com'], $this->eligibleEmails($this->campaign(CrmCampaign::CATEGORY_PROMOTIONAL, [$event])));
    }

    public function test_counts_report_ineligible_candidates(): void
    {
        $event = $this->event('River Run');
        $this->participant('unknown@example.com', $event);
        $this->grantMarketing($this->participant('granted@example.com', $event));

        $counts = app(CrmAudienceBuilder::class)->counts($this->campaign(CrmCampaign::CATEGORY_PROMOTIONAL, [$event]));

        $this->assertSame(['candidates' => 2, 'eligible' => 1, 'ineligible' => 1], $counts);
    }

    /** @return list<string> */
    private function eligibleEmails(CrmCampaign $campaign): array
    {
        return app(CrmAudienceBuilder::class)->eligibleContacts($campaign)->orderBy('email_normalized')->pluck('email_normalized')->all();
    }

    /** @param list<Event> $events */
    private function campaign(string $category, array $events): CrmCampaign
    {
        $campaign = CrmCampaign::create([
            'title' => 'Test',
            'category' => $category,
            'audience_type' => $events === [] ? CrmCampaign::AUDIENCE_DIRECTORY : CrmCampaign::AUDIENCE_EVENTS,
            'subject' => 'Hello',
            'body_html' => '<p>Hello</p>',
        ]);
        $campaign->events()->attach(array_map(fn (Event $event): int => $event->id, $events));

        return $campaign;
    }

    private function grantMarketing(CrmContact $contact, ?Event $scopeEvent = null): void
    {
        $contact->consents()->create([
            'category' => CrmContactConsent::CATEGORY_MARKETING,
            'scope_event_id' => $scopeEvent?->id,
            'status' => CrmContactConsent::STATUS_GRANTED,
            'source' => 'Signed form',
            'recorded_at' => now()->subDay(),
        ]);
    }

    private function suppress(string $email, string $category, ?Event $scopeEvent = null): void
    {
        CrmSuppression::create([
            'email_normalized' => $email,
            'reason' => CrmSuppression::REASON_MANUAL,
            'category' => $category,
            'scope_event_id' => $scopeEvent?->id,
        ]);
    }

    private function participant(string $email, Event $event): CrmContact
    {
        $contact = $this->contact($email);
        $contact->events()->attach($event);

        return $contact;
    }

    private function contact(string $email): CrmContact
    {
        return CrmContact::create(['email_normalized' => $email, 'email_original' => $email]);
    }

    private function event(string $title): Event
    {
        return Event::create(['title' => $title, 'slug' => Event::generateUniqueSlug($title), 'content' => 'CRM fixture.']);
    }
}
