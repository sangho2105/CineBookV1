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

        // Lấy ngày hiện tại để phân loại
        $today = \Carbon\Carbon::today()->format('Y-m-d');
        
        // Tách thành 2 nhóm: ngày hiện tại/tương lai và ngày đã qua
        $upcomingDates = collect();
        $pastDates = collect();
        
        foreach ($scheduleByDate->keys() as $date) {
            if ($date >= $today) {
                $upcomingDates->push($date);
            } else {
                $pastDates->push($date);
            }
        }
        
        // Sắp xếp: upcoming tăng dần (gần nhất trước), past giảm dần (gần nhất trước)
        $upcomingDates = $upcomingDates->sort()->values();
        $pastDates = $pastDates->sortDesc()->values();
        
        // Gộp lại: upcoming trước, past sau
        $sortedDates = $upcomingDates->merge($pastDates);
        
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

