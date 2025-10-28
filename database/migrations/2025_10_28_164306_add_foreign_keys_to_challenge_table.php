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
        Schema::table('challenge', function (Blueprint $table) {
            $table->foreign(['challenge_id'], 'challenge_ibfk_1')->references(['id'])->on('challenge_categories')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['achievement_id'], 'challenge_ibfk_2')->references(['id'])->on('challenge_achievements')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenge', function (Blueprint $table) {
            $table->dropForeign('challenge_ibfk_1');
            $table->dropForeign('challenge_ibfk_2');
        });
    }
};
