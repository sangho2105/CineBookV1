<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Showtime extends Model
{
    use HasFactory;
    protected $fillable = [
        'movie_id',
        'theater_id',
        'room_id',
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
    
    /**
     * Lấy show_time dưới dạng chuỗi H:i một cách an toàn
     * Parse thủ công từ chuỗi H:i thay vì dùng strtotime
     */
    public function getFormattedShowTime($format = 'H:i'): string
    {
        // Kiểm tra null hoặc empty
        if (empty($this->show_time)) {
            return '00:00';
        }
        
        if ($this->show_time instanceof Carbon) {
            return $this->show_time->format($format);
        }
        
        $timeStr = is_string($this->show_time) ? $this->show_time : (string)$this->show_time;
        
        // Kiểm tra chuỗi rỗng
        if (empty($timeStr) || trim($timeStr) === '') {
            return '00:00';
        }
        
        // Loại bỏ khoảng trắng và lấy phần đầu nếu có giây
        $timeStr = trim($timeStr);
        if (strlen($timeStr) > 5) {
            $timeStr = substr($timeStr, 0, 5); // Chỉ lấy H:i
        }
        
        // Parse thủ công từ chuỗi H:i
        $timeParts = explode(':', $timeStr);
        
        // Kiểm tra format hợp lệ (phải có ít nhất 2 phần: giờ và phút)
        if (count($timeParts) < 2) {
            return '00:00';
        }
        
        $hour = (int)($timeParts[0] ?? 0);
        $minute = (int)($timeParts[1] ?? 0);
        
        // Validate range
        $hour = max(0, min(23, $hour));
        $minute = max(0, min(59, $minute));
        
        // Tạo Carbon instance để format
        $time = Carbon::createFromTime($hour, $minute, 0);
        return $time->format($format);
    }
    // Relationship với Movie
public function movie()
{
    return $this->belongsTo(Movie::class);
}

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
public function bookings()
{
    return $this->hasMany(Booking::class);
}
}
