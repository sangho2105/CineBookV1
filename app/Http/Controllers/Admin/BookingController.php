<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Hiển thị danh sách tất cả vé đã đặt
     */
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'showtime.movie', 'showtime.room', 'seats']);

        // Lọc theo trạng thái thanh toán
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Lọc theo ngày đặt vé
        if ($request->filled('booking_date')) {
            $query->whereDate('booking_date', $request->booking_date);
        }

        // Lọc theo khoảng thời gian
        if ($request->filled('date_from')) {
            $query->whereDate('booking_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('booking_date', '<=', $request->date_to);
        }

        // Tìm kiếm theo mã vé
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('booking_id_unique', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('showtime.movie', function($q) use ($search) {
                      $q->where('title', 'like', '%' . $search . '%');
                  });
            });
        }

        // Sắp xếp theo ngày đặt vé mới nhất
        $bookings = $query->orderBy('booking_date', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        // Thống kê
        $stats = [
            'total' => Booking::count(),
            'completed' => Booking::where('payment_status', 'completed')->count(),
            'pending' => Booking::where('payment_status', 'pending')->count(),
            'cancelled' => Booking::where('payment_status', 'cancelled')->count(),
            'total_revenue' => Booking::where('payment_status', 'completed')->sum('total_amount'),
        ];

        // Dữ liệu cho biểu đồ doanh thu 30 ngày gần nhất
        $revenueData = $this->getRevenueChartData(30);

        return view('admin.bookings.index', compact('bookings', 'stats', 'revenueData'));
    }

    /**
     * Hiển thị chi tiết một vé
     */
    public function show(Booking $booking)
    {
        $booking->load([
            'user',
            'showtime.movie',
            'showtime.room',
            'showtime.theater',
            'seats',
            'combos'
        ]);

        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Lấy dữ liệu doanh thu cho biểu đồ
     */
    private function getRevenueChartData($days = 30)
    {
        $endDate = Carbon::today()->endOfDay();
        $startDate = Carbon::today()->subDays($days - 1)->startOfDay();

        // Lấy doanh thu theo ngày - sử dụng DB::raw để đảm bảo format đúng
        $revenueByDate = Booking::where('payment_status', 'completed')
            ->whereDate('booking_date', '>=', $startDate)
            ->whereDate('booking_date', '<=', $endDate)
            ->selectRaw('DATE(booking_date) as date, COALESCE(SUM(total_amount), 0) as revenue')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        // Tạo mảng đầy đủ cho tất cả các ngày
        $labels = [];
        $data = [];
        
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateKey = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format('d/m');
            
            if ($revenueByDate->has($dateKey)) {
                $data[] = (float)$revenueByDate[$dateKey]->revenue;
            } else {
                $data[] = 0;
            }
            
            $currentDate->addDay();
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'total' => array_sum($data)
        ];
    }
}

