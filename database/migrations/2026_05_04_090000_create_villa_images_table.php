<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villa_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('villa_id')->constrained()->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->string('disk')->default('public');
            $table->string('original_path');
            $table->string('webp_path')->nullable();
            $table->string('thumb_path')->nullable();
            $table->string('original_name');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->string('status', 30)->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['villa_id', 'sort_order']);
            $table->index(['villa_id', 'is_cover']);
            $table->index(['villa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villa_images');
    }
};
