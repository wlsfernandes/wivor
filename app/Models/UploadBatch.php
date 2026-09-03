<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class UploadBatch extends Model
{
    protected $fillable = [
        'uuid', 'event_id', 'photographer_id', 'assignment_id', 'selected_count',
        'uploaded_count', 'ready_count', 'rejected_count', 'published_count',
        'status', 'started_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $batch) => $batch->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string { return 'uuid'; }
    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function photographer(): BelongsTo { return $this->belongsTo(Photographer::class); }
    public function photos(): HasMany { return $this->hasMany(Photo::class); }

    public function recalculate(): void
    {
        $counts = $this->photos()->selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');
        $uploaded = $this->photos()->whereNotNull('uploaded_at')->count();
        $pending = (int) ($counts[Photo::STATUS_QUEUED] ?? 0)
            + (int) ($counts[Photo::STATUS_UPLOADING] ?? 0)
            + (int) ($counts[Photo::STATUS_PROCESSING] ?? 0);

        $this->update([
            'uploaded_count' => $uploaded,
            'ready_count' => (int) ($counts[Photo::STATUS_READY] ?? 0),
            'rejected_count' => (int) ($counts[Photo::STATUS_REJECTED] ?? 0),
            'published_count' => (int) ($counts[Photo::STATUS_PUBLISHED] ?? 0),
            'status' => $pending === 0 ? 'completed' : 'in_progress',
            'completed_at' => $pending === 0 ? now() : null,
        ]);
    }
}
