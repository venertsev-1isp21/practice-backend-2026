<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class BookingStatus extends Model
{
        use HasApiTokens, HasFactory, Notifiable;
        protected $fillable = [
            'name',
        ];
        public function bookings()
    {
        return $this->hasMany(Booking::class, 'status_id');
    }
}
