<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('seats', function (Blueprint $table) {
            // Xóa foreign key constraint cũ
            $table->dropForeign(['theater_id']);
            
            // Làm cho theater_id nullable
            $table->foreignId('theater_id')->nullable()->change();
            
            // Thêm lại foreign key constraint với nullable
            $table->foreign('theater_id')->references('id')->on('theaters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('seats', function (Blueprint $table) {
            // Xóa foreign key constraint
            $table->dropForeign(['theater_id']);
            
            // Đổi lại thành NOT NULL
            $table->foreignId('theater_id')->nullable(false)->change();
            
            // Thêm lại foreign key constraint
            $table->foreign('theater_id')->references('id')->on('theaters')->onDelete('cascade');
        });
    }
};
