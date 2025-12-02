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
        'discount_rules',
        'sort_order',
        'apply_type',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'discount_rules' => 'array',
    ];

    /**
     * Scope a query to include promotions that are currently active.
     * Khi is_active = true, hiển thị ngay không cần đợi đến start_date.
     */
    public function scopeActive(Builder $query): Builder
    {
        $today = Carbon::today();

        return $query->where('is_active', true)
            // Bỏ kiểm tra start_date - khi is_active = true thì hiển thị ngay
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
            'discount' => 'Discount',
            'event' => 'Event',
            'movie' => 'Movie',
            default => 'Promotion',
        };
    }

    /**
     * Promotion có thể liên kết đến một phim cụ thể.
     */
    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    /**
     * Lấy URL ảnh banner từ storage.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image_path)) {
            return null;
        }

        // Hỗ trợ cả Base64 (dữ liệu cũ) và đường dẫn storage (dữ liệu mới)
        if (str_starts_with($this->image_path, 'data:')) {
            // Nếu là Base64 data URI (dữ liệu cũ), trả về trực tiếp
            return $this->image_path;
        }

        // Trả về asset URL từ storage
        return asset('storage/' . $this->image_path);
    }

    /**
     * Tính toán trạng thái hiển thị của khuyến mãi.
     * Trả về: 'active' (đang kích hoạt), 'inactive' (đã tắt), 'ended' (đã kết thúc)
     */
    public function getStatusAttribute(): string
    {
        $today = Carbon::today();

        // Nếu có end_date và đã hết hạn → đã kết thúc
        if ($this->end_date && $this->end_date->lt($today)) {
            return 'ended';
        }

        // Nếu không hết hạn, kiểm tra is_active
        return $this->is_active ? 'active' : 'inactive';
    }

    /**
     * Nhãn trạng thái hiển thị cho admin.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Active',
            'inactive' => 'Inactive',
            'ended' => 'Ended',
            default => 'Unknown',
        };
    }

    /**
     * Màu badge cho trạng thái.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-success',
            'inactive' => 'bg-secondary',
            'ended' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Kiểm tra xem sự kiện có đang trong thời gian hoạt động không.
     * Sự kiện được coi là đang hoạt động nếu:
     * - start_date <= today
     * - (end_date == null OR end_date >= today)
     */
    public function isCurrentlyActive(): bool
    {
        $today = Carbon::today();
        
        // Kiểm tra start_date
        if ($this->start_date && $this->start_date->gt($today)) {
            return false; // Chưa bắt đầu
        }
        
        // Kiểm tra end_date
        if ($this->end_date && $this->end_date->lt($today)) {
            return false; // Đã kết thúc
        }
        
        return true; // Đang trong thời gian hoạt động
    }
}

