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
        Schema::table('promotions', function (Blueprint $table) {
            // Xóa các cột cũ
            $table->dropColumn(['discount_percentage', 'applies_to']);
            // Thêm cột mới để lưu nhiều quy tắc giảm giá
            $table->json('discount_rules')->nullable()->after('conditions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn('discount_rules');
            $table->integer('discount_percentage')->nullable();
            $table->json('applies_to')->nullable();
        });
    }
};
