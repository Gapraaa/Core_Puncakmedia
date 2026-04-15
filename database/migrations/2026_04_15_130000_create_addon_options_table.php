<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addon_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('addon_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedBigInteger('price');
            $table->string('charge_basis');
            $table->string('unit_label')->default('pcs');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['addon_id', 'is_active']);
            $table->index(['charge_basis', 'unit_label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addon_options');
    }
};
