@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Brand" />

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-success-200 bg-success-50 px-5 py-4 text-sm text-success-700 dark:border-success-800 dark:bg-success-500/10 dark:text-success-300">{{ session('success') }}</div>
        @endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Data Master Brand</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola brand operasional yang dipakai di seluruh proses booking dan pelaporan.</p>
            </div>

            <a href="{{ route('brands.create') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Tambah Brand</a>
        </div>

        <x-common.component-card title="Filter Brand" desc="Cari brand berdasarkan nama atau slug.">
            <form method="GET" action="{{ route('brands.index') }}" class="flex flex-col gap-4 md:flex-row md:items-end">
                <div class="w-full md:max-w-md"><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label><input onkeyup="let v=this.value.toLowerCase(); document.querySelectorAll('table tbody tr').forEach(tr => { if(tr.children.length > 1) tr.style.display = tr.innerText.toLowerCase().includes(v) ? '' : 'none' })" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama atau slug" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                <div class="flex gap-3"><button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Cari</button><a href="{{ route('brands.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Reset</a></div>
            </form>
        </x-common.component-card>

        <x-common.component-card title="Daftar Brand" desc="Tabel konfigurasi brand berbasis TailAdmin.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Brand</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Slug</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Villa</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th></tr></thead>
                    <tbody>
                        @forelse ($brands as $brand)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800"><td class="px-5 py-4"><div><p class="font-medium text-gray-800 dark:text-white/90">{{ $brand->name }}</p><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $brand->logo ?: 'Path logo belum diisi' }}</p></div></td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $brand->slug }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $brand->villas_count ?? 0 }}</td><td class="px-5 py-4"><div class="flex items-center justify-end gap-2"><a href="{{ route('brands.edit', $brand) }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Edit</a><form method="POST" action="{{ route('brands.destroy', $brand) }}" onsubmit="return confirm('Hapus brand ini?');">@csrf @method('DELETE')<button type="submit" class="rounded-lg border border-error-200 px-4 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-800 dark:text-error-400 dark:hover:bg-error-500/10">Hapus</button></form></div></td></tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada brand yang dibuat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $brands->links() }}</div>
        </x-common.component-card>
    </div>
@endsection


