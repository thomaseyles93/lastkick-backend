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
        Schema::create('user_challenge_answers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id');
            $table->integer('challenge_id')->index('challenge_id');
            $table->integer('position');
            $table->dateTime('date_added')->nullable()->useCurrent();

            $table->unique(['user_id', 'challenge_id', 'position'], 'unique_answer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_challenge_answers');
    }
};
