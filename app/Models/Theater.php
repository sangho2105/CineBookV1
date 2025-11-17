<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Showtime;


class Theater extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'address',
        'seating_capacity',
    ];
    public function showtimes()
    {
        return $this->hasMany(Showtime::class);
    }
    public function seats()
    {
        return $this->hasMany(Seat::class);
    }
}