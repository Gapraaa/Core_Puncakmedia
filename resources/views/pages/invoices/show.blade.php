@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detail Invoice" />

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $invoice->invoice_number }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $invoice->label }} - {{ $invoice->booking?->guest_name }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('bookings.show', $invoice->booking) }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Lihat Booking</a>
                <a href="{{ route('documents.invoices.show', ['invoice' => $invoice, 'download' => 1]) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">Unduh Invoice</a>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 xl:col-span-4">
                <x-common.component-card title="Informasi Invoice" desc="Invoice ini hanya referensi keluar ke tamu. Sumber utama transaksi internal tetap booking induknya.">
                    <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Booking Induk:</span> {{ $invoice->booking?->booking_code }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Tamu:</span> {{ $invoice->booking?->guest_name }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Villa:</span> {{ $invoice->booking?->villa?->name }}</div>
                        @if ($invoice->booking?->villa?->is_resort)
                            <div><span class="font-medium text-gray-800 dark:text-white/90">Unit:</span> {{ $invoice->booking?->villaUnit?->unit_name }}</div>
                        @endif
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Periode:</span> {{ $invoice->booking?->check_in?->format('d M Y') }} - {{ $invoice->booking?->check_out?->format('d M Y') }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Status:</span> <x-ui.badge color="{{ $invoice->payment_status === 'lunas' ? 'success' : ($invoice->payment_status === 'cicil' ? 'warning' : 'info') }}">{{ strtoupper($invoice->payment_status) }}</x-ui.badge></div>
                    </div>
                </x-common.component-card>
            </div>
            <div class="col-span-12 xl:col-span-8">
                <x-common.component-card title="Ringkasan Nominal Invoice" desc="Nominal invoice ini tetap diturunkan dari booking dan item yang dipetakan ke invoice.">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Subtotal</p><p class="mt-2 text-xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($invoice->subtotal, 0, ',', '.') }}</p></div>
                        <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Total Pembayaran</p><p class="mt-2 text-xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($invoice->total_paid, 0, ',', '.') }}</p></div>
                        <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Sisa Tagihan</p><p class="mt-2 text-xl font-semibold {{ $invoice->remaining_balance > 0 ? 'text-error-600 dark:text-error-400' : 'text-success-600 dark:text-success-400' }}">{{ number_format($invoice->remaining_balance, 0, ',', '.') }}</p></div>
                    </div>
                </x-common.component-card>
            </div>
        </div>

        <x-common.component-card title="Item Invoice" desc="Komponen biaya yang masuk ke invoice ini.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jenis</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Qty</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Harga</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</th></tr></thead>
                    <tbody>
                        @forelse ($invoice->items as $item)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800"><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $item->item_type }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $item->item_name }}<div class="text-xs text-gray-500 dark:text-gray-400">{{ $item->notes }}</div></td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $item->reference_date?->format('d M Y') ?: '-' }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $item->quantity }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ number_format($item->unit_price, 0, ',', '.') }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ number_format($item->total_price, 0, ',', '.') }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada item pada invoice ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-common.component-card>

        <x-common.component-card title="Riwayat Pembayaran" desc="Pembayaran yang sudah dialokasikan ke invoice ini.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Metode</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Penerima</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jumlah</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th></tr></thead>
                    <tbody>
                        @forelse ($invoice->payments as $payment)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800"><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $payment->paid_at?->format('d M Y H:i') }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ ucfirst($payment->payment_method) }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ ucfirst(str_replace('_', ' ', $payment->received_by)) }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ number_format($payment->amount, 0, ',', '.') }}</td><td class="px-5 py-4 text-right"><a href="{{ route('documents.payments.receipt', ['payment' => $payment, 'download' => 1]) }}" class="text-sm font-medium text-brand-600 dark:text-brand-300">Unduh Bukti</a></td></tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada pembayaran yang masuk ke invoice ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-common.component-card>
    </div>
@endsection
