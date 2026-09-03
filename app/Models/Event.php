<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Represents a sports event managed by one or more Wivor photographers.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $sport
 * @property bool $published
 * @property string $status
 * @property Carbon|null $published_at
 * @property Carbon|null $date_of_event
 * @property Carbon|null $starts_at
 * @property Carbon|null $photos_live_at
 * @property string $timezone
 * @property string|null $venue_name
 * @property string|null $city
 * @property string|null $state
 * @property string $country_code
 * @property string|null $image_url
 * @property string $cover_url
 * @property string $public_availability_label
 * @property string $date_label
 * @property string $location_label
 * @property string $status_label
 * @property bool $is_published
 * @property bool $is_archived
 * @property string $publish_action_label
 * @property string $sport_label
 * @property string $photos_live_label
 */
class Event extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUS_CANCELLED = 'cancelled';

    /** @var list<string> */
    protected $fillable = [
        'title',
        'slug',
        'sport',
        'published',
        'status',
        'published_at',
        'gallery_published_at',
        'sales_close_at',
        'price_cents',
        'date_of_event',
        'starts_at',
        'ends_at',
        'photos_live_at',
        'timezone',
        'venue_name',
        'city',
        'state',
        'country_code',
        'image_url',
        'file_url',
        'content',
        'summary',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'price_cents' => 'integer',
            'published_at' => 'datetime',
            'gallery_published_at' => 'datetime',
            'sales_close_at' => 'datetime',
            'retention_warning_14_sent_at' => 'datetime',
            'retention_warning_3_sent_at' => 'datetime',
            'date_of_event' => 'date',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'photos_live_at' => 'datetime',
        ];
    }

    /** Get photographers assigned to the event. */
    public function photographers(): BelongsToMany
    {
        return $this->belongsToMany(Photographer::class)
            ->withPivot(['id', 'status', 'upload_deadline_at', 'rights_confirmed_at'])
            ->withTimestamps();
    }

    public function photos(): HasMany { return $this->hasMany(Photo::class); }
    public function uploadBatches(): HasMany { return $this->hasMany(UploadBatch::class); }

    /** Return the event-specific deadline, falling back to 72 hours after its best-known end. */
    public function uploadDeadlineFor(Photographer $photographer): Carbon
    {
        $assignment = $this->photographers()->whereKey($photographer->id)->first()?->pivot;
        if ($assignment?->upload_deadline_at) {
            return Carbon::parse($assignment->upload_deadline_at);
        }

        $eventEnd = $this->ends_at
            ?? $this->starts_at
            ?? $this->date_of_event?->copy()->endOfDay()
            ?? $this->created_at;

        return $eventEnd->copy()->addHours(config('photo_uploads.upload_deadline_hours'));
    }

    protected static function booted(): void
    {
        static::creating(fn (self $event) => $event->uuid ??= (string) Str::uuid());
    }

    /** Limit a query to publicly available events. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /** Search public event fields using a single search term. */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search): void {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhere('sport', 'like', "%{$search}%");
        });
    }

    /** Generate a unique public slug for an event title. */
    public static function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title) ?: 'event';
        $slug = $baseSlug;
        $counter = 2;

        while (self::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /** Return the configured cover URL or the Wivor fallback image. */
    public function getCoverUrlAttribute(): string
    {
        if (blank($this->image_url)) {
            return asset('assets/images/events/1.jpg');
        }

        if (Str::startsWith($this->image_url, ['http://', 'https://'])) {
            return $this->image_url;
        }

        return Storage::disk(config('filesystems.event_covers'))->url($this->image_url);
    }

    /** Return the customer-facing photo availability state. */
    public function getPublicAvailabilityLabelAttribute(): string
    {
        if (! $this->photos_live_at) {
            return 'Event details available';
        }

        return $this->photos_live_at->isFuture()
            ? 'Photos coming soon'
            : 'Photos are live';
    }

    /** Return a display-ready event date. */
    public function getDateLabelAttribute(): string
    {
        return $this->date_of_event?->format('F j, Y') ?? 'Date to be announced';
    }

    /** Return a display-ready event location. */
    public function getLocationLabelAttribute(): string
    {
        return collect([$this->city, $this->state])->filter()->implode(', ')
            ?: 'Location to be announced';
    }

    /** Return a display-ready lifecycle label. */
    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }

    /** Indicate whether the event is currently public. */
    public function getIsPublishedAttribute(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /** Indicate whether the event is archived. */
    public function getIsArchivedAttribute(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    /** Return the display label for the publish toggle. */
    public function getPublishActionLabelAttribute(): string
    {
        return $this->is_published ? 'Unpublish' : 'Publish';
    }

    /** Return a display-ready sport label. */
    public function getSportLabelAttribute(): string
    {
        return $this->sport ?: 'Sport to be announced';
    }

    /** Return a display-ready photo publication time. */
    public function getPhotosLiveLabelAttribute(): string
    {
        return $this->photos_live_at
            ? $this->photos_live_at->timezone($this->timezone)->format('M j, Y g:i A T')
            : 'Not scheduled';
    }

    /** Return the per-photo price formatted as a US dollar amount. */
    public function getPriceLabelAttribute(): string
    {
        return $this->price_cents ? '$'.number_format($this->price_cents / 100, 2) : 'Not priced';
    }

    /** Determine whether photos may currently be added to a cart for this event. */
    public function isSellable(): bool
    {
        return $this->is_published
            && $this->price_cents > 0
            && ! $this->sales_close_at?->isPast();
    }
}
