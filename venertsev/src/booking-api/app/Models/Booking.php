<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Booking extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
    'user_id',
    'room_id',
    'status_id',
    'start_time',
    'end_time',
    ];
        public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function status()
    {
        return $this->belongsTo(BookingStatus::class, 'status_id');
    }
}
