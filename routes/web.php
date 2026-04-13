<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\Bookings\BookingAdjustmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterData\AddonController;
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
    Route::resource('villa-units', VillaUnitController::class)->except('show');
    Route::resource('seasonal-prices', SeasonalPriceController::class)->except('show');
    Route::resource('addons', AddonController::class)->except('show');
    Route::resource('vouchers', VoucherController::class)->except('show');
});

Route::get('bookings', [BookingController::class, 'indexVillas'])->name('bookings.index');
Route::get('bookings/villas/{villa}', [BookingController::class, 'index'])->name('bookings.list');
Route::get('bookings/villas/{villa}/create', [BookingController::class, 'create'])->name('bookings.create');
Route::post('bookings/villas/{villa}', [BookingController::class, 'store'])->name('bookings.store');
Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
Route::get('bookings/{booking}/adjustments/create', [BookingAdjustmentController::class, 'create'])->name('bookings.adjustments.create');
Route::post('bookings/{booking}/adjustments', [BookingAdjustmentController::class, 'store'])->name('bookings.adjustments.store');

// Payment: tambah dari detail booking, ledger tetap untuk Finance
Route::post('bookings/{booking}/payments', [PaymentController::class, 'store'])->name('bookings.payments.store');
Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');

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
