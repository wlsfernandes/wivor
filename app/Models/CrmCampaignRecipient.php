<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One contact in a campaign's recipient snapshot, with the event used to personalize the message.
 *
 * @property int $id
 * @property int $crm_campaign_id
 * @property int $crm_contact_id
 * @property int|null $event_id
 * @property string $status
 * @property int $attempt_count
 */
class CrmCampaignRecipient extends Model
{
    public const STATUS_PENDING = 'pending';

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(CrmCampaign::class, 'crm_campaign_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CrmContact::class, 'crm_contact_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
