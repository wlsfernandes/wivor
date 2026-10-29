<?php

namespace Tests\Unit;

use App\Exceptions\InvalidCrmCsvException;
use App\Services\CrmCsvReader;
use App\Services\CrmCsvRow;
use Tests\TestCase;

class CrmCsvReaderTest extends TestCase
{
    /** @var list<string> */
    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            @unlink($path);
        }

        parent::tearDown();
    }

    public function test_comma_separated_file_yields_rows_keyed_by_header(): void
    {
        $reader = CrmCsvReader::fromPath($this->csv("Email,First Name\nana@example.com,Ana\n"));

        $this->assertSame(['Email', 'First Name'], $reader->headers());
        $this->assertEquals(
            [new CrmCsvRow(2, ['Email' => 'ana@example.com', 'First Name' => 'Ana'], false)],
            iterator_to_array($reader->rows(), false)
        );
    }

    public function test_semicolon_and_tab_delimiters_are_detected(): void
    {
        $this->assertSame(';', CrmCsvReader::fromPath($this->csv("Email;Bib\na@example.com;1\n"))->delimiter());
        $this->assertSame("\t", CrmCsvReader::fromPath($this->csv("Email\tBib\na@example.com\t1\n"))->delimiter());
    }

    public function test_quoted_values_may_contain_the_delimiter(): void
    {
        $reader = CrmCsvReader::fromPath($this->csv("Email,Name\na@example.com,\"Silva, Ana\"\n"));

        $this->assertSame('Silva, Ana', iterator_to_array($reader->rows(), false)[0]->values['Name']);
    }

    public function test_utf8_byte_order_mark_is_removed_from_first_header(): void
    {
        $reader = CrmCsvReader::fromPath($this->csv("\xEF\xBB\xBFEmail,Bib\na@example.com,1\n"));

        $this->assertSame('Email', $reader->headers()[0]);
    }

    public function test_windows_1252_text_is_converted_to_utf8(): void
    {
        $reader = CrmCsvReader::fromPath($this->csv("Email,Name\na@example.com,Jos\xE9\n"));

        $this->assertSame('José', iterator_to_array($reader->rows(), false)[0]->values['Name']);
    }

    public function test_utf16_file_with_byte_order_mark_is_converted_to_utf8(): void
    {
        $utf16 = "\xFF\xFE".mb_convert_encoding("Email,Name\na@example.com,Zoë\n", 'UTF-16LE', 'UTF-8');

        $reader = CrmCsvReader::fromPath($this->csv($utf16));

        $this->assertSame('Zoë', iterator_to_array($reader->rows(), false)[0]->values['Name']);
    }

    public function test_blank_lines_are_skipped_and_line_numbers_are_kept(): void
    {
        $reader = CrmCsvReader::fromPath($this->csv("Email\r\na@example.com\r\n\r\nb@example.com\r\n"));

        $lineNumbers = array_map(fn (CrmCsvRow $row): int => $row->lineNumber, iterator_to_array($reader->rows(), false));

        $this->assertSame([2, 4], $lineNumbers);
    }

    public function test_row_with_wrong_column_count_is_flagged(): void
    {
        $reader = CrmCsvReader::fromPath($this->csv("Email,Bib\na@example.com,1,extra\n"));

        $this->assertTrue(iterator_to_array($reader->rows(), false)[0]->hasColumnCountMismatch);
    }

    public function test_empty_file_is_rejected(): void
    {
        $this->expectException(InvalidCrmCsvException::class);
        $this->expectExceptionMessage('The file has no header row.');

        CrmCsvReader::fromPath($this->csv(''));
    }

    public function test_blank_header_is_rejected(): void
    {
        $this->expectException(InvalidCrmCsvException::class);
        $this->expectExceptionMessage('Column 2 has no header name.');

        CrmCsvReader::fromPath($this->csv("Email,,Bib\n"));
    }

    public function test_duplicate_header_is_rejected(): void
    {
        $this->expectException(InvalidCrmCsvException::class);
        $this->expectExceptionMessage('The header "Email" appears more than once.');

        CrmCsvReader::fromPath($this->csv("Email,Bib,email\n"));
    }

    public function test_file_with_too_many_columns_is_rejected(): void
    {
        $headers = implode(',', array_map(fn (int $column): string => 'Column '.$column, range(1, 101)));

        $this->expectException(InvalidCrmCsvException::class);
        $this->expectExceptionMessage('The file has 101 columns; the limit is 100.');

        CrmCsvReader::fromPath($this->csv($headers."\n"));
    }

    private function csv(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'crm-csv-');
        file_put_contents($path, $contents);
        $this->temporaryFiles[] = $path;

        return $path;
    }
}
