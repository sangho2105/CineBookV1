<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
class ShowtimeController extends Controller
{
    /**
     * Kiểm tra xung đột thời gian với các suất chiếu khác
     */
    private function checkTimeConflict($roomId, $showDate, $showTime, $movieDuration, $excludeId = null)
    {
        // Parse show_time - có thể là "H:i" hoặc "H:i:s"
        $timeStr = is_string($showTime) ? $showTime : (is_object($showTime) ? $showTime->format('H:i') : (string)$showTime);
        
        // Kiểm tra chuỗi rỗng
        if (empty($timeStr) || trim($timeStr) === '') {
            throw ValidationException::withMessages([
                'show_time' => 'Showtime is invalid.',
            ]);
        }
        
        $timeStr = trim($timeStr);
        if (strlen($timeStr) > 5) {
            $timeStr = substr($timeStr, 0, 5); // Chỉ lấy H:i
        }
        
        // Parse thủ công từ chuỗi H:i
        $timeParts = explode(':', $timeStr);
        
        // Kiểm tra format hợp lệ
        if (count($timeParts) < 2) {
            throw ValidationException::withMessages([
                'show_time' => 'Invalid time format. Please enter in HH:mm format.',
            ]);
        }
        
        $hour = (int)($timeParts[0] ?? 0);
        $minute = (int)($timeParts[1] ?? 0);
        
        // Validate range
        if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
            throw ValidationException::withMessages([
                'show_time' => 'Invalid time. Hour must be 0-23, minute must be 0-59.',
            ]);
        }
        
        // Parse show_date
        $showDateCarbon = $showDate instanceof Carbon ? $showDate : Carbon::parse($showDate);
        
        // Tạo datetime cho suất chiếu mới
        $newStartDateTime = Carbon::create(
            $showDateCarbon->year,
            $showDateCarbon->month,
            $showDateCarbon->day,
            $hour,
            $minute,
            0
        );
        $newEndDateTime = $newStartDateTime->copy()->addMinutes($movieDuration);

        // Lấy tất cả suất chiếu trong cùng phòng và cùng ngày
        $query = Showtime::where('room_id', $roomId)
            ->whereDate('show_date', $showDate);

        // Nếu đang edit, loại trừ showtime hiện tại
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $existingShowtimes = $query->with('movie')->get();

