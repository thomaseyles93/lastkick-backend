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
        Schema::create('daily_streak', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id');
            $table->date('date');
            $table->integer('streak_count')->default(1);
            $table->timestamp('date_created')->nullable()->useCurrent();

            $table->unique(['user_id', 'date'], 'unique_user_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_streak');
    }
};
