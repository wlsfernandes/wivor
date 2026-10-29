<?php

namespace Tests\Unit;

use App\Services\CrmCsvRow;
use App\Services\CrmImportRowMapper;
use Tests\TestCase;

class CrmImportRowMapperTest extends TestCase
{
    private const MAPPING = [
        'email' => 'E-mail',
        'first_name' => 'First',
        'last_name' => 'Last',
        'bib_number' => 'Bib',
        'division' => 'Category',
        'extra_fields' => ['Shirt'],
    ];

    public function test_mapped_columns_become_normalized_contact_fields(): void
    {
        $result = (new CrmImportRowMapper(self::MAPPING))->map($this->row([
            'E-mail' => ' Ana@Example.COM ',
            'First' => 'Ana',
            'Last' => 'Silva',
            'Bib' => '101',
            'Category' => 'F30',
            'Shirt' => 'M',
            'Unmapped' => 'ignored',
        ]));

        $this->assertNull($result->error);
        $this->assertSame([
            'email_normalized' => 'ana@example.com',
            'email_original' => 'Ana@Example.COM',
            'first_name' => 'Ana',
            'last_name' => 'Silva',
            'full_name' => null,
            'phone' => null,
            'bib_number' => '101',
            'division' => 'F30',
            'extra_fields' => ['Shirt' => 'M'],
        ], $result->fields);
    }

    public function test_blank_values_become_null_and_blank_extras_are_dropped(): void
    {
        $result = (new CrmImportRowMapper(self::MAPPING))->map($this->row([
            'E-mail' => 'ana@example.com', 'First' => '', 'Last' => '', 'Bib' => '', 'Category' => '', 'Shirt' => '',
        ]));

        $this->assertNull($result->fields['first_name']);
        $this->assertNull($result->fields['bib_number']);
        $this->assertNull($result->fields['extra_fields']);
    }

    public function test_missing_email_is_rejected(): void
    {
        $result = (new CrmImportRowMapper(self::MAPPING))->map($this->row(['E-mail' => '  ']));

        $this->assertSame('Email is missing.', $result->error);
    }

    public function test_invalid_email_is_rejected(): void
    {
        $result = (new CrmImportRowMapper(self::MAPPING))->map($this->row(['E-mail' => 'not-an-email']));

        $this->assertSame('Email is not a valid address.', $result->error);
    }

    public function test_overlong_bib_number_is_rejected(): void
    {
        $result = (new CrmImportRowMapper(self::MAPPING))->map($this->row([
            'E-mail' => 'ana@example.com', 'Bib' => str_repeat('9', 31),
        ]));

        $this->assertSame('Bib number is longer than 30 characters.', $result->error);
    }

    public function test_row_with_column_count_mismatch_is_rejected(): void
    {
        $row = new CrmCsvRow(5, ['E-mail' => 'ana@example.com'], true);

        $result = (new CrmImportRowMapper(self::MAPPING))->map($row);

        $this->assertSame('Row has a different number of columns than the header.', $result->error);
    }

    /** @param array<string, string> $values */
    private function row(array $values): CrmCsvRow
    {
        return new CrmCsvRow(2, $values, false);
    }
}
