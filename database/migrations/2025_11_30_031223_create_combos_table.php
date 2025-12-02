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
        // Kiểm tra xem bảng đã tồn tại chưa
        if (!Schema::hasTable('combos')) {
            Schema::create('combos', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2);
                $table->boolean('is_active')->default(true); // Hiển thị ở trang user hay không
                $table->integer('sort_order')->default(0); // Thứ tự hiển thị
                $table->timestamps();
            });
        } else {
            // Nếu bảng đã tồn tại, chỉ thêm các cột còn thiếu
            Schema::table('combos', function (Blueprint $table) {
                if (!Schema::hasColumn('combos', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('is_active');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('combos');
    }
};
