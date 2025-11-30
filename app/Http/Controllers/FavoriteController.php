<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Toggle like/unlike cho một phim
     */
    public function toggle(Movie $movie)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để thích phim.',
                'redirect' => route('login')
            ], 401);
        }

        $favorite = Favorite::where('movie_id', $movie->id)
            ->where('user_id', $user->id)
            ->first();

        if ($favorite) {
            // Unlike
            $favorite->delete();
            $isLiked = false;
            $message = 'Đã bỏ thích phim.';
        } else {
            // Like
            Favorite::create([
                'movie_id' => $movie->id,
                'user_id' => $user->id,
            ]);
            $isLiked = true;
            $message = 'Đã thêm vào danh sách yêu thích.';
        }

        // Lấy số lượt like mới
        $likeCount = $movie->favorites()->count();

        return response()->json([
            'success' => true,
            'isLiked' => $isLiked,
            'likeCount' => $likeCount,
            'message' => $message
        ]);
    }

    /**
     * Lấy danh sách phim yêu thích của người dùng
     */
    public function index()
    {
        $user = Auth::user();
        
        $favoriteMovies = $user->favoritedMovies()
            ->with(['showtimes.theater'])
            ->withCount('favorites')
            ->orderBy('favorites.created_at', 'desc')
            ->paginate(12);

        return view('profile.favorites', compact('favoriteMovies'));
    }
}
