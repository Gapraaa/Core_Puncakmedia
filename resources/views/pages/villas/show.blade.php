@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detail Villa" />

    <div class="space-y-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $villa->name }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $villa->location ?: 'Lokasi belum diisi' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('villas.edit', $villa) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Edit Villa</a>
                <a href="{{ route('villas.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Kembali</a>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 xl:col-span-5">
                <x-common.component-card title="Informasi Villa" desc="Detail operasional utama untuk villa ini.">
                    <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Slug:</span> {{ $villa->slug }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Status:</span> {{ $villa->status }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Resort:</span> {{ $villa->is_resort ? 'Ya' : 'Tidak' }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Brand:</span> {{ $villa->brands->pluck('name')->join(', ') ?: 'Belum terhubung' }}</div>
                    </div>
                </x-common.component-card>
            </div>
            <div class="col-span-12 xl:col-span-7">
                <x-common.component-card title="Catatan Villa" desc="Deskripsi, kelebihan, aturan, dan tautan pendukung.">
                    <div class="space-y-4 text-sm text-gray-600 dark:text-gray-300">
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Deskripsi:</span> {{ $villa->description ?: '-' }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Rules:</span> {{ $villa->rules ?: '-' }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Pros:</span> {{ $villa->pros ?: '-' }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Cons:</span> {{ $villa->cons ?: '-' }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Youtube URL:</span> {{ $villa->youtube_url ?: '-' }}</div>
                    </div>
                </x-common.component-card>
            </div>
        </div>

        <x-common.component-card title="Villa Unit" desc="Unit yang terhubung ke villa ini.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Unit</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipe</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Harga Dasar</th></tr></thead>
                    <tbody>
                        @forelse ($villa->units as $unit)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800"><td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ $unit->unit_name }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $unit->unit_type ?: '-' }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">W: {{ number_format($unit->price_weekday, 0, ',', '.') }} | SW: {{ number_format($unit->price_semi_weekend, 0, ',', '.') }} | WE: {{ number_format($unit->price_weekend, 0, ',', '.') }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada unit untuk villa ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-common.component-card>
    </div>
@endsection
