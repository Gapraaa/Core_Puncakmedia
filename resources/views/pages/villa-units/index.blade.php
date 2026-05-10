@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Unit Resort" />

    <div class="space-y-6" data-async-page="true">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Katalog Villa Resort</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih villa resort terlebih dahulu, lalu buka daftar unit yang dimiliki villa tersebut.</p>
            </div>
        </div>

        <x-common.component-card title="Filter Villa Resort" desc="Cari villa resort berdasarkan nama, slug, lokasi, dan status operasional.">
            <form data-async-page-form="true" method="GET" action="{{ route('villa-units.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div class="xl:col-span-2"><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label><input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama villa, slug, lokasi" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label><select name="status" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90"><option value="">Semua</option><option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Draft</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option><option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option></select></div>
                <div class="flex gap-3 xl:col-span-3"><button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Terapkan Filter</button><a href="{{ route('villa-units.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Reset</a></div>
            </form>
        </x-common.component-card>

        <x-common.component-card title="Daftar Villa Resort" desc="Setiap villa menampilkan ringkasan jumlah unit dan jalan pintas untuk membuka daftar unitnya.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Villa</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Lokasi</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jumlah Unit</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Booking</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th></tr></thead>
                    <tbody>
                        @forelse ($villas as $villa)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                                <td class="px-5 py-4">
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white/90">{{ $villa->name }}</p>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $villa->slug }}</p>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $villa->location ?: 'Belum diisi' }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    <div>{{ $villa->units_count }} unit</div>
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $villa->active_units_count }} aktif</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $villa->bookings_count }} booking</td>
                                <td class="px-5 py-4">@php $badgeColor = match($villa->status) { 'active' => 'success', 'inactive' => 'error', default => 'warning', }; $statusLabel = match($villa->status) { 'active' => 'Aktif', 'inactive' => 'Nonaktif', default => 'Draft', }; @endphp <x-ui.badge :color="$badgeColor">{{ $statusLabel }}</x-ui.badge></td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('villa-units.list', $villa) }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">Lihat Unit</a>
                                        <a href="{{ route('villa-units.create-for-villa', $villa) }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Tambah Unit</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10">
                                    <x-common.empty-state
                                        compact
                                        title="Belum Ada Villa Resort"
                                        description="Unit resort baru bisa dibuat setelah ada data villa dengan tipe resort."
                                        actionLabel="Tambah Villa"
                                        :actionHref="route('villas.create')"
                                        secondaryActionLabel="Lihat Data Villa"
                                        :secondaryActionHref="route('villas.index')"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $villas->links() }}</div>
        </x-common.component-card>
    </div>
@endsection


