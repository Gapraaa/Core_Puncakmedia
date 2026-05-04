@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Daftar Harga High Season" />

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $villa->name }} - {{ $villaUnit->unit_name }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola harga high season untuk unit ini. Total data aktif tercatat: {{ $villaUnit->seasonal_prices_count }}.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ $villa->is_resort ? route('seasonal-prices.units', $villa) : route('seasonal-prices.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">{{ $villa->is_resort ? 'Kembali ke Daftar Unit' : 'Kembali ke Daftar Villa' }}</a>
                <a href="{{ $villa->is_resort ? route('seasonal-prices.create-for-unit', [$villa, $villaUnit]) : route('seasonal-prices.create-for-villa', $villa) }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Tambah Harga High Season</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <x-common.component-card title="Villa" desc="Properti induk dari unit ini.">
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $villa->name }}</p>
            </x-common.component-card>
            <x-common.component-card title="Unit" desc="Unit operasional yang sedang dikelola.">
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $villaUnit->unit_name }}</p>
            </x-common.component-card>
            <x-common.component-card title="Kapasitas" desc="Kapasitas dasar unit.">
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $villaUnit->capacity }}</p>
            </x-common.component-card>
        </div>

        <x-common.component-card title="Filter Harga High Season" desc="Cari data berdasarkan catatan override harga untuk unit ini.">
            <form method="GET" action="{{ $villa->is_resort ? route('seasonal-prices.unit', [$villa, $villaUnit]) : route('seasonal-prices.villa', $villa) }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label><input onkeyup="let v=this.value.toLowerCase(); document.querySelectorAll('table tbody tr').forEach(tr => { if(tr.children.length > 1) tr.style.display = tr.innerText.toLowerCase().includes(v) ? '' : 'none' })" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Catatan high season" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                <div class="flex items-end gap-3"><button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Terapkan Filter</button><a href="{{ $villa->is_resort ? route('seasonal-prices.unit', [$villa, $villaUnit]) : route('seasonal-prices.villa', $villa) }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Reset</a></div>
            </form>
        </x-common.component-card>

        <x-common.component-card title="Daftar Harga High Season" desc="Override harga per tanggal untuk unit ini.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Periode</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Harga Override</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Catatan</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th></tr></thead>
                    <tbody>
                        @forelse ($seasonalPrices as $seasonalPrice)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800"><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $seasonalPrice->start_date->format('d M Y') }} - {{ $seasonalPrice->end_date->format('d M Y') }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">Rp {{ number_format($seasonalPrice->price, 0, ',', '.') }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $seasonalPrice->note ?: '-' }}</td><td class="px-5 py-4"><div class="flex items-center justify-end gap-2"><a href="{{ route('seasonal-prices.edit', $seasonalPrice) }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Edit</a><form method="POST" action="{{ route('seasonal-prices.destroy', $seasonalPrice) }}" data-confirm="Hapus harga high season ini?" data-confirm-title="Hapus Harga Seasonal" data-confirm-label="Ya, hapus harga" data-toast-loading="Harga seasonal sedang dihapus." data-toast-loading-title="Menghapus Harga Seasonal">@csrf @method('DELETE')<button type="submit" class="rounded-lg border border-error-200 px-4 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-800 dark:text-error-400 dark:hover:bg-error-500/10">Hapus</button></form></div></td></tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada harga high season untuk unit ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $seasonalPrices->links() }}</div>
        </x-common.component-card>
    </div>
@endsection
