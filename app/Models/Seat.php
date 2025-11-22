<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Theater;
use App\Models\Booking;
class Seat extends Model
{
    use HasFactory;
    protected $fillable = [
        'theater_id',
        'room_id',
        'seat_number',
        'seat_category',
        'row_number',
        'is_available',
    ];
    
    protected $casts = [
        'is_available' => 'boolean',
    ];
    
    // Relationship với Theater (giữ lại để tương thích)
    public function theater()
    {
        return $this->belongsTo(Theater::class);
    }

    // Relationship với Room
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
    
    // Relationship với Bookings (many-to-many qua booking_seats)
    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_seats');
    }
}
