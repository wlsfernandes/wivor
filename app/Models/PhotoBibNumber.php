<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotoBibNumber extends Model
{
    protected $fillable = ['bib_number', 'confidence'];

    protected function casts(): array
    {
        return ['confidence' => 'float'];
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }
}
