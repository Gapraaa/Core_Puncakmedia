<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->string('booking_code')->unique();
            $table->string('guest_name');
            $table->string('guest_phone');
            $table->foreignId('brand_id')->constrained()->restrictOnDelete();
            $table->foreignId('villa_id')->constrained()->restrictOnDelete();
            $table->foreignId('villa_unit_id')->constrained()->restrictOnDelete();
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedBigInteger('total_before_discount')->default(0);
            $table->foreignId('voucher_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('voucher_discount_amount')->default(0);
            $table->unsignedBigInteger('manual_discount_amount')->default(0);
            $table->text('manual_discount_reason')->nullable();
            $table->unsignedBigInteger('grand_total')->default(0);
            $table->unsignedBigInteger('total_paid')->default(0);
            $table->unsignedBigInteger('remaining_balance')->default(0);
            $table->string('payment_status')->default('unpaid');
            $table->string('booking_status')->default('draft');
            $table->string('guest_link_token')->unique()->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['check_in', 'check_out']);
            $table->index(['brand_id', 'villa_id', 'villa_unit_id']);
            $table->index(['payment_status', 'booking_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
