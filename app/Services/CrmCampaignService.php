<?php

namespace App\Services;

use App\Exceptions\CrmCampaignStateException;
use App\Mail\CrmCampaignMail;
use App\Models\CrmCampaign;
use App\Models\CrmCampaignRecipient;
use App\Models\Event;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Move campaigns through their lifecycle and take the recipient snapshot when one is scheduled.
 */
class CrmCampaignService
{
    private const SNAPSHOT_CHUNK_SIZE = 1000;

    private const TEST_SUBJECT_PREFIX = '[TEST] ';

    /** Fields an administrator may set on a draft. */
    private const DRAFT_FIELDS = ['title', 'category', 'audience_type', 'crm_template_id', 'subject', 'preview_text', 'body_html', 'text_body', 'cta_label', 'cta_url'];

    /** Statuses from which a campaign may still be cancelled before Phase 4 adds sending. */
    private const CANCELLABLE_STATUSES = [CrmCampaign::STATUS_DRAFT, CrmCampaign::STATUS_SCHEDULED, CrmCampaign::STATUS_PAUSED];

    public function __construct(
        private readonly CrmAudienceBuilder $audienceBuilder,
        private readonly CrmCampaignContentValidator $contentValidator,
        private readonly CrmCampaignRenderer $renderer,
    ) {
    }

    /** @param array<string, mixed> $attributes validated draft fields plus optional event_ids */
    public function createDraft(array $attributes, User $author): CrmCampaign
    {
        return DB::transaction(function () use ($attributes, $author): CrmCampaign {
            $campaign = CrmCampaign::create(array_merge(
                array_intersect_key($attributes, array_flip(self::DRAFT_FIELDS)),
                ['user_id' => $author->id]
            ));
            $this->syncEvents($campaign, $attributes);

            return $campaign;
        });
    }

    /**
     * @param array<string, mixed> $attributes validated draft fields plus optional event_ids
     *
     * @throws CrmCampaignStateException
     */
    public function updateDraft(CrmCampaign $campaign, array $attributes): void
    {
        $this->assertDraft($campaign, 'Only draft campaigns can be edited.');

        DB::transaction(function () use ($campaign, $attributes): void {
            $campaign->update(array_merge(
                array_fill_keys(self::DRAFT_FIELDS, null),
                array_intersect_key($attributes, array_flip(self::DRAFT_FIELDS))
            ));
            $this->syncEvents($campaign, $attributes);
        });
    }

    /** @throws CrmCampaignStateException */
    public function deleteDraft(CrmCampaign $campaign): void
    {
        $this->assertDraft($campaign, 'Only draft campaigns can be deleted.');

        $campaign->delete();
    }

    /** Queue one copy to the signed-in administrator; never touches the recipient snapshot. */
    public function sendTest(CrmCampaign $campaign, User $administrator): void
    {
        $email = $this->renderer->render($campaign, CrmRecipientValues::sample($administrator, $this->eventContext($campaign)), null);

        Mail::to($administrator->email)->queue(new CrmCampaignMail(new CrmRenderedEmail(
            self::TEST_SUBJECT_PREFIX.$email->subject,
            $email->html,
            $email->text
        )));

        Log::info('CRM campaign test email queued.', [
            'event' => 'crm.campaign.test_sent',
            'crm_campaign_id' => $campaign->id,
            'user_id' => $administrator->id,
        ]);
    }

    /** The single selected event used for personalization, or null for multi-event and directory audiences. */
    public function eventContext(CrmCampaign $campaign): ?Event
    {
        if ($campaign->audience_type !== CrmCampaign::AUDIENCE_EVENTS) {
            return null;
        }

        $events = $campaign->events()->limit(2)->get();

        return $events->count() === 1 ? $events->first() : null;
    }

