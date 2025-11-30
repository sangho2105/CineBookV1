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
        'rated',
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

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites');
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

    /**
     * Lấy URL poster (hỗ trợ cả file storage, URL ngoài và Base64).
     */
    public function getPosterImageUrlAttribute(): ?string
    {
        if (empty($this->poster_url)) {
            return null;
        }

        // Hỗ trợ Base64 data URI (dữ liệu cũ)
        if (str_starts_with($this->poster_url, 'data:')) {
            return $this->poster_url;
        }

        // Nếu là URL (bắt đầu bằng http/https), trả về trực tiếp
        if (filter_var($this->poster_url, FILTER_VALIDATE_URL)) {
            return $this->poster_url;
        }

        // Nếu là đường dẫn file trong storage, trả về asset URL
        return asset('storage/' . $this->poster_url);
    }
}