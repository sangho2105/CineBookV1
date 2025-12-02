<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CommentController extends Controller
{
    /**
     * Constructor - chỉ cho phép admin truy cập
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check() && Auth::user()->role !== 'admin') {
                abort(403, 'Bạn không có quyền truy cập trang này.');
            }
            return $next($request);
        });
    }
    /**
     * Display a listing of comments.
     */
    public function index(Request $request)
    {
        $query = Comment::with(['user', 'movie']);

        // Lọc theo phim nếu có
        if ($request->filled('movie_id')) {
            $query->where('movie_id', $request->movie_id);
        }

        // Lọc theo tên user nếu có
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Lọc theo nội dung comment nếu có
        if ($request->filled('content_search')) {
            $query->where('content', 'like', '%' . $request->content_search . '%');
        }

        $comments = $query->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // Lấy danh sách phim đang chiếu và sắp chiếu để filter
        $today = Carbon::today();
        $movies = Movie::where(function($q) use ($today) {
                // Phim đang chiếu: status = now_showing
                $q->where('status', 'now_showing');
            })
            ->orWhere(function($q) use ($today) {
                // Phim sắp chiếu: status = upcoming và release_date > today
                $q->where('status', 'upcoming')
                  ->whereDate('release_date', '>', $today);
            })
            ->orderBy('title')
            ->get(['id', 'title', 'status', 'release_date']);

        return view('admin.comments.index', compact('comments', 'movies'));
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();

        return redirect()->route('admin.comments.index')
            ->with('success', 'Đã xóa bình luận thành công.');
    }
}

