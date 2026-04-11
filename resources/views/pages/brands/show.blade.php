@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detail Brand" />

    <div class="space-y-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $brand->name }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Slug: {{ $brand->slug }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('brands.edit', $brand) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Edit Brand</a>
                <a href="{{ route('brands.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Kembali</a>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 xl:col-span-4">
                <x-common.component-card title="Informasi Brand" desc="Data inti brand yang dipakai pada booking.">
                    <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Nama:</span> {{ $brand->name }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Slug:</span> {{ $brand->slug }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Logo:</span> {{ $brand->logo ?: 'Belum diisi' }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Info Bank:</span> {{ $brand->bank_info ?: 'Belum diisi' }}</div>
                    </div>
                </x-common.component-card>
            </div>
            <div class="col-span-12 xl:col-span-8">
                <x-common.component-card title="Ringkasan Penggunaan" desc="Keterkaitan brand dengan data operasional lain.">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Total Villa</p><p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($brand->villas->count(), 0, ',', '.') }}</p></div>
                        <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Booking Terbaru</p><p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($brand->bookings->count(), 0, ',', '.') }}</p></div>
                    </div>
                </x-common.component-card>
            </div>
        </div>

        <x-common.component-card title="Villa Terkait" desc="Villa terbaru yang menggunakan brand ini.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Villa</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Lokasi</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th></tr></thead>
                    <tbody>
                        @forelse ($brand->villas as $villa)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800"><td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ $villa->name }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $villa->location ?: '-' }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $villa->status }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada villa terkait.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-common.component-card>
    </div>
@endsection
