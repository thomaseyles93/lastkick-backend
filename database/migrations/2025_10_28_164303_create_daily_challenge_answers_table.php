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
        Schema::create('daily_challenge_answers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('answer_title');
            $table->integer('daily_challenge_id')->index('daily_challenge_id');
            $table->text('answer_info')->nullable();
            $table->timestamp('date_created')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_challenge_answers');
    }
};
