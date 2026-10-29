<?php

namespace Tests\Feature;

use App\Exceptions\CrmCampaignStateException;
use App\Models\CrmCampaign;
use App\Models\CrmCampaignRecipient;
use App\Models\CrmContact;
use App\Models\CrmSuppression;
use App\Models\Event;
use App\Services\CrmCampaignService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmCampaignSchedulingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['crm.postal_address' => '1 Finish Line Rd, Atlanta, GA 30301']);
    }

    public function test_scheduling_snapshots_eligible_recipients_with_their_event(): void
    {
        $event = $this->event('River Run');
        $this->participant('ana@example.com', $event);
        $this->participant('bo@example.com', $event);
        $this->participant('blocked@example.com', $event);
        CrmSuppression::create(['email_normalized' => 'blocked@example.com', 'reason' => CrmSuppression::REASON_BOUNCE, 'category' => CrmSuppression::CATEGORY_ALL]);
        $campaign = $this->campaign([$event]);
        $sendAt = now()->addHour();

        app(CrmCampaignService::class)->schedule($campaign, $sendAt);

        $campaign->refresh();
        $this->assertSame(CrmCampaign::STATUS_SCHEDULED, $campaign->status);
        $this->assertSame($sendAt->toDateTimeString(), $campaign->scheduled_at->toDateTimeString());
        $this->assertSame(2, $campaign->intended_count);
        $this->assertSame(1, $campaign->ineligible_count);
        $this->assertSame([$event->id, $event->id], $campaign->recipients()->pluck('event_id')->all());
        $this->assertSame([CrmCampaignRecipient::STATUS_PENDING], $campaign->recipients()->distinct()->pluck('status')->all());
    }

    public function test_multi_event_snapshot_has_one_row_per_person_and_no_event_context(): void
    {
        $riverRun = $this->event('River Run');
        $cityMarathon = $this->event('City Marathon');
        $this->participant('ana@example.com', $riverRun)->events()->attach($cityMarathon);
        $campaign = $this->campaign([$riverRun, $cityMarathon]);

        app(CrmCampaignService::class)->schedule($campaign, now()->addHour());

        $this->assertSame(1, $campaign->recipients()->count());
        $this->assertNull($campaign->recipients()->value('event_id'));
    }

    public function test_scheduling_is_blocked_until_a_postal_address_is_configured(): void
    {
        config(['crm.postal_address' => '']);
        $event = $this->event('River Run');
        $this->participant('ana@example.com', $event);

        $this->expectException(CrmCampaignStateException::class);
        $this->expectExceptionMessage('Set CRM_POSTAL_ADDRESS');

        app(CrmCampaignService::class)->schedule($this->campaign([$event]), now()->addHour());
    }

    public function test_scheduling_requires_a_future_time(): void
    {
        $event = $this->event('River Run');
        $this->participant('ana@example.com', $event);

        $this->expectExceptionMessage('Choose a send time in the future.');

        app(CrmCampaignService::class)->schedule($this->campaign([$event]), now()->subMinute());
    }

    public function test_scheduling_requires_at_least_one_eligible_recipient(): void
    {
        $this->expectExceptionMessage('No contacts in this audience are eligible to receive this campaign.');

        app(CrmCampaignService::class)->schedule($this->campaign([$this->event('River Run')]), now()->addHour());
    }

    public function test_event_placeholders_block_scheduling_for_several_events(): void
    {
        $riverRun = $this->event('River Run');
        $this->participant('ana@example.com', $riverRun);
        $campaign = $this->campaign([$riverRun, $this->event('City Marathon')]);
        $campaign->update(['body_html' => '<p>Your bib {{bib_number}}</p>']);

        $this->expectExceptionMessage('{{bib_number}} can only be used when the campaign targets exactly one event.');

        app(CrmCampaignService::class)->schedule($campaign, now()->addHour());
    }

    public function test_only_drafts_can_be_scheduled(): void
    {
        $event = $this->event('River Run');
        $this->participant('ana@example.com', $event);
        $campaign = $this->campaign([$event]);
        $service = app(CrmCampaignService::class);
        $service->schedule($campaign, now()->addHour());

        $this->expectExceptionMessage('Only draft campaigns can be scheduled.');

        $service->schedule($campaign->refresh(), now()->addHours(2));
    }

    public function test_unscheduling_returns_to_draft_and_discards_the_snapshot(): void
    {
        $event = $this->event('River Run');
        $this->participant('ana@example.com', $event);
        $campaign = $this->campaign([$event]);
        $service = app(CrmCampaignService::class);
        $service->schedule($campaign, now()->addHour());

        $service->unschedule($campaign->refresh());

        $campaign->refresh();
        $this->assertSame(CrmCampaign::STATUS_DRAFT, $campaign->status);
        $this->assertNull($campaign->scheduled_at);
        $this->assertSame(0, $campaign->recipients()->count());
    }

    public function test_cancel_stops_draft_or_scheduled_campaigns_only(): void
    {
        $campaign = $this->campaign([$this->event('River Run')]);
        $service = app(CrmCampaignService::class);

        $service->cancel($campaign);
        $this->assertSame(CrmCampaign::STATUS_CANCELLED, $campaign->refresh()->status);

        $this->expectExceptionMessage('This campaign can no longer be cancelled.');
        $service->cancel($campaign);
    }

    /** @param list<Event> $events */
    private function campaign(array $events): CrmCampaign
    {
        $campaign = CrmCampaign::create([
            'title' => 'Photos live',
            'category' => CrmCampaign::CATEGORY_EVENT_NOTIFICATION,
            'audience_type' => CrmCampaign::AUDIENCE_EVENTS,
            'subject' => 'Your photos are live',
            'body_html' => '<p>Hello {{first_name}}</p>',
        ]);
        $campaign->events()->attach(array_map(fn (Event $event): int => $event->id, $events));

        return $campaign->refresh();
    }

    private function participant(string $email, Event $event): CrmContact
    {
        $contact = CrmContact::create(['email_normalized' => $email, 'email_original' => $email]);
        $contact->events()->attach($event);

        return $contact;
    }

    private function event(string $title): Event
    {
        return Event::create(['title' => $title, 'slug' => Event::generateUniqueSlug($title), 'content' => 'CRM fixture.']);
    }
}
