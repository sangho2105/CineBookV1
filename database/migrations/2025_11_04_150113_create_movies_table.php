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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('poster_url')->nullable();
            $table->string('genre');
            $table->string('language');
            $table->integer('duration_minutes'); // minutes
            $table->string('trailer_url')->nullable();
            $table->text('description')->nullable();
            $table->date('release_date');
            $table->decimal('rating_average', 3, 2)->default(0); // 0.00 to 5.00
            $table->enum('status', ['upcoming', 'now_showing', 'ended'])->default('upcoming');
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
        Schema::dropIfExists('movies');
    }
};
