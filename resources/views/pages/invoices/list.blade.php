@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Daftar Invoice" />

    <div class="space-y-6" data-async-page="true">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $villa->name }} - {{ $villaUnit->unit_name }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cari invoice, cek status pembayaran, dan buka detail invoice untuk unit ini.</p>
            </div>
            <a href="{{ $villa->is_resort ? route('invoices.units', $villa) : route('invoices.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">{{ $villa->is_resort ? 'Kembali ke Daftar Unit' : 'Kembali ke Daftar Villa' }}</a>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <x-common.component-card title="Villa" desc="Properti induk dari unit ini.">
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $villa->name }}</p>
            </x-common.component-card>
            <x-common.component-card title="Unit" desc="Unit operasional yang sedang dipantau.">
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $villaUnit->unit_name }}</p>
            </x-common.component-card>
            <x-common.component-card title="Booking" desc="Jumlah booking tercatat pada unit ini.">
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $villaUnit->bookings_count }}</p>
            </x-common.component-card>
        </div>

        <x-common.component-card title="Filter Invoice" desc="Cari invoice berdasarkan nomor invoice, kode booking, nama tamu, dan status pembayaran.">
            <form data-async-page-form="true" method="GET" action="{{ $villa->is_resort ? route('invoices.unit', [$villa, $villaUnit]) : route('invoices.villa', $villa) }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div class="xl:col-span-2"><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label><input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nomor invoice, kode booking, tamu" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status Pembayaran</label><select name="payment_status" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90"><option value="">Semua</option><option value="dp" @selected(($filters['payment_status'] ?? '') === 'dp')>DP</option><option value="cicil" @selected(($filters['payment_status'] ?? '') === 'cicil')>Cicil</option><option value="lunas" @selected(($filters['payment_status'] ?? '') === 'lunas')>Lunas</option><option value="empty" @selected(($filters['payment_status'] ?? '') === 'empty')>Kosong</option></select></div>
                <div class="flex gap-3 xl:col-span-3"><button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Terapkan Filter</button><a href="{{ $villa->is_resort ? route('invoices.unit', [$villa, $villaUnit]) : route('invoices.villa', $villa) }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Reset</a></div>
            </form>
        </x-common.component-card>

        <x-common.component-card title="Daftar Invoice" desc="Invoice tampil bersama booking induk, nominal, status, dan akses cepat ke detail atau dokumen.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Invoice</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Booking</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tamu</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Dibayar</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sisa</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th></tr></thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                                <td class="px-5 py-4"><div><p class="font-medium text-gray-800 dark:text-white/90">{{ $invoice->invoice_number }}</p><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $invoice->label }}</p></div></td>
                                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $invoice->booking?->booking_code }}<div class="text-xs text-gray-500 dark:text-gray-400">{{ $invoice->booking?->check_in?->format('j M Y') }} - {{ $invoice->booking?->check_out?->format('j M Y') }}</div></td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $invoice->booking?->guest_name ?? '-' }}</td>
                                <td class="px-5 py-4">@php $badgeColor = match($invoice->payment_status) { 'lunas' => 'success', 'cicil' => 'warning', 'empty' => 'error', default => 'info', }; @endphp <x-ui.badge :color="$badgeColor">{{ strtoupper($invoice->payment_status) }}</x-ui.badge></td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">Rp {{ number_format($invoice->total_paid, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-sm {{ $invoice->remaining_balance > 0 ? 'text-error-600 dark:text-error-400' : 'text-success-600 dark:text-success-400' }}">Rp {{ number_format($invoice->remaining_balance, 0, ',', '.') }}</td>
                                <td class="px-5 py-4"><div class="flex items-center justify-end gap-2"><a href="{{ route('invoices.show', $invoice) }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Detail</a><a href="{{ route('documents.invoices.show', ['invoice' => $invoice, 'download' => 1]) }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">Unduh</a></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada invoice untuk unit ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $invoices->links() }}</div>
        </x-common.component-card>
    </div>
@endsection
