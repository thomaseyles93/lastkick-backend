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
        Schema::table('user_challenge_answers', function (Blueprint $table) {
            $table->foreign(['user_id'], 'user_challenge_answers_ibfk_1')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['challenge_id'], 'user_challenge_answers_ibfk_2')->references(['id'])->on('challenge')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_challenge_answers', function (Blueprint $table) {
            $table->dropForeign('user_challenge_answers_ibfk_1');
            $table->dropForeign('user_challenge_answers_ibfk_2');
        });
    }
};
