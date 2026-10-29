<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An administrator's CSV upload of participants for one event, with its provenance and progress.
 *
 * @property int $id
 * @property int $event_id
 * @property int|null $user_id
 * @property string $original_filename
 * @property string $disk_path
 * @property string $file_sha256
 * @property array<string, string> $mapping
 * @property string $data_source
 * @property string|null $organizer_name
 * @property string $consent_status
 * @property string|null $consent_category
 * @property string|null $consent_scope
 * @property string|null $consent_evidence
 * @property string $status
 * @property int $total_rows
 * @property int $imported_rows
 * @property int $skipped_rows
 * @property int $failed_rows
 * @property string|null $error_report_path
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property \Illuminate\Support\Carbon|null $files_purged_at
 */
class CrmImport extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public const CONSENT_SCOPE_EVENT = 'event';
    public const CONSENT_SCOPE_WIVOR = 'wivor';

    /** @var list<string> */
    protected $fillable = [
        'event_id',
        'user_id',
        'original_filename',
        'disk_path',
        'file_sha256',
        'mapping',
        'data_source',
        'organizer_name',
        'consent_status',
        'consent_category',
        'consent_scope',
        'consent_evidence',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'mapping' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'files_purged_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** Administrator who uploaded the file. */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
