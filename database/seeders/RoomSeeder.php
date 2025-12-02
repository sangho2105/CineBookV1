<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    public function run()
    {
        // Xóa dữ liệu cũ nếu có
        Room::truncate();

        // Phòng 1: 52 ghế
        // 3 hàng thường (A-C): 3 x 8 = 24 ghế
        // 3 hàng VIP (D-F): 3 x 8 = 24 ghế
        // 1 hàng đôi (G): 4 ghế đôi = 4 ghế (chiếm 8 vị trí)
        Room::create([
            'room_number' => 1,
            'name' => 'Room 1',
            'total_seats' => 52,
            'layout' => [
                ['row_letter' => 'A', 'seat_type' => 'normal', 'seat_count' => 8],
                ['row_letter' => 'B', 'seat_type' => 'normal', 'seat_count' => 8],
                ['row_letter' => 'C', 'seat_type' => 'normal', 'seat_count' => 8],
                ['row_letter' => 'D', 'seat_type' => 'vip', 'seat_count' => 8],
                ['row_letter' => 'E', 'seat_type' => 'vip', 'seat_count' => 8],
                ['row_letter' => 'F', 'seat_type' => 'vip', 'seat_count' => 8],
                ['row_letter' => 'G', 'seat_type' => 'couple', 'seat_count' => 4],
            ],
        ]);

        // Phòng 2: 65 ghế
        // 4 hàng thường (A-D): 4 x 8 = 32 ghế
        // 4 hàng VIP (E-H): 4 x 8 = 32 ghế
        // 1 hàng đôi (I): 4 ghế đôi = 4 ghế (chiếm 8 vị trí)
        Room::create([
            'room_number' => 2,
            'name' => 'Room 2',
            'total_seats' => 65,
            'layout' => [
                ['row_letter' => 'A', 'seat_type' => 'normal', 'seat_count' => 8],
                ['row_letter' => 'B', 'seat_type' => 'normal', 'seat_count' => 8],
                ['row_letter' => 'C', 'seat_type' => 'normal', 'seat_count' => 8],
                ['row_letter' => 'D', 'seat_type' => 'normal', 'seat_count' => 8],
                ['row_letter' => 'E', 'seat_type' => 'vip', 'seat_count' => 8],
                ['row_letter' => 'F', 'seat_type' => 'vip', 'seat_count' => 8],
                ['row_letter' => 'G', 'seat_type' => 'vip', 'seat_count' => 8],
                ['row_letter' => 'H', 'seat_type' => 'vip', 'seat_count' => 8],
                ['row_letter' => 'I', 'seat_type' => 'couple', 'seat_count' => 4],
            ],
        ]);

        // Phòng 3: 52 ghế
        // 3 hàng thường (A-C): 3 x 8 = 24 ghế (khác biệt: ít hàng thường hơn, nhiều ghế hơn)
        // 4 hàng VIP (D-G): 4 x 6 = 24 ghế (khác biệt: nhiều hàng VIP hơn, ít ghế hơn)
        // 1 hàng đôi (H): 4 ghế đôi = 4 ghế (chiếm 8 vị trí)
        Room::create([
            'room_number' => 3,
            'name' => 'Room 3',
            'total_seats' => 52,
            'layout' => [
                ['row_letter' => 'A', 'seat_type' => 'normal', 'seat_count' => 8],
                ['row_letter' => 'B', 'seat_type' => 'normal', 'seat_count' => 8],
                ['row_letter' => 'C', 'seat_type' => 'normal', 'seat_count' => 8],
                ['row_letter' => 'D', 'seat_type' => 'vip', 'seat_count' => 6],
                ['row_letter' => 'E', 'seat_type' => 'vip', 'seat_count' => 6],
                ['row_letter' => 'F', 'seat_type' => 'vip', 'seat_count' => 6],
                ['row_letter' => 'G', 'seat_type' => 'vip', 'seat_count' => 6],
                ['row_letter' => 'H', 'seat_type' => 'couple', 'seat_count' => 4],
            ],
        ]);

        // Phòng 4: 65 ghế
        // 4 hàng thường (A-D): 4 x 8 = 32 ghế
        // 5 hàng VIP (E-I): 5 x 6 = 30 ghế
        // 1 hàng đôi (J): 3 ghế đôi = 3 ghế (chiếm 6 vị trí)
        Room::create([
            'room_number' => 4,
            'name' => 'Room 4',
            'total_seats' => 65,
            'layout' => [
                ['row_letter' => 'A', 'seat_type' => 'normal', 'seat_count' => 8],
                ['row_letter' => 'B', 'seat_type' => 'normal', 'seat_count' => 8],
                ['row_letter' => 'C', 'seat_type' => 'normal', 'seat_count' => 8],
                ['row_letter' => 'D', 'seat_type' => 'normal', 'seat_count' => 8],
                ['row_letter' => 'E', 'seat_type' => 'vip', 'seat_count' => 6],
                ['row_letter' => 'F', 'seat_type' => 'vip', 'seat_count' => 6],
                ['row_letter' => 'G', 'seat_type' => 'vip', 'seat_count' => 6],
                ['row_letter' => 'H', 'seat_type' => 'vip', 'seat_count' => 6],
                ['row_letter' => 'I', 'seat_type' => 'vip', 'seat_count' => 6],
                ['row_letter' => 'J', 'seat_type' => 'couple', 'seat_count' => 3],
            ],
        ]);

        // Phòng 5: 44 ghế (điều chỉnh từ 45 để phù hợp với số chẵn)
        // 4 hàng thường (A-D): 4 x 6 = 24 ghế (khác biệt: nhiều hàng thường hơn, ít ghế hơn)
        // 2 hàng VIP (E-F): 2 x 8 = 16 ghế (khác biệt: ít hàng VIP hơn, nhiều ghế hơn)
        // 1 hàng đôi (G): 4 ghế đôi = 4 ghế (chiếm 8 vị trí)
        Room::create([
            'room_number' => 5,
            'name' => 'Room 5',
            'total_seats' => 44,
            'layout' => [
                ['row_letter' => 'A', 'seat_type' => 'normal', 'seat_count' => 6],
                ['row_letter' => 'B', 'seat_type' => 'normal', 'seat_count' => 6],
                ['row_letter' => 'C', 'seat_type' => 'normal', 'seat_count' => 6],
                ['row_letter' => 'D', 'seat_type' => 'normal', 'seat_count' => 6],
                ['row_letter' => 'E', 'seat_type' => 'vip', 'seat_count' => 8],
                ['row_letter' => 'F', 'seat_type' => 'vip', 'seat_count' => 8],
                ['row_letter' => 'G', 'seat_type' => 'couple', 'seat_count' => 4],
            ],
        ]);

        // Phòng 6: 45 ghế
        // 4 hàng thường (A-D): 4 x 6 = 24 ghế
        // 3 hàng VIP (E-G): 3 x 6 = 18 ghế
        // 1 hàng đôi (H): 3 ghế đôi = 3 ghế (chiếm 6 vị trí)
        Room::create([
            'room_number' => 6,
            'name' => 'Room 6',
            'total_seats' => 45,
            'layout' => [
                ['row_letter' => 'A', 'seat_type' => 'normal', 'seat_count' => 6],
                ['row_letter' => 'B', 'seat_type' => 'normal', 'seat_count' => 6],
                ['row_letter' => 'C', 'seat_type' => 'normal', 'seat_count' => 6],
                ['row_letter' => 'D', 'seat_type' => 'normal', 'seat_count' => 6],
                ['row_letter' => 'E', 'seat_type' => 'vip', 'seat_count' => 6],
                ['row_letter' => 'F', 'seat_type' => 'vip', 'seat_count' => 6],
                ['row_letter' => 'G', 'seat_type' => 'vip', 'seat_count' => 6],
                ['row_letter' => 'H', 'seat_type' => 'couple', 'seat_count' => 3],
            ],
        ]);

        $this->command->info('Đã tạo 6 phòng chiếu với sơ đồ ghế khác nhau!');
    }
}
