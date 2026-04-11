<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villa_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('villa_id')->constrained()->cascadeOnDelete();
            $table->string('unit_name');
            $table->string('unit_type')->nullable();
            $table->unsignedInteger('capacity')->default(0);
            $table->unsignedBigInteger('price_weekday')->default(0);
            $table->unsignedBigInteger('price_semi_weekend')->default(0);
            $table->unsignedBigInteger('price_weekend')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['villa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villa_units');
    }
};
