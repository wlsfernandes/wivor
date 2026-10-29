<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Evidence of what a contact agreed to receive, for which scope, and when it was revoked.
 *
 * @property int $id
 * @property int $crm_contact_id
 * @property string $category
 * @property int|null $scope_event_id
 * @property string $status
 * @property string $source
 * @property string|null $evidence
 * @property string|null $policy_version
 * @property \Illuminate\Support\Carbon $recorded_at
 * @property \Illuminate\Support\Carbon|null $revoked_at
 */
class CrmContactConsent extends Model
{
    public const CATEGORY_OPERATIONAL = 'operational';
    public const CATEGORY_MARKETING = 'marketing';

    public const STATUS_GRANTED = 'granted';
    public const STATUS_UNKNOWN = 'unknown';
    public const STATUS_REVOKED = 'revoked';

    /** @var list<string> */
    protected $fillable = [
        'category',
        'scope_event_id',
        'status',
        'source',
        'evidence',
        'policy_version',
        'recorded_at',
        'revoked_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'recorded_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CrmContact::class, 'crm_contact_id');
    }

    /** Event the consent is limited to; null means WiVor-wide. */
    public function scopedEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'scope_event_id');
    }
}
