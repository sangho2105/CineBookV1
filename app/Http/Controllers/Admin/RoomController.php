<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RoomController extends Controller
{
    /**
     * Hiển thị danh sách 6 phòng chiếu
     */
    public function index()
    {
        $rooms = Room::orderBy('room_number')->get();
        return view('admin.rooms.index', compact('rooms'));
    }

    /**
     * Hiển thị chi tiết sơ đồ phòng
     */
    public function show(Room $room)
    {
        return view('admin.rooms.show', compact('room'));
    }

    /**
     * Hiển thị lịch chiếu của phòng
     */
    public function schedule(Room $room, Request $request)
    {
        // Lấy tất cả lịch chiếu của phòng, sắp xếp theo ngày và giờ
        $showtimes = $room->showtimes()
            ->with('movie')
            ->orderBy('show_date')
            ->orderBy('show_time')
            ->get();

        // Nhóm lịch chiếu theo ngày
        $scheduleByDate = $showtimes->groupBy(function ($showtime) {
            return $showtime->show_date->format('Y-m-d');
        });

        // Sắp xếp theo key (ngày) và chuyển sang array để lấy keys
        $sortedDates = $scheduleByDate->keys()->sort()->values();
        
        // Phân trang: 6 ngày mỗi trang
        $perPage = 6;
        $currentPage = $request->get('page', 1);
        $total = $sortedDates->count();
        
        // Lấy 6 ngày cho trang hiện tại
        $currentDates = $sortedDates->slice(($currentPage - 1) * $perPage, $perPage);
        
        // Tạo collection mới chỉ chứa các ngày trong trang hiện tại
        $paginatedItems = collect();
        foreach ($currentDates as $date) {
            $paginatedItems->put($date, $scheduleByDate->get($date));
        }
        
        // Tạo paginator
        $paginatedSchedule = new LengthAwarePaginator(
            $paginatedItems,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.rooms.schedule', compact('room', 'scheduleByDate', 'showtimes', 'paginatedSchedule'));
    }
}

