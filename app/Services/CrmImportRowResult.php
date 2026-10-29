<?php

namespace App\Services;

/** Outcome of mapping one CSV row: contact fields when valid, otherwise an administrator-readable error. */
final class CrmImportRowResult
{
    /** @param array<string, mixed>|null $fields */
    private function __construct(
        public readonly int $lineNumber,
        public readonly ?array $fields,
        public readonly ?string $error,
    ) {
    }

    /** @param array<string, mixed> $fields */
    public static function valid(int $lineNumber, array $fields): self
    {
        return new self($lineNumber, $fields, null);
    }

    public static function invalid(int $lineNumber, string $error): self
    {
        return new self($lineNumber, null, $error);
    }
}
