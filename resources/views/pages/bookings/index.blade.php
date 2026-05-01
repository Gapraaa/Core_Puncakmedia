@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Daftar Booking" />

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Daftar Booking - {{ $villa->name }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pantau booking, status pembayaran, dan sisa tagihan dengan tampilan yang lebih cepat dibaca.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('bookings.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50">Kembali ke Daftar Villa</a>
                <a href="{{ route('bookings.create', $villa) }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Buat Booking</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="ops-kpi-card">
                <div class="ops-kpi-label">Total Booking</div>
                <div class="ops-kpi-value">{{ number_format($bookings->total(), 0, ',', '.') }}</div>
                <div class="ops-kpi-note">Jumlah booking hasil filter saat ini.</div>
            </div>
            <div class="ops-kpi-card">
                <div class="ops-kpi-label">Belum Lunas</div>
                <div class="ops-kpi-value">{{ number_format($bookings->getCollection()->where('payment_status', '!=', 'lunas')->count(), 0, ',', '.') }}</div>
                <div class="ops-kpi-note">Booking yang masih perlu follow-up pembayaran.</div>
            </div>
            <div class="ops-kpi-card">
                <div class="ops-kpi-label">Sisa Tagihan</div>
                <div class="ops-kpi-value">Rp {{ number_format($bookings->getCollection()->sum('remaining_balance'), 0, ',', '.') }}</div>
                <div class="ops-kpi-note">Akumulasi saldo berjalan di halaman ini.</div>
            </div>
            <div class="ops-kpi-card">
                <div class="ops-kpi-label">Check-in Terdekat</div>
                <div class="ops-kpi-value text-xl">{{ optional($bookings->getCollection()->sortBy('check_in')->first()?->check_in)->format('d M Y') ?? '-' }}</div>
                <div class="ops-kpi-note">Membantu admin cepat melihat prioritas operasional.</div>
            </div>
        </div>

        <x-common.component-card title="Filter Booking" desc="Cari booking berdasarkan kode, tamu, status, dan tanggal check-in.">
            <form method="GET" action="{{ route('bookings.list', $villa) }}" x-data="{ dateFrom: '{{ $filters['date_from'] ?? '' }}' }" class="ops-form-grid">
                <div class="xl:col-span-2"><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label><input onkeyup="let v=this.value.toLowerCase(); document.querySelectorAll('table tbody tr').forEach(tr => { if(tr.children.length > 1) tr.style.display = tr.innerText.toLowerCase().includes(v) ? '' : 'none' })" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Kode booking, tamu, telepon" class="ops-input" /></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status Payment</label><select name="payment_status" class="ops-input"><option value="">Semua</option><option value="dp" @selected(($filters['payment_status'] ?? '') === 'dp')>DP</option><option value="cicil" @selected(($filters['payment_status'] ?? '') === 'cicil')>Cicil</option><option value="lunas" @selected(($filters['payment_status'] ?? '') === 'lunas')>Lunas</option></select></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status Booking</label><select name="booking_status" class="ops-input"><option value="">Semua</option><option value="confirmed" @selected(($filters['booking_status'] ?? '') === 'confirmed')>Confirmed</option><option value="cancelled" @selected(($filters['booking_status'] ?? '') === 'cancelled')>Cancelled</option></select></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Check-in Dari</label><input x-model="dateFrom" onclick="this.showPicker()" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="ops-input" /></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Check-in Sampai</label><input :min="dateFrom" onclick="this.showPicker()" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="ops-input" /></div>
                <div class="flex items-end gap-3 xl:col-span-5"><button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Terapkan Filter</button><a href="{{ route('bookings.list', $villa) }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Reset</a></div>
            </form>
        </x-common.component-card>

        <x-common.component-card title="Booking Terbaru" desc="Daftar booking yang menjadi sumber utama pantauan operasional dan keuangan internal.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="ops-compact-table">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th>Kode</th>
                            <th>Tamu</th>
                            <th>Unit</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                            <tr>
                                <td class="font-medium text-gray-800 dark:text-white/90">{{ $booking->booking_code }}</td>
                                <td>{{ $booking->guest_name }}<div class="text-xs text-gray-500 dark:text-gray-400">{{ $booking->guest_phone }}</div></td>
                                <td class="font-medium text-gray-600 dark:text-gray-300">{{ $booking->villaUnit?->unit_name }}</td>
                                <td>{{ $booking->check_in->format('d M Y') }} - {{ $booking->check_out->format('d M Y') }}</td>
                                <td>Rp {{ number_format($booking->grand_total, 0, ',', '.') }}<div class="text-xs text-gray-500 dark:text-gray-400">Sisa: Rp {{ number_format($booking->remaining_balance, 0, ',', '.') }}</div></td>
                                <td><div class="flex flex-col gap-1.5">@php $paymentBadge = match($booking->payment_status) { 'lunas' => 'success', 'cicil' => 'warning', default => 'info', }; @endphp <x-ui.badge :color="$paymentBadge">{{ strtoupper($booking->payment_status) }}</x-ui.badge><x-ui.badge :color="$booking->booking_status === 'confirmed' ? 'success' : 'error'">{{ ucfirst($booking->booking_status) }}</x-ui.badge></div></td>
                                <td class="text-right"><a href="{{ route('bookings.show', $booking) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada booking yang dibuat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $bookings->links() }}</div>
        </x-common.component-card>
    </div>
@endsection
