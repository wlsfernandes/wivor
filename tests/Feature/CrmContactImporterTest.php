<?php

namespace Tests\Feature;

use App\Models\CrmContact;
use App\Models\CrmContactConsent;
use App\Models\CrmImport;
use App\Models\CrmSuppression;
use App\Models\Event;
use App\Services\CrmContactImporter;
use App\Services\CrmImportRowResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmContactImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_row_creates_contact_and_event_participation(): void
    {
        $import = $this->import();

        app(CrmContactImporter::class)->importRows($import, [$this->row('ana@example.com', ['first_name' => 'Ana', 'bib_number' => '101'])]);

        $contact = CrmContact::where('email_normalized', 'ana@example.com')->firstOrFail();
        $this->assertSame('Ana', $contact->first_name);
        $this->assertSame(CrmContact::SOURCE_CSV_IMPORT, $contact->first_source);
        $this->assertSame('101', $contact->events()->firstOrFail()->participation->bib_number);
        $this->assertSame($import->id, $contact->events()->firstOrFail()->participation->crm_import_id);
    }

    public function test_reimporting_the_same_rows_does_not_duplicate_contacts_or_participation(): void
    {
        $import = $this->import();
        $importer = app(CrmContactImporter::class);

        $importer->importRows($import, [$this->row('ana@example.com', ['bib_number' => '101'])]);
        $importer->importRows($import, [$this->row('ana@example.com', ['bib_number' => '102'])]);

        $this->assertDatabaseCount('crm_contacts', 1);
        $this->assertDatabaseCount('crm_event_contacts', 1);
        $this->assertDatabaseHas('crm_event_contacts', ['bib_number' => '102']);
    }

    public function test_blank_import_value_keeps_existing_participant_bib(): void
    {
        $import = $this->import();
        $importer = app(CrmContactImporter::class);

        $importer->importRows($import, [$this->row('ana@example.com', ['bib_number' => '101'])]);
        $importer->importRows($import, [$this->row('ana@example.com')]);

        $this->assertDatabaseHas('crm_event_contacts', ['bib_number' => '101']);
    }

    public function test_existing_contact_names_are_kept_and_only_empty_ones_are_filled(): void
    {
        CrmContact::create([
            'email_normalized' => 'ana@example.com',
            'email_original' => 'Ana@example.com',
            'first_name' => 'Ana',
        ]);

        app(CrmContactImporter::class)->importRows($this->import(), [
            $this->row('ana@example.com', ['first_name' => 'A.', 'last_name' => 'Silva']),
        ]);

        $contact = CrmContact::firstOrFail();
        $this->assertSame('Ana', $contact->first_name);
        $this->assertSame('Silva', $contact->last_name);
        $this->assertNull($contact->first_source);
    }

    public function test_unknown_consent_import_records_no_consent(): void
    {
        app(CrmContactImporter::class)->importRows($this->import(), [$this->row('ana@example.com')]);

        $this->assertDatabaseCount('crm_contact_consents', 0);
    }

    public function test_granted_consent_import_records_event_scoped_evidence_once(): void
    {
        $import = $this->import([
            'consent_status' => CrmContactConsent::STATUS_GRANTED,
            'consent_category' => CrmContactConsent::CATEGORY_MARKETING,
            'consent_scope' => CrmImport::CONSENT_SCOPE_EVENT,
            'consent_evidence' => 'Registration form checkbox, text v3',
        ]);
        $importer = app(CrmContactImporter::class);

        $importer->importRows($import, [$this->row('ana@example.com')]);
        $importer->importRows($import, [$this->row('ana@example.com')]);

        $consent = CrmContactConsent::sole();
        $this->assertSame(CrmContactConsent::STATUS_GRANTED, $consent->status);
        $this->assertSame($import->event_id, $consent->scope_event_id);
        $this->assertSame('Registration form checkbox, text v3', $consent->evidence);
        $this->assertSame('CSV import #'.$import->id, $consent->source);
    }

    public function test_granted_consent_import_does_not_override_a_suppression(): void
    {
        $contact = CrmContact::create(['email_normalized' => 'ana@example.com', 'email_original' => 'ana@example.com']);
        CrmSuppression::create([
            'crm_contact_id' => $contact->id,
            'email_normalized' => 'ana@example.com',
            'reason' => CrmSuppression::REASON_UNSUBSCRIBE,
            'category' => CrmContactConsent::CATEGORY_MARKETING,
        ]);

        app(CrmContactImporter::class)->importRows($this->import([
            'consent_status' => CrmContactConsent::STATUS_GRANTED,
            'consent_category' => CrmContactConsent::CATEGORY_MARKETING,
            'consent_scope' => CrmImport::CONSENT_SCOPE_WIVOR,
            'consent_evidence' => 'Signed form',
        ]), [$this->row('ana@example.com')]);

        $this->assertDatabaseCount('crm_contact_consents', 0);
        $this->assertDatabaseCount('crm_suppressions', 1);
    }

    public function test_granted_consent_import_does_not_override_a_revocation(): void
    {
        $contact = CrmContact::create(['email_normalized' => 'ana@example.com', 'email_original' => 'ana@example.com']);
        $contact->consents()->create([
            'category' => CrmContactConsent::CATEGORY_MARKETING,
            'status' => CrmContactConsent::STATUS_REVOKED,
            'source' => 'Preference page',
            'recorded_at' => now()->subYear(),
            'revoked_at' => now()->subMonth(),
        ]);

        app(CrmContactImporter::class)->importRows($this->import([
            'consent_status' => CrmContactConsent::STATUS_GRANTED,
            'consent_category' => CrmContactConsent::CATEGORY_MARKETING,
            'consent_scope' => CrmImport::CONSENT_SCOPE_WIVOR,
            'consent_evidence' => 'Signed form',
        ]), [$this->row('ana@example.com')]);

        $this->assertDatabaseCount('crm_contact_consents', 1);
    }

    /** @param array<string, mixed> $attributes */
    private function import(array $attributes = []): CrmImport
    {
        $event = Event::create(['title' => 'River Run', 'slug' => 'river-run-'.uniqid(), 'content' => 'CRM fixture.']);

        return CrmImport::create(array_merge([
            'event_id' => $event->id,
            'original_filename' => 'river-run.csv',
            'disk_path' => 'crm-imports/river-run.csv',
            'file_sha256' => str_repeat('a', 64),
            'mapping' => ['email' => 'Email'],
            'data_source' => 'Organizer export',
            'consent_status' => CrmContactConsent::STATUS_UNKNOWN,
        ], $attributes));
    }

    /** @param array<string, string> $fields */
    private function row(string $email, array $fields = []): CrmImportRowResult
    {
        return CrmImportRowResult::valid(2, array_merge([
            'email_normalized' => $email,
            'email_original' => $email,
            'first_name' => null,
            'last_name' => null,
            'full_name' => null,
            'phone' => null,
            'bib_number' => null,
            'division' => null,
            'extra_fields' => null,
        ], $fields));
    }
}
