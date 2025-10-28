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
        Schema::table('challenge_categories', function (Blueprint $table) {
            $table->foreign(['achievement_id'], 'challenge_categories_ibfk_2')->references(['id'])->on('challenge_achievements')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenge_categories', function (Blueprint $table) {
            $table->dropForeign('challenge_categories_ibfk_2');
        });
    }
};
