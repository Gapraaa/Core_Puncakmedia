@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detail Harga Musiman" />

    <div class="space-y-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $seasonalPrice->villaUnit?->villa?->name }} - {{ $seasonalPrice->villaUnit?->unit_name }}</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $seasonalPrice->start_date->format('j M Y') }} - {{ $seasonalPrice->end_date->format('j M Y') }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('seasonal-prices.edit', $seasonalPrice) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Edit Harga Musiman</a>
                <a href="{{ route('seasonal-prices.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Kembali</a>
            </div>
        </div>

        <x-common.component-card title="Detail Override Harga" desc="Ringkasan harga khusus per periode.">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Harga Override</p><p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">Rp {{ number_format($seasonalPrice->price, 0, ',', '.') }}</p></div>
                <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Catatan</p><p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ $seasonalPrice->note ?: '-' }}</p></div>
            </div>
        </x-common.component-card>
    </div>
@endsection
