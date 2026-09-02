<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function hasRole($role)
    {
        return $this->roles->pluck('name')->contains($role);
    }

    /** Determine whether this user may enter the photographer workspace. */
    public function canAccessPhotographerArea(): bool
    {
        return $this->hasRole('photographer')
            && $this->hasVerifiedEmail()
            && $this->photographer()
                ->where('status', Photographer::STATUS_APPROVED)
                ->exists();
    }

    public function photographer()
    {
        return $this->hasOne(Photographer::class);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

}
