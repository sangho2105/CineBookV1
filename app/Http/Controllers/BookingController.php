<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Showtime;
use App\Models\Seat;
use App\Models\Room;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            ->get();
        
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
            ->get();
        
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
        $showtime->load(['movie', 'room']);
        
        if (!$showtime->room) {
            abort(404, 'Phòng chiếu không tồn tại cho suất chiếu này.');
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
        
        return view('bookings.select-seats', compact('showtime', 'seats', 'bookedSeatIds', 'coupleRows'));
    }

    // Xử lý đặt vé
    public function store(Request $request, Showtime $showtime)
    {
        $request->validate([
            'selected_seats' => 'required|string',
        ]);
        
        // Parse JSON từ hidden input
        $selectedSeatIds = json_decode($request->selected_seats, true);
        
        if (empty($selectedSeatIds)) {
            return back()->withErrors(['selected_seats' => 'Vui lòng chọn ít nhất một ghế!']);
        }
        // Validate ids tồn tại
        $validSeatCount = Seat::whereIn('id', $selectedSeatIds)->count();
        if ($validSeatCount !== count($selectedSeatIds)) {
            return back()->withErrors(['selected_seats' => 'Ghế không hợp lệ, vui lòng thử lại.']);
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
            return back()->withErrors(['selected_seats' => 'Một số ghế đã được đặt rồi!']);
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
        
        // Tạo booking
        DB::beginTransaction();
        try {
            $booking = Booking::create([
                'user_id' => auth()->id(),
                'showtime_id' => $showtime->id,
                'booking_date' => now(),
                'total_amount' => $totalAmount,
                'payment_status' => 'pending',
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
                    ->withErrors(['error' => 'Không tạo được đơn PayPal (approve url trống). Vui lòng thanh toán qua nút PayPal trên trang tiếp theo.']);
            } catch (\Throwable $e) {
                // Bỏ qua và rơi xuống trang thanh toán nội bộ nếu PayPal lỗi
                return redirect()->route('bookings.payment', $booking)
                    ->withErrors(['error' => 'Lỗi tạo PayPal Order: ' . $e->getMessage()]);
            }
            // Fallback: về trang thanh toán nội bộ có PayPal Buttons
            return redirect()->route('bookings.payment', $booking)
                ->with('success', 'Chọn ghế thành công! Vui lòng thanh toán.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi đặt vé!']);
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
            return redirect()->route('bookings.payment', $booking)->with('success', 'Đơn hàng đã thanh toán trước đó.');
        }
        $booking->update(['payment_status' => 'completed']);
        return redirect()->route('bookings.payment', $booking)->with('success', 'Thanh toán thành công!');
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
                ->withErrors(['error' => 'Thiếu mã đơn PayPal']);
        }
        // Capture and redirect back to payment page
        request()->merge(['orderID' => $orderId]);
        $result = $this->paypalCapture($booking);
        $json = $result->getData(true);
        if (($json['status'] ?? '') === 'COMPLETED') {
            return redirect()->route('bookings.payment', $booking->id)
                ->with('success', 'Thanh toán thành công!');
        }
        return redirect()->route('bookings.payment', $booking->id)
            ->withErrors(['error' => 'Thanh toán chưa hoàn tất, vui lòng thử lại.']);
    }
}