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
        Schema::create('user_quiz_answers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id')->index('user_id');
            $table->integer('quiz_id')->index('quiz_id');
            $table->integer('question_id')->index('question_id');
            $table->integer('selected_answer_id')->index('selected_answer_id');
            $table->boolean('is_correct');
            $table->timestamp('answered_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_quiz_answers');
    }
};
