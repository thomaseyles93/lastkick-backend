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
        Schema::create('players', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name');
            $table->string('full_name');
            $table->integer('whoscored_id')->nullable()->index('idx_whoscored_id');
            $table->timestamp('date_added')->nullable()->useCurrent();

            $table->unique(['whoscored_id'], 'whoscored_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
