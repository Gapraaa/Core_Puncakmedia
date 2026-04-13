@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Daftar Booking" />

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-success-200 bg-success-50 px-5 py-4 text-sm text-success-700 dark:border-success-800 dark:bg-success-500/10 dark:text-success-300">{{ session('success') }}</div>
        @endif
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Daftar Booking - {{ $villa->name }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pantau booking yang sudah dibuat beserta status saldo dan progresnya.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('bookings.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50">Kembali ke Daftar Villa</a>
                <a href="{{ route('bookings.create', $villa) }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Buat Booking</a>
            </div>
        </div>

        <x-common.component-card title="Filter Booking" desc="Cari booking berdasarkan kode, tamu, status, dan tanggal check-in.">
            <form method="GET" action="{{ route('bookings.list', $villa) }}" x-data="{ dateFrom: '{{ $filters['date_from'] ?? '' }}' }" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div class="xl:col-span-2"><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label><input onkeyup="let v=this.value.toLowerCase(); document.querySelectorAll('table tbody tr').forEach(tr => { if(tr.children.length > 1) tr.style.display = tr.innerText.toLowerCase().includes(v) ? '' : 'none' })" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Kode, invoice, tamu, telepon" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status Payment</label><select name="payment_status" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90"><option value="">Semua</option><option value="dp" @selected(($filters['payment_status'] ?? '') === 'dp')>DP</option><option value="cicil" @selected(($filters['payment_status'] ?? '') === 'cicil')>Cicil</option><option value="lunas" @selected(($filters['payment_status'] ?? '') === 'lunas')>Lunas</option></select></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status Booking</label><select name="booking_status" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90"><option value="">Semua</option><option value="confirmed" @selected(($filters['booking_status'] ?? '') === 'confirmed')>Confirmed</option><option value="cancelled" @selected(($filters['booking_status'] ?? '') === 'cancelled')>Cancelled</option></select></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Check-in Dari</label><input x-model="dateFrom" onclick="this.showPicker()" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Check-in Sampai</label><input :min="dateFrom" onclick="this.showPicker()" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                <div class="flex items-end gap-3 xl:col-span-5"><button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Terapkan Filter</button><a href="{{ route('bookings.list', $villa) }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Reset</a></div>
            </form>
        </x-common.component-card>

        <x-common.component-card title="Booking Terbaru" desc="Daftar booking awal yang sudah dihitung dari nightly pricing dan add-on.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Kode</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tamu</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Unit</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                                <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ $booking->booking_code }}<div class="text-xs text-gray-500 dark:text-gray-400">{{ $booking->invoice_no }}</div></td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $booking->guest_name }}<div class="text-xs text-gray-500 dark:text-gray-400">{{ $booking->guest_phone }}</div></td>
                                <td class="px-5 py-4 text-sm font-medium text-gray-600 dark:text-gray-300">{{ $booking->villaUnit?->unit_name }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $booking->check_in->format('d M Y') }} - {{ $booking->check_out->format('d M Y') }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">Rp {{ number_format($booking->grand_total, 0, ',', '.') }}<div class="text-xs text-gray-500 dark:text-gray-400">Sisa: Rp {{ number_format($booking->remaining_balance, 0, ',', '.') }}</div></td>
                                <td class="px-5 py-4"><div class="flex flex-col gap-2">@php $paymentBadge = match($booking->payment_status) { 'lunas' => 'success', 'cicil' => 'warning', default => 'info', }; @endphp <x-ui.badge :color="$paymentBadge">{{ strtoupper($booking->payment_status) }}</x-ui.badge><x-ui.badge :color="$booking->booking_status === 'confirmed' ? 'success' : 'error'">{{ ucfirst($booking->booking_status) }}</x-ui.badge></div></td>
                                <td class="px-5 py-4 text-right"><a href="{{ route('bookings.show', $booking) }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada booking yang dibuat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $bookings->links() }}</div>
        </x-common.component-card>
    </div>
@endsection
