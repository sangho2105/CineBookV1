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

        // 1. Lấy 5 phim "Nổi bật" (lấy phim mới nhất đang chiếu)
        $featuredMovies = Movie::where('release_date', '<=', $today)
                                ->orderBy('release_date', 'desc')
                                ->take(5)
                                ->get();
                                
        // 2. Lấy 12 phim "Đang chiếu"
        $nowShowingMovies = Movie::where('release_date', '<=', $today)
                                  ->orderBy('release_date', 'desc')
                                  ->take(12)
                                  ->get();

        // 3. Lấy 12 phim "Sắp chiếu"
        $comingSoonMovies = Movie::where('release_date', '>', $today)
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

        // Eligibility: user đã thanh toán completed cho phim này?
        $canRate = false;
        if (auth()->check()) {
            $canRate = \App\Models\Booking::where('user_id', auth()->id())
                ->where('payment_status', 'completed')
                ->whereHas('showtime', function ($q) use ($movie) {
                    $q->where('movie_id', $movie->id);
                })
                ->exists();
        }

        return view('pages.movie-details', compact('movie', 'comments', 'ratingAverage', 'ratingCount', 'canRate'));
    }
}