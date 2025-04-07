<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
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
