@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detail Pembayaran" />

    <div class="space-y-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Pembayaran untuk {{ $payment->booking?->booking_code ?? 'Booking tidak ditemukan' }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $payment->paid_at?->format('d M Y H:i') }}</p>
            </div>
            <div class="flex gap-2">
                @if ($payment->booking)
                    <a href="{{ route('bookings.show', $payment->booking) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Lihat Booking</a>
                @endif
                <a href="{{ route('payments.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Kembali</a>
            </div>
        </div>

        <x-common.component-card title="Informasi Payment" desc="Detail pencatatan payment yang sudah masuk.">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Jumlah</p><p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p></div>
                <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Metode</p><p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ ucfirst($payment->payment_method) }}</p></div>
                <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Diterima Oleh</p><p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ str_replace('_', ' ', $payment->received_by) }}</p></div>
            </div>
            <div class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                <div><span class="font-medium text-gray-800 dark:text-white/90">Catatan:</span> {{ $payment->note ?: '-' }}</div>
                <div><span class="font-medium text-gray-800 dark:text-white/90">Bukti Payment:</span> {{ $payment->proof_image ?: '-' }}</div>
                @if ($payment->booking)
                    <div><span class="font-medium text-gray-800 dark:text-white/90">Tamu:</span> {{ $payment->booking->guest_name }}</div>
                @endif
            </div>
        </x-common.component-card>
    </div>
@endsection
