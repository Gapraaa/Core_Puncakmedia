<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('label');
            $table->string('invoice_type')->default('combined');
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('total_paid')->default(0);
            $table->unsignedBigInteger('remaining_balance')->default(0);
            $table->string('payment_status')->default('dp');
            $table->timestamps();

            $table->index(['booking_id', 'payment_status']);
        });

        Schema::table('booking_items', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('booking_id')->constrained()->nullOnDelete();
            $table->index(['invoice_id', 'item_type']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('booking_id')->constrained()->nullOnDelete();
            $table->index(['invoice_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_id');
        });

        Schema::table('booking_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_id');
        });

        Schema::dropIfExists('invoices');
    }
};
