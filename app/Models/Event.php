<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'published',
        'published_at',
        'date_of_event',
        'image_url',
        'file_url',
        'content',
        'summary',
    ];
    protected $casts = [
        'published_at' => 'datetime',
        'date_of_event' => 'datetime', // or 'datetime' if you care about time too
    ];

    public function photographers()
    {
        return $this->belongsToMany(Photographer::class);
    }

    public static function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 2;

        // Check if the slug exists
        while (self::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }


}
