<?php

namespace Tests\Feature;

use App\Jobs\ProcessCrmImport;
use App\Models\CrmContact;
use App\Models\CrmContactConsent;
use App\Models\CrmImport;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class ProcessCrmImportJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_five_thousand_row_import_completes_with_accurate_counts(): void
    {
        $lines = ['Email,First Name,Bib'];
        foreach (range(1, 5000) as $number) {
            $lines[] = "runner{$number}@example.com,Runner {$number},{$number}";
        }
        $import = $this->import(implode("\n", $lines));

        ProcessCrmImport::dispatchSync($import->id);

        $import->refresh();
        $this->assertSame(CrmImport::STATUS_COMPLETED, $import->status);
        $this->assertSame(5000, $import->total_rows);
        $this->assertSame(5000, $import->imported_rows);
        $this->assertSame(0, $import->failed_rows);
        $this->assertNotNull($import->finished_at);
        $this->assertNull($import->error_report_path);
        $this->assertDatabaseCount('crm_contacts', 5000);
        $this->assertDatabaseCount('crm_event_contacts', 5000);
    }

    public function test_invalid_and_duplicate_rows_are_counted_and_reported(): void
    {
        $import = $this->import(implode("\n", [
            'Email,First Name,Bib',
            'ana@example.com,Ana,1',
            ',Blank,2',
            'not-an-email,Bad,3',
            'ANA@example.com,Ana again,4',
        ]));

        ProcessCrmImport::dispatchSync($import->id);

        $import->refresh();
        $this->assertSame(4, $import->total_rows);
        $this->assertSame(1, $import->imported_rows);
        $this->assertSame(1, $import->skipped_rows);
        $this->assertSame(2, $import->failed_rows);

        $report = Storage::disk('local')->get($import->error_report_path);
        $this->assertStringContainsString('3,,"Email is missing."', $report);
        $this->assertStringContainsString('4,not-an-email,"Email is not a valid address."', $report);
        $this->assertStringContainsString('5,ANA@example.com,"Duplicate of an earlier row in this file."', $report);
    }

    public function test_error_report_neutralizes_spreadsheet_formulas(): void
    {
        $import = $this->import("Email,First Name,Bib\n=HYPERLINK(\"http://evil.test\"),x,1\n");

        ProcessCrmImport::dispatchSync($import->id);

        $report = Storage::disk('local')->get($import->refresh()->error_report_path);
        $this->assertStringContainsString("'=HYPERLINK", $report);
        $this->assertStringNotContainsString(',=HYPERLINK', $report);
    }

    public function test_running_the_job_again_after_an_interruption_does_not_duplicate_data(): void
    {
        $import = $this->import("Email,First Name,Bib\nana@example.com,Ana,1\nbo@example.com,Bo,2\n");

        ProcessCrmImport::dispatchSync($import->id);
        $import->update(['status' => CrmImport::STATUS_FAILED]);
        ProcessCrmImport::dispatchSync($import->id);

        $import->refresh();
        $this->assertSame(CrmImport::STATUS_COMPLETED, $import->status);
        $this->assertSame(2, $import->imported_rows);
        $this->assertDatabaseCount('crm_contacts', 2);
        $this->assertDatabaseCount('crm_event_contacts', 2);
    }

    public function test_unreadable_file_marks_import_failed_with_reason(): void
    {
        $import = $this->import('');

        ProcessCrmImport::dispatchSync($import->id);

        $import->refresh();
        $this->assertSame(CrmImport::STATUS_FAILED, $import->status);
        $this->assertSame('The file has no header row.', $import->failure_message);
    }

    public function test_unexpected_failure_marks_import_failed_without_leaking_details(): void
    {
        $import = $this->import("Email\nana@example.com\n");

        (new ProcessCrmImport($import->id))->failed(new RuntimeException('SQLSTATE secret detail'));

        $import->refresh();
        $this->assertSame(CrmImport::STATUS_FAILED, $import->status);
        $this->assertSame('The import stopped unexpectedly. It is safe to retry.', $import->failure_message);
    }

    public function test_existing_contact_from_another_event_is_linked_not_duplicated(): void
    {
        $otherEvent = Event::create(['title' => 'City Marathon', 'slug' => 'city-marathon', 'content' => 'CRM fixture.']);
        $contact = CrmContact::create(['email_normalized' => 'ana@example.com', 'email_original' => 'ana@example.com']);
        $contact->events()->attach($otherEvent, ['bib_number' => '7']);
        $import = $this->import("Email,First Name,Bib\nana@example.com,Ana,101\n");

        ProcessCrmImport::dispatchSync($import->id);

        $this->assertDatabaseCount('crm_contacts', 1);
        $this->assertSame(['7', '101'], $contact->events()->orderBy('crm_event_contacts.id')->get()->pluck('participation.bib_number')->all());
    }

    private function import(string $csv): CrmImport
    {
        $event = Event::create(['title' => 'River Run', 'slug' => 'river-run', 'content' => 'CRM fixture.']);
        Storage::disk('local')->put('crm-imports/test.csv', $csv);

        return CrmImport::create([
            'event_id' => $event->id,
            'original_filename' => 'test.csv',
            'disk_path' => 'crm-imports/test.csv',
            'file_sha256' => hash('sha256', $csv),
            'mapping' => ['email' => 'Email', 'first_name' => 'First Name', 'bib_number' => 'Bib'],
            'data_source' => 'Organizer export',
            'consent_status' => CrmContactConsent::STATUS_UNKNOWN,
        ]);
    }
}
