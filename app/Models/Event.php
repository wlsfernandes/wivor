<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    /** @var list<string> */
    protected $fillable = [
        'title',
        'slug',
        'sport',
        'published',
        'status',
        'published_at',
        'date_of_event',
        'starts_at',
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
            'published_at' => 'datetime',
            'date_of_event' => 'date',
            'starts_at' => 'datetime',
            'photos_live_at' => 'datetime',
        ];
    }

    /** Get photographers assigned to the event. */
    public function photographers(): BelongsToMany
    {
        return $this->belongsToMany(Photographer::class)->withTimestamps();
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
}
