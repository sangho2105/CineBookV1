<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seat;
use App\Models\Theater;

class SeatSeeder extends Seeder
{
    public function run()
    {
        $theaters = Theater::all();

        if ($theaters->isEmpty()) {
            $this->command->warn('SeatSeeder: Không có rạp nào. Vui lòng chạy TheaterSeeder trước.');
            return;
        }

        $totalSeatsCreated = 0;
        $totalSeatsSkipped = 0;

        foreach ($theaters as $theater) {
            // Kiểm tra xem rạp này đã có ghế chưa
            $existingSeatsCount = Seat::where('theater_id', $theater->id)->count();
            
            if ($existingSeatsCount > 0) {
                $this->command->info("Rạp '{$theater->name}' đã có {$existingSeatsCount} ghế. Bỏ qua.");
                continue;
            }

            // Tạo ghế cho mỗi rạp
            // Giả sử mỗi rạp có 10 hàng (A-J), mỗi hàng 15 ghế (1-15)
            
            $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
            $seatsCreated = 0;
            
            foreach ($rows as $row) {
                for ($seatNum = 1; $seatNum <= 15; $seatNum++) {
                    $seatNumber = $row . $seatNum;
                    
                    // Phân loại: Hàng A-E = Gold, F-G = Platinum, H-J = Box
                    if (in_array($row, ['A', 'B', 'C', 'D', 'E'])) {
                        $category = 'Gold';
                    } elseif (in_array($row, ['F', 'G'])) {
                        $category = 'Platinum';
                    } else {
                        $category = 'Box';
                    }
                    
                    Seat::create([
                        'theater_id' => $theater->id,
                        'seat_number' => $seatNumber,
                        'seat_category' => $category,
                        'row_number' => $row,
                        'is_available' => true,
                    ]);
                    $seatsCreated++;
                }
            }
            
            $totalSeatsCreated += $seatsCreated;
            $this->command->info("Đã tạo {$seatsCreated} ghế cho rạp '{$theater->name}'.");
        }

        $this->command->info("SeatSeeder: Tổng cộng đã tạo {$totalSeatsCreated} ghế mới.");
    }
}
