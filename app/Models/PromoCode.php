<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents an administrator-managed promotional discount code.
 *
 * @property int $id
 * @property string $code
 * @property int $discount_percent
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PromoCode extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'code',
        'discount_percent',
        'is_active',
        'expires_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'date',
    ];
}