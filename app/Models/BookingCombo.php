<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingCombo extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'combo_name',
        'quantity',
        'unit_price',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}


