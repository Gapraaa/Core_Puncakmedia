<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('price');
            $table->string('charge_type');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['charge_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addons');
    }
};
