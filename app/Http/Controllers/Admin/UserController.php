<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Constructor - chỉ cho phép admin truy cập
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check() && Auth::user()->role !== 'admin') {
                abort(403, 'You do not have permission to access this page.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Lọc theo tên
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        // Lọc theo role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Lọc theo thành phố
        if ($request->filled('city')) {
            $query->where('preferred_city', $request->city);
        }

        // Sắp xếp
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate(15)->withQueryString();

        // Lấy danh sách các thành phố duy nhất để filter
        $cities = User::whereNotNull('preferred_city')
            ->distinct()
            ->pluck('preferred_city')
            ->sort()
            ->values();

        return view('admin.users.index', compact('users', 'cities'));
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        // Load relationships
        $user->load(['bookings.showtime.movie', 'bookings.seats', 'bookings.combos']);

        // Thống kê
        $stats = [
            'total_bookings' => $user->bookings()->count(),
            'completed_bookings' => $user->bookings()->where('payment_status', 'completed')->count(),
            'pending_bookings' => $user->bookings()->where('payment_status', 'pending')->count(),
            'total_spent' => $user->bookings()
                ->where('payment_status', 'completed')
                ->sum('total_amount'),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }
}

