<?php

namespace App\Services;

use App\Models\CrmContact;
use App\Models\CrmEventContact;
use App\Models\Event;
use App\Models\User;

/**
 * Build the personalization values for one recipient, or sample values for a test send.
 */
class CrmRecipientValues
{
    private const SAMPLE_BIB_NUMBER = '1234';

    /** @return array<string, string|null> */
    public static function forContact(CrmContact $contact, ?Event $event): array
    {
        $bibNumber = $event === null ? null : CrmEventContact::query()
            ->where('event_id', $event->id)
            ->where('crm_contact_id', $contact->id)
            ->value('bib_number');

        return array_merge(self::eventValues($event), [
            'first_name' => $contact->first_name ?? self::firstWord($contact->full_name),
            'last_name' => $contact->last_name,
            'bib_number' => $bibNumber,
        ]);
    }

    /** @return array<string, string|null> */
    public static function sample(User $administrator, ?Event $event): array
    {
        return array_merge(self::eventValues($event), [
            'first_name' => self::firstWord($administrator->name),
            'last_name' => null,
            'bib_number' => $event === null ? null : self::SAMPLE_BIB_NUMBER,
        ]);
    }

    /** @return array<string, string|null> */
    private static function eventValues(?Event $event): array
    {
        if ($event === null) {
            return ['event_name' => null, 'event_date' => null, 'event_url' => null, 'photo_gallery_url' => null];
        }

        return [
            'event_name' => $event->title,
            'event_date' => $event->date_label,
            'event_url' => route('events.show', $event),
            'photo_gallery_url' => route('events.photos.index', $event),
        ];
    }

    private static function firstWord(?string $name): ?string
    {
        $name = trim((string) $name);

        return $name === '' ? null : explode(' ', $name)[0];
    }
}
