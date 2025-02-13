<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_en',
        'title_es',
        'title_pt',
        'content_en',
        'content_es',
        'content_pt',
        'slug',
        'published',
        'published_at',
        'date',
        'summary_en',
        'summary_es',
        'summary_pt',
        'image_url',
        'post_type_id',
        'file_url',
        'file_url_es',
        'file_url_ptBR',
    ];


    public function postType()
    {
        return $this->belongsTo(PostType::class);
    }


}
