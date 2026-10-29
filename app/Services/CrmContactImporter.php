<?php

namespace App\Services;

use App\Models\CrmContact;
use App\Models\CrmContactConsent;
use App\Models\CrmEventContact;
use App\Models\CrmImport;
use App\Models\CrmSuppression;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Upsert validated import rows into the contact directory and the import's event, idempotently.
 */
class CrmContactImporter
{
    /** Contact fields that an import may fill but never overwrite. */
    private const FILLABLE_WHEN_EMPTY = ['first_name', 'last_name', 'full_name', 'phone'];

    /** Participant fields that a newer import value replaces. */
    private const PARTICIPANT_FIELDS = ['bib_number', 'division', 'extra_fields'];

    /** @param list<CrmImportRowResult> $rows valid rows with unique emails */
    public function importRows(CrmImport $import, array $rows): void
    {
        if ($rows === []) {
            return;
        }

        DB::transaction(function () use ($import, $rows): void {
            $contactsByEmail = $this->upsertContacts($rows);

            foreach ($rows as $row) {
                $contact = $contactsByEmail[$row->fields['email_normalized']];
                $this->upsertParticipation($import, $contact, $row->fields);
                $this->recordConsent($import, $contact);
            }
        });
    }

    /**
     * @param list<CrmImportRowResult> $rows
     * @return Collection<string, CrmContact>
     */
    private function upsertContacts(array $rows): Collection
    {
        $now = now();

        // insertOrIgnore keeps concurrent imports of the same address from failing on the unique key.
        CrmContact::query()->insertOrIgnore(array_map(fn (CrmImportRowResult $row): array => [
            'email_normalized' => $row->fields['email_normalized'],
            'email_original' => $row->fields['email_original'],
            'first_name' => $row->fields['first_name'],
            'last_name' => $row->fields['last_name'],
            'full_name' => $row->fields['full_name'],
            'phone' => $row->fields['phone'],
            'first_source' => CrmContact::SOURCE_CSV_IMPORT,
            'created_at' => $now,
            'updated_at' => $now,
        ], $rows));

        $contactsByEmail = CrmContact::query()
            ->whereIn('email_normalized', array_map(fn (CrmImportRowResult $row): string => $row->fields['email_normalized'], $rows))
            ->get()
            ->keyBy('email_normalized');

        foreach ($rows as $row) {
            $this->fillEmptyContactFields($contactsByEmail[$row->fields['email_normalized']], $row->fields);
        }

        return $contactsByEmail;
    }

    /** @param array<string, mixed> $fields */
    private function fillEmptyContactFields(CrmContact $contact, array $fields): void
    {
        foreach (self::FILLABLE_WHEN_EMPTY as $field) {
            if ($contact->{$field} === null && $fields[$field] !== null) {
                $contact->{$field} = $fields[$field];
            }
        }

        $contact->save();
    }

    /** @param array<string, mixed> $fields */
    private function upsertParticipation(CrmImport $import, CrmContact $contact, array $fields): void
    {
        $participation = CrmEventContact::query()->firstOrNew([
            'event_id' => $import->event_id,
            'crm_contact_id' => $contact->id,
        ]);

        foreach (self::PARTICIPANT_FIELDS as $field) {
            if ($fields[$field] !== null) {
                $participation->{$field} = $fields[$field];
            }
        }

        $participation->crm_import_id = $import->id;
        $participation->save();
    }

    private function recordConsent(CrmImport $import, CrmContact $contact): void
    {
        if ($import->consent_status !== CrmContactConsent::STATUS_GRANTED) {
            return;
        }

        if ($this->hasOptedOut($contact, $import->consent_category)) {
            return;
        }

        $contact->consents()->firstOrCreate([
            'category' => $import->consent_category,
            'scope_event_id' => $import->consent_scope === CrmImport::CONSENT_SCOPE_EVENT ? $import->event_id : null,
            'source' => 'CSV import #'.$import->id,
        ], [
            'status' => CrmContactConsent::STATUS_GRANTED,
            'evidence' => $import->consent_evidence,
            'recorded_at' => now(),
        ]);
    }

    /** An import never re-grants a category the person revoked or was suppressed from. */
    private function hasOptedOut(CrmContact $contact, string $category): bool
    {
        $hasRevocation = $contact->consents()
            ->where('category', $category)
            ->where(fn ($query) => $query->whereNotNull('revoked_at')->orWhere('status', CrmContactConsent::STATUS_REVOKED))
            ->exists();

        return $hasRevocation || CrmSuppression::query()
            ->where('email_normalized', $contact->email_normalized)
            ->where('category', $category)
            ->exists();
    }
}
