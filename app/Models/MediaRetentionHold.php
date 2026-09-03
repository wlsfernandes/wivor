<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaRetentionHold extends Model
{
    protected $fillable = ['event_id', 'photo_id', 'created_by', 'reason', 'review_at', 'released_at', 'released_by'];
    protected function casts(): array { return ['review_at' => 'datetime', 'released_at' => 'datetime']; }
}
