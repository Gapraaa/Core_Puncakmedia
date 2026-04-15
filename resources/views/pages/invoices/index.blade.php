@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Invoice" />

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-success-200 bg-success-50 px-5 py-4 text-sm text-success-700 dark:border-success-800 dark:bg-success-500/10 dark:text-success-300">{{ session('success') }}</div>
        @endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Katalog Invoice per Villa</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih villa terlebih dahulu. Villa biasa langsung ke daftar invoice, sedangkan resort masuk ke daftar unit dulu.</p>
            </div>
        </div>

        <x-common.component-card title="Filter Villa" desc="Cari villa berdasarkan nama, lokasi, dan tipe properti.">
            <form method="GET" action="{{ route('invoices.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div class="xl:col-span-2"><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label><input onkeyup="let v=this.value.toLowerCase(); document.querySelectorAll('table tbody tr').forEach(tr => { if(tr.children.length > 1) tr.style.display = tr.innerText.toLowerCase().includes(v) ? '' : 'none' })" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama villa, slug, lokasi" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe</label><select name="type" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90"><option value="">Semua</option><option value="villa" @selected(($filters['type'] ?? '') === 'villa')>Villa Biasa</option><option value="resort" @selected(($filters['type'] ?? '') === 'resort')>Resort</option></select></div>
                <div class="flex gap-3 xl:col-span-3"><button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Terapkan Filter</button><a href="{{ route('invoices.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Reset</a></div>
            </form>
        </x-common.component-card>

        <x-common.component-card title="Daftar Villa" desc="Masuk ke alur pencarian invoice berdasarkan villa dan unit.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Villa</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipe</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Lokasi</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Unit</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Booking</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Invoice</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th></tr></thead>
                    <tbody>
                        @forelse ($villas as $villa)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                                <td class="px-5 py-4"><div><p class="font-medium text-gray-800 dark:text-white/90">{{ $villa->name }}</p><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $villa->slug }}</p></div></td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $villa->is_resort ? 'Resort' : 'Villa Biasa' }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $villa->location ?: 'Belum diisi' }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $villa->units_count }} unit</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $villa->bookings_count }} booking</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $villa->invoices_count }} invoice</td>
                                <td class="px-5 py-4"><div class="flex items-center justify-end gap-2"><a href="{{ $villa->is_resort ? route('invoices.units', $villa) : route('invoices.villa', $villa) }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">{{ $villa->is_resort ? 'Lihat Unit' : 'Lihat Invoice' }}</a></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada villa yang tersedia untuk pencarian invoice.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $villas->links() }}</div>
        </x-common.component-card>
    </div>
@endsection
