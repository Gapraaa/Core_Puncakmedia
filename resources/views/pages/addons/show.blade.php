@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detail Add-on" />

    <div class="space-y-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $addon->name }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $addon->charge_type === 'per_night' ? 'Per malam' : 'Per stay' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('addons.edit', $addon) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Edit Add-on</a>
                <a href="{{ route('addons.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Kembali</a>
            </div>
        </div>

        <x-common.component-card title="Konfigurasi Add-on" desc="Detail biaya tambahan yang bisa dipakai saat booking.">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Harga</p><p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">Rp {{ number_format($addon->price, 0, ',', '.') }}</p></div>
                <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Tipe Charge</p><p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ $addon->charge_type }}</p></div>
                <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Status</p><p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ $addon->is_active ? 'Aktif' : 'Nonaktif' }}</p></div>
            </div>
        </x-common.component-card>
    </div>
@endsection
