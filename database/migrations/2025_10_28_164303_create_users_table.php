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
        Schema::create('users', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('photo', 512)->nullable();
            $table->string('email', 150)->unique('email');
            $table->text('password')->nullable();
            $table->string('telephone', 25)->nullable();
            $table->dateTime('date_edited')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->tinyText('date_added')->nullable();
            $table->string('username', 50)->index('idx_username');
            $table->integer('support_team_id')->nullable()->index('support_team_id');
            $table->char('country', 2)->nullable();
            $table->enum('login_type', ['local', 'facebook', 'google'])->default('local');
            $table->string('google_token', 1024)->nullable();

            $table->unique(['username'], 'username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
