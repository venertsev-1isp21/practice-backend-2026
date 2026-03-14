<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class RoomFeature extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
        protected $fillable = [
            'feature',
            'room_id',
        ];
        public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
