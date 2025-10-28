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
        Schema::create('user_challenge_achievements', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id');
            $table->integer('achievement_id')->index('achievement_id');
            $table->dateTime('date_achieved')->nullable()->useCurrent();

            $table->unique(['user_id', 'achievement_id'], 'unique_user_achievement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_challenge_achievements');
    }
};
