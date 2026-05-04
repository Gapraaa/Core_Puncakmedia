@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detail Villa" />

    <div class="space-y-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $villa->name }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $villa->location ?: 'Lokasi belum diisi' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('villas.edit', $villa) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Edit Villa</a>
                <a href="{{ route('villas.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Kembali</a>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12">
                <x-common.component-card title="Gallery Villa" desc="Cover utama dan gallery gambar yang dipakai untuk tampilan villa/resort ini.">
                    @if ($villa->images->isEmpty())
                        <div class="rounded-xl border border-dashed border-gray-300 px-5 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            Belum ada gambar untuk villa ini.
                        </div>
                    @else
                        <div class="space-y-5">
                            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                                @if ($villa->coverImage?->display_url)
                                    <img src="{{ $villa->coverImage->display_url }}" alt="{{ $villa->name }}" class="aspect-[16/6] w-full object-cover" />
                                @else
                                    <div class="flex aspect-[16/6] items-center justify-center text-sm text-gray-500 dark:text-gray-400">
                                        Cover sedang diproses atau belum siap ditampilkan.
                                    </div>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-5">
                                @foreach ($villa->images as $image)
                                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.02]">
                                        <div class="relative aspect-[4/3] bg-gray-100 dark:bg-gray-800">
                                            @if ($image->preview_url)
                                                <img src="{{ $image->preview_url }}" alt="{{ $image->original_name }}" class="h-full w-full object-cover" />
                                            @else
                                                <div class="flex h-full items-center justify-center text-xs text-gray-500 dark:text-gray-400">Sedang diproses</div>
                                            @endif

                                            <div class="absolute left-3 top-3 flex flex-wrap gap-2">
                                                @if ($image->is_cover)
                                                    <span class="inline-flex items-center rounded-full bg-brand-500 px-2.5 py-1 text-[11px] font-medium text-white">Cover</span>
                                                @endif
                                                <span class="inline-flex items-center rounded-full bg-black/70 px-2.5 py-1 text-[11px] font-medium text-white">#{{ $image->sort_order }}</span>
                                            </div>
                                        </div>
                                        <div class="space-y-2 p-3">
                                            <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">{{ $image->original_name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Status: {{ ucfirst($image->status) }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </x-common.component-card>
            </div>

            <div class="col-span-12 xl:col-span-5">
                <x-common.component-card title="Informasi Villa" desc="Detail operasional utama untuk villa ini.">
                    <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Slug:</span> {{ $villa->slug }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Status:</span> {{ $villa->status }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Resort:</span> {{ $villa->is_resort ? 'Ya' : 'Tidak' }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Brand:</span> {{ $villa->brands->pluck('name')->join(', ') ?: 'Belum terhubung' }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Jumlah Gambar:</span> {{ $villa->images->count() }}</div>
                    </div>
                </x-common.component-card>
            </div>
            <div class="col-span-12 xl:col-span-7">
                <x-common.component-card title="Detail Villa" desc="Deskripsi, kelebihan, aturan, fasilitas, dan tautan pendukung.">
                    <div class="space-y-4 text-sm text-gray-600 dark:text-gray-300">
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Deskripsi:</span> {{ $villa->description ?: '-' }}</div>
                        <div>
                            <span class="font-medium text-gray-800 dark:text-white/90">Fasilitas Villa:</span>
                            @if ($villa->primaryFacilities->isNotEmpty())
                                <ul class="mt-2 list-disc space-y-1 pl-5">
                                    @foreach ($villa->primaryFacilities as $facility)
                                        <li>{{ $facility->name }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span> -</span>
                            @endif
                        </div>
                        <div>
                            <span class="font-medium text-gray-800 dark:text-white/90">Fasilitas Tambahan:</span>
                            @if ($villa->additionalFacilities->isNotEmpty())
                                <ul class="mt-2 list-disc space-y-1 pl-5">
                                    @foreach ($villa->additionalFacilities as $facility)
                                        <li>{{ $facility->name }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span> -</span>
                            @endif
                        </div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Rules:</span> {{ $villa->rules ?: '-' }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Pros:</span> {{ $villa->pros ?: '-' }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Cons:</span> {{ $villa->cons ?: '-' }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Youtube URL:</span> {{ $villa->youtube_url ?: '-' }}</div>
                    </div>
                </x-common.component-card>
            </div>
        </div>

        <x-common.component-card title="Villa Unit" desc="Unit yang terhubung ke villa ini.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Unit</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipe</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Harga Dasar</th></tr></thead>
                    <tbody>
                        @forelse ($villa->units as $unit)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800"><td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ $unit->unit_name }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $unit->unit_type ?: '-' }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">W: {{ number_format($unit->price_weekday, 0, ',', '.') }} | SW: {{ number_format($unit->price_semi_weekend, 0, ',', '.') }} | WE: {{ number_format($unit->price_weekend, 0, ',', '.') }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada unit untuk villa ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-common.component-card>
    </div>
@endsection
