<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaActivityLog extends Model
{
    public const UPDATED_AT = null;
    protected $fillable = ['event_id', 'photo_id', 'actor_id', 'action', 'details'];
    protected function casts(): array { return ['details' => 'array']; }
}
