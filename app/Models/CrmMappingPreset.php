<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A saved CSV column mapping that administrators reuse for organizers with the same export layout.
 *
 * @property int $id
 * @property string $name
 * @property array<string, mixed> $mapping
 */
class CrmMappingPreset extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'name',
        'mapping',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'mapping' => 'array',
    ];
}
