<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Blocks sending to an email address; kept by address so it survives contact deletion and reimports.
 *
 * @property int $id
 * @property int|null $crm_contact_id
 * @property string $email_normalized
 * @property string $reason
 * @property string $category
 * @property int|null $scope_event_id
 */
class CrmSuppression extends Model
{
    public const REASON_UNSUBSCRIBE = 'unsubscribe';
    public const REASON_BOUNCE = 'bounce';
    public const REASON_COMPLAINT = 'complaint';
    public const REASON_MANUAL = 'manual';

    /** Suppresses every category, used for bounces and spam complaints. */
    public const CATEGORY_ALL = 'all';

    /** @var list<string> */
    protected $fillable = [
        'crm_contact_id',
        'email_normalized',
        'reason',
        'category',
        'scope_event_id',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CrmContact::class, 'crm_contact_id');
    }
}
