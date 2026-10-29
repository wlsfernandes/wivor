<?php

namespace Tests\Feature;

use App\Models\CrmContact;
use App\Models\CrmContactConsent;
use App\Models\CrmEventContact;
use App\Models\CrmImport;
use App\Models\CrmSuppression;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmContactDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_is_normalized_by_trimming_and_lowercasing(): void
    {
        $this->assertSame('ana@example.com', CrmContact::normalizeEmail('  Ana@Example.COM '));
    }

    public function test_the_same_normalized_email_cannot_create_two_contacts(): void
    {
        $this->contact('ana@example.com');

        $this->expectException(QueryException::class);

        $this->contact('ana@example.com');
    }

    public function test_one_athlete_in_many_events_keeps_a_separate_bib_per_event(): void
    {
        $contact = $this->contact('runner@example.com');
        $riverRun = $this->event('River Run');
        $cityMarathon = $this->event('City Marathon');

        $contact->events()->attach($riverRun, ['bib_number' => '101', 'division' => 'M30']);
        $contact->events()->attach($cityMarathon, ['bib_number' => '7', 'division' => 'Open']);

        $bibsByEvent = $contact->events()->orderBy('title')->get()->mapWithKeys(
            fn (Event $event): array => [$event->title => $event->participation->bib_number]
        );

        $this->assertSame(['City Marathon' => '7', 'River Run' => '101'], $bibsByEvent->all());
        $this->assertSame(1, CrmContact::count());
    }

    public function test_an_event_cannot_link_the_same_contact_twice(): void
    {
        $contact = $this->contact('runner@example.com');
        $event = $this->event('River Run');
        $contact->events()->attach($event, ['bib_number' => '101']);

        $this->expectException(QueryException::class);

        CrmEventContact::create(['event_id' => $event->id, 'crm_contact_id' => $contact->id, 'bib_number' => '102']);
    }

    public function test_deleting_an_event_removes_its_participant_links_but_keeps_the_contact(): void
    {
        $contact = $this->contact('runner@example.com');
        $event = $this->event('River Run');
        $contact->events()->attach($event, ['bib_number' => '101']);

        $event->delete();

        $this->assertDatabaseCount('crm_event_contacts', 0);
        $this->assertDatabaseHas('crm_contacts', ['id' => $contact->id]);
    }

    public function test_suppression_history_survives_contact_deletion(): void
    {
        $contact = $this->contact('runner@example.com');
        $suppression = CrmSuppression::create([
            'crm_contact_id' => $contact->id,
            'email_normalized' => $contact->email_normalized,
            'reason' => CrmSuppression::REASON_UNSUBSCRIBE,
            'category' => CrmContactConsent::CATEGORY_MARKETING,
        ]);

        $contact->delete();

        $suppression->refresh();
        $this->assertNull($suppression->crm_contact_id);
        $this->assertSame('runner@example.com', $suppression->email_normalized);
    }

    public function test_consent_records_keep_scope_source_and_evidence(): void
    {
        $contact = $this->contact('runner@example.com');
        $event = $this->event('River Run');

        $consent = $contact->consents()->create([
            'category' => CrmContactConsent::CATEGORY_OPERATIONAL,
            'scope_event_id' => $event->id,
            'status' => CrmContactConsent::STATUS_GRANTED,
            'source' => 'Organizer registration form',
            'evidence' => 'Registration checkbox text v2',
            'recorded_at' => now(),
        ]);

        $this->assertTrue($consent->scopedEvent->is($event));
        $this->assertNull($consent->revoked_at);
    }

    public function test_import_records_its_event_operator_and_column_mapping(): void
    {
        $operator = User::factory()->create();
        $event = $this->event('River Run');

        $import = CrmImport::create([
            'event_id' => $event->id,
            'user_id' => $operator->id,
            'original_filename' => 'river-run.csv',
            'disk_path' => 'crm-imports/abc.csv',
            'file_sha256' => str_repeat('a', 64),
            'mapping' => ['email' => 'E-mail', 'bib_number' => 'Bib'],
            'data_source' => 'Organizer export',
            'consent_status' => CrmContactConsent::STATUS_UNKNOWN,
        ]);

        $import->refresh();
        $this->assertSame(CrmImport::STATUS_PENDING, $import->status);
        $this->assertSame('Bib', $import->mapping['bib_number']);
        $this->assertTrue($import->event->is($event));
        $this->assertTrue($import->operator->is($operator));
    }

    private function contact(string $email): CrmContact
    {
        return CrmContact::create([
            'email_normalized' => CrmContact::normalizeEmail($email),
            'email_original' => $email,
        ]);
    }

    private function event(string $title): Event
    {
        return Event::create([
            'title' => $title,
            'slug' => Event::generateUniqueSlug($title),
            'content' => 'CRM fixture.',
        ]);
    }
}
