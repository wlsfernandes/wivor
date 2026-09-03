<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Photo extends Model
{
    public const STATUS_QUEUED = 'queued';
    public const STATUS_UPLOADING = 'uploading';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY = 'ready';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_REMOVED = 'removed';

    protected $fillable = [
        'uuid', 'event_id', 'photographer_id', 'assignment_id', 'upload_batch_id',
        'original_filename', 'original_key', 'preview_key', 'thumbnail_key',
        'detected_mime', 'file_size', 'width', 'height', 'color_mode', 'checksum',
        'status', 'rejection_code', 'rejection_reason', 'sale_count', 'uploaded_at',
        'processing_started_at', 'processed_at', 'published_at', 'expires_at',
        'most_recent_purchase_at', 'deleted_at', 'deletion_reason',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer', 'width' => 'integer', 'height' => 'integer', 'sale_count' => 'integer',
            'uploaded_at' => 'datetime', 'processing_started_at' => 'datetime',
            'processed_at' => 'datetime', 'published_at' => 'datetime',
            'expires_at' => 'datetime', 'most_recent_purchase_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $photo) => $photo->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string { return 'uuid'; }
    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function photographer(): BelongsTo { return $this->belongsTo(Photographer::class); }
    public function batch(): BelongsTo { return $this->belongsTo(UploadBatch::class, 'upload_batch_id'); }
    public function holds(): HasMany { return $this->hasMany(MediaRetentionHold::class); }

    public function hasActiveHold(): bool
    {
        return $this->holds()->whereNull('released_at')->exists()
            || MediaRetentionHold::where('event_id', $this->event_id)->whereNull('photo_id')->whereNull('released_at')->exists();
    }
}
