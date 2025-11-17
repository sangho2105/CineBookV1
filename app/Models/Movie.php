<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Showtime;
use Carbon\Carbon;


class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'poster_url',
        'genre',
        'director',
        'cast',
        'language',
        'duration_minutes',
        'trailer_url',
        'synopsis',
        'release_date',
        'rating_average',
        'status',
    ];

    protected $casts = [ //casts là một phương thức trong Laravel để chuyển đổi kiểu dữ liệu của các cột trong database
        'release_date' => 'date',
        'rating_average' => 'decimal:2',
    ];
    public function showtimes()
    {
        return $this->hasMany(Showtime::class);
    }

    /**
     * Auto-correct status based on release_date when creating/updating.
     * If a movie is marked as 'upcoming' but the release_date has arrived/passed,
     * force status to 'now_showing'.
     */
    protected static function booted()
    {
        static::saving(function (Movie $movie) {
            if (!empty($movie->release_date) && $movie->status === 'upcoming') {
                $today = Carbon::today();
                $release = $movie->getAttribute('release_date') instanceof \Illuminate\Support\Carbon
                    ? $movie->getAttribute('release_date')
                    : Carbon::parse($movie->release_date);
                if ($release->lte($today)) {
                    $movie->status = 'now_showing';
                }
            }
        });
    }

    /**
     * Refresh statuses in bulk for all movies where release_date has passed.
     * Useful for correcting existing records created before the saving hook was added.
     */
    public static function refreshStatuses(): int
    {
        return static::where('status', 'upcoming')
            ->whereDate('release_date', '<=', Carbon::today())
            ->update(['status' => 'now_showing']);
    }
}