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
        Schema::create('users_reset_tokens', function (Blueprint $table) {
            $table->integer('account_id')->primary();
            $table->text('reset_token')->nullable();
            $table->dateTime('reset_expire')->nullable()->index('reset_expire');
            $table->dateTime('date_edited')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->dateTime('date_added')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_reset_tokens');
    }
};
