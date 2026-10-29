<?php

namespace App\Services;

use App\Exceptions\InvalidCrmCsvException;
use Generator;

/**
 * Read organizer CSV exports whose delimiter and encoding vary, yielding rows keyed by header.
 */
class CrmCsvReader
{
    public const MAX_COLUMNS = 100;

    private const CANDIDATE_DELIMITERS = [',', ';', "\t", '|'];

    private const UTF8_BOM = "\xEF\xBB\xBF";

    private const UTF16_LE_BOM = "\xFF\xFE";

    private const UTF16_BE_BOM = "\xFE\xFF";

    /** @param list<string> $headers */
    private function __construct(
        private readonly string $contents,
        private readonly string $delimiter,
        private readonly array $headers,
    ) {
    }

    /** @throws InvalidCrmCsvException when the file has no usable header row */
    public static function fromPath(string $path): self
    {
        $rawContents = is_readable($path) ? file_get_contents($path) : false;

        if ($rawContents === false) {
            throw new InvalidCrmCsvException('The file could not be read.');
        }

        $contents = self::toUtf8($rawContents);
        $firstLine = strtok($contents, "\r\n");

        if ($firstLine === false || trim($firstLine) === '') {
            throw new InvalidCrmCsvException('The file has no header row.');
        }

        $delimiter = self::detectDelimiter($firstLine);
        $headers = array_map('trim', str_getcsv($firstLine, $delimiter, '"', ''));
        self::assertValidHeaders($headers);

        return new self($contents, $delimiter, $headers);
    }

    /** @return list<string> */
    public function headers(): array
    {
        return $this->headers;
    }

    public function delimiter(): string
    {
        return $this->delimiter;
    }

    /** @return Generator<int, CrmCsvRow> */
    public function rows(): Generator
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $this->contents);
        rewind($stream);
        fgetcsv($stream, null, $this->delimiter, '"', '');

        $lineNumber = 1;

        try {
            while (($fields = fgetcsv($stream, null, $this->delimiter, '"', '')) !== false) {
                $lineNumber++;

                if ($fields === [null]) {
                    continue;
                }

                yield $this->toRow($lineNumber, $fields);
            }
        } finally {
            fclose($stream);
        }
    }

    /** @param list<string|null> $fields */
    private function toRow(int $lineNumber, array $fields): CrmCsvRow
    {
        $values = array_map(fn (?string $field): string => trim((string) $field), $fields);
        $hasColumnCountMismatch = count($values) !== count($this->headers);
        $values = array_slice(array_pad($values, count($this->headers), ''), 0, count($this->headers));

        return new CrmCsvRow($lineNumber, array_combine($this->headers, $values), $hasColumnCountMismatch);
    }

    private static function toUtf8(string $contents): string
    {
        if (str_starts_with($contents, self::UTF16_LE_BOM)) {
            return mb_convert_encoding(substr($contents, 2), 'UTF-8', 'UTF-16LE');
        }

        if (str_starts_with($contents, self::UTF16_BE_BOM)) {
            return mb_convert_encoding(substr($contents, 2), 'UTF-8', 'UTF-16BE');
        }

        if (str_starts_with($contents, self::UTF8_BOM)) {
            return substr($contents, strlen(self::UTF8_BOM));
        }

        if (mb_check_encoding($contents, 'UTF-8')) {
            return $contents;
        }

        return mb_convert_encoding($contents, 'UTF-8', 'Windows-1252');
    }

    private static function detectDelimiter(string $headerLine): string
    {
        $counts = array_map(
            fn (string $delimiter): int => substr_count($headerLine, $delimiter),
            self::CANDIDATE_DELIMITERS
        );

        return max($counts) === 0
            ? self::CANDIDATE_DELIMITERS[0]
            : self::CANDIDATE_DELIMITERS[array_search(max($counts), $counts, true)];
    }

    /** @param list<string> $headers */
    private static function assertValidHeaders(array $headers): void
    {
        if (count($headers) > self::MAX_COLUMNS) {
            throw new InvalidCrmCsvException(sprintf(
                'The file has %d columns; the limit is %d.',
                count($headers),
                self::MAX_COLUMNS
            ));
        }

        $seenHeaders = [];

        foreach ($headers as $index => $header) {
            if ($header === '') {
                throw new InvalidCrmCsvException(sprintf('Column %d has no header name.', $index + 1));
            }

            $headerKey = mb_strtolower($header);

            if (isset($seenHeaders[$headerKey])) {
                throw new InvalidCrmCsvException(sprintf('The header "%s" appears more than once.', $seenHeaders[$headerKey]));
            }

            $seenHeaders[$headerKey] = $header;
        }
    }
}
