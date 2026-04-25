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

        <x-common.component-card title="Filter Pembayaran" desc="Cari pembayaran berdasarkan booking, tamu, metode, dan penerima.">
            <form method="GET" action="{{ route('payments.unit', [$villa, $villaUnit]) }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="xl:col-span-2"><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label><input onkeyup="let v=this.value.toLowerCase(); document.querySelectorAll('table tbody tr').forEach(tr => { if(tr.children.length > 1) tr.style.display = tr.innerText.toLowerCase().includes(v) ? '' : 'none' })" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Kode booking, tamu, catatan" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Metode</label><select name="payment_method" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90"><option value="">Semua</option><option value="cash" @selected(($filters['payment_method'] ?? '') === 'cash')>Cash</option><option value="transfer" @selected(($filters['payment_method'] ?? '') === 'transfer')>Transfer</option></select></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Diterima Oleh</label><select name="received_by" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90"><option value="">Semua</option><option value="finance" @selected(($filters['received_by'] ?? '') === 'finance')>Finance</option><option value="office" @selected(($filters['received_by'] ?? '') === 'office')>Office</option><option value="field_staff" @selected(($filters['received_by'] ?? '') === 'field_staff')>Field Staff</option></select></div>
                <div class="flex items-end gap-3 xl:col-span-5"><button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Terapkan Filter</button><a href="{{ route('payments.unit', [$villa, $villaUnit]) }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Reset</a></div>
            </form>
        </x-common.component-card>

        <x-common.component-card title="Riwayat Pembayaran Booking" desc="Pembayaran dicatat terhadap booking. Dokumen tamu hanya menjadi referensi bukti yang dikirim keluar.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Booking</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tamu</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Metode</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Penerima</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                                <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->booking?->booking_code }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $payment->booking?->guest_name ?? '-' }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $payment->paid_at?->format('d M Y H:i') }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ ucfirst($payment->payment_method) }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ str_replace('_', ' ', $payment->received_by) }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">Rp {{ number_format($payment->amount, 0, ',', '.') }}<div class="mt-2"><a href="{{ route('documents.payments.receipt', ['payment' => $payment, 'download' => 1]) }}" class="text-xs font-medium text-brand-600 dark:text-brand-300">Unduh Bukti</a></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada pembayaran yang dicatat untuk unit ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $payments->links() }}</div>
        </x-common.component-card>
    </div>
@endsection
