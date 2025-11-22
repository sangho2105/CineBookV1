<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TheaterSeeder extends Seeder
{
    public function run()
    {
        $theaters = [
            [
                'name' => 'CGV Hanoi',
                'city' => 'Hanoi',
                'address' => '123 ABC Street, XYZ District, Hanoi',
                'seating_capacity' => 200,
            ],
            [
                'name' => 'CGV Ho Chi Minh City',
                'city' => 'Ho Chi Minh City',
                'address' => '456 DEF Street, UVW District, Ho Chi Minh City',
                'seating_capacity' => 250,
            ],
            [
                'name' => 'Lotte Cinema Da Nang',
                'city' => 'Da Nang',
                'address' => '789 GHI Street, RST District, Da Nang',
                'seating_capacity' => 180,
            ],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($theaters as $theater) {
            $exists = DB::table('theaters')
                ->where('name', $theater['name'])
                ->where('city', $theater['city'])
                ->exists();
            
            if (!$exists) {
                DB::table('theaters')->insert(array_merge($theater, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
                $created++;
            } else {
                $skipped++;
            }
        }

        $this->command->info("TheaterSeeder: Đã tạo {$created} rạp mới, bỏ qua {$skipped} rạp đã tồn tại.");
    }
}
