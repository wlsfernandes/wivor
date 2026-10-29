<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One canonical CRM person per normalized email address, shared across events.
 *
 * @property int $id
 * @property string $email_normalized
 * @property string $email_original
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $full_name
 * @property string|null $phone
 * @property string|null $first_source
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CrmContact extends Model
{
    public const SOURCE_CSV_IMPORT = 'csv_import';
    public const SOURCE_ORDER = 'order';
    public const SOURCE_CUSTOMER_ACCOUNT = 'customer_account';

    /** @var list<string> */
    protected $fillable = [
        'email_normalized',
        'email_original',
        'first_name',
        'last_name',
        'full_name',
        'phone',
        'first_source',
    ];

    /** Build the matching key used to deduplicate contacts across imports. */
    public static function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    /** Events this contact took part in, with per-event bib and division. */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'crm_event_contacts')
            ->using(CrmEventContact::class)
            ->as('participation')
            ->withPivot(['id', 'crm_import_id', 'bib_number', 'division', 'extra_fields'])
            ->withTimestamps();
    }

    public function consents(): HasMany
    {
        return $this->hasMany(CrmContactConsent::class);
    }

    public function suppressions(): HasMany
    {
        return $this->hasMany(CrmSuppression::class);
    }
}
