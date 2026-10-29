<?php

namespace App\Services;

use App\Models\CrmCampaign;
use App\Models\CrmContact;
use App\Models\CrmContactConsent;
use App\Models\CrmSuppression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Resolve a campaign's audience to deduplicated contacts and apply consent and suppression rules.
 *
 * Event notifications reach selected events' participants unless they opted out.
 * Promotions and newsletters additionally require granted marketing consent whose scope
 * is WiVor-wide or one of the selected events.
 */
class CrmAudienceBuilder
{
    /** Contacts the audience selects before eligibility rules; each appears once. */
    public function candidates(CrmCampaign $campaign): Builder
    {
        $query = CrmContact::query();

        if ($campaign->audience_type === CrmCampaign::AUDIENCE_EVENTS) {
            $eventIds = $this->eventIds($campaign);
            $query->whereExists(fn (QueryBuilder $participation) => $participation
                ->from('crm_event_contacts')
                ->whereColumn('crm_event_contacts.crm_contact_id', 'crm_contacts.id')
                ->whereIn('crm_event_contacts.event_id', $eventIds));
        }

        return $query;
    }

    public function eligibleContacts(CrmCampaign $campaign): Builder
    {
        $category = $campaign->consentCategory();
        $eventIds = $this->eventIds($campaign);

        $query = $this->candidates($campaign)
            ->whereNotExists(fn (QueryBuilder $suppression) => $suppression
                ->from('crm_suppressions')
                ->whereColumn('crm_suppressions.email_normalized', 'crm_contacts.email_normalized')
                ->whereIn('crm_suppressions.category', [$category, CrmSuppression::CATEGORY_ALL])
                ->where(fn (QueryBuilder $scope) => $scope
                    ->whereNull('crm_suppressions.scope_event_id')
                    ->orWhereIn('crm_suppressions.scope_event_id', $eventIds)))
            ->whereNotExists(fn (QueryBuilder $revocation) => $revocation
                ->from('crm_contact_consents')
                ->whereColumn('crm_contact_consents.crm_contact_id', 'crm_contacts.id')
                ->where('crm_contact_consents.category', $category)
                ->where(fn (QueryBuilder $revoked) => $revoked
                    ->where('crm_contact_consents.status', CrmContactConsent::STATUS_REVOKED)
                    ->orWhereNotNull('crm_contact_consents.revoked_at')));

        if ($category === CrmContactConsent::CATEGORY_MARKETING) {
            $query->whereExists(fn (QueryBuilder $grant) => $grant
                ->from('crm_contact_consents')
                ->whereColumn('crm_contact_consents.crm_contact_id', 'crm_contacts.id')
                ->where('crm_contact_consents.category', CrmContactConsent::CATEGORY_MARKETING)
                ->where('crm_contact_consents.status', CrmContactConsent::STATUS_GRANTED)
                ->whereNull('crm_contact_consents.revoked_at')
                ->where(fn (QueryBuilder $scope) => $scope
                    ->whereNull('crm_contact_consents.scope_event_id')
                    ->orWhereIn('crm_contact_consents.scope_event_id', $eventIds)));
        }

        return $query;
    }

    /** @return array{candidates: int, eligible: int, ineligible: int} */
    public function counts(CrmCampaign $campaign): array
    {
        $candidateCount = $this->candidates($campaign)->count();
        $eligibleCount = $this->eligibleContacts($campaign)->count();

        return [
            'candidates' => $candidateCount,
            'eligible' => $eligibleCount,
            'ineligible' => $candidateCount - $eligibleCount,
        ];
    }

    /** @return list<int> */
    private function eventIds(CrmCampaign $campaign): array
    {
        return $campaign->audience_type === CrmCampaign::AUDIENCE_EVENTS
            ? $campaign->events()->pluck('events.id')->all()
            : [];
    }
}
