@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detail Booking" />

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-success-200 bg-success-50 px-5 py-4 text-sm text-success-700 dark:border-success-800 dark:bg-success-500/10 dark:text-success-300">{{ session('success') }}</div>
        @endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $booking->booking_code }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $booking->guest_name }} - {{ $booking->guest_phone }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('payments.create', ['booking_id' => $booking->id]) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Catat Payment</a>
                <a href="{{ route('bookings.adjustments.create', $booking) }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Penyesuaian Booking</a>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 xl:col-span-4">
                <x-common.component-card title="Ringkasan Booking" desc="Informasi inti booking dan status transaksi saat ini.">
                    <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Brand:</span> {{ $booking->brand?->name }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Villa:</span> {{ $booking->villa?->name }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Villa Unit:</span> {{ $booking->villaUnit?->unit_name }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Periode:</span> {{ $booking->check_in->format('d M Y') }} - {{ $booking->check_out->format('d M Y') }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Status Booking:</span> <x-ui.badge color="{{ $booking->booking_status === 'confirmed' ? 'success' : 'warning' }}">{{ $booking->booking_status }}</x-ui.badge></div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Status Payment:</span> <x-ui.badge color="{{ $booking->payment_status === 'paid' ? 'success' : ($booking->payment_status === 'partial' ? 'warning' : 'light') }}">{{ $booking->payment_status }}</x-ui.badge></div>
                    </div>
                </x-common.component-card>
            </div>
            <div class="col-span-12 xl:col-span-8">
                <x-common.component-card title="Ringkasan Total" desc="Total booking akan selalu dihitung ulang dari item booking dan payment.">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Subtotal</p><p class="mt-2 text-xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($booking->total_before_discount, 0, ',', '.') }}</p></div>
                        <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Total Payment</p><p class="mt-2 text-xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($booking->total_paid, 0, ',', '.') }}</p></div>
                        <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Sisa Tagihan</p><p class="mt-2 text-xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($booking->remaining_balance, 0, ',', '.') }}</p></div>
                    </div>
                    <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Grand Total</p><p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($booking->grand_total, 0, ',', '.') }}</p></div>
                </x-common.component-card>
            </div>
        </div>

        <x-common.component-card title="Booking Items" desc="Daftar nightly pricing, add-on, dan penyesuaian yang terhubung ke booking ini.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jenis</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Qty</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</th></tr></thead>
                    <tbody>
                        @foreach ($booking->items as $item)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800"><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $item->item_type }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $item->item_name }}<div class="text-xs text-gray-500 dark:text-gray-400">{{ $item->notes }}</div></td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $item->reference_date?->format('d M Y') ?: '-' }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $item->quantity }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ number_format($item->total_price, 0, ',', '.') }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-common.component-card>

        <x-common.component-card title="Riwayat Payment" desc="Semua payment tercatat terpisah dari total booking dan mempengaruhi saldo tersisa.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Metode</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Penerima</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jumlah</th></tr></thead>
                    <tbody>
                        @forelse ($booking->payments as $payment)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800"><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $payment->paid_at?->format('d M Y H:i') }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $payment->payment_method }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $payment->received_by }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ number_format($payment->amount, 0, ',', '.') }}<div class="text-xs text-gray-500 dark:text-gray-400">{{ $payment->note }}</div></td></tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada payment untuk booking ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-common.component-card>
    </div>
@endsection
