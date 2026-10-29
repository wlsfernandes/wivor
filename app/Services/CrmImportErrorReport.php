<?php

namespace App\Services;

use App\Models\CrmImport;
use Illuminate\Support\Facades\Storage;

/**
 * Write the private, spreadsheet-safe CSV listing rows an import rejected or skipped.
 */
class CrmImportErrorReport
{
    public const DIRECTORY = 'crm-imports/errors';

    /** Leading characters that make spreadsheet programs evaluate a cell as a formula. */
    private const FORMULA_PREFIXES = ['=', '+', '-', '@', "\t", "\r"];

    /** @var list<array{int, string, string}> */
    private array $entries = [];

    public function add(int $lineNumber, string $email, string $error): void
    {
        $this->entries[] = [$lineNumber, $email, $error];
    }

    public function count(): int
    {
        return count($this->entries);
    }

    /** Store the report and return its private disk path, or null when nothing was rejected. */
    public function store(CrmImport $import): ?string
    {
        if ($this->entries === []) {
            return null;
        }

        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, ['Line', 'Email', 'Error'], ',', '"', '');

        foreach ($this->entries as $entry) {
            fputcsv($stream, array_map(fn (int|string $cell): string => self::safeCell((string) $cell), $entry), ',', '"', '');
        }

        rewind($stream);
        $path = self::DIRECTORY.'/import-'.$import->id.'.csv';
        Storage::disk('local')->put($path, stream_get_contents($stream));
        fclose($stream);

        return $path;
    }

    public static function safeCell(string $value): string
    {
        foreach (self::FORMULA_PREFIXES as $prefix) {
            if (str_starts_with($value, $prefix)) {
                return "'".$value;
            }
        }

        return $value;
    }
}
