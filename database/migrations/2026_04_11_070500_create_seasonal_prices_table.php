<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasonal_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('villa_unit_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedBigInteger('price');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['villa_unit_id', 'start_date', 'end_date'], 'seasonal_prices_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasonal_prices');
    }
};
