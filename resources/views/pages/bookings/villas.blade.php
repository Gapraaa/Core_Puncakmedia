@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$pageTitle" />

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $pageTitle }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $pageDescription }}</p>
            </div>
        </div>

        <x-common.component-card title="Pencarian Villa" desc="Cari villa berdasarkan nama.">
            <form method="GET" action="{{ $searchAction }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label>
                    <input onkeyup="let v=this.value.toLowerCase(); document.querySelectorAll('table tbody tr').forEach(tr => { if(tr.children.length > 1) tr.style.display = tr.innerText.toLowerCase().includes(v) ? '' : 'none' })" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama villa" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                </div>
                <div class="flex items-end gap-3">
                    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Terapkan Filter</button>
                    <a href="{{ $searchAction }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Reset</a>
                </div>
            </form>
        </x-common.component-card>

        <x-common.component-card title="Daftar Villa" desc="Total: {{ $villas->total() }} villa • Halaman {{ $villas->currentPage() }} dari {{ $villas->lastPage() }}" class="overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="ops-compact-table">
                    <thead class="bg-gray-50/50 text-xs font-medium uppercase text-gray-700 dark:bg-white/[0.02] dark:text-gray-300">
                        <tr>
                            <th>Nama Villa</th>
                            <th>Tipe</th>
                            <th>Harga</th>
                            <th>Kapasitas</th>
                            <th>Total Booking</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($villas as $villa)
                            @php
                                $units = $villa->units ?? collect();
                                $displayWeekday = $villa->is_resort
                                    ? $units->min('price_weekday')
                                    : ($units->first()?->price_weekday ?? 0);
                                $displaySemiWeekend = $villa->is_resort
                                    ? $units->min('price_semi_weekend')
                                    : ($units->first()?->price_semi_weekend ?? 0);
                                $displayWeekend = $villa->is_resort
                                    ? $units->min('price_weekend')
                                    : ($units->first()?->price_weekend ?? 0);
                                $displayCapacity = $villa->is_resort
                                    ? $units->sum('capacity')
                                    : ($units->first()?->capacity ?? 0);
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                <td class="font-medium text-gray-800 dark:text-white/90">
                                    {{ $villa->name }}
                                </td>
                                <td>
                                    <span class="inline-flex items-center rounded-full {{ $villa->is_resort ? 'bg-warning-50 text-warning-700 ring-warning-600/20 dark:bg-warning-500/10 dark:text-warning-400 dark:ring-warning-500/20' : 'bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-500/10 dark:text-success-400 dark:ring-success-500/20' }} px-2 py-1 text-xs font-medium ring-1 ring-inset">
                                        {{ $villa->is_resort ? 'Resort' : 'Villa' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="space-y-1 text-xs text-gray-500 dark:text-gray-400">
                                        <div><span class="font-medium text-gray-700 dark:text-gray-200">Weekday:</span> Rp {{ number_format($displayWeekday, 0, ',', '.') }}</div>
                                        <div><span class="font-medium text-gray-700 dark:text-gray-200">Semi Weekend:</span> Rp {{ number_format($displaySemiWeekend, 0, ',', '.') }}</div>
                                        <div><span class="font-medium text-gray-700 dark:text-gray-200">Weekend:</span> Rp {{ number_format($displayWeekend, 0, ',', '.') }}</div>
                                    </div>
                                    @if ($villa->is_resort)
                                        <div class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">Harga mulai dari unit termurah</div>
                                    @endif
                                </td>
                                <td>
                                    {{ number_format($displayCapacity, 0, ',', '.') }} orang
                                </td>
                                <td>
                                    <span class="inline-flex items-center rounded-full bg-brand-50 px-2 py-1 text-xs font-medium text-brand-700 ring-1 ring-inset ring-brand-600/20 dark:bg-brand-500/10 dark:text-brand-400 dark:ring-brand-500/20">
                                        {{ number_format($villa->bookings_count ?? 0, 0, ',', '.') }} Booking
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route($primaryActionRouteName, $villa) }}" class="inline-flex items-center justify-center rounded-lg {{ $mode === 'create' ? 'bg-brand-500 text-white hover:bg-brand-600' : 'bg-brand-50 text-brand-600 hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-400 dark:hover:bg-brand-500/20' }} px-4 py-2 text-sm font-medium transition">
                                            {{ $primaryActionLabel }}
                                        </a>
                                        <a href="{{ route($secondaryActionRouteName, $villa) }}" class="inline-flex items-center justify-center rounded-lg bg-gray-50 px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:bg-white/10">
                                            {{ $secondaryActionLabel }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">{{ $emptyStateMessage }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($villas->hasPages())<div class="border-t border-gray-100 p-5 dark:border-gray-800">{{ $villas->links() }}</div>@endif
        </x-common.component-card>
    </div>
@endsection
