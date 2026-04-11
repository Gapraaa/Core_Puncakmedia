<?php

use App\Models\Addon;
use App\Models\Booking;
use App\Models\Brand;
use App\Models\Payment;
use App\Models\SeasonalPrice;
use App\Models\Villa;
use App\Models\VillaUnit;
use App\Models\Voucher;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;

use function Pest\Laravel\post;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

function createMasterData(): array
{
    $suffix = Str::lower(Str::random(6));

    $brand = Brand::query()->create([
        'name' => 'PuncakMediaBogor ' . $suffix,
        'slug' => 'puncakmediabogor-' . $suffix,
    ]);

    $villa = Villa::query()->create([
        'name' => 'Villa Melati ' . $suffix,
        'slug' => 'villa-melati-' . $suffix,
        'location' => 'Puncak',
        'capacity' => 10,
        'is_resort' => false,
        'status' => 'active',
    ]);

    $villa->brands()->attach($brand->id);

    $villaUnit = VillaUnit::query()->create([
        'villa_id' => $villa->id,
        'unit_name' => 'Unit A ' . Str::upper($suffix),
        'unit_type' => 'private',
        'capacity' => 10,
        'price_weekday' => 100000,
        'price_semi_weekend' => 150000,
        'price_weekend' => 200000,
        'status' => 'active',
    ]);

    SeasonalPrice::query()->create([
        'villa_unit_id' => $villaUnit->id,
        'start_date' => '2026-04-18',
        'end_date' => '2026-04-18',
        'price' => 250000,
        'note' => 'Libur panjang',
    ]);

    $perNightAddon = Addon::query()->create([
        'name' => 'Extra Bed ' . $suffix,
        'price' => 10000,
        'charge_type' => 'per_night',
        'is_active' => true,
    ]);

    $perStayAddon = Addon::query()->create([
        'name' => 'Grill Package ' . $suffix,
        'price' => 20000,
        'charge_type' => 'per_stay',
        'is_active' => true,
    ]);

    $voucher = Voucher::query()->create([
        'code' => 'HEMAT-' . Str::upper($suffix),
        'discount_type' => 'fixed',
        'amount' => 20000,
        'minimum_transaction' => 100000,
        'valid_until' => now()->addDays(7)->toDateString(),
        'is_active' => true,
    ]);

    return compact('brand', 'villa', 'villaUnit', 'voucher', 'perNightAddon', 'perStayAddon');
}

function createBookingPayload(array $data, array $overrides = []): array
{
    return array_merge([
        'brand_id' => $data['brand']->id,
        'villa_id' => $data['villa']->id,
        'villa_unit_id' => $data['villaUnit']->id,
        'guest_name' => 'Budi Santoso',
        'guest_phone' => '081234567890',
        'check_in' => '2026-04-16',
        'check_out' => '2026-04-19',
        'voucher_id' => $data['voucher']->id,
        'manual_discount_amount' => 15000,
        'manual_discount_reason' => 'Promo admin sales',
        'selected_addons' => [$data['perNightAddon']->id],
    ], $overrides);
}

test('booking creation calculates mixed nightly pricing in rupiah integers', function () {
    $data = createMasterData();

    post(route('bookings.store'), createBookingPayload($data))
        ->assertRedirect();

    $booking = Booking::query()->with(['items', 'voucher'])->latest('id')->first();

    expect($booking)->not->toBeNull();
    expect($booking->items)->toHaveCount(4);
    expect($booking->total_before_discount)->toBe(530000);
    expect($booking->voucher_discount_amount)->toBe(20000);
    expect($booking->manual_discount_amount)->toBe(15000);
    expect($booking->grand_total)->toBe(495000);
    expect($booking->total_paid)->toBe(0);
    expect($booking->remaining_balance)->toBe(495000);
    expect($booking->payment_status)->toBe('unpaid');
    expect($booking->booking_status)->toBe('draft');

    $nightPrices = $booking->items
        ->where('item_type', 'night')
        ->sortBy('reference_date')
        ->pluck('unit_price')
        ->values()
        ->all();

    expect($nightPrices)->toBe([100000, 150000, 250000]);
    expect($booking->items->where('item_type', 'addon')->first()?->unit_price)->toBe(10000);
});

test('manual discount requires a reason', function () {
    $data = createMasterData();
    $initialBookingCount = Booking::query()->count();

    post(route('bookings.store'), createBookingPayload($data, [
        'manual_discount_reason' => '',
    ]))
        ->assertSessionHasErrors(['manual_discount_reason']);

    expect(Booking::query()->count())->toBe($initialBookingCount);
});

test('payment updates booking totals and confirms fully paid bookings', function () {
    $data = createMasterData();

    post(route('bookings.store'), createBookingPayload($data))->assertRedirect();

    $booking = Booking::query()->latest('id')->firstOrFail();
    $initialPaymentCount = Payment::query()->count();

    post(route('payments.store'), [
        'booking_id' => $booking->id,
        'amount' => 495000,
        'payment_method' => 'transfer',
        'received_by' => 'finance',
        'note' => 'Pelunasan',
        'proof_image' => 'bukti/transfer.jpg',
        'paid_at' => now()->format('Y-m-d H:i:s'),
    ])->assertRedirect();

    $booking->refresh();

    expect(Payment::query()->count())->toBe($initialPaymentCount + 1);
    expect($booking->total_paid)->toBe(495000);
    expect($booking->remaining_balance)->toBe(0);
    expect($booking->payment_status)->toBe('paid');
    expect($booking->booking_status)->toBe('confirmed');
});

test('booking adjustment can make a paid booking partial again', function () {
    $data = createMasterData();

    post(route('bookings.store'), createBookingPayload($data))->assertRedirect();

    $booking = Booking::query()->latest('id')->firstOrFail();

    post(route('payments.store'), [
        'booking_id' => $booking->id,
        'amount' => 495000,
        'payment_method' => 'cash',
        'received_by' => 'office',
        'note' => 'Lunas awal',
        'paid_at' => now()->format('Y-m-d H:i:s'),
    ])->assertRedirect();

    $booking->refresh();
    expect($booking->payment_status)->toBe('paid');
    expect($booking->booking_status)->toBe('confirmed');

    post(route('bookings.adjustments.store', $booking), [
        'extend_check_out' => null,
        'selected_addons' => [$data['perStayAddon']->id],
    ])->assertRedirect(route('bookings.show', $booking));

    $booking->refresh();

    expect($booking->total_before_discount)->toBe(550000);
    expect($booking->grand_total)->toBe(515000);
    expect($booking->total_paid)->toBe(495000);
    expect($booking->remaining_balance)->toBe(20000);
    expect($booking->payment_status)->toBe('partial');
    expect($booking->booking_status)->toBe('pending_payment');
    expect($booking->items()->where('item_type', 'addon_adjustment')->count())->toBe(1);
});
