@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Villa" />

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Data Master Villa</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola identitas villa, status operasional, dan konten deskriptif.</p>
            </div>

            <a href="{{ route('villas.create') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Tambah Villa</a>
        </div>

        <x-common.component-card title="Filter Villa" desc="Cari villa berdasarkan nama, slug, lokasi, dan status.">
            <form method="GET" action="{{ route('villas.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div class="xl:col-span-2"><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label><input onkeyup="let v=this.value.toLowerCase(); document.querySelectorAll('table tbody tr').forEach(tr => { if(tr.children.length > 1) tr.style.display = tr.innerText.toLowerCase().includes(v) ? '' : 'none' })" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama, slug, lokasi" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label><select name="status" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90"><option value="">Semua</option><option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Draft</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option><option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option></select></div>
                <div class="flex gap-3 xl:col-span-3"><button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Terapkan Filter</button><a href="{{ route('villas.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Reset</a></div>
            </form>
        </x-common.component-card>

        <x-common.component-card title="Daftar Villa" desc="Tabel data master villa operasional dengan gaya TailAdmin.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Villa</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Brand</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Lokasi</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Units</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th></tr></thead>
                    <tbody>
                        @forelse ($villas as $villa)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800"><td class="px-5 py-4"><div><p class="font-medium text-gray-800 dark:text-white/90">{{ $villa->name }}</p><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $villa->slug }}</p></div></td><td class="px-5 py-4"><div class="flex flex-wrap gap-1">@forelse($villa->brands as $brand) <span class="rounded bg-brand-50 px-2 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">{{ $brand->name }}</span> @empty <span class="text-xs text-gray-400 italic">Tanpa brand</span> @endforelse</div></td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $villa->location ?: 'Belum diisi' }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $villa->units_count }} {{ $villa->is_resort ? '(Resort)' : '' }}</td><td class="px-5 py-4">@php $badgeColor = match($villa->status) { 'active' => 'success', 'inactive' => 'error', default => 'warning', }; $statusLabel = match($villa->status) { 'active' => 'Aktif', 'inactive' => 'Nonaktif', default => 'Draft', }; @endphp <x-ui.badge :color="$badgeColor">{{ $statusLabel }}</x-ui.badge></td><td class="px-5 py-4"><div class="flex items-center justify-end gap-2"><a href="{{ route('villas.edit', $villa) }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Edit</a><form method="POST" action="{{ route('villas.destroy', $villa) }}" data-confirm="Hapus villa ini?" data-confirm-title="Hapus Villa" data-confirm-label="Ya, hapus villa" data-toast-loading="Villa sedang dihapus dari sistem." data-toast-loading-title="Menghapus Villa">@csrf @method('DELETE')<button type="submit" class="rounded-lg border border-error-200 px-4 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-800 dark:text-error-400 dark:hover:bg-error-500/10">Hapus</button></form></div></td></tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada villa yang dibuat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $villas->links() }}</div>
        </x-common.component-card>
    </div>
@endsection
