<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Combo extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image_path',
        'price',
        'is_active',
        'is_hidden',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    /**
     * Scope để lấy các combo đang hoạt động
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope để lấy các combo không bị ẩn
     */
    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }

    /**
     * Kiểm tra xem combo đã có khách hàng đặt hay chưa
     */
    public function hasBookings(): bool
    {
        return \App\Models\BookingCombo::where('combo_name', $this->name)->exists();
    }

    /**
     * Relationship với ComboItem
     */
    public function items()
    {
        return $this->hasMany(ComboItem::class);
    }

    /**
     * Lấy URL ảnh combo
     */
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return null;
    }
}
