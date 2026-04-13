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
                        <div>
                            <span class="font-medium text-gray-800 dark:text-white/90">Status Booking:</span>
                            <x-ui.badge color="{{ $booking->booking_status === 'confirmed' ? 'success' : 'error' }}">{{ ucfirst($booking->booking_status) }}</x-ui.badge>
                        </div>
                        <div>
                            <span class="font-medium text-gray-800 dark:text-white/90">Status Payment:</span>
                            @php
                                $paymentBadgeColor = match($booking->payment_status) {
                                    'lunas' => 'success',
                                    'cicil' => 'warning',
                                    default => 'info',
                                };
                            @endphp
                            <x-ui.badge color="{{ $paymentBadgeColor }}">{{ strtoupper($booking->payment_status) }}</x-ui.badge>
                        </div>
                    </div>
                </x-common.component-card>
            </div>
            <div class="col-span-12 xl:col-span-8">
                <x-common.component-card title="Ringkasan Total" desc="Total booking dihitung dari item booking dan payment.">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Subtotal</p><p class="mt-2 text-xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($booking->total_before_discount, 0, ',', '.') }}</p></div>
                        <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Total Payment</p><p class="mt-2 text-xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($booking->total_paid, 0, ',', '.') }}</p></div>
                        <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Sisa Tagihan</p><p class="mt-2 text-xl font-semibold {{ $booking->remaining_balance > 0 ? 'text-error-600 dark:text-error-400' : 'text-success-600 dark:text-success-400' }}">{{ number_format($booking->remaining_balance, 0, ',', '.') }}</p></div>
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
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800"><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $payment->paid_at?->format('d M Y H:i') }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ ucfirst($payment->payment_method) }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ ucfirst(str_replace('_', ' ', $payment->received_by)) }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ number_format($payment->amount, 0, ',', '.') }}<div class="text-xs text-gray-500 dark:text-gray-400">{{ $payment->note }}</div></td></tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada payment untuk booking ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-common.component-card>

        {{-- Tambah Pembayaran (hanya jika belum lunas dan belum cancelled) --}}
        @if ($booking->payment_status !== 'lunas' && $booking->booking_status !== 'cancelled')
            <x-common.component-card title="Tambah Pembayaran" desc="Cicilan atau pelunasan untuk booking ini.">
                <form method="POST" action="{{ route('bookings.payments.store', $booking) }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nominal (Rupiah)</label>
                            <input type="number" step="1" min="1" name="amount" value="{{ old('amount') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                            @error('amount')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Metode</label>
                            <select name="payment_method" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                                <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
                                <option value="transfer" @selected(old('payment_method') === 'transfer')>Transfer</option>
                            </select>
                            @error('payment_method')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Penerima</label>
                            <select name="received_by" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                                <option value="finance" @selected(old('received_by') === 'finance')>Finance</option>
                                <option value="office" @selected(old('received_by') === 'office')>Office</option>
                                <option value="field_staff" @selected(old('received_by') === 'field_staff')>Field Staff</option>
                            </select>
                            @error('received_by')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Bayar</label>
                            <input onclick="this.showPicker()" type="date" name="paid_at" value="{{ old('paid_at', now()->format('Y-m-d')) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                        <textarea name="note" rows="2" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">{{ old('note') }}</textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Catat Pembayaran</button>
                    </div>
                </form>
            </x-common.component-card>
        @endif
    </div>
@endsection
