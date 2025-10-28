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
        Schema::create('challenge_answers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('challenge_id')->nullable()->index('challenge_answers_ibfk_1');
            $table->text('answer');
            $table->integer('position')->nullable();
            $table->string('answer_info')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_answers');
    }
};
