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
    public function storeComment(Request $request, Movie $movie)
    {
        $this->middleware('auth');
        $validated = $request->validate([
            'content' => 'required|string|min:2|max:2000',
        ]);

        Comment::create([
            'movie_id' => $movie->id,
            'user_id' => Auth::id(),
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Đã đăng bình luận.');
    }

    public function updateComment(Request $request, Movie $movie, Comment $comment)
    {
        $this->middleware('auth');
        // Đảm bảo comment thuộc về movie hiện tại
        if ($comment->movie_id !== $movie->id) {
            abort(404);
        }
        // Chỉ chủ sở hữu được sửa
        if ($comment->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền sửa bình luận này.');
        }
        $validated = $request->validate([
            'content' => 'required|string|min:2|max:2000',
        ]);
        $comment->update(['content' => $validated['content']]);
        return back()->with('success', 'Đã cập nhật bình luận.');
    }

    public function deleteComment(Movie $movie, Comment $comment)
    {
        $this->middleware('auth');
        if ($comment->movie_id !== $movie->id) {
            abort(404);
        }
        if ($comment->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xóa bình luận này.');
        }
        $comment->delete();
        return back()->with('success', 'Đã xóa bình luận.');
    }

    public function storeRating(Request $request, Movie $movie)
    {
        $this->middleware('auth');
        $validated = $request->validate([
            'score' => 'required|integer|min:1|max:5',
        ]);

        // Chỉ cho phép chấm điểm nếu user có booking đã thanh toán cho movie này
        $eligible = Booking::where('user_id', Auth::id())
            ->where('payment_status', 'completed')
            ->whereHas('showtime', function ($q) use ($movie) {
                $q->where('movie_id', $movie->id);
            })
            ->exists();

        if (!$eligible) {
            return back()->withErrors(['score' => 'Bạn cần hoàn tất thanh toán vé của phim này trước khi chấm điểm.']);
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

        return back()->with('success', 'Đã ghi nhận đánh giá của bạn.');
    }
}


