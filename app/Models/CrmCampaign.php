<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An email sent to an audience of CRM contacts, from draft through scheduling and delivery.
 *
 * @property int $id
 * @property string $title
 * @property string $category
 * @property string $audience_type
 * @property string $status
 * @property int|null $crm_template_id
 * @property int|null $user_id
 * @property string $subject
 * @property string|null $preview_text
 * @property string $body_html
 * @property string|null $text_body
 * @property string|null $cta_label
 * @property string|null $cta_url
 * @property \Illuminate\Support\Carbon|null $scheduled_at
 * @property int $intended_count
 * @property int $ineligible_count
 */
class CrmCampaign extends Model
{
    public const CATEGORY_EVENT_NOTIFICATION = 'event_notification';
    public const CATEGORY_PROMOTIONAL = 'promotional';
    public const CATEGORY_NEWSLETTER = 'newsletter';

    public const CATEGORIES = [
        self::CATEGORY_EVENT_NOTIFICATION => 'Event / photo notification',
        self::CATEGORY_PROMOTIONAL => 'Promotion',
        self::CATEGORY_NEWSLETTER => 'Newsletter',
    ];

    /** Administrators enter and read send times in WiVor's home time zone (EST/EDT). */
    public const SCHEDULE_TIMEZONE = 'America/New_York';

    public const AUDIENCE_EVENTS = 'events';
    public const AUDIENCE_DIRECTORY = 'directory';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_SENDING = 'sending';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';

    /** @var list<string> */
    protected $fillable = [
        'title',
        'category',
        'audience_type',
        'crm_template_id',
        'user_id',
        'subject',
        'preview_text',
        'body_html',
        'text_body',
        'cta_label',
        'cta_url',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    /** Consent category whose permissions and opt-outs govern this campaign. */
    public function consentCategory(): string
    {
        return $this->category === self::CATEGORY_EVENT_NOTIFICATION
            ? CrmContactConsent::CATEGORY_OPERATIONAL
            : CrmContactConsent::CATEGORY_MARKETING;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'crm_campaign_events')->withTimestamps();
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CrmCampaignRecipient::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CrmTemplate::class, 'crm_template_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
