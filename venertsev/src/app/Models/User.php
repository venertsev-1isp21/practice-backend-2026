<?php

namespace App\Models;

use App\Models\Role;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }


    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }


    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }


    public function hasRoles(): bool
    {
        return $this->roles()->exists();
    }


    public function getRoleNamesAttribute()
    {
        return $this->roles->pluck('name');
    }
}