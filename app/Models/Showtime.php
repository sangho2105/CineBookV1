<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Showtime extends Model
{
    use HasFactory;
    protected $fillable = [
        'movie_id',
        'theater_id',
        'show_date',
        'show_time',
        'gold_price',
        'platinum_price',
        'box_price',
        'is_peak_hour',
    ];
    
    protected $casts = [
        'show_date' => 'date',
        'show_time' => 'datetime:H:i',
        'gold_price' => 'decimal:2',
        'platinum_price' => 'decimal:2',
        'box_price' => 'decimal:2',
        'is_peak_hour' => 'boolean',
    ];
    // Relationship với Movie
public function movie()
{
    return $this->belongsTo(Movie::class);
}

// Relationship với Theater
public function theater()
{
    return $this->belongsTo(Theater::class);
}
public function bookings()
{
    return $this->hasMany(Booking::class);
}
}
