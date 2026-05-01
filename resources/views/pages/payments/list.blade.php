@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Daftar Pembayaran" />

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $villa->is_resort ? $villa->name . ' - ' . $villaUnit->unit_name : $villa->name }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pembayaran yang sudah masuk untuk booking di unit ini. Finance utama membaca data ini dari booking, sedangkan invoice hanya referensi dokumen tamu.</p>
            </div>
            <a href="{{ $villa->is_resort ? route('payments.units', $villa) : route('payments.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Kembali</a>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="ops-kpi-card">
                <div class="ops-kpi-label">Total Transaksi</div>
                <div class="ops-kpi-value">{{ number_format($payments->total(), 0, ',', '.') }}</div>
                <div class="ops-kpi-note">Jumlah pembayaran hasil filter saat ini.</div>
            </div>
            <div class="ops-kpi-card">
                <div class="ops-kpi-label">Nominal Masuk</div>
                <div class="ops-kpi-value">Rp {{ number_format($payments->getCollection()->sum('amount'), 0, ',', '.') }}</div>
                <div class="ops-kpi-note">Akumulasi pembayaran yang terlihat di tabel.</div>
            </div>
            <div class="ops-kpi-card">
                <div class="ops-kpi-label">Metode Dominan</div>
                <div class="ops-kpi-value text-xl">{{ strtoupper($payments->getCollection()->groupBy('payment_method')->sortByDesc(fn ($rows) => $rows->count())->keys()->first() ?? '-') }}</div>
                <div class="ops-kpi-note">Membantu finance membaca pola transaksi.</div>
            </div>
        </div>

        <x-common.component-card title="Filter Pembayaran" desc="Cari pembayaran berdasarkan booking, tamu, metode, dan penerima.">
            <form method="GET" action="{{ route('payments.unit', [$villa, $villaUnit]) }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="xl:col-span-2"><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label><input onkeyup="let v=this.value.toLowerCase(); document.querySelectorAll('table tbody tr').forEach(tr => { if(tr.children.length > 1) tr.style.display = tr.innerText.toLowerCase().includes(v) ? '' : 'none' })" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Kode booking, tamu, catatan" class="ops-input" /></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Metode</label><select name="payment_method" class="ops-input"><option value="">Semua</option><option value="cash" @selected(($filters['payment_method'] ?? '') === 'cash')>Cash</option><option value="transfer" @selected(($filters['payment_method'] ?? '') === 'transfer')>Transfer</option></select></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Diterima Oleh</label><select name="received_by" class="ops-input"><option value="">Semua</option><option value="finance" @selected(($filters['received_by'] ?? '') === 'finance')>Finance</option><option value="office" @selected(($filters['received_by'] ?? '') === 'office')>Office</option><option value="field_staff" @selected(($filters['received_by'] ?? '') === 'field_staff')>Field Staff</option></select></div>
                <div class="flex items-end gap-3 xl:col-span-5"><button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Terapkan Filter</button><a href="{{ route('payments.unit', [$villa, $villaUnit]) }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Reset</a></div>
            </form>
        </x-common.component-card>

        <x-common.component-card title="Riwayat Pembayaran Booking" desc="Pembayaran dicatat terhadap booking. Dokumen tamu hanya menjadi referensi bukti yang dikirim keluar.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="ops-compact-table">
                    <thead>
                        <tr><th>Booking</th><th>Tamu</th><th>Tanggal</th><th>Metode</th><th>Penerima</th><th>Jumlah</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td class="font-medium text-gray-800 dark:text-white/90">{{ $payment->booking?->booking_code }}</td>
                                <td>{{ $payment->booking?->guest_name ?? '-' }}</td>
                                <td>{{ $payment->paid_at?->format('d M Y H:i') }}</td>
                                <td>{{ ucfirst($payment->payment_method) }}</td>
                                <td>{{ str_replace('_', ' ', $payment->received_by) }}</td>
                                <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}<div class="mt-2"><a href="{{ route('documents.payments.receipt', ['payment' => $payment, 'download' => 1]) }}" class="text-xs font-medium text-brand-600 dark:text-brand-300">Unduh Bukti</a></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada pembayaran yang dicatat untuk unit ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $payments->links() }}</div>
        </x-common.component-card>
    </div>
@endsection