        foreach ($existingShowtimes as $existing) {
            // Lấy thời gian bắt đầu và kết thúc của suất chiếu hiện có
            $existingDate = $existing->show_date instanceof Carbon 
                ? $existing->show_date
                : Carbon::parse($existing->show_date);
            
            // Parse show_time thủ công
            $existingTimeStr = '';
            if ($existing->show_time instanceof Carbon) {
                $existingTimeStr = $existing->show_time->format('H:i');
            } else {
                $existingTimeStr = is_string($existing->show_time) ? $existing->show_time : (string)$existing->show_time;
                $existingTimeStr = trim($existingTimeStr);
                if (strlen($existingTimeStr) > 5) {
                    $existingTimeStr = substr($existingTimeStr, 0, 5); // Chỉ lấy H:i
                }
            }
            
            // Bỏ qua nếu time không hợp lệ
            if (empty($existingTimeStr)) {
                continue;
            }
            
            $existingTimeParts = explode(':', $existingTimeStr);
            
            // Bỏ qua nếu format không đúng
            if (count($existingTimeParts) < 2) {
                continue;
            }
            
            $existingHour = (int)($existingTimeParts[0] ?? 0);
            $existingMinute = (int)($existingTimeParts[1] ?? 0);
            
            // Validate range và bỏ qua nếu không hợp lệ
            if ($existingHour < 0 || $existingHour > 23 || $existingMinute < 0 || $existingMinute > 59) {
                continue;
            }
            
            // Kiểm tra trùng giờ bắt đầu chính xác (cùng giờ:phút)
            if ($hour === $existingHour && $minute === $existingMinute) {
                throw ValidationException::withMessages([
                    'show_time' => "Another showtime already starts at {$timeStr} in this room. Cannot create multiple showtimes with the same start time for one room.",
                ]);
            }
            
            // Tạo datetime từ các thành phần riêng biệt
            $existingStartDateTime = Carbon::create(
                $existingDate->year,
                $existingDate->month,
                $existingDate->day,
                $existingHour,
                $existingMinute,
                0
            );
            $existingDuration = $existing->movie->duration_minutes ?? 0;
            $existingEndDateTime = $existingStartDateTime->copy()->addMinutes($existingDuration);

            // Kiểm tra xung đột: thời gian bắt đầu mới nằm trong khoảng thời gian phim cũ đang chiếu
            if ($newStartDateTime->between($existingStartDateTime, $existingEndDateTime, false)) {
                throw ValidationException::withMessages([
                    'show_time' => "This time conflicts with another showtime in progress in this room. Existing showtime: {$existingStartDateTime->format('H:i')} - {$existingEndDateTime->format('H:i')}",
                ]);
            }

            // Kiểm tra xung đột: thời gian kết thúc mới nằm trong khoảng thời gian phim cũ đang chiếu
            if ($newEndDateTime->between($existingStartDateTime, $existingEndDateTime, false)) {
                throw ValidationException::withMessages([
                    'show_time' => "This time conflicts with another showtime in progress in this room. Existing showtime: {$existingStartDateTime->format('H:i')} - {$existingEndDateTime->format('H:i')}",
                ]);
            }

            // Kiểm tra xung đột: phim cũ bao trùm phim mới
            if ($newStartDateTime->lt($existingStartDateTime) && $newEndDateTime->gt($existingEndDateTime)) {
                throw ValidationException::withMessages([
                    'show_time' => "This time conflicts with another showtime in progress in this room. Existing showtime: {$existingStartDateTime->format('H:i')} - {$existingEndDateTime->format('H:i')}",
                ]);
            }

            // Kiểm tra xung đột: phim cũ bắt đầu trong khoảng thời gian phim mới đang chiếu
            if ($existingStartDateTime->between($newStartDateTime, $newEndDateTime, false)) {
                throw ValidationException::withMessages([
                    'show_time' => "This time conflicts with another showtime in progress in this room. Existing showtime: {$existingStartDateTime->format('H:i')} - {$existingEndDateTime->format('H:i')}",
                ]);
            }

            // Kiểm tra: nếu suất chiếu hiện có kết thúc trước hoặc bằng thời gian bắt đầu mới, phải cách ít nhất 20 phút
            if ($existingEndDateTime->lte($newStartDateTime)) {
                $requiredStartTime = $existingEndDateTime->copy()->addMinutes(20);
                if ($newStartDateTime->lt($requiredStartTime)) {
                    throw ValidationException::withMessages([
                        'show_time' => "This time is too close to the previous showtime. Previous showtime ends at {$existingEndDateTime->format('H:i')}. You must schedule after {$requiredStartTime->format('H:i')} (at least 20 minutes apart for theater cleanup).",
                    ]);
                }
            }

            // Kiểm tra: nếu suất chiếu mới kết thúc trước hoặc bằng thời gian bắt đầu của suất chiếu hiện có, phải cách ít nhất 20 phút
            if ($newEndDateTime->lte($existingStartDateTime)) {
                $requiredEndTime = $existingStartDateTime->copy()->subMinutes(20);
                if ($newEndDateTime->gt($requiredEndTime)) {
                    throw ValidationException::withMessages([
                        'show_time' => "This time is too close to the next showtime. Next showtime starts at {$existingStartDateTime->format('H:i')}. You must schedule to end before {$requiredEndTime->format('H:i')} (at least 20 minutes apart for theater cleanup).",
                    ]);
                }
            }
        }

