<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TheaterSeeder extends Seeder
{
    public function run()
    {
        DB::table('theaters')->insert([
            [
                'name' => 'CGV Hanoi',
                'city' => 'Hanoi',
                'address' => '123 ABC Street, XYZ District, Hanoi',
                'seating_capacity' => 200,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CGV Ho Chi Minh City',
                'city' => 'Ho Chi Minh City',
                'address' => '456 DEF Street, UVW District, Ho Chi Minh City',
                'seating_capacity' => 250,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lotte Cinema Da Nang',
                'city' => 'Da Nang',
                'address' => '789 GHI Street, RST District, Da Nang',
                'seating_capacity' => 180,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
