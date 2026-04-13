@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Voucher" />

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-success-200 bg-success-50 px-5 py-4 text-sm text-success-700 dark:border-success-800 dark:bg-success-500/10 dark:text-success-300">{{ session('success') }}</div>
        @endif
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Data Voucher</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola kode voucher dan aturan diskon untuk booking.</p>
            </div>
            <a href="{{ route('vouchers.create') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Tambah Voucher</a>
        </div>

        <x-common.component-card title="Filter Voucher" desc="Cari voucher berdasarkan kode, tipe diskon, dan status aktif.">
            <form method="GET" action="{{ route('vouchers.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="xl:col-span-2"><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label><input onkeyup="let v=this.value.toLowerCase(); document.querySelectorAll('table tbody tr').forEach(tr => { if(tr.children.length > 1) tr.style.display = tr.innerText.toLowerCase().includes(v) ? '' : 'none' })" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Kode voucher" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe Diskon</label><select name="discount_type" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90"><option value="">Semua</option><option value="fixed" @selected(($filters['discount_type'] ?? '') === 'fixed')>Nominal tetap</option><option value="percentage" @selected(($filters['discount_type'] ?? '') === 'percentage')>Persentase</option></select></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label><select name="is_active" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90"><option value="">Semua</option><option value="1" @selected(($filters['is_active'] ?? '') === '1')>Aktif</option><option value="0" @selected(($filters['is_active'] ?? '') === '0')>Nonaktif</option></select></div>
                <div class="flex gap-3 xl:col-span-4"><button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Terapkan Filter</button><a href="{{ route('vouchers.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Reset</a></div>
            </form>
        </x-common.component-card>

        <x-common.component-card title="Daftar Voucher" desc="Konfigurasi voucher dan diskon berbasis TailAdmin.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Kode</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipe Diskon</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nilai</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Minimum Transaksi</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th></tr></thead>
                    <tbody>
                        @forelse ($vouchers as $voucher)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800"><td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ $voucher->code }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $voucher->discount_type === 'percentage' ? 'Persentase' : 'Nominal tetap' }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $voucher->discount_type === 'percentage' ? $voucher->amount.'%' : 'Rp '.number_format($voucher->amount, 0, ',', '.') }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">Rp {{ number_format($voucher->minimum_transaction, 0, ',', '.') }}</td><td class="px-5 py-4"><div class="flex items-center justify-end gap-2"><a href="{{ route('vouchers.edit', $voucher) }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Edit</a><form method="POST" action="{{ route('vouchers.destroy', $voucher) }}" onsubmit="return confirm('Hapus voucher ini?');">@csrf @method('DELETE')<button type="submit" class="rounded-lg border border-error-200 px-4 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-800 dark:text-error-400 dark:hover:bg-error-500/10">Hapus</button></form></div></td></tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada voucher yang dibuat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $vouchers->links() }}</div>
        </x-common.component-card>
    </div>
@endsection


