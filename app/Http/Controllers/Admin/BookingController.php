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
     * Hiển thị trang thống kê
     */
    public function statistics(Request $request)
    {
        // Thống kê
        $stats = [
            'total' => Booking::count(),
            'completed' => Booking::where('payment_status', 'completed')->count(),
            'pending' => Booking::where('payment_status', 'pending')->count(),
            'cancelled' => Booking::where('payment_status', 'cancelled')->count(),
            'total_revenue' => Booking::where('payment_status', 'completed')->sum('total_amount'),
        ];

        // Dữ liệu cho biểu đồ doanh thu - mặc định là 30 ngày
        $period = $request->get('period', 'day'); // day, month, quarter
        $selectedMonth = $request->get('revenue_month');
        $selectedYear = $request->get('revenue_year');
        $revenueData = $this->getRevenueChartData($period, $selectedMonth, $selectedYear);

        return view('admin.bookings.statistics', compact('stats', 'revenueData', 'period', 'selectedMonth', 'selectedYear'));
    }

    /**
     * Hiển thị thống kê số lượng vé đã bán cho mỗi phim
     */
    public function index(Request $request)
    {
        $query = Booking::where('payment_status', 'completed')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->join('movies', 'showtimes.movie_id', '=', 'movies.id');

        // Lọc theo tên phim
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('movies.title', 'like', '%' . $search . '%');
        }

        // Thống kê số lượng vé đã bán cho mỗi phim (chỉ tính vé đã thanh toán)
        $ticketsByMovie = $query->selectRaw('movies.id, movies.title, COUNT(bookings.id) as total_tickets')
            ->groupBy('movies.id', 'movies.title')
            ->orderBy('total_tickets', 'desc')
            ->paginate(8)
            ->withQueryString();

        // Tính tổng tất cả vé (không phân trang) - có filter nếu có
        $totalTicketsQuery = Booking::where('payment_status', 'completed')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
            ->join('movies', 'showtimes.movie_id', '=', 'movies.id');
        
        if ($request->filled('search')) {
            $totalTicketsQuery->where('movies.title', 'like', '%' . $request->search . '%');
        }
        
        $totalTickets = $totalTicketsQuery->count();

        return view('admin.bookings.list', compact('ticketsByMovie', 'totalTickets'));
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
    private function getRevenueChartData($period = 'day', $month = null, $year = null)
    {
        switch ($period) {
            case 'month':
                return $this->getRevenueByMonth($month, $year);
            case 'quarter':
                return $this->getRevenueByQuarter($month, $year);
            case 'day':
            default:
                return $this->getRevenueByDay($month, $year);
        }
    }

    /**
     * Lấy doanh thu theo ngày
     */
    private function getRevenueByDay($month = null, $year = null)
    {
        if ($month && $year) {
            // Nếu có chọn tháng và năm, lấy tất cả các ngày trong tháng đó
            $startDate = Carbon::create($year, $month, 1)->startOfDay();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
        } else {
            // Mặc định: 30 ngày gần nhất
            $days = 30;
            $endDate = Carbon::today()->endOfDay();
            $startDate = Carbon::today()->subDays($days - 1)->startOfDay();
        }

        // Lấy doanh thu theo ngày
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
            'total' => array_sum($data),
            'period' => 'day'
        ];
    }

    /**
     * Lấy doanh thu theo tháng
     */
    private function getRevenueByMonth($month = null, $year = null)
    {
        if ($year) {
            // Nếu có chọn năm, lấy tất cả các tháng trong năm đó
            $startDate = Carbon::create($year, 1, 1)->startOfMonth();
            $endDate = Carbon::create($year, 12, 31)->endOfMonth();
        } else {
            // Mặc định: 12 tháng gần nhất
            $months = 12;
            $endDate = Carbon::today()->endOfMonth();
            $startDate = Carbon::today()->subMonths($months - 1)->startOfMonth();
        }

        // Lấy doanh thu theo tháng
        $revenueByMonth = Booking::where('payment_status', 'completed')
            ->whereDate('booking_date', '>=', $startDate)
            ->whereDate('booking_date', '<=', $endDate)
            ->selectRaw('YEAR(booking_date) as year, MONTH(booking_date) as month, COALESCE(SUM(total_amount), 0) as revenue')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->keyBy(function($item) {
                return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            });

        // Tạo mảng đầy đủ cho tất cả các tháng
        $labels = [];
        $data = [];
        
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $monthKey = $currentDate->format('Y-m');
            $labels[] = $currentDate->format('m/Y');
            
            if ($revenueByMonth->has($monthKey)) {
                $data[] = (float)$revenueByMonth[$monthKey]->revenue;
            } else {
                $data[] = 0;
            }
            
            $currentDate->addMonth();
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'total' => array_sum($data),
            'period' => 'month'
        ];
    }

    /**
     * Lấy doanh thu theo quý
     */
    private function getRevenueByQuarter($month = null, $year = null)
    {
        if ($year) {
            // Nếu có chọn năm, lấy tất cả các quý trong năm đó
            $startDate = Carbon::create($year, 1, 1)->startOfQuarter();
            $endDate = Carbon::create($year, 12, 31)->endOfQuarter();
        } else {
            // Mặc định: 4 quý gần nhất
            $quarters = 4;
            $endDate = Carbon::today()->endOfQuarter();
            $startDate = Carbon::today()->subQuarters($quarters - 1)->startOfQuarter();
        }

        // Lấy doanh thu theo quý - sử dụng CEIL(MONTH/3) để tính quý (tương thích hơn)
        $revenueByQuarter = Booking::where('payment_status', 'completed')
            ->whereDate('booking_date', '>=', $startDate)
            ->whereDate('booking_date', '<=', $endDate)
            ->selectRaw('YEAR(booking_date) as year, CEIL(MONTH(booking_date) / 3) as quarter, COALESCE(SUM(total_amount), 0) as revenue')
            ->groupBy('year', 'quarter')
            ->orderBy('year', 'asc')
            ->orderBy('quarter', 'asc')
            ->get()
            ->keyBy(function($item) {
                return $item->year . '-Q' . $item->quarter;
            });

        // Tạo mảng đầy đủ cho tất cả các quý
        $labels = [];
        $data = [];
        
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $year = $currentDate->year;
            $quarter = ceil($currentDate->month / 3);
            $quarterKey = $year . '-Q' . $quarter;
            
            $labels[] = 'Q' . $quarter . '/' . $year;
            
            if ($revenueByQuarter->has($quarterKey)) {
                $data[] = (float)$revenueByQuarter[$quarterKey]->revenue;
            } else {
                $data[] = 0;
            }
            
            $currentDate->addQuarter();
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'total' => array_sum($data),
            'period' => 'quarter'
        ];
    }
}

