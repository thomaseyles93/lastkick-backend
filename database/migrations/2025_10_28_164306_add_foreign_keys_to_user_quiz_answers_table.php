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
        Schema::table('user_quiz_answers', function (Blueprint $table) {
            $table->foreign(['quiz_id'], 'user_quiz_answers_ibfk_2')->references(['id'])->on('quizzes')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['question_id'], 'user_quiz_answers_ibfk_3')->references(['id'])->on('questions')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['selected_answer_id'], 'user_quiz_answers_ibfk_4')->references(['id'])->on('answers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['user_id'], 'user_quiz_answers_ibfk_5')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_quiz_answers', function (Blueprint $table) {
            $table->dropForeign('user_quiz_answers_ibfk_2');
            $table->dropForeign('user_quiz_answers_ibfk_3');
            $table->dropForeign('user_quiz_answers_ibfk_4');
            $table->dropForeign('user_quiz_answers_ibfk_5');
        });
    }
};
