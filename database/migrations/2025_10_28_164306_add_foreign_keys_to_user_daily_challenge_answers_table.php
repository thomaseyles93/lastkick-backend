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
        Schema::table('user_daily_challenge_answers', function (Blueprint $table) {
            $table->foreign(['user_id'], 'user_daily_challenge_answers_ibfk_1')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['daily_challenge_id'], 'user_daily_challenge_answers_ibfk_2')->references(['id'])->on('daily_challenges')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['answer_id'], 'user_daily_challenge_answers_ibfk_3')->references(['id'])->on('daily_challenge_answers')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_daily_challenge_answers', function (Blueprint $table) {
            $table->dropForeign('user_daily_challenge_answers_ibfk_1');
            $table->dropForeign('user_daily_challenge_answers_ibfk_2');
            $table->dropForeign('user_daily_challenge_answers_ibfk_3');
        });
    }
};
