<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

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
    public function schedule(Room $room)
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

        return view('admin.rooms.schedule', compact('room', 'scheduleByDate', 'showtimes'));
    }
}

