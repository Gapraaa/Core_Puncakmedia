@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Daftar Unit Resort" />

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $villa->name }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Villa ini memiliki {{ $villa->units_count }} unit resort dan {{ $villa->bookings_count }} booking tercatat.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('villa-units.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Kembali ke Daftar Villa</a>
                <a href="{{ route('villa-units.create-for-villa', $villa) }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Tambah Unit Resort</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <x-common.component-card title="Lokasi" desc="Area operasional villa resort ini.">
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $villa->location ?: 'Belum diisi' }}</p>
            </x-common.component-card>
            <x-common.component-card title="Total Unit" desc="Jumlah unit yang dimiliki villa ini.">
                <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $villa->units_count }}</p>
            </x-common.component-card>
            <x-common.component-card title="Total Booking" desc="Booking yang sudah terhubung ke villa ini.">
                <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $villa->bookings_count }}</p>
            </x-common.component-card>
        </div>

        <x-common.component-card title="Filter Unit" desc="Cari unit berdasarkan nama, tipe, dan status di dalam villa ini.">
            <form method="GET" action="{{ route('villa-units.list', $villa) }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div class="xl:col-span-2"><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label><input onkeyup="let v=this.value.toLowerCase(); document.querySelectorAll('table tbody tr').forEach(tr => { if(tr.children.length > 1) tr.style.display = tr.innerText.toLowerCase().includes(v) ? '' : 'none' })" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama unit atau tipe unit" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label><select name="status" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90"><option value="">Semua</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option><option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option></select></div>
                <div class="flex gap-3 xl:col-span-3"><button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Terapkan Filter</button><a href="{{ route('villa-units.list', $villa) }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Reset</a></div>
            </form>
        </x-common.component-card>

        <x-common.component-card title="Daftar Unit Resort" desc="Kelola unit yang terikat langsung ke villa resort ini.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Unit</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Kapasitas</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Harga Dasar</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Relasi</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th></tr></thead>
                    <tbody>
                        @forelse ($villaUnits as $villaUnit)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                                <td class="px-5 py-4"><div><p class="font-medium text-gray-800 dark:text-white/90">{{ $villaUnit->unit_name }}</p><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $villaUnit->unit_type ?: 'Tipe belum diisi' }}</p></div></td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $villaUnit->capacity }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">W: {{ number_format($villaUnit->price_weekday, 0, ',', '.') }} | SW: {{ number_format($villaUnit->price_semi_weekend, 0, ',', '.') }} | WE: {{ number_format($villaUnit->price_weekend, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $villaUnit->seasonal_prices_count }} harga musiman • {{ $villaUnit->bookings_count }} booking</td>
                                <td class="px-5 py-4">@php $badgeColor = $villaUnit->status === 'active' ? 'success' : 'error'; $statusLabel = $villaUnit->status === 'active' ? 'Aktif' : 'Nonaktif'; @endphp <x-ui.badge :color="$badgeColor">{{ $statusLabel }}</x-ui.badge></td>
                                <td class="px-5 py-4"><div class="flex items-center justify-end gap-2"><a href="{{ route('villa-units.edit', $villaUnit) }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Edit</a><form method="POST" action="{{ route('villa-units.destroy', $villaUnit) }}" data-confirm="Hapus unit resort ini?" data-confirm-title="Hapus Unit Resort" data-confirm-label="Ya, hapus unit" data-toast-loading="Unit resort sedang dihapus dari villa." data-toast-loading-title="Menghapus Unit Resort">@csrf @method('DELETE')<button type="submit" class="rounded-lg border border-error-200 px-4 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-800 dark:text-error-400 dark:hover:bg-error-500/10">Hapus</button></form></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Villa ini belum memiliki unit resort.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $villaUnits->links() }}</div>
        </x-common.component-card>
    </div>
@endsection
