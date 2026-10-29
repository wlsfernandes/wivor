<?php

namespace App\Services;

use App\Exceptions\CrmImportStateException;
use App\Exceptions\InvalidCrmCsvException;
use App\Jobs\ProcessCrmImport;
use App\Models\CrmImport;
use App\Models\CrmMappingPreset;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Drive a participant CSV import from upload through mapping to the queued job.
 */
class CrmImportService
{
    public const UPLOAD_DIRECTORY = 'crm-imports';

    /**
     * Store the upload privately and record its provenance; the file must have a readable header row.
     *
     * @param array{data_source: string, organizer_name?: string|null, consent_status: string, consent_category?: string|null, consent_scope?: string|null, consent_evidence?: string|null} $provenance
     *
     * @throws InvalidCrmCsvException
     */
    public function createFromUpload(Event $event, User $operator, UploadedFile $file, array $provenance): CrmImport
    {
        $diskPath = $file->store(self::UPLOAD_DIRECTORY, 'local');

        try {
            CrmCsvReader::fromPath(Storage::disk('local')->path($diskPath));
        } catch (InvalidCrmCsvException $exception) {
            Storage::disk('local')->delete($diskPath);

            throw $exception;
        }

        $import = CrmImport::create(array_merge($provenance, [
            'event_id' => $event->id,
            'user_id' => $operator->id,
            'original_filename' => $file->getClientOriginalName(),
            'disk_path' => $diskPath,
            'file_sha256' => hash_file('sha256', Storage::disk('local')->path($diskPath)),
            'mapping' => [],
        ]));

        Log::info('CRM import uploaded.', [
            'event' => 'crm.import.uploaded',
            'crm_import_id' => $import->id,
            'event_id' => $event->id,
            'user_id' => $operator->id,
        ]);

        return $import;
    }

    /** @param array<string, mixed> $mapping */
    public function saveMapping(CrmImport $import, array $mapping, ?string $presetName): void
    {
        $this->assertPending($import);

        DB::transaction(function () use ($import, $mapping, $presetName): void {
            $import->update(['mapping' => $mapping]);

            if ($presetName !== null) {
                CrmMappingPreset::create(['name' => $presetName, 'mapping' => $mapping]);
            }
        });
    }

    public function start(CrmImport $import): void
    {
        $this->assertPending($import);

        if (! isset($import->mapping['email'])) {
            throw new CrmImportStateException('Choose the email column before starting the import.');
        }

        $this->queue($import);
    }

    public function retry(CrmImport $import): void
    {
        if ($import->status !== CrmImport::STATUS_FAILED) {
            throw new CrmImportStateException('Only failed imports can be retried.');
        }

        if ($import->files_purged_at !== null) {
            throw new CrmImportStateException('The uploaded file was deleted after the retention period. Upload it again.');
        }

        $this->queue($import);
    }

    public function assertPending(CrmImport $import): void
    {
        if ($import->status !== CrmImport::STATUS_PENDING) {
            throw new CrmImportStateException('This import has already started.');
        }
    }

    private function queue(CrmImport $import): void
    {
        // The status check and update happen in one statement so a double submit cannot queue twice.
        $wasQueued = CrmImport::query()
            ->whereKey($import->id)
            ->where('status', $import->status)
            ->update(['status' => CrmImport::STATUS_QUEUED, 'failure_message' => null]);

        if ($wasQueued === 0) {
            throw new CrmImportStateException('This import has already started.');
        }

        ProcessCrmImport::dispatch($import->id);
    }
}
