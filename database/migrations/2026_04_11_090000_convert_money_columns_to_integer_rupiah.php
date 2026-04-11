<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE villa_units MODIFY price_weekday BIGINT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE villa_units MODIFY price_semi_weekend BIGINT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE villa_units MODIFY price_weekend BIGINT UNSIGNED NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE seasonal_prices MODIFY price BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE addons MODIFY price BIGINT UNSIGNED NOT NULL');

        DB::statement('ALTER TABLE vouchers MODIFY amount BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE vouchers MODIFY minimum_transaction BIGINT UNSIGNED NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE bookings MODIFY total_before_discount BIGINT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE bookings MODIFY voucher_discount_amount BIGINT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE bookings MODIFY manual_discount_amount BIGINT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE bookings MODIFY grand_total BIGINT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE bookings MODIFY total_paid BIGINT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE bookings MODIFY remaining_balance BIGINT UNSIGNED NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE booking_items MODIFY unit_price BIGINT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE booking_items MODIFY total_price BIGINT UNSIGNED NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE payments MODIFY amount BIGINT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE villa_units MODIFY price_weekday DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE villa_units MODIFY price_semi_weekend DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE villa_units MODIFY price_weekend DECIMAL(12,2) NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE seasonal_prices MODIFY price DECIMAL(12,2) NOT NULL');
        DB::statement('ALTER TABLE addons MODIFY price DECIMAL(12,2) NOT NULL');

        DB::statement('ALTER TABLE vouchers MODIFY amount DECIMAL(12,2) NOT NULL');
        DB::statement('ALTER TABLE vouchers MODIFY minimum_transaction DECIMAL(12,2) NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE bookings MODIFY total_before_discount DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE bookings MODIFY voucher_discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE bookings MODIFY manual_discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE bookings MODIFY grand_total DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE bookings MODIFY total_paid DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE bookings MODIFY remaining_balance DECIMAL(12,2) NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE booking_items MODIFY unit_price DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE booking_items MODIFY total_price DECIMAL(12,2) NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE payments MODIFY amount DECIMAL(12,2) NOT NULL');
    }
};
