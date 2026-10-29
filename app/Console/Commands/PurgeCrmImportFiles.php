<?php

namespace App\Console\Commands;

use App\Models\CrmImport;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PurgeCrmImportFiles extends Command
{
    public const RETENTION_DAYS = 30;

    protected $signature = 'crm:purge-import-files';
    protected $description = 'Delete participant CSVs and error reports once imports are older than the retention period.';

    public function handle(): int
    {
        $cutoff = now()->subDays(self::RETENTION_DAYS);
        $purgedCount = 0;

        CrmImport::query()
            ->whereNull('files_purged_at')
            ->where(fn (Builder $query) => $query
                ->where(fn (Builder $query) => $query
                    ->whereIn('status', [CrmImport::STATUS_COMPLETED, CrmImport::STATUS_FAILED])
                    ->where('finished_at', '<', $cutoff))
                ->orWhere(fn (Builder $query) => $query
                    ->where('status', CrmImport::STATUS_PENDING)
                    ->where('created_at', '<', $cutoff)))
            ->orderBy('id')
            ->each(function (CrmImport $import) use (&$purgedCount): void {
                $this->purge($import);
                $purgedCount++;
            });

        Log::info('CRM import files purged.', ['event' => 'crm.import.files_purged', 'count' => $purgedCount]);
        $this->info("Purged files for {$purgedCount} imports.");

        return self::SUCCESS;
    }

    private function purge(CrmImport $import): void
    {
        Storage::disk('local')->delete(array_filter([$import->disk_path, $import->error_report_path]));

        if ($import->status === CrmImport::STATUS_PENDING) {
            $import->forceFill([
                'status' => CrmImport::STATUS_FAILED,
                'failure_message' => 'Not started within '.self::RETENTION_DAYS.' days; the uploaded file was deleted.',
                'finished_at' => now(),
            ]);
        }

        $import->forceFill(['files_purged_at' => now()])->save();
    }
}
