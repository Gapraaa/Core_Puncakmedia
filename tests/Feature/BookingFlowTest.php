<?php

use App\Models\Addon;
use App\Models\AddonOption;
use App\Models\Booking;
use App\Models\Brand;
use App\Models\Invoice;
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

    $extraBedOption = AddonOption::query()->create([
        'addon_id' => $perNightAddon->id,
        'name' => 'Extra Bed Reguler ' . $suffix,
        'price' => 15000,
        'charge_basis' => 'per_item_per_night',
        'unit_label' => 'pcs',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $grillOption = AddonOption::query()->create([
        'addon_id' => $perStayAddon->id,
        'name' => 'Grill Paket B ' . $suffix,
        'price' => 50000,
        'charge_basis' => 'per_item',
        'unit_label' => 'paket',
        'sort_order' => 1,
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

    return compact('brand', 'villa', 'villaUnit', 'voucher', 'perNightAddon', 'perStayAddon', 'extraBedOption', 'grillOption');
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
        // DP wajib
        'dp_amount' => 200000,
        'payment_method' => 'transfer',
        'received_by' => 'finance',
        'payment_note' => 'DP booking test',
    ], $overrides);
}

test('booking creation includes DP and sets confirmed + dp status', function () {
    $data = createMasterData();

    post(route('bookings.store', $data['villa']), createBookingPayload($data))
        ->assertRedirect();

    $booking = Booking::query()->with(['items', 'payments', 'voucher'])->latest('id')->first();

    expect($booking)->not->toBeNull();
    expect($booking->items)->toHaveCount(4); // 3 nights + 1 addon
    expect($booking->total_before_discount)->toBe(530000);
    expect($booking->voucher_discount_amount)->toBe(20000);
    expect($booking->manual_discount_amount)->toBe(15000);
    expect($booking->grand_total)->toBe(495000);
    expect($booking->total_paid)->toBe(200000);
    expect($booking->remaining_balance)->toBe(295000);
    expect($booking->payment_status)->toBe('dp');
    expect($booking->booking_status)->toBe('confirmed');
    expect($booking->payments)->toHaveCount(1);
    expect($booking->payments->first()->amount)->toBe(200000);
    expect($booking->invoices()->count())->toBe(1);
    expect($booking->payments->first()->invoice_id)->not->toBeNull();
    expect($booking->invoices()->first()?->label)->toBe('INVOICE UTAMA');

    $nightPrices = $booking->items
        ->where('item_type', 'night')
        ->sortBy('reference_date')
        ->pluck('unit_price')
        ->values()
        ->all();

    expect($nightPrices)->toBe([100000, 150000, 250000]);
    expect($booking->items->where('item_type', 'addon')->first()?->unit_price)->toBe(10000);
});

test('booking creation supports addon options with quantity-based pricing', function () {
    $data = createMasterData();

    post(route('bookings.store', $data['villa']), createBookingPayload($data, [
        'selected_addons' => [],
        'selected_addon_choices' => [
            'option:' . $data['extraBedOption']->id,
            'option:' . $data['grillOption']->id,
        ],
        'addon_choice_quantities' => [
            'option:' . $data['extraBedOption']->id => 2,
            'option:' . $data['grillOption']->id => 1,
        ],
    ]))->assertRedirect();

    $booking = Booking::query()->with(['items', 'payments', 'voucher'])->latest('id')->firstOrFail();

    expect($booking->items)->toHaveCount(5); // 3 nights + 2 addon items
    expect($booking->total_before_discount)->toBe(640000); // 500000 + (15000*2*3) + 50000
    expect($booking->grand_total)->toBe(605000);

    $addonItems = $booking->items->where('item_type', 'addon')->values();

    expect($addonItems)->toHaveCount(2);
    expect($addonItems->pluck('item_name')->all())->toBe([
        $data['perNightAddon']->name . ' - ' . $data['extraBedOption']->name,
        $data['perStayAddon']->name . ' - ' . $data['grillOption']->name,
    ]);
    expect($addonItems->pluck('quantity')->all())->toBe([6, 1]);
    expect($addonItems->pluck('total_price')->all())->toBe([90000, 50000]);
});

test('booking creation without DP is rejected', function () {
    $data = createMasterData();
    $initialBookingCount = Booking::query()->count();

    post(route('bookings.store', $data['villa']), createBookingPayload($data, [
        'dp_amount' => '',
    ]))
        ->assertSessionHasErrors(['dp_amount']);

    expect(Booking::query()->count())->toBe($initialBookingCount);
});

test('booking creation rejects DP greater than grand total', function () {
    $data = createMasterData();
    $initialBookingCount = Booking::query()->count();

    post(route('bookings.store', $data['villa']), createBookingPayload($data, [
        'dp_amount' => 999999999,
    ]))
        ->assertSessionHasErrors(['dp_amount']);

    expect(Booking::query()->count())->toBe($initialBookingCount);
});

test('manual discount requires a reason', function () {
    $data = createMasterData();
    $initialBookingCount = Booking::query()->count();

    post(route('bookings.store', $data['villa']), createBookingPayload($data, [
        'manual_discount_reason' => '',
    ]))
        ->assertSessionHasErrors(['manual_discount_reason']);

    expect(Booking::query()->count())->toBe($initialBookingCount);
});

test('additional payment from booking detail updates status to cicil then lunas', function () {
    $data = createMasterData();

    post(route('bookings.store', $data['villa']), createBookingPayload($data))->assertRedirect();

    $booking = Booking::query()->latest('id')->firstOrFail();
    expect($booking->payment_status)->toBe('dp');

    // Cicilan pertama
    post(route('bookings.payments.store', $booking), [
        'amount' => 100000,
        'payment_method' => 'cash',
        'received_by' => 'office',
        'note' => 'Cicilan 1',
    ])->assertRedirect();

    $booking->refresh();
    expect($booking->payment_status)->toBe('cicil');
    expect($booking->total_paid)->toBe(300000);
    expect($booking->remaining_balance)->toBe(195000);

    // Pelunasan
    post(route('bookings.payments.store', $booking), [
        'amount' => 195000,
        'payment_method' => 'transfer',
        'received_by' => 'finance',
        'note' => 'Pelunasan',
    ])->assertRedirect();

    $booking->refresh();
    expect($booking->payment_status)->toBe('lunas');
    expect($booking->total_paid)->toBe(495000);
    expect($booking->remaining_balance)->toBe(0);
    expect($booking->booking_status)->toBe('confirmed');
});

test('additional payment cannot exceed remaining balance', function () {
    $data = createMasterData();

    post(route('bookings.store', $data['villa']), createBookingPayload($data))->assertRedirect();

    $booking = Booking::query()->latest('id')->firstOrFail();

    post(route('bookings.payments.store', $booking), [
        'amount' => 999999999,
        'payment_method' => 'cash',
        'received_by' => 'office',
        'note' => 'Overpayment',
    ])->assertSessionHasErrors(['amount']);

    $booking->refresh();
    expect($booking->total_paid)->toBe(200000);
    expect($booking->remaining_balance)->toBe(295000);
});

test('booking adjustment can make a lunas booking cicil again', function () {
    $data = createMasterData();

    // Create with full payment as DP
    post(route('bookings.store', $data['villa']), createBookingPayload($data, [
        'dp_amount' => 495000,
    ]))->assertRedirect();

    $booking = Booking::query()->latest('id')->firstOrFail();
    expect($booking->payment_status)->toBe('lunas');
    expect($booking->booking_status)->toBe('confirmed');

    // Add adjustment (add-on)
    post(route('bookings.adjustments.store', $booking), [
        'extend_check_out' => null,
        'selected_addons' => [$data['perStayAddon']->id],
    ])->assertRedirect(route('bookings.show', $booking));

    $booking->refresh();

    expect($booking->total_before_discount)->toBe(550000);
    expect($booking->grand_total)->toBe(515000);
    expect($booking->total_paid)->toBe(495000);
    expect($booking->remaining_balance)->toBe(20000);
    expect($booking->payment_status)->toBe('dp'); // 1 payment, not fully paid
    expect($booking->booking_status)->toBe('confirmed');
    expect($booking->items()->where('item_type', 'addon_adjustment')->count())->toBe(1);
});

test('booking items can be split into a separate invoice while booking total stays intact', function () {
    $data = createMasterData();

    post(route('bookings.store', $data['villa']), createBookingPayload($data))->assertRedirect();

    $booking = Booking::query()->with('items', 'invoices')->latest('id')->firstOrFail();
    $addonItem = $booking->items()->where('item_type', 'addon')->firstOrFail();

    post(route('bookings.invoices.split', $booking), [
        'label' => 'Invoice Add-on',
        'item_ids' => [$addonItem->id],
    ])->assertRedirect(route('bookings.show', $booking));

    $booking->refresh();
    $booking->load(['items', 'payments', 'voucher', 'invoices.items', 'invoices.payments']);

    expect($booking->invoices)->toHaveCount(2);

    $splitInvoice = $booking->invoices->firstWhere('label', 'Invoice Add-on');
    if (! $splitInvoice) {
        $splitInvoice = $booking->invoices->firstWhere('label', 'INVOICE ADD-ON');
    }
    expect($splitInvoice)->not->toBeNull();
    expect($splitInvoice->label)->toBe('INVOICE ADD-ON');
    expect($splitInvoice->items)->toHaveCount(1);
    expect($splitInvoice->subtotal)->toBe($addonItem->total_price);

    expect($booking->grand_total)->toBe(495000);
    expect($booking->total_paid)->toBe(200000);
    expect($booking->remaining_balance)->toBe(295000);
});

test('invoice and payment receipt documents can be opened', function () {
    $data = createMasterData();

    post(route('bookings.store', $data['villa']), createBookingPayload($data))->assertRedirect();

    $booking = Booking::query()->with(['invoices', 'payments'])->latest('id')->firstOrFail();
    $invoice = $booking->invoices()->oldest()->firstOrFail();
    $payment = $booking->payments()->oldest('id')->firstOrFail();

    $invoicePreviewResponse = $this->get(route('documents.invoices.show', $invoice));
    $invoicePreviewResponse->assertOk();
    expect($invoicePreviewResponse->headers->get('content-type'))->toContain('application/pdf');

    $invoiceDownloadResponse = $this->get(route('documents.invoices.show', ['invoice' => $invoice, 'download' => 1]));
    $invoiceDownloadResponse->assertOk();
    expect($invoiceDownloadResponse->headers->get('content-type'))->toContain('application/pdf');
    expect($invoiceDownloadResponse->headers->get('content-disposition'))->toContain('attachment');
    expect($invoiceDownloadResponse->headers->get('content-disposition'))->toContain(strtoupper('invoice-' . $invoice->invoice_number . '.pdf'));

    $receiptPreviewResponse = $this->get(route('documents.payments.receipt', $payment));
    $receiptPreviewResponse->assertOk();
    expect($receiptPreviewResponse->headers->get('content-type'))->toContain('application/pdf');

    $receiptDownloadResponse = $this->get(route('documents.payments.receipt', ['payment' => $payment, 'download' => 1]));
    $receiptDownloadResponse->assertOk();
    expect($receiptDownloadResponse->headers->get('content-type'))->toContain('application/pdf');
    expect($receiptDownloadResponse->headers->get('content-disposition'))->toContain('attachment');
    expect($receiptDownloadResponse->headers->get('content-disposition'))->toContain(strtoupper('bukti-pembayaran-' . ($payment->invoice?->invoice_number ?? $payment->id) . '.pdf'));
});

test('invoice module pages can be opened from villa to detail invoice', function () {
    $data = createMasterData();

    post(route('bookings.store', $data['villa']), createBookingPayload($data))->assertRedirect();

    $booking = Booking::query()->with(['invoices'])->latest('id')->firstOrFail();
    $invoice = $booking->invoices()->oldest()->firstOrFail();

    $this->get(route('invoices.index'))
        ->assertOk()
        ->assertSee('Katalog Invoice per Villa', false);

    $this->get(route('invoices.villa', $data['villa']))
        ->assertOk()
        ->assertSee('Daftar Invoice', false)
        ->assertSee($invoice->invoice_number, false);

    $this->get(route('invoices.show', $invoice))
        ->assertOk()
        ->assertSee($invoice->invoice_number, false)
        ->assertSee('Riwayat Pembayaran', false);
});
