@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Daftar Pembayaran" />

    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Katalog Villa Pembayaran</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih villa terlebih dahulu. Villa biasa langsung ke daftar pembayaran, sedangkan resort masuk ke daftar unit dulu.</p>
        </div>

        <x-common.component-card title="Filter Villa" desc="Cari villa atau resort sebelum masuk ke daftar pembayaran yang tercatat.">
            <form method="GET" action="{{ route('payments.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="xl:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama villa, slug, atau lokasi" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe Properti</label>
                    <select name="type" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                        <option value="">Semua</option>
                        <option value="villa" @selected(($filters['type'] ?? '') === 'villa')>Villa</option>
                        <option value="resort" @selected(($filters['type'] ?? '') === 'resort')>Resort</option>
                    </select>
                </div>
                <div class="flex items-end gap-3">
                    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Terapkan</button>
                    <a href="{{ route('payments.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Reset</a>
                </div>
            </form>
        </x-common.component-card>

        <x-common.component-card title="Daftar Villa" desc="Masuk ke alur pembayaran berdasarkan struktur villa dan unit operasional.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Villa / Resort</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Lokasi</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipe</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Unit</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pembayaran</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($villas as $villa)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                                <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ $villa->name }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $villa->location ?: '-' }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $villa->is_resort ? 'Resort' : 'Villa' }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $villa->units_count }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $villa->payments_count }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ $villa->is_resort ? route('payments.units', $villa) : route('payments.villa', $villa) }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">{{ $villa->is_resort ? 'Lihat Unit' : 'Lihat Pembayaran' }}</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada villa atau resort yang cocok dengan filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $villas->links() }}</div>
        </x-common.component-card>
    </div>
@endsection
