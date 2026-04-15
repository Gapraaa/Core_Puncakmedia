<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\Bookings\BookingAdjustmentController;
use App\Http\Controllers\Bookings\BookingInvoiceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MasterData\AddonController;
use App\Http\Controllers\MasterData\AddonOptionController;
use App\Http\Controllers\MasterData\BrandController;
use App\Http\Controllers\MasterData\SeasonalPriceController;
use App\Http\Controllers\MasterData\VillaController;
use App\Http\Controllers\MasterData\VillaUnitController;
use App\Http\Controllers\MasterData\VoucherController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::view('/calendar', 'pages.module-placeholder', [
    'title' => 'Kalender Booking',
    'pageTitle' => 'Kalender Booking',
    'description' => 'Perencanaan ketersediaan dan okupansi akan tetap memakai layout TailAdmin dan disambungkan ke data booking pada tahap berikutnya.',
])->name('calendar');

Route::prefix('master-data')->group(function (): void {
    Route::resource('brands', BrandController::class)->except('show');
    Route::resource('villas', VillaController::class)->except('show');
    Route::get('villa-units', [VillaUnitController::class, 'index'])->name('villa-units.index');
    Route::get('villa-units/villas/{villa}', [VillaUnitController::class, 'listByVilla'])->name('villa-units.list');
    Route::get('villa-units/create', [VillaUnitController::class, 'create'])->name('villa-units.create');
    Route::get('villa-units/villas/{villa}/create', [VillaUnitController::class, 'createForVilla'])->name('villa-units.create-for-villa');
    Route::post('villa-units', [VillaUnitController::class, 'store'])->name('villa-units.store');
    Route::get('villa-units/{villaUnit}/edit', [VillaUnitController::class, 'edit'])->name('villa-units.edit');
    Route::put('villa-units/{villaUnit}', [VillaUnitController::class, 'update'])->name('villa-units.update');
    Route::delete('villa-units/{villaUnit}', [VillaUnitController::class, 'destroy'])->name('villa-units.destroy');
    Route::get('seasonal-prices', [SeasonalPriceController::class, 'index'])->name('seasonal-prices.index');
    Route::get('seasonal-prices/villas/{villa}', [SeasonalPriceController::class, 'showVilla'])->name('seasonal-prices.villa');
    Route::get('seasonal-prices/villas/{villa}/units', [SeasonalPriceController::class, 'showVillaUnits'])->name('seasonal-prices.units');
    Route::get('seasonal-prices/villas/{villa}/units/{villaUnit}', [SeasonalPriceController::class, 'showUnit'])->name('seasonal-prices.unit');
    Route::get('seasonal-prices/create', [SeasonalPriceController::class, 'create'])->name('seasonal-prices.create');
    Route::get('seasonal-prices/villas/{villa}/create', [SeasonalPriceController::class, 'createForVilla'])->name('seasonal-prices.create-for-villa');
    Route::get('seasonal-prices/villas/{villa}/units/{villaUnit}/create', [SeasonalPriceController::class, 'createForUnit'])->name('seasonal-prices.create-for-unit');
    Route::post('seasonal-prices', [SeasonalPriceController::class, 'store'])->name('seasonal-prices.store');
    Route::get('seasonal-prices/{seasonalPrice}/edit', [SeasonalPriceController::class, 'edit'])->name('seasonal-prices.edit');
    Route::put('seasonal-prices/{seasonalPrice}', [SeasonalPriceController::class, 'update'])->name('seasonal-prices.update');
    Route::delete('seasonal-prices/{seasonalPrice}', [SeasonalPriceController::class, 'destroy'])->name('seasonal-prices.destroy');
    Route::resource('addons', AddonController::class);
    Route::get('addons/{addon}/options/create', [AddonOptionController::class, 'create'])->name('addon-options.create');
    Route::post('addons/{addon}/options', [AddonOptionController::class, 'store'])->name('addon-options.store');
    Route::get('addons/{addon}/options/{addonOption}/edit', [AddonOptionController::class, 'edit'])->name('addon-options.edit');
    Route::put('addons/{addon}/options/{addonOption}', [AddonOptionController::class, 'update'])->name('addon-options.update');
    Route::delete('addons/{addon}/options/{addonOption}', [AddonOptionController::class, 'destroy'])->name('addon-options.destroy');
    Route::resource('vouchers', VoucherController::class)->except('show');
});

Route::get('bookings', [BookingController::class, 'indexVillas'])->name('bookings.index');
Route::get('bookings/villas/{villa}', [BookingController::class, 'index'])->name('bookings.list');
Route::get('bookings/villas/{villa}/create', [BookingController::class, 'create'])->name('bookings.create');
Route::post('bookings/villas/{villa}', [BookingController::class, 'store'])->name('bookings.store');
Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
Route::get('bookings/{booking}/adjustments/create', [BookingAdjustmentController::class, 'create'])->name('bookings.adjustments.create');
Route::post('bookings/{booking}/adjustments', [BookingAdjustmentController::class, 'store'])->name('bookings.adjustments.store');
Route::post('bookings/{booking}/invoices/split', [BookingInvoiceController::class, 'split'])->name('bookings.invoices.split');

// Payment: tambah dari detail booking, ledger tetap untuk Finance
Route::post('bookings/{booking}/payments', [PaymentController::class, 'store'])->name('bookings.payments.store');
Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
Route::get('invoices/villas/{villa}', [InvoiceController::class, 'showVilla'])->name('invoices.villa');
Route::get('invoices/villas/{villa}/units', [InvoiceController::class, 'showVillaUnits'])->name('invoices.units');
Route::get('invoices/villas/{villa}/units/{villaUnit}', [InvoiceController::class, 'showUnit'])->name('invoices.unit');
Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
Route::get('documents/invoices/{invoice}', [DocumentController::class, 'showInvoice'])->name('documents.invoices.show');
Route::get('documents/payments/{payment}/receipt', [DocumentController::class, 'showReceipt'])->name('documents.payments.receipt');

Route::prefix('reports')->group(function (): void {
    Route::view('/finance', 'pages.module-placeholder', [
        'title' => 'Laporan Keuangan',
        'pageTitle' => 'Laporan Keuangan',
        'description' => 'Ekspor finance, visibilitas saldo, dan keluaran spreadsheet sync nantinya tersedia di sini.',
    ])->name('reports.finance');
});

Route::prefix('migration')->group(function (): void {
    Route::view('/legacy', 'pages.module-placeholder', [
        'title' => 'Pemetaan Legacy',
        'pageTitle' => 'Pemetaan Legacy',
        'description' => 'Pemetaan data legacy `vilas`, `reservasi`, dan data historis akan didokumentasikan serta disambungkan di sini pada fase berikutnya.',
    ])->name('migration.legacy');
});

Route::view('/profile', 'pages.profile', ['title' => 'Profil'])->name('profile');
Route::view('/signin', 'pages.auth.signin', ['title' => 'Masuk'])->name('signin');
Route::view('/signup', 'pages.auth.signup', ['title' => 'Daftar'])->name('signup');
