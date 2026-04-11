<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('item_type');
            $table->string('item_name');
            $table->date('reference_date')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->unsignedBigInteger('total_price')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'item_type']);
            $table->index('reference_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};
