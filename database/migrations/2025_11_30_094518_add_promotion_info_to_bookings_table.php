<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('applied_promotion_id')->nullable()->after('total_amount')->constrained('promotions')->nullOnDelete();
            $table->boolean('has_gift_promotion')->default(false)->after('applied_promotion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['applied_promotion_id']);
            $table->dropColumn(['applied_promotion_id', 'has_gift_promotion']);
        });
    }
};
