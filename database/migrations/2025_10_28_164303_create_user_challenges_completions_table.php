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
        Schema::create('user_challenges_completions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id');
            $table->integer('challenge_id');
            $table->enum('challenge_type', ['quiz', 'who_am_i']);
            $table->integer('score');
            $table->timestamp('completed_at')->nullable()->useCurrent();
            $table->integer('time_taken');

            $table->unique(['user_id', 'challenge_id', 'challenge_type'], 'unique_user_challenge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_challenges_completions');
    }
};
