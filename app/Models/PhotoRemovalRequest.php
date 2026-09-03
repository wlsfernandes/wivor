<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a customer-submitted request to review or remove one photo.
 *
 * @property int $id
 * @property int $photo_id
 * @property string $requester_name
 * @property string $requester_email
 * @property string $reason
 * @property string|null $explanation
 * @property string $status
 * @property int|null $reviewed_by
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 */
class PhotoRemovalRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_REVIEWED = 'reviewed';

    public const REASON_DEPICTS_ME = 'depicts_me_without_consent';
    public const REASON_INAPPROPRIATE = 'inappropriate_content';
    public const REASON_COPYRIGHT = 'copyright_concern';
    public const REASON_INCORRECT_EVENT = 'incorrect_event';
    public const REASON_OTHER = 'other';

    protected $fillable = [
        'photo_id', 'requester_name', 'requester_email', 'reason', 'explanation',
        'status', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function photo(): BelongsTo { return $this->belongsTo(Photo::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }

    /** Return the fixed set of reasons customers may choose from. */
    public static function reasons(): array
    {
        return [
            self::REASON_DEPICTS_ME => 'This photo shows me and I did not consent to it',
            self::REASON_INAPPROPRIATE => 'Inappropriate content',
            self::REASON_COPYRIGHT => 'Copyright concern',
            self::REASON_INCORRECT_EVENT => 'This photo is not from this event',
            self::REASON_OTHER => 'Other',
        ];
    }

    /** Return a display-ready label for the selected reason. */
    public function getReasonLabelAttribute(): string
    {
        return self::reasons()[$this->reason] ?? 'Other';
    }

    /** Return a display-ready review status. */
    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }
}
