<?php

namespace App\Services;

use App\Models\CrmContact;
use App\Models\CrmImport;
use Illuminate\Support\Facades\Storage;

/**
 * Estimate what an import will do, without writing anything, so administrators can check the mapping first.
 */
class CrmImportPreview
{
    public const SAMPLE_SIZE = 20;

    private const EXISTING_CONTACT_LOOKUP_CHUNK = 1000;

    /**
     * @return array{
     *     counts: array{rows: int, valid: int, invalid: int, duplicates: int, existingContacts: int},
     *     sampleRows: list<CrmImportRowResult>
     * }
     */
    public function build(CrmImport $import): array
    {
        $reader = CrmCsvReader::fromPath(Storage::disk('local')->path($import->disk_path));
        $mapper = new CrmImportRowMapper($import->mapping);
        $uniqueEmails = [];
        $sampleRows = [];
        $rowCount = 0;
        $invalidCount = 0;
        $duplicateCount = 0;

        foreach ($reader->rows() as $row) {
            $rowCount++;
            $result = $mapper->map($row);

            if (count($sampleRows) < self::SAMPLE_SIZE) {
                $sampleRows[] = $result;
            }

            if ($result->error !== null) {
                $invalidCount++;
            } elseif (isset($uniqueEmails[$result->fields['email_normalized']])) {
                $duplicateCount++;
            } else {
                $uniqueEmails[$result->fields['email_normalized']] = true;
            }
        }

        return [
            'counts' => [
                'rows' => $rowCount,
                'valid' => count($uniqueEmails),
                'invalid' => $invalidCount,
                'duplicates' => $duplicateCount,
                'existingContacts' => $this->countExistingContacts(array_keys($uniqueEmails)),
            ],
            'sampleRows' => $sampleRows,
        ];
    }

    /** @param list<string> $emails */
    private function countExistingContacts(array $emails): int
    {
        $existingCount = 0;

        foreach (array_chunk($emails, self::EXISTING_CONTACT_LOOKUP_CHUNK) as $emailChunk) {
            $existingCount += CrmContact::query()->whereIn('email_normalized', $emailChunk)->count();
        }

        return $existingCount;
    }
}
