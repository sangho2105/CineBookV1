<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Comment;
use App\Models\Movie;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MovieFeedbackController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function storeComment(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'content' => 'required|string|min:2|max:2000',
        ]);

        Comment::create([
            'movie_id' => $movie->id,
            'user_id' => Auth::id(),
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Comment has been posted.');
    }

    public function updateComment(Request $request, Movie $movie, Comment $comment)
    {
        // Đảm bảo comment thuộc về movie hiện tại
        if ($comment->movie_id !== $movie->id) {
            abort(404);
        }
        // Chỉ chủ sở hữu được sửa
        if ($comment->user_id !== Auth::id()) {
            abort(403, 'You do not have permission to edit this comment.');
        }
        $validated = $request->validate([
            'content' => 'required|string|min:2|max:2000',
        ]);
        $comment->update(['content' => $validated['content']]);
        return back()->with('success', 'Comment has been updated.');
    }

    public function deleteComment(Movie $movie, Comment $comment)
    {
        if ($comment->movie_id !== $movie->id) {
            abort(404);
        }
        // Chỉ cho phép user xóa comment của chính họ
        // Admin xóa comment thông qua trang quản lý admin
        if ($comment->user_id !== Auth::id()) {
            abort(403, 'You do not have permission to delete this comment.');
        }
        $comment->delete();
        return back()->with('success', 'Comment has been deleted.');
    }

    public function storeRating(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'score' => 'required|integer|min:1|max:5',
        ]);

        // Chỉ cho phép chấm điểm nếu:
        // 1. User có booking đã thanh toán cho movie này
        // 2. Suất chiếu của booking đó đã kết thúc
        $eligibleBooking = Booking::where('user_id', Auth::id())
            ->where('payment_status', 'completed')
            ->whereHas('showtime', function ($q) use ($movie) {
                $q->where('movie_id', $movie->id);
            })
            ->with('showtime.movie')
            ->get()
            ->first(function ($booking) {
                return $booking->showtime && $booking->showtime->hasEnded();
            });

        if (!$eligibleBooking) {
            return back()->withErrors(['score' => 'You can only rate the movie after the showtime has ended.']);
        }

        DB::transaction(function () use ($movie, $validated) {
            Rating::updateOrCreate(
                ['movie_id' => $movie->id, 'user_id' => Auth::id()],
                ['score' => $validated['score']]
            );

            // Cập nhật điểm trung bình của phim (0-5)
            $avg = Rating::where('movie_id', $movie->id)->avg('score') ?? 0;
            $movie->update(['rating_average' => $avg]);
        });

        return back()->with('success', 'Your rating has been recorded.');
    }
}

