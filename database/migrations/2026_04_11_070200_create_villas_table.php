<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('capacity')->default(0);
            $table->boolean('is_resort')->default(false);
            $table->string('status')->default('draft');
            $table->text('rules')->nullable();
            $table->text('pros')->nullable();
            $table->text('cons')->nullable();
            $table->string('youtube_url')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villas');
    }
};
