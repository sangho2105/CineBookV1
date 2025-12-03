<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Showtime;
use App\Models\Seat;
use App\Models\Room;
use App\Models\Movie;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\BookingConfirmationMail;

class BookingController extends Controller
{
    // Hiển thị modal chọn ngày và giờ chiếu (AJAX)
    public function selectShowtimeModal(Movie $movie, Request $request)
    {
        $movie->load(['showtimes.room']);
        
        // Lấy các ngày có suất chiếu (30 ngày tới)
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(30);
        
        // Lấy tất cả showtimes của phim trong khoảng thời gian này
        $showtimes = $movie->showtimes()
            ->whereBetween('show_date', [$startDate, $endDate])
            ->where('show_date', '>=', $startDate)
            ->orderBy('show_date')
            ->orderBy('show_time')
            ->get()
            ->filter(function($showtime) {
                // Lọc bỏ những suất chiếu đã qua
                $now = Carbon::now();
                $showDate = $showtime->show_date instanceof Carbon 
                    ? $showtime->show_date 
                    : Carbon::parse($showtime->show_date);
                
                // Parse show_time - có thể là string hoặc Carbon instance
                $timeStr = '';
                if ($showtime->show_time instanceof Carbon) {
                    $timeStr = $showtime->show_time->format('H:i');
                } else {
                    $timeStr = is_string($showtime->show_time) ? $showtime->show_time : (string)$showtime->show_time;
                    $timeStr = trim($timeStr);
                    if (strlen($timeStr) > 5) {
                        $timeStr = substr($timeStr, 0, 5); // Chỉ lấy H:i
                    }
                }
                
                // Bỏ qua nếu time không hợp lệ
                if (empty($timeStr)) {
                    return false;
                }
                
                $timeParts = explode(':', $timeStr);
                
                // Bỏ qua nếu format không đúng
                if (count($timeParts) < 2) {
                    return false;
                }
                
                $hour = (int)($timeParts[0] ?? 0);
                $minute = (int)($timeParts[1] ?? 0);
                
                // Validate range
                if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
                    return false;
                }
                
                // Tạo datetime từ các thành phần riêng biệt
                $showDateTime = Carbon::create(
                    $showDate->year,
                    $showDate->month,
                    $showDate->day,
                    $hour,
                    $minute,
                    0
                );
                
                return $showDateTime->gt($now);
            });
        
        // Nhóm showtimes theo ngày
        $showtimesByDate = $showtimes->groupBy(function($showtime) {
            return $showtime->show_date->format('Y-m-d');
        });
        
        // Tạo danh sách các ngày có suất chiếu
        $availableDates = $showtimesByDate->keys()->map(function($date) {
            return Carbon::parse($date);
        })->sort();
        
        // Ngày được chọn (mặc định là hôm nay hoặc ngày đầu tiên có suất chiếu)
        $selectedDate = $request->get('date', $availableDates->first()?->format('Y-m-d') ?? $startDate->format('Y-m-d'));
        $selectedDateCarbon = Carbon::parse($selectedDate);
        
        // Lấy showtimes của ngày được chọn
        $selectedDateShowtimes = $showtimesByDate->get($selectedDate, collect());
        
        // Nhóm theo phòng
        $showtimesByRoom = $selectedDateShowtimes->groupBy('room_id');
        
