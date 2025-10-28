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
        Schema::create('user_daily_challenge_answers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id');
            $table->integer('daily_challenge_id')->index('daily_challenge_id');
            $table->integer('answer_id')->nullable()->index('answer_id');
            $table->dateTime('date_added')->nullable()->useCurrent();
            $table->integer('lives_remaining')->default(3);

            $table->unique(['user_id', 'daily_challenge_id', 'answer_id'], 'unique_user_daily_answer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_daily_challenge_answers');
    }
};
