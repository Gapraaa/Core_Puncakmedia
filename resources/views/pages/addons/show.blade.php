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
                <a href="{{ route('addon-options.create', $addon) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Tambah Opsi</a>
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

        <x-common.component-card title="Opsi Add-on" desc="Gunakan opsi untuk membuat variasi harga seperti Grill A/B/C, Extra Bed, Tambahan Orang, atau Catering.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama Opsi</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Basis Charge</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Satuan</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Harga</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th></tr></thead>
                    <tbody>
                        @forelse ($addon->options as $option)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800"><td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ $option->name }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $option->charge_basis }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $option->unit_label }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">Rp {{ number_format($option->price, 0, ',', '.') }}</td><td class="px-5 py-4">@if($option->is_active)<x-ui.badge color="success">Aktif</x-ui.badge>@else<x-ui.badge color="light">Nonaktif</x-ui.badge>@endif</td><td class="px-5 py-4"><div class="flex items-center justify-end gap-2"><a href="{{ route('addon-options.edit', [$addon, $option]) }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Edit</a><form method="POST" action="{{ route('addon-options.destroy', [$addon, $option]) }}" onsubmit="return confirm('Hapus opsi add-on ini?');">@csrf @method('DELETE')<button type="submit" class="rounded-lg border border-error-200 px-4 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-800 dark:text-error-400 dark:hover:bg-error-500/10">Hapus</button></form></div></td></tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada opsi add-on untuk kategori ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-common.component-card>
    </div>
@endsection
