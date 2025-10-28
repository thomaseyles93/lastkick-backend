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
        Schema::create('challenge', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('title');
            $table->string('challenge_question', 555);
            $table->integer('challenge_id')->nullable()->index('challenge_id');
            $table->integer('answers')->nullable();
            $table->dateTime('date_added')->nullable()->useCurrent();
            $table->integer('achievement_id')->nullable()->index('achievement_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge');
    }
};