        return true;
    }
   
    public function index(Request $request)
    {
        $query = Showtime::with(['movie', 'room', 'bookings.seats', 'bookings.combos']);
        
        // Lọc theo tên phim
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('movie', function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%');
            });
        }
        
        // Lọc theo phòng
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }
        
        // Lọc theo trạng thái
        $now = Carbon::now('Asia/Ho_Chi_Minh');
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'upcoming') {
                // Chỉ lấy suất chiếu sắp tới (chưa bắt đầu)
                $query->where(function($q) use ($now) {
                    $q->where('show_date', '>', $now->toDateString())
                      ->orWhere(function($q2) use ($now) {
                          $q2->whereDate('show_date', $now->toDateString())
                             ->whereRaw("TIME(show_time) > TIME(?)", [$now->format('H:i:s')]);
                      });
                });
            } elseif ($status === 'past') {
                // Chỉ lấy suất chiếu đã qua (bao gồm cả suất chiếu hôm nay đã kết thúc)
                $query->where(function($q) use ($now) {
                    // Ngày đã qua
                    $q->where('show_date', '<', $now->toDateString())
                      // Hoặc ngày hôm nay nhưng đã kết thúc (show_time + duration < now)
                      ->orWhere(function($q2) use ($now) {
                          $q2->whereDate('show_date', $now->toDateString())
                             ->whereRaw("
                                 ADDTIME(
                                     TIME(showtimes.show_time),
                                     SEC_TO_TIME((SELECT duration_minutes FROM movies WHERE movies.id = showtimes.movie_id) * 60)
                                 ) <= TIME(?)
                             ", [$now->format('H:i:s')]);
                      });
                });
            } elseif ($status === 'now_showing') {
                // Chỉ lấy suất chiếu đang chiếu (đã bắt đầu nhưng chưa kết thúc)
                $query->where(function($q) use ($now) {
                    // Ngày hôm nay: đã bắt đầu (show_time <= now) nhưng chưa kết thúc (show_time + duration > now)
                    $q->whereDate('show_date', $now->toDateString())
                      ->whereRaw("TIME(showtimes.show_time) <= TIME(?)", [$now->format('H:i:s')])
                      ->whereRaw("
                          ADDTIME(
                              TIME(showtimes.show_time),
                              SEC_TO_TIME((SELECT duration_minutes FROM movies WHERE movies.id = showtimes.movie_id) * 60)
                          ) > TIME(?)
                      ", [$now->format('H:i:s')]);
                });
            }
        }
        
        // Sắp xếp: Sắp tới trước (theo ngày tăng dần, giờ tăng dần), sau đó là đã qua (theo ngày giảm dần)
        $query->orderByRaw("
            CASE 
                WHEN show_date > ? OR (show_date = ? AND TIME(show_time) >= TIME(?)) THEN 0
                ELSE 1
            END ASC,
            CASE 
                WHEN show_date > ? OR (show_date = ? AND TIME(show_time) >= TIME(?)) THEN show_date
                ELSE '9999-12-31'
            END ASC,
            CASE 
                WHEN show_date > ? OR (show_date = ? AND TIME(show_time) >= TIME(?)) THEN TIME(show_time)
                ELSE '23:59:59'
            END ASC,
            show_date DESC,
            TIME(show_time) DESC
        ", [
            $now->toDateString(), $now->toDateString(), $now->format('H:i:s'),
            $now->toDateString(), $now->toDateString(), $now->format('H:i:s'),
            $now->toDateString(), $now->toDateString(), $now->format('H:i:s')
        ]);
        
        $showtimes = $query->paginate(7)
            ->withQueryString();
        
        // Map qua từng item để tính stats
        $showtimes->getCollection()->transform(function ($showtime) {
            $completedBookings = $showtime->bookings->where('payment_status', 'completed');
            $seatCount = 0;
            $byCategory = ['Gold' => 0, 'Platinum' => 0, 'Box' => 0];
            foreach ($completedBookings as $bk) {
                foreach ($bk->seats as $s) {
                    $seatCount += 1;
                    if (isset($byCategory[$s->seat_category])) {
                        $byCategory[$s->seat_category] += 1;
                    }
                }
            }
            $comboTotals = [];
            foreach ($completedBookings as $bk) {
                foreach ($bk->combos as $cb) {
                    $comboTotals[$cb->combo_name] = ($comboTotals[$cb->combo_name] ?? 0) + $cb->quantity;
                }
            }
            $showtime->stats = [
                'seat_count' => $seatCount,
                'by_category' => $byCategory,
                'combos' => $comboTotals,
            ];
            return $showtime;
        });
        
        // Lấy danh sách phòng cho filter
        $rooms = Room::orderBy('room_number')->get();
        
        return view('showtimes.index', compact('showtimes', 'rooms'));
    }
