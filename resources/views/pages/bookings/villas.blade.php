@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Daftar Booking Villa" />

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Daftar Booking Villa</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih villa untuk melihat antrean per reservasi atau menambahkan reservasi baru.</p>
            </div>
        </div>

        <x-common.component-card title="Pencarian Villa" desc="Cari villa berdasarkan nama.">
            <form method="GET" action="{{ route('bookings.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label>
                    <input onkeyup="let v=this.value.toLowerCase(); document.querySelectorAll('table tbody tr').forEach(tr => { if(tr.children.length > 1) tr.style.display = tr.innerText.toLowerCase().includes(v) ? '' : 'none' })" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama villa" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                </div>
                <div class="flex items-end gap-3">
                    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Terpakan Filter</button>
                    <a href="{{ route('bookings.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Reset</a>
                </div>
            </form>
        </x-common.component-card>

        <x-common.component-card title="Daftar Villa" desc="Total: {{ $villas->total() }} villa" class="overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-50/50 text-xs font-medium uppercase text-gray-700 dark:bg-white/[0.02] dark:text-gray-300">
                        <tr>
                            <th class="px-5 py-4">Nama Villa</th>
                            <th class="px-5 py-4">Lokasi</th>
                            <th class="px-5 py-4">Total Reservasi</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($villas as $villa)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 font-medium text-gray-800 dark:text-white/90">
                                    {{ $villa->name }}
                                </td>
                                <td class="px-5 py-4">{{ $villa->location }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full bg-brand-50 px-2 py-1 text-xs font-medium text-brand-700 ring-1 ring-inset ring-brand-600/20 dark:bg-brand-500/10 dark:text-brand-400 dark:ring-brand-500/20">
                                        {{ number_format($villa->bookings_count ?? 0, 0, ',', '.') }} Reservasi
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('bookings.create', $villa) }}" class="inline-flex items-center justify-center rounded-lg bg-brand-50 px-4 py-2 text-sm font-medium text-brand-600 transition hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-400 dark:hover:bg-brand-500/20">Tambah</a>
                                        <a href="{{ route('bookings.list', $villa) }}" class="inline-flex items-center justify-center rounded-lg bg-gray-50 px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:bg-white/10">Lihat Detail</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada data villa.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($villas->hasPages())<div class="border-t border-gray-100 p-5 dark:border-gray-800">{{ $villas->links() }}</div>@endif
        </x-common.component-card>
    </div>
@endsection
