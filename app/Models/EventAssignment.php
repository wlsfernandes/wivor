<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAssignment extends Model
{
    protected $table = 'event_photographer';
    protected $fillable = ['event_id', 'photographer_id', 'status', 'upload_deadline_at', 'rights_confirmed_at'];
    protected function casts(): array { return ['upload_deadline_at' => 'datetime', 'rights_confirmed_at' => 'datetime']; }
    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function photographer(): BelongsTo { return $this->belongsTo(Photographer::class); }
}
