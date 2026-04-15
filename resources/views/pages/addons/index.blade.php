@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Data Add-ons" />

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-success-200 bg-success-50 px-5 py-4 text-sm text-success-700 dark:border-success-800 dark:bg-success-500/10 dark:text-success-300">{{ session('success') }}</div>
        @endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Data Add-ons</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola add-on utama dan siapkan opsi harga seperti Grill A/B/C, extra bed, tambahan orang, atau catering.</p>
            </div>
            <a href="{{ route('addons.create') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Tambah Add-on</a>
        </div>

        <x-common.component-card title="Filter Add-on" desc="Cari add-on berdasarkan nama, tipe charge, dan status aktif.">
            <form method="GET" action="{{ route('addons.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="xl:col-span-2"><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label><input onkeyup="let v=this.value.toLowerCase(); document.querySelectorAll('table tbody tr').forEach(tr => { if(tr.children.length > 1) tr.style.display = tr.innerText.toLowerCase().includes(v) ? '' : 'none' })" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama add-on" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe Charge</label><select name="charge_type" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90"><option value="">Semua</option><option value="per_night" @selected(($filters['charge_type'] ?? '') === 'per_night')>Per malam</option><option value="per_stay" @selected(($filters['charge_type'] ?? '') === 'per_stay')>Per stay</option></select></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label><select name="is_active" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90"><option value="">Semua</option><option value="1" @selected(($filters['is_active'] ?? '') === '1')>Aktif</option><option value="0" @selected(($filters['is_active'] ?? '') === '0')>Nonaktif</option></select></div>
                <div class="flex gap-3 xl:col-span-4"><button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Terapkan Filter</button><a href="{{ route('addons.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Reset</a></div>
            </form>
        </x-common.component-card>

        <x-common.component-card title="Daftar Add-ons" desc="Konfigurasi add-ons berbasis TailAdmin untuk pricing tambahan.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipe Charge Dasar</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Harga Dasar</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Opsi</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th></tr></thead>
                    <tbody>
                        @forelse ($addons as $addon)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800"><td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ $addon->name }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $addon->charge_type === 'per_night' ? 'Per malam' : 'Per stay' }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">Rp {{ number_format($addon->price, 0, ',', '.') }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $addon->options_count }} opsi</td><td class="px-5 py-4">@if($addon->is_active)<x-ui.badge color="success">Aktif</x-ui.badge>@else<x-ui.badge color="light">Nonaktif</x-ui.badge>@endif</td><td class="px-5 py-4"><div class="flex items-center justify-end gap-2"><a href="{{ route('addons.show', $addon) }}" class="rounded-lg border border-brand-200 px-4 py-2 text-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-800 dark:text-brand-300 dark:hover:bg-brand-500/10">Kelola Opsi</a><a href="{{ route('addons.edit', $addon) }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Edit</a><form method="POST" action="{{ route('addons.destroy', $addon) }}" onsubmit="return confirm('Hapus add-on ini?');">@csrf @method('DELETE')<button type="submit" class="rounded-lg border border-error-200 px-4 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-800 dark:text-error-400 dark:hover:bg-error-500/10">Hapus</button></form></div></td></tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada add-on yang dibuat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $addons->links() }}</div>
        </x-common.component-card>
    </div>
@endsection


