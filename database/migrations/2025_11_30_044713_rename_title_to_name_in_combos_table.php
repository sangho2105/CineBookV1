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
        // Rename title to name using raw SQL
        if (Schema::hasColumn('combos', 'title')) {
            DB::statement('ALTER TABLE `combos` CHANGE `title` `name` VARCHAR(255) NOT NULL');
        }
        
        // Remove image_path if it exists
        if (Schema::hasColumn('combos', 'image_path')) {
        Schema::table('combos', function (Blueprint $table) {
                $table->dropColumn('image_path');
            });
        }
        
        // Add sort_order if it doesn't exist
        if (!Schema::hasColumn('combos', 'sort_order')) {
            Schema::table('combos', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('is_active');
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
        // Rename name back to title
        if (Schema::hasColumn('combos', 'name')) {
            DB::statement('ALTER TABLE `combos` CHANGE `name` `title` VARCHAR(255) NOT NULL');
        }
        
        // Add image_path back
        if (!Schema::hasColumn('combos', 'image_path')) {
            Schema::table('combos', function (Blueprint $table) {
                $table->string('image_path')->nullable()->after('description');
            });
        }
        
        // Remove sort_order
        if (Schema::hasColumn('combos', 'sort_order')) {
        Schema::table('combos', function (Blueprint $table) {
                $table->dropColumn('sort_order');
        });
        }
    }
};
