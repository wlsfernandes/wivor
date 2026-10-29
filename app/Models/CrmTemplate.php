<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Reusable campaign content that administrators start new campaigns from.
 *
 * @property int $id
 * @property string $name
 * @property string $subject
 * @property string|null $preview_text
 * @property string $body_html
 * @property string|null $text_body
 * @property string|null $cta_label
 * @property string|null $cta_url
 */
class CrmTemplate extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'name',
        'subject',
        'preview_text',
        'body_html',
        'text_body',
        'cta_label',
        'cta_url',
    ];
}