public function create()
{
    $movies = Movie::all();
    $rooms = Room::orderBy('room_number')->get();
    return view('showtimes.create', compact('movies', 'rooms'));
}
public function store(Request $request)
{
    // Merge giá trị mặc định cho checkbox trước khi validate
    $request->merge([
        'is_peak_hour' => $request->has('is_peak_hour') ? 1 : 0
    ]);

    $validated = $request->validate([
        'movie_id' => 'required|exists:movies,id',
        'room_id' => 'required|exists:rooms,id',
        'show_date' => [
            'required',
            'date',
            'after_or_equal:today',
        ],
        'show_time' => 'required',
        'gold_price' => 'required|numeric|min:1|max:1000',
        'platinum_price' => 'required|numeric|min:1|max:1000',
        'box_price' => 'required|numeric|min:1|max:1000',
        'is_peak_hour' => 'boolean',
    ]);

    // Validation: Thời gian suất chiếu phải lớn hơn thời gian hiện tại ít nhất 1 giờ
    $now = Carbon::now('Asia/Ho_Chi_Minh');
    $showDate = Carbon::parse($validated['show_date'])->setTimezone('Asia/Ho_Chi_Minh');
    
    // Parse show_time an toàn với định dạng cụ thể
    $timeStr = $validated['show_time'];
    // Loại bỏ giây nếu có (chỉ lấy H:i)
    if (strlen($timeStr) > 5) {
        $timeStr = substr($timeStr, 0, 5);
    }
    $timeParts = explode(':', $timeStr);
    $hour = (int)($timeParts[0] ?? 0);
    $minute = (int)($timeParts[1] ?? 0);
    
    // Tạo datetime từ các thành phần riêng biệt với timezone
    $showDateTime = Carbon::create(
        $showDate->year,
        $showDate->month,
        $showDate->day,
        $hour,
        $minute,
        0,
        'Asia/Ho_Chi_Minh'
    );

    // Thời gian suất chiếu phải lớn hơn thời gian hiện tại ít nhất 1 giờ
    $minimumDateTime = $now->copy()->addHour();
    if ($showDateTime->lte($minimumDateTime)) {
        throw ValidationException::withMessages([
            'show_time' => 'Showtime must be at least 1 hour after the current time to allow customers time to purchase tickets.',
        ]);
    }

    // Lấy thông tin phim để biết thời lượng
    $movie = Movie::findOrFail($validated['movie_id']);
    $movieDuration = $movie->duration_minutes ?? 0;

    // Kiểm tra xung đột thời gian
    $this->checkTimeConflict(
        $validated['room_id'],
        $validated['show_date'],
        $validated['show_time'],
        $movieDuration
    );

    // Tính giá tự động dựa trên Peak hour
    $baseGoldPrice = 17;
    $basePlatinumPrice = 20;
    $baseBoxPrice = 40;
    $multiplier = $validated['is_peak_hour'] ? 1.2 : 1; // Tăng 20% nếu Peak hour
    
    $validated['gold_price'] = round($baseGoldPrice * $multiplier, 2);
    $validated['platinum_price'] = round($basePlatinumPrice * $multiplier, 2);
    $validated['box_price'] = round($baseBoxPrice * $multiplier, 2);

    Showtime::create($validated);

    return redirect()->route('admin.showtimes.index')
            ->with('success', 'Created successfully');
}
public function show(Showtime $showtime)
{
    $showtime->load(['movie', 'room']);
    
    // Lấy thông tin ghế và bookings
    $room = $showtime->room;
    $seats = collect();
    $bookedSeatIds = [];
    $coupleRows = [];
    
    if ($room) {
        // Lấy tất cả ghế của phòng
        $seats = \App\Models\Seat::where('room_id', $room->id)
            ->orderBy('row_number')
            ->orderBy('seat_number')
            ->get();
        
        // Lấy các ghế đã được đặt cho showtime này (chỉ completed bookings)
        $bookedSeatIds = \App\Models\Booking::where('showtime_id', $showtime->id)
            ->where('payment_status', 'completed')
            ->with('seats')
            ->get()
            ->pluck('seats')
            ->flatten()
            ->pluck('id')
            ->toArray();
        
        // Xác định hàng couple
        if ($room->layout) {
            foreach ($room->layout as $rowData) {
                if ($rowData['seat_type'] === 'couple') {
                    $coupleRows[] = $rowData['row_letter'];
                }
            }
        }
    }
    
    return view('showtimes.show', compact('showtime', 'seats', 'bookedSeatIds', 'coupleRows'));
}
public function edit(Showtime $showtime)
{
    $movies = Movie::all();
    $rooms = Room::orderBy('room_number')->get();
    // Pass thông tin về bookings để JavaScript biết có thể chỉnh sửa hay không
    $hasCompletedBookings = $showtime->bookings()
        ->where('payment_status', 'completed')
        ->exists();
    return view('showtimes.edit', compact('showtime', 'movies', 'rooms', 'hasCompletedBookings'));
}
public function update(Request $request, Showtime $showtime)
{
    // Merge giá trị mặc định cho checkbox trước khi validate
    $request->merge([
        'is_peak_hour' => $request->has('is_peak_hour') ? 1 : 0
    ]);

    $validated = $request->validate([
        'movie_id' => 'required|exists:movies,id',
        'room_id' => 'required|exists:rooms,id',
        'show_date' => [
            'required',
            'date',
            'after_or_equal:today',
        ],
        'show_time' => 'required',
        'gold_price' => 'required|numeric|min:1|max:1000',
        'platinum_price' => 'required|numeric|min:1|max:1000',
        'box_price' => 'required|numeric|min:1|max:1000',
        'is_peak_hour' => 'boolean',
    ]);

    // Kiểm tra nếu show_date là hôm nay thì show_time phải lớn hơn thời gian hiện tại
    $now = Carbon::now();
    $showDate = Carbon::parse($validated['show_date']);
    
    // Parse show_time an toàn với định dạng cụ thể
    $timeStr = $validated['show_time'];
    // Loại bỏ giây nếu có (chỉ lấy H:i)
    if (strlen($timeStr) > 5) {
        $timeStr = substr($timeStr, 0, 5);
    }
    $timeParts = explode(':', $timeStr);
    $hour = (int)($timeParts[0] ?? 0);
    $minute = (int)($timeParts[1] ?? 0);
    
    // Tạo datetime từ các thành phần riêng biệt
    $showDateTime = Carbon::create(
        $showDate->year,
        $showDate->month,
        $showDate->day,
        $hour,
        $minute,
        0
    );

    // Chỉ validate thời gian nếu showtime chưa có booking nào đã thanh toán
    // Nếu showtime đã có booking, vẫn cho phép chỉnh sửa để sửa lỗi
    $hasCompletedBookings = $showtime->bookings()
        ->where('payment_status', 'completed')
        ->exists();
    
    // Nếu chưa có booking đã thanh toán, validate thời gian phải lớn hơn hiện tại ít nhất 1 giờ
    if (!$hasCompletedBookings) {
        $minimumDateTime = $now->copy()->addHour();
        if ($showDateTime->lte($minimumDateTime)) {
            throw ValidationException::withMessages([
                'show_time' => 'Showtime must be at least 1 hour after the current time to allow customers time to purchase tickets.',
            ]);
        }
    }

    // Lấy thông tin phim để biết thời lượng
    $movie = Movie::findOrFail($validated['movie_id']);
    $movieDuration = $movie->duration_minutes ?? 0;

    // Kiểm tra xung đột thời gian (loại trừ showtime hiện tại)
    $this->checkTimeConflict(
        $validated['room_id'],
        $validated['show_date'],
        $validated['show_time'],
        $movieDuration,
        $showtime->id
    );

    // Tính giá tự động dựa trên Peak hour
    $baseGoldPrice = 17;
    $basePlatinumPrice = 20;
    $baseBoxPrice = 40;
    $multiplier = $validated['is_peak_hour'] ? 1.2 : 1; // Tăng 20% nếu Peak hour
    
    $validated['gold_price'] = round($baseGoldPrice * $multiplier, 2);
    $validated['platinum_price'] = round($basePlatinumPrice * $multiplier, 2);
    $validated['box_price'] = round($baseBoxPrice * $multiplier, 2);

    $showtime->update($validated);

    return redirect()->route('admin.showtimes.index')
        ->with('success', 'Updated successfully');
}
public function destroy(Showtime $showtime)
{
    $showtime->delete();

    return redirect()->route('admin.showtimes.index')
        ->with('success', 'Deleted successfully');
}
}