        return view('bookings.select-showtime-modal', compact(
            'movie',
            'availableDates',
            'selectedDate',
            'selectedDateCarbon',
            'showtimesByRoom',
            'showtimes'
        ));
    }
    
    // Hiển thị trang chọn ngày và giờ chiếu
    public function selectShowtime(Movie $movie)
    {
        $movie->load(['showtimes.room']);
        
        // Lấy các ngày có suất chiếu (30 ngày tới)
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(30);
        
        // Lấy tất cả showtimes của phim trong khoảng thời gian này
        $showtimes = $movie->showtimes()
            ->whereBetween('show_date', [$startDate, $endDate])
            ->where('show_date', '>=', $startDate)
            ->orderBy('show_date')
            ->orderBy('show_time')
            ->get()
            ->filter(function($showtime) {
                // Lọc bỏ những suất chiếu đã qua
                $now = Carbon::now();
                $showDate = $showtime->show_date instanceof Carbon 
                    ? $showtime->show_date 
                    : Carbon::parse($showtime->show_date);
                
                // Parse show_time - có thể là string hoặc Carbon instance
                $timeStr = '';
                if ($showtime->show_time instanceof Carbon) {
                    $timeStr = $showtime->show_time->format('H:i');
                } else {
                    $timeStr = is_string($showtime->show_time) ? $showtime->show_time : (string)$showtime->show_time;
                    $timeStr = trim($timeStr);
                    if (strlen($timeStr) > 5) {
                        $timeStr = substr($timeStr, 0, 5); // Chỉ lấy H:i
                    }
                }
                
                // Bỏ qua nếu time không hợp lệ
                if (empty($timeStr)) {
                    return false;
                }
                
                $timeParts = explode(':', $timeStr);
                
                // Bỏ qua nếu format không đúng
                if (count($timeParts) < 2) {
                    return false;
                }
                
                $hour = (int)($timeParts[0] ?? 0);
                $minute = (int)($timeParts[1] ?? 0);
                
                // Validate range
                if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
                    return false;
                }
                
                // Tạo datetime từ các thành phần riêng biệt
                $showDateTime = Carbon::create(
                    $showDate->year,
                    $showDate->month,
                    $showDate->day,
                    $hour,
                    $minute,
                    0
                );
                
                return $showDateTime->gt($now);
            });
        
        // Nhóm showtimes theo ngày
        $showtimesByDate = $showtimes->groupBy(function($showtime) {
            return $showtime->show_date->format('Y-m-d');
        });
        
        // Tạo danh sách các ngày có suất chiếu
        $availableDates = $showtimesByDate->keys()->map(function($date) {
            return Carbon::parse($date);
        })->sort();
        
        // Ngày được chọn (mặc định là hôm nay hoặc ngày đầu tiên có suất chiếu)
        $selectedDate = request('date', $availableDates->first()?->format('Y-m-d') ?? $startDate->format('Y-m-d'));
        $selectedDateCarbon = Carbon::parse($selectedDate);
        
        // Lấy showtimes của ngày được chọn
        $selectedDateShowtimes = $showtimesByDate->get($selectedDate, collect());
        
        // Nhóm theo phòng
        $showtimesByRoom = $selectedDateShowtimes->groupBy('room_id');
        
        return view('bookings.select-showtime', compact(
            'movie',
            'availableDates',
            'selectedDate',
            'selectedDateCarbon',
            'showtimesByRoom',
            'showtimes'
        ));
    }
    
    // Hiển thị trang chọn ghế
    public function selectSeats(Showtime $showtime)
    {
        // Kiểm tra suất chiếu chưa qua
        $now = Carbon::now();
        $showDate = $showtime->show_date instanceof Carbon 
            ? $showtime->show_date 
            : Carbon::parse($showtime->show_date);
        
        // Parse show_time - có thể là string hoặc Carbon instance
        $timeStr = '';
        if ($showtime->show_time instanceof Carbon) {
            $timeStr = $showtime->show_time->format('H:i');
        } else {
            $timeStr = is_string($showtime->show_time) ? $showtime->show_time : (string)$showtime->show_time;
            $timeStr = trim($timeStr);
            if (strlen($timeStr) > 5) {
                $timeStr = substr($timeStr, 0, 5); // Chỉ lấy H:i
            }
        }
        
        // Kiểm tra time hợp lệ
        if (empty($timeStr)) {
            abort(403, 'Invalid showtime time.');
        }
        
        $timeParts = explode(':', $timeStr);
        
        // Kiểm tra format hợp lệ
        if (count($timeParts) < 2) {
            abort(403, 'Invalid time format.');
        }
        
        $hour = (int)($timeParts[0] ?? 0);
        $minute = (int)($timeParts[1] ?? 0);
        
        // Validate range
        if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
            abort(403, 'Invalid time.');
        }
        
        // Tạo datetime từ các thành phần riêng biệt
        $showDateTime = Carbon::create(
            $showDate->year,
            $showDate->month,
            $showDate->day,
            $hour,
            $minute,
            0
        );
        
        if ($showDateTime->lte($now)) {
            abort(403, 'This showtime has passed, cannot book tickets.');
        }
        
        $showtime->load(['movie', 'room']);
        
        if (!$showtime->room) {
            abort(404, 'Room does not exist for this showtime.');
        }
        
        $room = $showtime->room;
        
        // Lấy tất cả ghế của phòng này
        $seatsQuery = Seat::where('room_id', $room->id)
            ->orderBy('row_number')
            ->orderBy('seat_number');
        $seats = $seatsQuery->get();

        // Nếu phòng chưa có ghế, tự động khởi tạo sơ đồ ghế từ layout
        if ($seats->isEmpty() && $room->layout) {
            foreach ($room->layout as $rowData) {
                $rowLetter = $rowData['row_letter'];
                $seatType = $rowData['seat_type'];
                $seatCount = $rowData['seat_count'];
                
                // Map seat_type sang seat_category
                $category = match($seatType) {
                    'normal' => 'Gold',
                    'vip' => 'Platinum',
                    'couple' => 'Box',
                    default => 'Gold',
                };
                
                for ($seatNum = 1; $seatNum <= $seatCount; $seatNum++) {
                    Seat::create([
                        'room_id'       => $room->id,
                        'seat_number'   => $rowLetter . $seatNum,
                        'seat_category' => $category,
                        'row_number'    => $rowLetter,
                        'is_available'  => true,
                    ]);
                }
            }
            $seats = $seatsQuery->get();
        }
        
        // Lấy các ghế đã được đặt cho showtime này
        $bookedSeatIds = Booking::where('showtime_id', $showtime->id)
            ->where('payment_status', 'completed')
            ->with('seats')
            ->get()
            ->pluck('seats')
            ->flatten()
            ->pluck('id')
            ->toArray();
        
        // Tạo map để xác định hàng nào là couple (để hiển thị chiều rộng gấp đôi)
        $coupleRows = [];
        if ($room->layout) {
            foreach ($room->layout as $rowData) {
                if ($rowData['seat_type'] === 'couple') {
                    $coupleRows[] = $rowData['row_letter'];
                }
            }
        }
        
        // Lấy các combo đang hoạt động từ database
        $combos = \App\Models\Combo::active()->visible()->orderBy('id')->get();
        
        return view('bookings.select-seats', compact('showtime', 'seats', 'bookedSeatIds', 'coupleRows', 'combos'));
    }

    // Hiển thị trang xác nhận trước khi thanh toán
    public function confirm(Request $request, Showtime $showtime)
    {
        // Kiểm tra suất chiếu chưa bắt đầu chiếu
        $now = Carbon::now('Asia/Ho_Chi_Minh');
        $showDate = $showtime->show_date instanceof Carbon 
            ? $showtime->show_date->setTimezone('Asia/Ho_Chi_Minh')
            : Carbon::parse($showtime->show_date)->setTimezone('Asia/Ho_Chi_Minh');
        
        $timeStr = '';
        if ($showtime->show_time instanceof Carbon) {
            $timeStr = $showtime->show_time->format('H:i');
        } else {
            $timeStr = is_string($showtime->show_time) ? $showtime->show_time : (string)$showtime->show_time;
            if (strlen($timeStr) > 5) {
                $timeStr = substr($timeStr, 0, 5);
            }
        }
        
        $timeParts = explode(':', $timeStr);
        $hour = (int)($timeParts[0] ?? 0);
        $minute = (int)($timeParts[1] ?? 0);
        
        $showDateTime = Carbon::create(
            $showDate->year,
            $showDate->month,
            $showDate->day,
            $hour,
            $minute,
            0,
            'Asia/Ho_Chi_Minh'
        );
        
        if ($showDateTime->lte($now)) {
            return back()->withErrors(['error' => 'This showtime has already started, cannot book tickets.']);
        }
        
        $request->validate([
            'selected_seats' => 'required|string',
        ]);

        // Parse JSON từ hidden input
        $selectedSeatIds = json_decode($request->selected_seats, true);

        if (empty($selectedSeatIds)) {
            return back()->withErrors(['selected_seats' => 'Please select at least one seat!']);
        }

        // Validate ids tồn tại
        $validSeatCount = Seat::whereIn('id', $selectedSeatIds)->count();
        if ($validSeatCount !== count($selectedSeatIds)) {
            return back()->withErrors(['selected_seats' => 'Invalid seats, please try again.']);
        }

        // Kiểm tra xem các ghế có available không
        $bookedSeatIds = Booking::where('showtime_id', $showtime->id)
            ->where('payment_status', 'completed')
            ->with('seats')
            ->get()
            ->pluck('seats')
            ->flatten()
            ->pluck('id')
            ->toArray();

        $conflictingSeats = array_intersect($selectedSeatIds, $bookedSeatIds);
        if (!empty($conflictingSeats)) {
            return back()->withErrors(['selected_seats' => 'Some seats have already been booked!']);
        }

        // Lấy thông tin ghế
        $seats = Seat::whereIn('id', $selectedSeatIds)->get();
        $showtime->load(['movie', 'room']);
        
        // Tính tiền vé
        $ticketPrice = 0;
        $seatDetails = [];
        foreach ($seats as $seat) {
            $seatPrice = 0;
            if ($seat->seat_category == 'Gold') {
                $seatPrice = $showtime->gold_price;
            } elseif ($seat->seat_category == 'Platinum') {
                $seatPrice = $showtime->platinum_price;
            } else {
                $seatPrice = $showtime->box_price;
            }
            $ticketPrice += $seatPrice;
            $seatDetails[] = [
                'seat' => $seat,
                'price' => $seatPrice,
            ];
        }

        // Tính tiền combo
        $comboPrice = 0;
        $comboDetails = [];
        if ($request->filled('combos')) {
            $decoded = json_decode($request->combos, true);
            if (is_array($decoded)) {
                foreach ($decoded as $c) {
                    $qty = max(0, (int)($c['quantity'] ?? 0));
                    $price = (float)($c['unit_price'] ?? 0);
                    if ($qty > 0 && $price >= 0) {
                        $comboTotal = $qty * $price;
                        $comboPrice += $comboTotal;
                        $comboDetails[] = [
                            'name' => (string)($c['name'] ?? 'Combo'),
                            'quantity' => $qty,
                            'unit_price' => $price,
                            'total' => $comboTotal,
                        ];
                    }
                }
            }
        }

        // Tính khuyến mãi (giả lập booking để tính promotion)
        $tempBooking = new Booking([
            'user_id' => auth()->id(),
            'showtime_id' => $showtime->id,
            'total_amount' => $ticketPrice + $comboPrice,
        ]);
        $tempBooking->setRelation('showtime', $showtime);
        
        // Tạo temp combos để tính promotion
        $tempCombos = collect($comboDetails)->map(function($c) {
            return new \App\Models\BookingCombo([
                'combo_name' => $c['name'],
                'quantity' => $c['quantity'],
                'unit_price' => $c['unit_price'],
            ]);
        });
        $tempBooking->setRelation('combos', $tempCombos);
        
        $promotionInfo = $this->calculatePromotionForConfirm($tempBooking, $ticketPrice, $comboPrice, count($seats));
        
        $discountAmount = $promotionInfo['discount_amount'] ?? 0;
        $promotionDetails = $promotionInfo['promotion_details'] ?? [];
        $hasGiftPromotion = $promotionInfo['has_gift_promotion'] ?? false;
        
        // Tổng tiền cuối cùng
        $finalTotal = $ticketPrice + $comboPrice - $discountAmount;

        return view('bookings.confirm', compact(
            'showtime',
            'seats',
            'seatDetails',
            'ticketPrice',
            'comboDetails',
            'comboPrice',
            'promotionDetails',
            'discountAmount',
            'hasGiftPromotion',
            'finalTotal',
            'selectedSeatIds',
            'promotionInfo'
        ));
    }

    // Tính toán promotion cho trang xác nhận (không tạo booking)
    private function calculatePromotionForConfirm($booking, float $ticketPrice, float $comboPrice, int $ticketCount): array
    {
        $showtime = $booking->showtime;
        $movie = $showtime->movie;
        
        // Lấy tất cả promotion đang active
        $now = Carbon::now('Asia/Ho_Chi_Minh');
        $promotions = Promotion::where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where(function($query) use ($now) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', $now);
            })
            ->get();
        
        // Tách promotions thành 2 nhóm: exclusive và shared
        $exclusivePromotions = [];
        $sharedPromotions = [];
        
        foreach ($promotions as $promotion) {
            $applyType = $promotion->apply_type ?? 'shared'; // Mặc định là shared
            if ($applyType === 'exclusive') {
                $exclusivePromotions[] = $promotion;
            } else {
                $sharedPromotions[] = $promotion;
            }
        }
        
        $bestDiscount = 0;
        $bestPromotion = null;
        $promotionDetails = [];
        $hasGiftPromotion = false;
        
        // Hàm helper để kiểm tra và tính discount cho một promotion
        $processPromotion = function($promotion) use ($booking, $movie, $ticketPrice, $comboPrice, $ticketCount) {
            $rules = $promotion->discount_rules ?? [];
            if (empty($rules) || !is_array($rules)) {
                return null;
            }
            
            // Chỉ lấy rule đầu tiên
            $discountRules = $promotion->discount_rules;
            $rule = is_array($discountRules) && !empty($discountRules) ? reset($discountRules) : [];
            if (empty($rule)) {
                return null;
            }
            
            // Kiểm tra phim
            if (!empty($rule['movie_id']) && $rule['movie_id'] != $movie->id) {
                return null;
            }
            
            // Kiểm tra số vé
            if (!empty($rule['min_tickets']) && $ticketCount < $rule['min_tickets']) {
                return null;
            }
            
            // Kiểm tra yêu cầu combo
            // Nếu có requires_combo_ids (combo bắt buộc), phải kiểm tra xem booking có combo đó không
            if (!empty($rule['requires_combo_ids']) && is_array($rule['requires_combo_ids']) && count($rule['requires_combo_ids']) > 0) {
                // Có combo bắt buộc được chỉ định
                if ($comboPrice <= 0) {
                    // Không có combo nào trong booking → không đủ điều kiện
                    return null;
                }
                
                // Kiểm tra xem booking có chứa ít nhất một combo trong danh sách bắt buộc không
                $bookingComboNames = $booking->combos->pluck('combo_name')->toArray();
                $requiredComboNames = \App\Models\Combo::whereIn('id', $rule['requires_combo_ids'])
                    ->pluck('name')
                    ->toArray();
                
                if (empty(array_intersect($bookingComboNames, $requiredComboNames))) {
                    // Booking không có combo nào trong danh sách bắt buộc → không đủ điều kiện
                    return null;
                }
            } elseif (!empty($rule['requires_combo'])) {
                // Nếu chỉ có checkbox requires_combo (không có requires_combo_ids cụ thể)
                // Chỉ cần kiểm tra có combo hay không
                if ($comboPrice <= 0) {
                    return null;
                }
            }
            
            // Kiểm tra áp dụng tặng quà
            $discountPercentage = $rule['discount_percentage'] ?? 0;
            $appliesTo = $rule['applies_to'] ?? [];
            $hasGift = !empty($rule['gift_only']);
            
            // Tính discount (có thể có discount ngay cả khi có gift)
            $ruleDiscount = 0;
            if (in_array('ticket', $appliesTo)) {
                $ruleDiscount += ($ticketPrice * $discountPercentage / 100);
            }
            if (in_array('combo', $appliesTo)) {
                $ruleDiscount += ($comboPrice * $discountPercentage / 100);
            }
            if (in_array('total', $appliesTo)) {
                $totalBeforeDiscount = $ticketPrice + $comboPrice;
                $ruleDiscount += ($totalBeforeDiscount * $discountPercentage / 100);
            }
            
            // Nếu có gift và không có discount → chỉ tặng quà
            if ($hasGift && $ruleDiscount == 0) {
                return [
                    'promotion' => $promotion,
                    'type' => 'gift',
                    'discount' => 0,
                    'percentage' => 0,
                    'has_gift' => true,
                ];
            }
            
            // Nếu có discount (có thể kèm gift hoặc không)
            if ($ruleDiscount > 0) {
                return [
                    'promotion' => $promotion,
                    'type' => $hasGift ? 'discount_gift' : 'discount',
                    'discount' => $ruleDiscount,
                    'percentage' => $discountPercentage,
                    'has_gift' => $hasGift,
                ];
            }
            
            // Nếu chỉ có gift mà không có discount
            if ($hasGift) {
                return [
                    'promotion' => $promotion,
                    'type' => 'gift',
                    'discount' => 0,
                    'percentage' => 0,
                    'has_gift' => true,
                ];
            }
            
            return null;
        };
        
        // Xử lý exclusive promotions trước (nếu có)
        if (!empty($exclusivePromotions)) {
            foreach ($exclusivePromotions as $promotion) {
                $result = $processPromotion($promotion);
                if ($result === null) {
                    continue;
                }
                
                // Xử lý gift
                if (!empty($result['has_gift'])) {
                    $hasGiftPromotion = true;
                }
                
                // Xử lý discount
                if ($result['type'] === 'gift') {
                    // Chỉ có gift, không có discount
                    if (!$bestPromotion) {
                        $bestPromotion = $promotion;
                    }
                    $promotionDetails = [[
                        'name' => $promotion->title,
                        'type' => 'gift',
                        'discount' => 0,
                    ]];
                } elseif ($result['type'] === 'discount_gift') {
                    // Vừa có discount vừa có gift
                    if ($result['discount'] > $bestDiscount) {
                        $bestDiscount = $result['discount'];
                        $bestPromotion = $promotion;
                        $promotionDetails = [[
                            'name' => $promotion->title,
                            'type' => 'discount_gift',
                            'discount' => $result['discount'],
                            'percentage' => $result['percentage'],
                        ]];
                    }
                } else {
                    // Chỉ có discount
                    if ($result['discount'] > $bestDiscount) {
                        $bestDiscount = $result['discount'];
                        $bestPromotion = $promotion;
                        $promotionDetails = [[
                            'name' => $promotion->title,
                            'type' => 'discount',
                            'discount' => $result['discount'],
                            'percentage' => $result['percentage'],
                        ]];
                    }
                }
            }
            
            // Nếu đã tìm thấy exclusive promotion hợp lệ, dừng lại
            if ($bestPromotion) {
                return [
                    'discount_amount' => $bestDiscount,
                    'promotion_details' => $promotionDetails,
                    'has_gift_promotion' => $hasGiftPromotion,
                    'best_promotion' => $bestPromotion,
                ];
            }
        }
        
        // Xử lý shared promotions (có thể kết hợp nhiều promotion)
        $totalSharedDiscount = 0;
        $sharedPromotionDetails = [];
        foreach ($sharedPromotions as $promotion) {
            $result = $processPromotion($promotion);
            if ($result === null) {
                continue;
            }
            
            // Xử lý gift
            if (!empty($result['has_gift'])) {
                $hasGiftPromotion = true;
            }
            
            // Xử lý discount và gift
            if ($result['type'] === 'gift') {
                // Chỉ có gift, không có discount
                $sharedPromotionDetails[] = [
                    'name' => $promotion->title,
                    'type' => 'gift',
                    'discount' => 0,
                ];
            } elseif ($result['type'] === 'discount_gift') {
                // Vừa có discount vừa có gift
                $totalSharedDiscount += $result['discount'];
                $sharedPromotionDetails[] = [
                    'name' => $promotion->title,
                    'type' => 'discount_gift',
                    'discount' => $result['discount'],
                    'percentage' => $result['percentage'],
                ];
                
                if ($result['discount'] > $bestDiscount) {
                    $bestDiscount = $result['discount'];
                    $bestPromotion = $promotion;
                }
            } else {
                // Chỉ có discount
                $totalSharedDiscount += $result['discount'];
                $sharedPromotionDetails[] = [
                    'name' => $promotion->title,
                    'type' => 'discount',
                    'discount' => $result['discount'],
                    'percentage' => $result['percentage'],
                ];
                
                if ($result['discount'] > $bestDiscount) {
                    $bestDiscount = $result['discount'];
                    $bestPromotion = $promotion;
                }
            }
        }
        
        // Nếu có shared promotions, sử dụng tổng discount
        if ($totalSharedDiscount > 0) {
            $bestDiscount = $totalSharedDiscount;
        }
        
        $promotionDetails = $sharedPromotionDetails;
        
        return [
            'discount_amount' => $bestDiscount,
            'promotion_details' => $promotionDetails,
            'has_gift_promotion' => $hasGiftPromotion,
            'best_promotion' => $bestPromotion,
        ];
    }

    // Xử lý đặt vé
    public function store(Request $request, Showtime $showtime)
    {
        // Kiểm tra suất chiếu chưa qua
        $now = Carbon::now();
        $showDate = $showtime->show_date instanceof Carbon 
            ? $showtime->show_date 
            : Carbon::parse($showtime->show_date);
        
        // Parse show_time - có thể là string hoặc Carbon instance
        $timeStr = '';
        if ($showtime->show_time instanceof Carbon) {
            $timeStr = $showtime->show_time->format('H:i');
        } else {
            $timeStr = is_string($showtime->show_time) ? $showtime->show_time : (string)$showtime->show_time;
            if (strlen($timeStr) > 5) {
                $timeStr = substr($timeStr, 0, 5); // Chỉ lấy H:i
            }
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
        
        if ($showDateTime->lte($now)) {
            return back()->withErrors(['error' => 'This showtime has passed, cannot book tickets.']);
        }
        
        $request->validate([
            'selected_seats' => 'required|string',
        ]);
        
        // Parse JSON từ hidden input
        $selectedSeatIds = json_decode($request->selected_seats, true);
        
        if (empty($selectedSeatIds)) {
            return back()->withErrors(['selected_seats' => 'Please select at least one seat!']);
        }
        // Validate ids tồn tại
        $validSeatCount = Seat::whereIn('id', $selectedSeatIds)->count();
        if ($validSeatCount !== count($selectedSeatIds)) {
            return back()->withErrors(['selected_seats' => 'Invalid seats, please try again.']);
        }
        
        // Kiểm tra xem các ghế có available không
        $bookedSeatIds = Booking::where('showtime_id', $showtime->id)
            ->where('payment_status', 'completed')
            ->with('seats')
            ->get()
            ->pluck('seats')
            ->flatten()
            ->pluck('id')
            ->toArray();
        
        $conflictingSeats = array_intersect($selectedSeatIds, $bookedSeatIds);
        if (!empty($conflictingSeats)) {
            return back()->withErrors(['selected_seats' => 'Some seats have already been booked!']);
        }
        
        // Tính tổng tiền
        $seats = Seat::whereIn('id', $selectedSeatIds)->get();
        $totalAmount = 0;
        
        foreach ($seats as $seat) {
            if ($seat->seat_category == 'Gold') {
                $totalAmount += $showtime->gold_price;
            } elseif ($seat->seat_category == 'Platinum') {
                $totalAmount += $showtime->platinum_price;
            } else {
                $totalAmount += $showtime->box_price;
            }
        }
        // Combos (tùy chọn)
        $combosPayload = [];
        if ($request->filled('combos')) {
            $decoded = json_decode($request->combos, true);
            if (is_array($decoded)) {
                foreach ($decoded as $c) {
                    $qty = max(0, (int)($c['quantity'] ?? 0));
                    $price = (float)($c['unit_price'] ?? 0);
                    if ($qty > 0 && $price >= 0) {
                        $totalAmount += $qty * $price;
                        $combosPayload[] = [
                            'combo_name' => (string)($c['name'] ?? 'Combo'),
                            'quantity' => $qty,
                            'unit_price' => $price,
                        ];
                    }
                }
            }
        }
        
        // Lấy promotion info từ request (nếu có)
        $appliedPromotionId = null;
        $hasGiftPromotion = false;
        if ($request->filled('promotion_info')) {
            $promoInfo = json_decode($request->promotion_info, true);
            if (is_array($promoInfo)) {
                $appliedPromotionId = $promoInfo['applied_promotion_id'] ?? null;
                // Đảm bảo xử lý đúng giá trị boolean
                $hasGiftPromotion = isset($promoInfo['has_gift_promotion']) 
                    && ($promoInfo['has_gift_promotion'] === true || $promoInfo['has_gift_promotion'] === 'true' || $promoInfo['has_gift_promotion'] === 1);
            }
        }
        
        // Tạo booking
        DB::beginTransaction();
        try {
            // Lấy thời gian hiện tại ở timezone VN và convert về UTC để lưu vào database
            // Laravel sẽ tự động lưu UTC, nhưng chúng ta cần đảm bảo thời gian đúng
            $nowVN = Carbon::now('Asia/Ho_Chi_Minh');
            
            $booking = Booking::create([
                'user_id' => auth()->id(),
                'showtime_id' => $showtime->id,
                'booking_date' => $nowVN->utc(), // Convert về UTC để lưu vào database
                'total_amount' => $totalAmount,
                'payment_status' => 'pending',
                'applied_promotion_id' => $appliedPromotionId,
                'has_gift_promotion' => $hasGiftPromotion,
            ]);
            
            // Gắn ghế vào booking
            $booking->seats()->attach($selectedSeatIds);
            // Lưu combos nếu có
            if (!empty($combosPayload)) {
                foreach ($combosPayload as $row) {
                    $booking->combos()->create($row);
                }
            }
            
            DB::commit();
            // Tạo PayPal Order và chuyển hướng đến trang thanh toán của PayPal
            try {
                $order = $this->createPaypalOrderForBooking($booking);
                if (!empty($order['approve_url'])) {
                    return redirect()->away($order['approve_url']);
                }
                // Không có approve url → đẩy về trang thanh toán nội bộ kèm thông báo
                return redirect()->route('bookings.payment', $booking)
                    ->withErrors(['error' => 'Could not create PayPal order (approve url is empty). Please pay via PayPal button on the next page.']);
            } catch (\Throwable $e) {
                // Bỏ qua và rơi xuống trang thanh toán nội bộ nếu PayPal lỗi
                return redirect()->route('bookings.payment', $booking)
                    ->withErrors(['error' => 'Error creating PayPal Order: ' . $e->getMessage()]);
            }
            // Fallback: về trang thanh toán nội bộ có PayPal Buttons
            return redirect()->route('bookings.payment', $booking)
                ->with('success', 'Seats selected successfully! Please proceed to payment.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'An error occurred while booking tickets!']);
        }
    }

    // Trang thanh toán
    public function payment(Booking $booking)
    {
        // Chỉ cho chủ booking xem
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }
        $booking->load(['showtime.movie', 'showtime.room', 'showtime.theater', 'seats']);
        return view('bookings.payment', compact('booking'));
    }

    // Xử lý thanh toán (giả lập)
    public function pay(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }
        if ($booking->payment_status === 'completed') {
            return redirect()->route('bookings.ticket', $booking)->with('success', 'Đơn hàng đã thanh toán trước đó.');
        }
        $booking->update(['payment_status' => 'completed']);
        
        // Gửi email xác nhận đặt vé
        try {
            $booking->load(['showtime.movie', 'showtime.room', 'showtime.theater', 'seats', 'combos']);
            $ticketInfo = $this->calculateTicketInfo($booking);
            
            // Tạo PDF ticket để gửi kèm email
            $pdfPath = null;
            try {
                $pdf = Pdf::loadView('bookings.ticket-pdf', [
                    'booking' => $booking,
                    'ticketInfo' => $ticketInfo,
                    'barcodeBase64' => $this->generateBarcode($booking->booking_id_unique),
                    'barcodeHtml' => $this->generateBarcodeHtml($booking->booking_id_unique),
                ]);
                $pdfPath = storage_path('app/temp/ticket-' . $booking->booking_id_unique . '.pdf');
                if (!file_exists(storage_path('app/temp'))) {
                    mkdir(storage_path('app/temp'), 0755, true);
                }
                $pdf->save($pdfPath);
            } catch (\Exception $e) {
                \Log::error('Lỗi tạo PDF ticket: ' . $e->getMessage());
            }
            
            Mail::to($booking->user->email)->send(new BookingConfirmationMail($booking, $ticketInfo, $pdfPath));
            
            // Xóa file PDF tạm sau khi gửi email
            if ($pdfPath && file_exists($pdfPath)) {
                @unlink($pdfPath);
            }
        } catch (\Exception $e) {
            \Log::error('Lỗi gửi email xác nhận đặt vé: ' . $e->getMessage());
        }
        
        return redirect()->route('bookings.ticket', $booking)->with('success', 'Payment successful!');
    }

    // ===== PayPal Integration =====
    private function paypalBaseUrl(): string
    {
        $mode = env('PAYPAL_MODE', 'sandbox');
        return $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    private function paypalAccessToken(): string
    {
        $clientId = env('PAYPAL_CLIENT_ID');
        $secret = env('PAYPAL_SECRET');
        if (!$clientId || !$secret) {
            abort(500, 'Paypal credentials missing. Please set PAYPAL_CLIENT_ID and PAYPAL_SECRET in .env');
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->paypalBaseUrl() . '/v1/oauth2/token',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_USERPWD => $clientId . ':' . $secret,
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'Accept-Language: en_US'],
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $response = curl_exec($ch);
        if ($response === false) {
            abort(500, 'Paypal auth error: ' . curl_error($ch));
        }
        $data = json_decode($response, true);
        curl_close($ch);
        return $data['access_token'] ?? abort(500, 'Paypal access token missing');
    }

    // Create PayPal order (server-side)
    public function paypalCreateOrder(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }
        if ($booking->payment_status === 'completed') {
            return response()->json(['alreadyPaid' => true]);
        }

        $data = $this->createPaypalOrderRaw($booking);
        return response()->json([
            'id' => $data['id'] ?? null,
            'status' => $data['status'] ?? null,
        ]);
    }
    private function createPaypalOrderRaw(Booking $booking): array
    {
        $amount = (float) $booking->total_amount;
        $returnUrl = route('bookings.paypal.return', $booking->id);
        $cancelUrl = route('bookings.payment', $booking->id);
        $payload = json_encode([
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => (string)$booking->id,
                'amount' => [
                    'currency_code' => 'USD',
                    'value' => number_format($amount, 2, '.', ''),
                ],
            ]],
            'application_context' => [
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
                'brand_name' => config('app.name', 'CineBook'),
                'user_action' => 'PAY_NOW',
            ],
        ]);
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->paypalBaseUrl() . '/v2/checkout/orders',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->paypalAccessToken(),
            ],
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $response = curl_exec($ch);
        if ($response === false) {
            throw new \RuntimeException('PayPal create order error: ' . curl_error($ch));
        }
        curl_close($ch);
        return json_decode($response, true) ?: [];
    }
    private function createPaypalOrderForBooking(Booking $booking): array
    {
        $data = $this->createPaypalOrderRaw($booking);
        $approveUrl = null;
        if (!empty($data['links']) && is_array($data['links'])) {
            foreach ($data['links'] as $link) {
                if (($link['rel'] ?? '') === 'approve') {
                    $approveUrl = $link['href'];
                    break;
                }
            }
        }
        return ['data' => $data, 'approve_url' => $approveUrl];
    }

    // Capture PayPal order (server-side)
    public function paypalCapture(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }
        $orderId = request('orderID');
        if (!$orderId) {
            return response()->json(['error' => 'Missing orderID'], 422);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->paypalBaseUrl() . "/v2/checkout/orders/{$orderId}/capture",
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->paypalAccessToken(),
            ],
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $response = curl_exec($ch);
        if ($response === false) {
            return response()->json(['error' => curl_error($ch)], 500);
        }
        curl_close($ch);
        $data = json_decode($response, true);
        $status = $data['status'] ?? 'FAILED';

        if ($status === 'COMPLETED') {
            $booking->update(['payment_status' => 'completed']);
            
            // Gửi email xác nhận đặt vé
            try {
                $booking->load(['showtime.movie', 'showtime.room', 'showtime.theater', 'seats', 'combos']);
                $ticketInfo = $this->calculateTicketInfo($booking);
                
                // Tạo PDF ticket để gửi kèm email
                $pdfPath = null;
                try {
                    $pdf = Pdf::loadView('bookings.ticket-pdf', [
                        'booking' => $booking,
                        'ticketInfo' => $ticketInfo,
                        'barcodeBase64' => $this->generateBarcode($booking->booking_id_unique),
                        'barcodeHtml' => $this->generateBarcodeHtml($booking->booking_id_unique),
                    ]);
                    $pdfPath = storage_path('app/temp/ticket-' . $booking->booking_id_unique . '.pdf');
                    if (!file_exists(storage_path('app/temp'))) {
                        mkdir(storage_path('app/temp'), 0755, true);
                    }
                    $pdf->save($pdfPath);
                } catch (\Exception $e) {
                    \Log::error('Lỗi tạo PDF ticket: ' . $e->getMessage());
                }
                
                Mail::to($booking->user->email)->send(new BookingConfirmationMail($booking, $ticketInfo, $pdfPath));
                
                // Xóa file PDF tạm sau khi gửi email
                if ($pdfPath && file_exists($pdfPath)) {
                    @unlink($pdfPath);
                }
            } catch (\Exception $e) {
                \Log::error('Lỗi gửi email xác nhận đặt vé: ' . $e->getMessage());
            }
        }

        return response()->json(['status' => $status]);
    }

    // PayPal redirect return (GET)
    public function paypalReturn(Booking $booking, Request $request)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }
        $orderId = $request->query('token'); // PayPal returns ?token={ORDER_ID}
        if (!$orderId) {
            return redirect()->route('bookings.payment', $booking->id)
                ->withErrors(['error' => 'Missing PayPal order ID']);
        }
        // Capture and redirect back to payment page
        request()->merge(['orderID' => $orderId]);
        $result = $this->paypalCapture($booking);
        $json = $result->getData(true);
        if (($json['status'] ?? '') === 'COMPLETED') {
            return redirect()->route('bookings.ticket', $booking->id)
                ->with('success', 'Payment successful!');
        }
        return redirect()->route('bookings.payment', $booking->id)
            ->withErrors(['error' => 'Payment not completed, please try again.']);
    }

    // Xem chi tiết vé điện tử
    public function show(Booking $booking)
    {
        // Chỉ cho chủ booking xem
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }
        $booking->load(['showtime.movie', 'showtime.room', 'showtime.theater', 'seats', 'combos']);
        return view('bookings.show', compact('booking'));
    }

    // Hiển thị vé điện tử
    public function showTicket(Booking $booking)
    {
        // Chỉ cho chủ booking xem
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        $booking->load(['showtime.movie', 'showtime.room', 'showtime.theater', 'seats', 'combos']);
        
        // Tính toán thông tin vé
        $ticketInfo = $this->calculateTicketInfo($booking);
        
        // Tạo mã vạch
        $barcodeBase64 = $this->generateBarcode($booking->booking_id_unique);
        
        return view('bookings.ticket', compact('booking', 'ticketInfo', 'barcodeBase64'));
    }

    // Tải vé PDF
    public function downloadTicket(Booking $booking)
    {
        // Chỉ cho chủ booking tải
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        $booking->load(['showtime.movie', 'showtime.room', 'showtime.theater', 'seats', 'combos']);
        
        // Tính toán thông tin vé
        $ticketInfo = $this->calculateTicketInfo($booking);
        
        // Tạo mã vạch dưới dạng HTML/CSS (DomPDF hỗ trợ tốt hơn)
        $barcodeHtml = $this->generateBarcodeHTML($booking->booking_id_unique);
        $barcodeBase64 = $this->generateBarcode($booking->booking_id_unique); // Fallback
        
        // Tạo PDF
        $pdf = Pdf::loadView('bookings.ticket-pdf', compact('booking', 'ticketInfo', 'barcodeBase64', 'barcodeHtml'));
        
        // Cấu hình DomPDF
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', false);
        
        $filename = 've-' . $booking->booking_id_unique . '.pdf';
        
        return $pdf->download($filename);
    }
    
    // Tạo barcode dưới dạng HTML/CSS (DomPDF hỗ trợ tốt)
    private function generateBarcodeHTML(string $code): string
    {
        try {
            // Sử dụng thư viện picqer để tạo barcode pattern
            if (class_exists('\Picqer\Barcode\BarcodeGeneratorHTML')) {
                $generator = new \Picqer\Barcode\BarcodeGeneratorHTML();
                // Tạo barcode với kích thước phù hợp cho PDF nhỏ
                $html = $generator->getBarcode($code, $generator::TYPE_CODE_128, 1, 25);
                // Wrap trong container với CSS đẹp hơn
                return '<div style="text-align: center; width: 100%; padding: 0.5mm 0;">' . $html . '</div>';
            }
            
            // Fallback: Tạo barcode HTML đơn giản với CSS đẹp
            return $this->generateSimpleBarcodeHTML($code);
        } catch (\Exception $e) {
            \Log::error('Lỗi tạo barcode HTML: ' . $e->getMessage());
            return $this->generateSimpleBarcodeHTML($code);
        }
    }
    
    // Tạo barcode HTML đơn giản với CSS đẹp cho PDF
    private function generateSimpleBarcodeHTML(string $code): string
    {
        // Sử dụng table layout để đảm bảo không bị méo trong PDF
        $html = '<div style="text-align: center; width: 100%; margin: 0 auto; padding: 0.3mm 0;">';
        $html .= '<div style="display: inline-block; background: #ffffff; padding: 0.8mm 1mm; border: 0.3px solid #d0d0d0; border-radius: 1px; box-sizing: border-box;">';
        
        // Container cho các vạch - sử dụng table để đảm bảo alignment
        $html .= '<table style="border-collapse: collapse; margin: 0 auto; display: inline-table; vertical-align: bottom;">';
        $html .= '<tr style="vertical-align: bottom;">';
        
        // Tạo các vạch dựa trên code với kích thước phù hợp cho PDF nhỏ
        $barWidth = 0.8; // mm - nhỏ hơn để vừa với PDF A8
        $baseHeight = 6; // mm - chiều cao cơ bản
        $spacing = 0.15; // mm - khoảng cách giữa các vạch
        
        for ($i = 0; $i < strlen($code); $i++) {
            $char = ord($code[$i]);
            // Tạo chiều cao vạch đa dạng (3 mức)
            $heightMultiplier = ($char % 3) + 1; // 1, 2, hoặc 3
            $height = $baseHeight + ($heightMultiplier * 1.2);
            
            $html .= '<td style="padding: 0; margin: 0; vertical-align: bottom; line-height: 0;">';
            $html .= '<div style="display: block; width: ' . $barWidth . 'mm; height: ' . $height . 'mm; background: #000000; margin-right: ' . $spacing . 'mm; min-width: ' . $barWidth . 'mm;"></div>';
            $html .= '</td>';
        }
        
        $html .= '</tr>';
        $html .= '</table>';
        
        // Mã đặt chỗ bên dưới với font đẹp hơn
        $html .= '<div style="font-size: 4px; margin-top: 0.6mm; color: #333333; font-weight: 600; letter-spacing: 0.5px; font-family: Arial, Helvetica, sans-serif; text-transform: uppercase;">' . htmlspecialchars($code) . '</div>';
        $html .= '</div>'; // End container
        $html .= '</div>'; // End wrapper
        
        return $html;
    }

    // Tính toán thông tin vé
    private function calculateTicketInfo(Booking $booking): array
    {
        $showtime = $booking->showtime;
        $movie = $showtime->movie;
        
        // Lấy thời gian bắt đầu
        $startTime = $showtime->getFormattedShowTime('H:i');
        
        // Tính thời gian kết thúc (start_time + duration_minutes)
        $durationMinutes = $movie->duration_minutes ?? 120; // Mặc định 120 phút nếu không có
        $showDate = $showtime->show_date instanceof Carbon 
            ? $showtime->show_date 
            : Carbon::parse($showtime->show_date);
        
        // Parse start_time
        $timeStr = $showtime->getFormattedShowTime('H:i');
        $timeParts = explode(':', $timeStr);
        $hour = (int)($timeParts[0] ?? 0);
        $minute = (int)($timeParts[1] ?? 0);
        
        $startDateTime = Carbon::create(
            $showDate->year,
            $showDate->month,
            $showDate->day,
            $hour,
            $minute,
            0
        );
        
        $endDateTime = $startDateTime->copy()->addMinutes($durationMinutes);
        $endTime = $endDateTime->format('H:i');
        
        // Tính tiền vé
        $ticketPrice = 0;
        $seatDetails = [];
        foreach ($booking->seats as $seat) {
            $seatPrice = 0;
            if ($seat->seat_category == 'Gold') {
                $seatPrice = $showtime->gold_price;
            } elseif ($seat->seat_category == 'Platinum') {
                $seatPrice = $showtime->platinum_price;
            } else {
                $seatPrice = $showtime->box_price;
            }
            $ticketPrice += $seatPrice;
            
            $seatDetails[] = [
                'number' => $seat->seat_number,
                'category' => $seat->seat_category,
            ];
        }
        
        // Tính tiền combo
        $comboPrice = 0;
        $comboDetails = [];
        foreach ($booking->combos as $combo) {
            $comboTotal = $combo->quantity * $combo->unit_price;
            $comboPrice += $comboTotal;
            $comboDetails[] = [
                'name' => $combo->combo_name,
                'quantity' => $combo->quantity,
                'unit_price' => $combo->unit_price,
                'total' => $comboTotal,
            ];
        }
        
        // Tính giảm giá (nếu có promotion code - tạm thời để 0)
        $discountAmount = 0;
        
        // Tổng tiền
        $total = $ticketPrice + $comboPrice - $discountAmount;
        
        return [
            'start_time' => $startTime,
            'end_time' => $endTime,
            'seat_details' => $seatDetails,
            'ticket_price' => $ticketPrice,
            'combo_price' => $comboPrice,
            'combo_details' => $comboDetails,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'has_gift_promotion' => $booking->has_gift_promotion ?? false,
        ];
    }

    // Tạo mã vạch sử dụng thư viện picqer/php-barcode-generator
    private function generateBarcode(string $code): ?string
    {
        try {
            // Sử dụng thư viện picqer/php-barcode-generator (đã có trong composer.json)
            if (class_exists('\Picqer\Barcode\BarcodeGeneratorPNG')) {
                $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                // Sử dụng CODE128 format (hỗ trợ chữ và số, chuẩn cho PDF)
                // Tham số: code, type, width factor, height
                $barcodeData = $generator->getBarcode($code, $generator::TYPE_CODE_128, 2, 50);
                return base64_encode($barcodeData);
            }
            
            // Fallback: Sử dụng GD library nếu thư viện không có sẵn
            if (!extension_loaded('gd')) {
                \Log::warning('GD extension không được cài đặt và barcode library không có sẵn');
                return null;
            }
            
            // Tạo barcode đơn giản bằng GD
            $width = 200;
            $height = 60;
            $image = imagecreate($width, $height);
            
            // Màu nền trắng
            $white = imagecolorallocate($image, 255, 255, 255);
            // Màu đen cho vạch
            $black = imagecolorallocate($image, 0, 0, 0);
            
            // Vẽ các vạch đơn giản (barcode giả lập)
            $barWidth = 2;
            $x = 10;
            for ($i = 0; $i < strlen($code); $i++) {
                $char = ord($code[$i]);
                $barHeight = ($char % 3 + 1) * 15 + 20;
                imagefilledrectangle($image, $x, 10, $x + $barWidth, 10 + $barHeight, $black);
                $x += $barWidth + 1;
            }
            
            // Chuyển đổi sang base64
            ob_start();
            imagepng($image);
            $imageData = ob_get_contents();
            ob_end_clean();
            imagedestroy($image);
            
            return base64_encode($imageData);
        } catch (\Exception $e) {
            \Log::error('Lỗi tạo barcode: ' . $e->getMessage());
            return null;
        }
    }
}