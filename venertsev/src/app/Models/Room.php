<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Room extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'capacity',
        'location',
    ];
        public function features()
    {
        return $this->hasMany(RoomFeature::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
