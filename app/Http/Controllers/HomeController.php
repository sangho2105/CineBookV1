<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Movie; // Import model Movie
use App\Models\Promotion;
use Carbon\Carbon;     // Import Carbon để xử lý ngày tháng


class HomeController extends Controller
{
    public function index()
    {
        // Đồng bộ trạng thái phim dựa trên ngày phát hành (cho các bản ghi cũ)
        Movie::refreshStatuses();
        $today = Carbon::today();

        // 1. Lấy 5 phim "Nổi bật" (lấy phim mới nhất đang chiếu) - chỉ lấy phim chưa bị ẩn
        $featuredMovies = Movie::where('release_date', '<=', $today)
                                ->where('is_hidden', false)
                                ->orderBy('release_date', 'desc')
                                ->take(5)
                                ->get();
                                
        // 2. Lấy 12 phim "Đang chiếu" - chỉ lấy phim chưa bị ẩn
        $nowShowingMovies = Movie::where('release_date', '<=', $today)
                                  ->where('is_hidden', false)
                                  ->with('showtimes')
                                  ->orderBy('release_date', 'desc')
                                  ->take(12)
                                  ->get();

        // 3. Lấy 12 phim "Sắp chiếu" - chỉ lấy phim chưa bị ẩn
        $comingSoonMovies = Movie::where('release_date', '>', $today)
                                  ->where('is_hidden', false)
                                  ->orderBy('release_date', 'asc')
                                  ->take(12)
                                  ->get();

        $promotions = Promotion::with('movie')
            ->active()
            ->orderBy('start_date')
            ->take(6)
            ->get();

        // 4. Gửi cả 3 nhóm dữ liệu này ra view
        return view('home', [
            'featuredMovies'   => $featuredMovies,
            'nowShowingMovies' => $nowShowingMovies,
            'comingSoonMovies' => $comingSoonMovies,
            'promotions'       => $promotions,
        ]);
    }
    public function show(Movie $movie)
    {
        // Kiểm tra nếu phim bị ẩn và user không phải admin thì không cho xem
        if ($movie->is_hidden && (!auth()->check() || auth()->user()->role !== 'admin')) {
            abort(404, 'Phim không tồn tại hoặc đã bị ẩn');
        }
        
        $movie->load(['showtimes.theater']);

        // Lấy bình luận mới nhất
        $comments = \App\Models\Comment::with('user')
            ->where('movie_id', $movie->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Điểm trung bình (0-5) và tổng lượt đánh giá
        $ratingsQuery = \App\Models\Rating::where('movie_id', $movie->id);
        $ratingAverage = round($ratingsQuery->avg('score') ?? 0, 1);
        $ratingCount = $ratingsQuery->count();

        // Eligibility: user đã thanh toán completed cho phim này VÀ suất chiếu đã kết thúc?
        $canRate = false;
        $hasCompletedBooking = false;
        $isFavorited = false;
        $favoritesCount = $movie->favorites()->count();
        
        if (auth()->check()) {
            // Kiểm tra xem user có booking đã thanh toán không
            $completedBookings = \App\Models\Booking::where('user_id', auth()->id())
                ->where('payment_status', 'completed')
                ->whereHas('showtime', function ($q) use ($movie) {
                    $q->where('movie_id', $movie->id);
                })
                ->with('showtime.movie')
                ->get();
            
            $hasCompletedBooking = $completedBookings->isNotEmpty();
            
            // Kiểm tra xem có booking nào mà suất chiếu đã kết thúc không
            $eligibleBooking = $completedBookings->first(function ($booking) {
                return $booking->showtime && $booking->showtime->hasEnded();
            });
            
            $canRate = $eligibleBooking !== null;
            
            $isFavorited = $movie->favorites()->where('user_id', auth()->id())->exists();
        }

        return view('pages.movie-details', compact('movie', 'comments', 'ratingAverage', 'ratingCount', 'canRate', 'hasCompletedBooking', 'isFavorited', 'favoritesCount'));
    }
}