    /** @throws CrmCampaignStateException */
    public function schedule(CrmCampaign $campaign, CarbonInterface $sendAt): void
    {
        $this->assertDraft($campaign, 'Only draft campaigns can be scheduled.');

        if (blank(config('crm.postal_address'))) {
            throw new CrmCampaignStateException('Set CRM_POSTAL_ADDRESS in the environment before scheduling campaigns.');
        }

        if ($sendAt->isPast()) {
            throw new CrmCampaignStateException('Choose a send time in the future.');
        }

        $contentErrors = $this->contentValidator->campaignErrors($campaign);

        if ($contentErrors !== []) {
            throw new CrmCampaignStateException($contentErrors[0]);
        }

        DB::transaction(function () use ($campaign, $sendAt): void {
            $lockedCampaign = CrmCampaign::query()->lockForUpdate()->findOrFail($campaign->id);
            $this->assertDraft($lockedCampaign, 'Only draft campaigns can be scheduled.');

            $counts = $this->audienceBuilder->counts($lockedCampaign);

            if ($counts['eligible'] === 0) {
                throw new CrmCampaignStateException('No contacts in this audience are eligible to receive this campaign.');
            }

            $this->snapshotRecipients($lockedCampaign);

            $lockedCampaign->forceFill([
                'status' => CrmCampaign::STATUS_SCHEDULED,
                'scheduled_at' => $sendAt->copy()->setTimezone(config('app.timezone')),
                'intended_count' => $lockedCampaign->recipients()->count(),
                'ineligible_count' => $counts['ineligible'],
            ])->save();
        });

        Log::info('CRM campaign scheduled.', ['event' => 'crm.campaign.scheduled', 'crm_campaign_id' => $campaign->id]);
    }

    /** @throws CrmCampaignStateException */
    public function unschedule(CrmCampaign $campaign): void
    {
        DB::transaction(function () use ($campaign): void {
            $lockedCampaign = CrmCampaign::query()->lockForUpdate()->findOrFail($campaign->id);

            if ($lockedCampaign->status !== CrmCampaign::STATUS_SCHEDULED) {
                throw new CrmCampaignStateException('Only scheduled campaigns can be moved back to draft.');
            }

            $lockedCampaign->recipients()->delete();
            $lockedCampaign->forceFill([
                'status' => CrmCampaign::STATUS_DRAFT,
                'scheduled_at' => null,
                'intended_count' => 0,
                'ineligible_count' => 0,
            ])->save();
        });
    }

    /** @throws CrmCampaignStateException */
    public function cancel(CrmCampaign $campaign): void
    {
        $wasCancelled = CrmCampaign::query()
            ->whereKey($campaign->id)
            ->whereIn('status', self::CANCELLABLE_STATUSES)
            ->update(['status' => CrmCampaign::STATUS_CANCELLED]);

        if ($wasCancelled === 0) {
            throw new CrmCampaignStateException('This campaign can no longer be cancelled.');
        }

        Log::info('CRM campaign cancelled.', ['event' => 'crm.campaign.cancelled', 'crm_campaign_id' => $campaign->id]);
    }

    private function snapshotRecipients(CrmCampaign $campaign): void
    {
        $eventContextId = $this->eventContext($campaign)?->id;
        $now = now();

        $this->audienceBuilder->eligibleContacts($campaign)
            ->select('crm_contacts.id')
            ->chunkById(self::SNAPSHOT_CHUNK_SIZE, function ($contacts) use ($campaign, $eventContextId, $now): void {
                CrmCampaignRecipient::query()->insert($contacts->map(fn ($contact): array => [
                    'crm_campaign_id' => $campaign->id,
                    'crm_contact_id' => $contact->id,
                    'event_id' => $eventContextId,
                    'status' => CrmCampaignRecipient::STATUS_PENDING,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all());
            }, 'crm_contacts.id', 'id');
    }

    /** @param array<string, mixed> $attributes */
    private function syncEvents(CrmCampaign $campaign, array $attributes): void
    {
        $campaign->events()->sync(
            $campaign->audience_type === CrmCampaign::AUDIENCE_EVENTS ? ($attributes['event_ids'] ?? []) : []
        );
    }

    private function assertDraft(CrmCampaign $campaign, string $message): void
    {
        if (! $campaign->isDraft()) {
            throw new CrmCampaignStateException($message);
        }
    }
}
