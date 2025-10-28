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
        Schema::create('config', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('company_name');
            $table->text('email');
            $table->text('phone')->nullable();
            $table->mediumText('address')->nullable();
            $table->mediumText('analytics')->nullable();
            $table->text('twitter')->nullable();
            $table->text('facebook')->nullable();
            $table->text('linkedin')->nullable();
            $table->text('googleplus')->nullable();
            $table->text('youtube')->nullable();
            $table->text('keyword')->nullable();
            $table->boolean('templates')->default(false);
            $table->boolean('stats')->default(false);
            $table->boolean('redirect')->default(false);
            $table->text('colour');
            $table->string('pm_email', 50);
            $table->string('project_ref', 50);
            $table->integer('launch_requested')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config');
    }
};
