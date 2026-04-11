<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('discount_type');
            $table->unsignedBigInteger('amount');
            $table->date('valid_until')->nullable();
            $table->unsignedBigInteger('minimum_transaction')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['discount_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
