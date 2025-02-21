<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photographer extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'camera_model',
        'profile_url',
        'about',
        'address',
        'city',
        'state',
        'zipcode'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
