<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents editable policy content displayed on the public website.
 *
 * @property int $id
 * @property string $slug
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class WebsitePolicy extends Model
{
    public const PRIVACY = 'privacy';
    public const TERMS = 'terms';
    public const REFUND = 'refund';

    /** @var array<string, string> */
    public const TITLES = [
        self::PRIVACY => 'Privacy Policy',
        self::TERMS => 'Terms of Use',
        self::REFUND => 'Refund Policy',
    ];

    /**
     * Attributes that may be mass assigned.
     *
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'content',
    ];
}
