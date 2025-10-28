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
        Schema::table('challenge_answers', function (Blueprint $table) {
            $table->foreign(['challenge_id'], 'challenge_answers_ibfk_1')->references(['id'])->on('challenge')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenge_answers', function (Blueprint $table) {
            $table->dropForeign('challenge_answers_ibfk_1');
        });
    }
};
