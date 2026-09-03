<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Represents a photographer profile and its marketplace approval state.
 *
 * @property int $id
 * @property int $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $status
 * @property string|null $status_reason
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property \Illuminate\Support\Carbon|null $age_confirmed_at
 * @property \Illuminate\Support\Carbon|null $terms_accepted_at
 * @property string $stripe_onboarding_status
 * @property-read User $user
 * @property-read string $full_name
 */
class Photographer extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_SUSPENDED = 'suspended';

    public const STRIPE_NOT_STARTED = 'not_started';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'camera_model',
        'profile_url',
        'about',
        'address',
        'city',
        'state',
        'zipcode',
        'age_confirmed_at',
        'terms_accepted_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'reviewed_at' => 'datetime',
        'age_confirmed_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
    ];

    /** Return the user account that authenticates this photographer. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Return events associated with this photographer. */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class)
            ->withPivot(['id', 'status', 'upload_deadline_at', 'rights_confirmed_at'])
            ->withTimestamps();
    }

    public function photos(): HasMany { return $this->hasMany(Photo::class); }
    public function uploadBatches(): HasMany { return $this->hasMany(UploadBatch::class); }

    protected static function booted(): void
    {
        static::creating(fn (self $photographer) => $photographer->uuid ??= (string) Str::uuid());
    }

    /** Return the public byline used for photo credits. */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /** Return the administrator who last reviewed the application. */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** Determine whether marketplace access has been approved. */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /** Return a display-ready label for the current application state. */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_DECLINED => 'Declined',
            self::STATUS_SUSPENDED => 'Suspended',
            default => 'Pending review',
        };
    }
}
