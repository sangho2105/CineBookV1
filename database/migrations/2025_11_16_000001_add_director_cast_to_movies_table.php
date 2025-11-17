<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->string('director')->nullable()->after('genre');
            $table->text('cast')->nullable()->after('director'); // danh sách diễn viên, phân tách bởi dấu phẩy
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn(['director', 'cast']);
        });
    }
};


