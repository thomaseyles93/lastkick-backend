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
        Schema::create('reporting', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('page_id')->nullable()->index('page_id');
            $table->integer('post_id')->nullable()->index('post_id');
            $table->string('ip_address', 130);
            $table->text('referer');
            $table->dateTime('date')->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reporting');
    }
};
