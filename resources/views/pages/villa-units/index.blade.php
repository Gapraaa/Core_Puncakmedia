@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Villa Units" />

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-success-200 bg-success-50 px-5 py-4 text-sm text-success-700 dark:border-success-800 dark:bg-success-500/10 dark:text-success-300">{{ session('success') }}</div>
        @endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Data Master Villa Units</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola unit villa dan harga dasar harian yang akan dipakai oleh booking.</p>
            </div>
            <a href="{{ route('villa-units.create') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Tambah Villa Unit</a>
        </div>

        <x-common.component-card title="Filter Villa Unit" desc="Cari unit berdasarkan nama unit, tipe, villa, dan status.">
            <form method="GET" action="{{ route('villa-units.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="xl:col-span-2"><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label><input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama unit, tipe, villa" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Villa</label><select name="villa_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90"><option value="">Semua villa</option>@foreach ($villas as $villa)<option value="{{ $villa->id }}" @selected((string) ($filters['villa_id'] ?? '') === (string) $villa->id)>{{ $villa->name }}</option>@endforeach</select></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label><select name="status" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90"><option value="">Semua</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option><option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option></select></div>
                <div class="flex gap-3 xl:col-span-4"><button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Terapkan Filter</button><a href="{{ route('villa-units.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Reset</a></div>
            </form>
        </x-common.component-card>

        <x-common.component-card title="Daftar Villa Units" desc="Konfigurasi unit villa dan harga dasar berbasis TailAdmin.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Unit</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Villa</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Kapasitas</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Harga</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th></tr></thead>
                    <tbody>
                        @forelse ($villaUnits as $villaUnit)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800"><td class="px-5 py-4"><div><p class="font-medium text-gray-800 dark:text-white/90">{{ $villaUnit->unit_name }}</p><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $villaUnit->unit_type ?: 'Tipe belum diisi' }}</p></div></td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $villaUnit->villa?->name }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $villaUnit->capacity }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">W: {{ number_format($villaUnit->price_weekday, 0, ',', '.') }} | SW: {{ number_format($villaUnit->price_semi_weekend, 0, ',', '.') }} | WE: {{ number_format($villaUnit->price_weekend, 0, ',', '.') }}</td><td class="px-5 py-4"><div class="flex items-center justify-end gap-2"><a href="{{ route('villa-units.edit', $villaUnit) }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Edit</a><form method="POST" action="{{ route('villa-units.destroy', $villaUnit) }}" onsubmit="return confirm('Hapus villa unit ini?');">@csrf @method('DELETE')<button type="submit" class="rounded-lg border border-error-200 px-4 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-800 dark:text-error-400 dark:hover:bg-error-500/10">Hapus</button></form></div></td></tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada villa unit yang dibuat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $villaUnits->links() }}</div>
        </x-common.component-card>
    </div>
@endsection


