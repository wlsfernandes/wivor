<?php

namespace Tests\Feature;

use App\Jobs\ProcessCrmImport;
use App\Models\CrmContact;
use App\Models\CrmContactConsent;
use App\Models\CrmImport;
use App\Models\CrmMappingPreset;
use App\Models\Event;
use App\Models\Photographer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CrmImportFlowTest extends TestCase
{
    use RefreshDatabase;

    private const CSV = "E-mail,First,Bib\nana@example.com,Ana,1\nbo@example.com,Bo,2\nnot-an-email,Bad,3\nANA@example.com,Dup,4\n";

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_admin_upload_stores_file_privately_and_records_provenance(): void
    {
        $event = $this->event();

        $response = $this->actingAs($this->admin())->post(route('admin.crm.imports.store', $event), $this->uploadPayload());

        $import = CrmImport::sole();
        $response->assertRedirect(route('admin.crm.imports.mapping.edit', $import));
        $this->assertSame(CrmImport::STATUS_PENDING, $import->status);
        $this->assertSame('Organizer export', $import->data_source);
        $this->assertSame('River Run Club', $import->organizer_name);
        $this->assertSame(CrmContactConsent::STATUS_UNKNOWN, $import->consent_status);
        $this->assertSame(hash('sha256', self::CSV), $import->file_sha256);
        $this->assertStringStartsWith('crm-imports/', $import->disk_path);
        Storage::disk('local')->assertExists($import->disk_path);
    }

    public function test_upload_without_a_header_row_is_rejected_and_not_kept(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.crm.imports.store', $this->event()), $this->uploadPayload(['file' => UploadedFile::fake()->createWithContent('empty.csv', "\n")]))
            ->assertSessionHasErrors(['file' => 'The file has no header row.']);

        $this->assertDatabaseCount('crm_imports', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('crm-imports'));
    }

    public function test_upload_rejects_non_csv_files(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.crm.imports.store', $this->event()), $this->uploadPayload(['file' => UploadedFile::fake()->image('photo.jpg')]))
            ->assertSessionHasErrors('file');
    }

    public function test_granted_consent_requires_written_evidence_category_and_scope(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.crm.imports.store', $this->event()), $this->uploadPayload(['consent_status' => CrmContactConsent::STATUS_GRANTED]))
            ->assertSessionHasErrors(['consent_evidence', 'consent_category', 'consent_scope']);
    }

    public function test_mapping_page_lists_csv_headers(): void
    {
        $import = $this->uploadedImport();

        $this->actingAs($this->admin())
            ->get(route('admin.crm.imports.mapping.edit', $import))
            ->assertOk()
            ->assertSee('E-mail')
            ->assertSee('First')
            ->assertSee('Bib');
    }

    public function test_mapping_must_use_existing_headers_and_include_email(): void
    {
        $import = $this->uploadedImport();

        $this->actingAs($this->admin())
            ->put(route('admin.crm.imports.mapping.update', $import), ['mapping' => ['email' => 'Missing Column']])
            ->assertSessionHasErrors('mapping.email');
    }

    public function test_saving_mapping_can_store_a_reusable_preset(): void
    {
        $import = $this->uploadedImport();

        $this->actingAs($this->admin())
            ->put(route('admin.crm.imports.mapping.update', $import), [
                'mapping' => ['email' => 'E-mail', 'first_name' => 'First', 'bib_number' => 'Bib'],
                'preset_name' => 'River Run Club export',
            ])
            ->assertRedirect(route('admin.crm.imports.preview', $import));

        $this->assertSame('Bib', CrmMappingPreset::sole()->mapping['bib_number']);
        $this->assertSame('E-mail', $import->refresh()->mapping['email']);
    }

    public function test_mapping_page_can_be_prefilled_from_a_preset(): void
    {
        $preset = CrmMappingPreset::create(['name' => 'Club export', 'mapping' => ['email' => 'E-mail', 'bib_number' => 'Bib']]);

        $this->actingAs($this->admin())
            ->get(route('admin.crm.imports.mapping.edit', [$this->uploadedImport(), 'preset' => $preset->id]))
            ->assertOk()
            ->assertViewHas('mapping', fn (array $mapping): bool => $mapping['bib_number'] === 'Bib');
    }

    public function test_preview_shows_estimated_counts_and_sample_rows(): void
    {
        CrmContact::create(['email_normalized' => 'bo@example.com', 'email_original' => 'bo@example.com']);
        $import = $this->mappedImport();

        $this->actingAs($this->admin())
            ->get(route('admin.crm.imports.preview', $import))
            ->assertOk()
            ->assertViewHas('counts', [
                'rows' => 4,
                'valid' => 2,
                'invalid' => 1,
                'duplicates' => 1,
                'existingContacts' => 1,
            ])
            ->assertSee('ana@example.com')
            ->assertSee('Email is not a valid address.');
    }

    public function test_starting_an_import_queues_the_job_once(): void
    {
        Queue::fake();
        $import = $this->mappedImport();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.crm.imports.start', $import))->assertRedirect(route('admin.crm.imports.show', $import));
        $this->actingAs($admin)->post(route('admin.crm.imports.start', $import))->assertSessionHasErrors('import');

        $this->assertSame(CrmImport::STATUS_QUEUED, $import->refresh()->status);
        Queue::assertPushed(ProcessCrmImport::class, 1);
    }

    public function test_import_cannot_start_before_mapping_is_saved(): void
    {
        Queue::fake();

        $this->actingAs($this->admin())
            ->post(route('admin.crm.imports.start', $this->uploadedImport()))
            ->assertSessionHasErrors('import');

        Queue::assertNothingPushed();
    }

    public function test_only_failed_imports_can_be_retried(): void
    {
        Queue::fake();
        $import = $this->mappedImport();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.crm.imports.retry', $import))->assertSessionHasErrors('import');

        $import->forceFill(['status' => CrmImport::STATUS_FAILED])->save();
        $this->actingAs($admin)->post(route('admin.crm.imports.retry', $import))->assertRedirect(route('admin.crm.imports.show', $import));

        Queue::assertPushed(ProcessCrmImport::class, 1);
    }

    public function test_completed_import_shows_counts_and_offers_error_report_download(): void
    {
        $import = $this->mappedImport();
        ProcessCrmImport::dispatchSync($import->id);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.crm.imports.show', $import))
            ->assertOk()
            ->assertSee('Completed')
            ->assertSee(route('admin.crm.imports.errors', $import), false);

        $this->actingAs($admin)
            ->get(route('admin.crm.imports.errors', $import))
            ->assertOk()
            ->assertDownload('import-'.$import->id.'-errors.csv');
    }

    public function test_non_admin_cannot_reach_any_import_route(): void
    {
        $import = $this->mappedImport();
        $event = $import->event;
        /** @var User $user */
        $user = User::factory()->createOne();

        $this->actingAs($user);
        $this->get(route('admin.crm.imports.index'))->assertForbidden();
        $this->get(route('admin.crm.imports.create', $event))->assertForbidden();
        $this->post(route('admin.crm.imports.store', $event), $this->uploadPayload())->assertForbidden();
        $this->get(route('admin.crm.imports.show', $import))->assertForbidden();
        $this->get(route('admin.crm.imports.mapping.edit', $import))->assertForbidden();
        $this->put(route('admin.crm.imports.mapping.update', $import), ['mapping' => ['email' => 'E-mail']])->assertForbidden();
        $this->get(route('admin.crm.imports.preview', $import))->assertForbidden();
        $this->post(route('admin.crm.imports.start', $import))->assertForbidden();
        $this->post(route('admin.crm.imports.retry', $import))->assertForbidden();
        $this->get(route('admin.crm.imports.errors', $import))->assertForbidden();
        $this->get(route('admin.crm.contacts.index'))->assertForbidden();
        $this->get(route('admin.crm.events.participants', $event))->assertForbidden();
    }

    public function test_admin_event_pages_link_to_participant_import(): void
    {
        $event = $this->event();
        $admin = $this->admin();
        $importUrl = route('admin.crm.imports.create', $event);

        $this->actingAs($admin)->get(route('admin.media.show', $event))->assertOk()->assertSee($importUrl, false);
        $this->actingAs($admin)->get(route('events.edit', ['event' => $event->id]))->assertOk()->assertSee($importUrl, false);
    }

    public function test_photographer_event_edit_page_has_no_crm_links(): void
    {
        $event = $this->event();
        $photographerRole = Role::firstOrCreate(['name' => 'photographer']);
        /** @var User $owner */
        $owner = User::factory()->createOne();
        $owner->roles()->attach($photographerRole);
        $photographer = Photographer::create(['user_id' => $owner->id, 'first_name' => 'Event', 'last_name' => 'Photographer']);
        $photographer->forceFill(['status' => Photographer::STATUS_APPROVED])->save();
        $event->photographers()->attach($photographer);

        $this->actingAs($owner)
            ->get(route('events.edit', ['event' => $event->id]))
            ->assertOk()
            ->assertDontSee(route('admin.crm.imports.create', $event), false);
    }

    /** @param array<string, mixed> $overrides */
    private function uploadPayload(array $overrides = []): array
    {
        return array_merge([
            'file' => UploadedFile::fake()->createWithContent('participants.csv', self::CSV),
            'data_source' => 'Organizer export',
            'organizer_name' => 'River Run Club',
            'consent_status' => CrmContactConsent::STATUS_UNKNOWN,
        ], $overrides);
    }

    private function uploadedImport(): CrmImport
    {
        Storage::disk('local')->put('crm-imports/participants.csv', self::CSV);

        return CrmImport::create([
            'event_id' => $this->event()->id,
            'original_filename' => 'participants.csv',
            'disk_path' => 'crm-imports/participants.csv',
            'file_sha256' => hash('sha256', self::CSV),
            'mapping' => [],
            'data_source' => 'Organizer export',
            'consent_status' => CrmContactConsent::STATUS_UNKNOWN,
        ]);
    }

    private function mappedImport(): CrmImport
    {
        $import = $this->uploadedImport();
        $import->update(['mapping' => ['email' => 'E-mail', 'first_name' => 'First', 'bib_number' => 'Bib']]);

        return $import;
    }

    private function event(): Event
    {
        return Event::create([
            'title' => 'River Run',
            'slug' => Event::generateUniqueSlug('River Run'),
            'sport' => 'Running',
            'status' => Event::STATUS_DRAFT,
            'date_of_event' => '2026-10-10',
            'timezone' => 'America/New_York',
            'city' => 'Orlando',
            'state' => 'FL',
            'country_code' => 'US',
            'content' => 'CRM fixture.',
        ]);
    }

    private function admin(): User
    {
        /** @var User $admin */
        $admin = User::factory()->createOne();
        $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

        return $admin;
    }
}
