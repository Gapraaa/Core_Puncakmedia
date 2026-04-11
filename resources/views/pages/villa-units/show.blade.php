@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detail Villa Unit" />

    <div class="space-y-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $villaUnit->unit_name }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $villaUnit->villa?->name }} • {{ $villaUnit->unit_type ?: 'Tipe belum diisi' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('villa-units.edit', $villaUnit) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Edit Villa Unit</a>
                <a href="{{ route('villa-units.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Kembali</a>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 xl:col-span-4">
                <x-common.component-card title="Informasi Unit" desc="Konfigurasi dasar unit untuk pricing dan booking.">
                    <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Villa:</span> {{ $villaUnit->villa?->name }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Tipe:</span> {{ $villaUnit->unit_type ?: '-' }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Kapasitas:</span> {{ $villaUnit->capacity }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Status:</span> {{ $villaUnit->status }}</div>
                    </div>
                </x-common.component-card>
            </div>
            <div class="col-span-12 xl:col-span-8">
                <x-common.component-card title="Harga Dasar" desc="Harga harian default sebelum seasonal override.">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Weekday</p><p class="mt-2 text-xl font-semibold text-gray-800 dark:text-white/90">Rp {{ number_format($villaUnit->price_weekday, 0, ',', '.') }}</p></div>
                        <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Semi Weekend</p><p class="mt-2 text-xl font-semibold text-gray-800 dark:text-white/90">Rp {{ number_format($villaUnit->price_semi_weekend, 0, ',', '.') }}</p></div>
                        <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Weekend</p><p class="mt-2 text-xl font-semibold text-gray-800 dark:text-white/90">Rp {{ number_format($villaUnit->price_weekend, 0, ',', '.') }}</p></div>
                    </div>
                </x-common.component-card>
            </div>
        </div>

        <x-common.component-card title="Harga Musiman" desc="Override harga yang aktif untuk unit ini.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Periode</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Harga</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Catatan</th></tr></thead>
                    <tbody>
                        @forelse ($villaUnit->seasonalPrices as $seasonalPrice)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800"><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $seasonalPrice->start_date->format('d M Y') }} - {{ $seasonalPrice->end_date->format('d M Y') }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">Rp {{ number_format($seasonalPrice->price, 0, ',', '.') }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $seasonalPrice->note ?: '-' }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada harga musiman.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-common.component-card>
    </div>
@endsection
