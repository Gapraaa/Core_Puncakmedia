@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detail Voucher" />

    <div class="space-y-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $voucher->code }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $voucher->discount_type === 'percentage' ? 'Persentase' : 'Nominal tetap' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('vouchers.edit', $voucher) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Edit Voucher</a>
                <a href="{{ route('vouchers.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Kembali</a>
            </div>
        </div>

        <x-common.component-card title="Konfigurasi Voucher" desc="Aturan diskon voucher yang berlaku pada booking.">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Nilai Diskon</p><p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $voucher->discount_type === 'percentage' ? $voucher->amount.'%' : 'Rp '.number_format($voucher->amount, 0, ',', '.') }}</p></div>
                <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Minimum Transaksi</p><p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">Rp {{ number_format($voucher->minimum_transaction, 0, ',', '.') }}</p></div>
                <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Status</p><p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ $voucher->is_active ? 'Aktif' : 'Nonaktif' }}</p></div>
            </div>
        </x-common.component-card>

        <x-common.component-card title="Booking Terbaru" desc="Booking terbaru yang memakai voucher ini.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Booking</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tamu</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Grand Total</th></tr></thead>
                    <tbody>
                        @forelse ($voucher->bookings as $booking)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800"><td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ $booking->booking_code }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $booking->guest_name }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">Rp {{ number_format($booking->grand_total, 0, ',', '.') }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada booking yang memakai voucher ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-common.component-card>
    </div>
@endsection
