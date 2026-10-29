<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Links a CRM contact to one event and holds event-specific participant data.
 *
 * @property int $id
 * @property int $event_id
 * @property int $crm_contact_id
 * @property int|null $crm_import_id
 * @property string|null $bib_number
 * @property string|null $division
 * @property array<string, string>|null $extra_fields
 */
class CrmEventContact extends Pivot
{
    protected $table = 'crm_event_contacts';

    public $incrementing = true;

    /** @var list<string> */
    protected $fillable = [
        'event_id',
        'crm_contact_id',
        'crm_import_id',
        'bib_number',
        'division',
        'extra_fields',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'extra_fields' => 'array',
    ];
}
