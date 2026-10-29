<?php

namespace App\Services;

use App\Models\CrmContact;

/**
 * Turn a CSV row into CRM contact and participant fields using an administrator's column mapping.
 */
class CrmImportRowMapper
{
    public const MAX_EMAIL_LENGTH = 320;

    /** Mappable single-value fields, their labels and maximum lengths. */
    public const FIELDS = [
        'first_name' => ['label' => 'First name', 'max' => 255],
        'last_name' => ['label' => 'Last name', 'max' => 255],
        'full_name' => ['label' => 'Full name', 'max' => 255],
        'phone' => ['label' => 'Phone', 'max' => 40],
        'bib_number' => ['label' => 'Bib number', 'max' => 30],
        'division' => ['label' => 'Division', 'max' => 100],
    ];

    public const MAX_EXTRA_FIELD_LENGTH = 255;

    /** @param array{email: string, extra_fields?: list<string>}&array<string, mixed> $mapping */
    public function __construct(private readonly array $mapping)
    {
    }

    public function map(CrmCsvRow $row): CrmImportRowResult
    {
        if ($row->hasColumnCountMismatch) {
            return CrmImportRowResult::invalid($row->lineNumber, 'Row has a different number of columns than the header.');
        }

        $email = trim($row->values[$this->mapping['email']] ?? '');

        if ($email === '') {
            return CrmImportRowResult::invalid($row->lineNumber, 'Email is missing.');
        }

        if (mb_strlen($email) > self::MAX_EMAIL_LENGTH || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return CrmImportRowResult::invalid($row->lineNumber, 'Email is not a valid address.');
        }

        $fields = [
            'email_normalized' => CrmContact::normalizeEmail($email),
            'email_original' => $email,
        ];

        foreach (self::FIELDS as $field => $rules) {
            $value = $this->mappedValue($row, $field);

            if ($value !== null && mb_strlen($value) > $rules['max']) {
                return CrmImportRowResult::invalid(
                    $row->lineNumber,
                    sprintf('%s is longer than %d characters.', $rules['label'], $rules['max'])
                );
            }

            $fields[$field] = $value;
        }

        $extraFields = [];

        foreach ($this->mapping['extra_fields'] ?? [] as $header) {
            $value = trim($row->values[$header] ?? '');

            if (mb_strlen($value) > self::MAX_EXTRA_FIELD_LENGTH) {
                return CrmImportRowResult::invalid(
                    $row->lineNumber,
                    sprintf('%s is longer than %d characters.', $header, self::MAX_EXTRA_FIELD_LENGTH)
                );
            }

            if ($value !== '') {
                $extraFields[$header] = $value;
            }
        }

        $fields['extra_fields'] = $extraFields === [] ? null : $extraFields;

        return CrmImportRowResult::valid($row->lineNumber, $fields);
    }

    private function mappedValue(CrmCsvRow $row, string $field): ?string
    {
        $header = $this->mapping[$field] ?? null;
        $value = $header === null ? '' : trim($row->values[$header] ?? '');

        return $value === '' ? null : $value;
    }
}
