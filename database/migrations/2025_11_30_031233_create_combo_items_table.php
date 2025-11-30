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
        Schema::create('combo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_id')->constrained()->cascadeOnDelete();
            $table->string('item_type'); // 'popcorn', 'drink', 'food'
            $table->string('item_name'); // Tên item: "Bắp (M)", "Nước (L)", "Hotdog", etc.
            $table->string('size')->nullable(); // Size: "S", "M", "L", "XL"
            $table->integer('quantity')->default(1); // Số lượng item trong combo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('combo_items');
    }
};
