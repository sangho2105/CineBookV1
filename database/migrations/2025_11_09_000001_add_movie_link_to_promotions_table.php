<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->foreignId('movie_id')->nullable()->constrained()->nullOnDelete()->after('category');
        });

        DB::statement("ALTER TABLE promotions MODIFY category ENUM('promotion','discount','event','movie') DEFAULT 'promotion'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE promotions MODIFY category ENUM('promotion','discount','event') DEFAULT 'promotion'");

        Schema::table('promotions', function (Blueprint $table) {
            $table->dropForeign(['movie_id']);
            $table->dropColumn('movie_id');
        });
    }
};

