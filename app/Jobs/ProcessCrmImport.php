<?php

namespace App\Jobs;

use App\Exceptions\InvalidCrmCsvException;
use App\Models\CrmImport;
use App\Services\CrmContactImporter;
use App\Services\CrmCsvReader;
use App\Services\CrmImportErrorReport;
use App\Services\CrmImportRowMapper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/** Imports one participant CSV into the CRM in chunks; rerunning it is safe because every write is an upsert. */
class ProcessCrmImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const CHUNK_SIZE = 500;

    public const UNEXPECTED_FAILURE_MESSAGE = 'The import stopped unexpectedly. It is safe to retry.';

    public int $tries = 3;

    public int $timeout = 170;

    public function __construct(public int $crmImportId) {}

    /** @return list<int> */
    public function backoff(): array
    {
        return [30, 120];
    }

    /** Prevent two workers from processing the same import at once. */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("crm-import-{$this->crmImportId}"))
                ->dontRelease()
                ->expireAfter($this->timeout + 30),
        ];
    }

    public function handle(CrmContactImporter $importer): void
    {
        $import = CrmImport::find($this->crmImportId);

        if (! $import || $import->status === CrmImport::STATUS_COMPLETED) {
            return;
        }

        $this->markRunning($import);

        try {
            $reader = CrmCsvReader::fromPath(Storage::disk('local')->path($import->disk_path));
        } catch (InvalidCrmCsvException $exception) {
            $this->markFailed($import, $exception->getMessage());

            return;
        }

        $this->importRows($import, $reader, $importer);
    }

    /** Keep an exhausted import visible as failed without exposing internal error text. */
    public function failed(Throwable $exception): void
    {
        Log::error('CRM import failed.', [
            'event' => 'crm.import.failed',
            'crm_import_id' => $this->crmImportId,
            'exception' => $exception::class,
        ]);

        $import = CrmImport::find($this->crmImportId);

        if ($import) {
            $this->markFailed($import, self::UNEXPECTED_FAILURE_MESSAGE);
        }
    }

    private function importRows(CrmImport $import, CrmCsvReader $reader, CrmContactImporter $importer): void
    {
        $mapper = new CrmImportRowMapper($import->mapping);
        $errorReport = new CrmImportErrorReport;
        $emailField = $import->mapping['email'];
        $seenEmails = [];
        $pendingRows = [];
        $totalRows = 0;
        $importedRows = 0;
        $skippedRows = 0;

        foreach ($reader->rows() as $row) {
            $totalRows++;
            $result = $mapper->map($row);

            if ($result->error !== null) {
                $errorReport->add($row->lineNumber, $row->values[$emailField] ?? '', $result->error);

                continue;
            }

            if (isset($seenEmails[$result->fields['email_normalized']])) {
                $skippedRows++;
                $errorReport->add($row->lineNumber, $result->fields['email_original'], 'Duplicate of an earlier row in this file.');

                continue;
            }

            $seenEmails[$result->fields['email_normalized']] = true;
            $pendingRows[] = $result;

            if (count($pendingRows) === self::CHUNK_SIZE) {
                $importer->importRows($import, $pendingRows);
                $importedRows += count($pendingRows);
                $pendingRows = [];
            }
        }

        $importer->importRows($import, $pendingRows);
        $importedRows += count($pendingRows);

        $import->forceFill([
            'status' => CrmImport::STATUS_COMPLETED,
            'total_rows' => $totalRows,
            'imported_rows' => $importedRows,
            'skipped_rows' => $skippedRows,
            'failed_rows' => $errorReport->count() - $skippedRows,
            'error_report_path' => $errorReport->store($import),
            'finished_at' => now(),
        ])->save();
    }

    private function markRunning(CrmImport $import): void
    {
        $import->forceFill([
            'status' => CrmImport::STATUS_RUNNING,
            'failure_message' => null,
            'total_rows' => 0,
            'imported_rows' => 0,
            'skipped_rows' => 0,
            'failed_rows' => 0,
            'started_at' => now(),
            'finished_at' => null,
        ])->save();
    }

    private function markFailed(CrmImport $import, string $message): void
    {
        $import->forceFill([
            'status' => CrmImport::STATUS_FAILED,
            'failure_message' => $message,
            'finished_at' => now(),
        ])->save();
    }
}
