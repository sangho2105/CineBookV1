<?php

namespace App\Models;

use App\Models\Movie;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'description',
        'conditions',
        'movie_id',
        'image_path',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to include promotions that are currently active.
     */
    public function scopeActive(Builder $query): Builder
    {
        $today = Carbon::today();

        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', $today)
            ->where(function (Builder $query) use ($today) {
                $query->whereNull('end_date')
                      ->orWhereDate('end_date', '>=', $today);
            })
            ->when(
                request()->is('admin/*') === false,
                fn (Builder $builder) => $builder->where(function (Builder $inner) {
                    $inner->where('category', '!=', 'movie')
                        ->orWhereNotNull('movie_id');
                })
            );
    }

    /**
     * Human readable category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'discount' => 'Giảm giá',
            'event' => 'Sự kiện',
            'movie' => 'Phim',
            default => 'Ưu đãi',
        };
    }

    /**
     * Promotion có thể liên kết đến một phim cụ thể.
     */
    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }
}

