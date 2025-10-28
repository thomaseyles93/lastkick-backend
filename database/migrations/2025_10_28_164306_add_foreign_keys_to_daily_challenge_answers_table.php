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
        Schema::table('daily_challenge_answers', function (Blueprint $table) {
            $table->foreign(['daily_challenge_id'], 'daily_challenge_answers_ibfk_1')->references(['id'])->on('daily_challenges')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_challenge_answers', function (Blueprint $table) {
            $table->dropForeign('daily_challenge_answers_ibfk_1');
        });
    }
};
