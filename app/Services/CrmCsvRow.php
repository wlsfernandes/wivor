<?php

namespace App\Services;

/** One data record from a participant CSV, keyed by header name. */
final class CrmCsvRow
{
    /** @param array<string, string> $values */
    public function __construct(
        public readonly int $lineNumber,
        public readonly array $values,
        public readonly bool $hasColumnCountMismatch,
    ) {
    }
}
