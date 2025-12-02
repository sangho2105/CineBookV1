<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update room names from Vietnamese to English
        DB::table('rooms')->where('name', 'Phòng 1')->update(['name' => 'Room 1']);
        DB::table('rooms')->where('name', 'Phòng 2')->update(['name' => 'Room 2']);
        DB::table('rooms')->where('name', 'Phòng 3')->update(['name' => 'Room 3']);
        DB::table('rooms')->where('name', 'Phòng 4')->update(['name' => 'Room 4']);
        DB::table('rooms')->where('name', 'Phòng 5')->update(['name' => 'Room 5']);
        DB::table('rooms')->where('name', 'Phòng 6')->update(['name' => 'Room 6']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert room names from English to Vietnamese
        DB::table('rooms')->where('name', 'Room 1')->update(['name' => 'Phòng 1']);
        DB::table('rooms')->where('name', 'Room 2')->update(['name' => 'Phòng 2']);
        DB::table('rooms')->where('name', 'Room 3')->update(['name' => 'Phòng 3']);
        DB::table('rooms')->where('name', 'Room 4')->update(['name' => 'Phòng 4']);
        DB::table('rooms')->where('name', 'Room 5')->update(['name' => 'Phòng 5']);
        DB::table('rooms')->where('name', 'Room 6')->update(['name' => 'Phòng 6']);
    }
};
