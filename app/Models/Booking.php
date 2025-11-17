<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'showtime_id',
        'booking_date',
        'total_amount',
        'payment_status',
        'booking_id_unique',
    ];
    
    protected $casts = [
        'booking_date' => 'datetime',
        'total_amount' => 'decimal:2',
    ];
    
    // Relationship với User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Relationship với Showtime
    public function showtime()
    {
        return $this->belongsTo(Showtime::class);
    }
    
    // Relationship với Seats (many-to-many qua booking_seats)
    public function seats()
    {
        return $this->belongsToMany(Seat::class, 'booking_seats');
    }
    public function combos()
    {
        return $this->hasMany(BookingCombo::class);
    }
    
    // Tạo booking ID unique tự động
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($booking) {
            if (empty($booking->booking_id_unique)) {
                $booking->booking_id_unique = 'BK' . strtoupper(uniqid());
            }
        });
    }
}
