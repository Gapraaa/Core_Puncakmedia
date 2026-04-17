@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Kalender Booking" />

    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Kalender Booking Semua Villa dan Unit</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Semua kartu tampil langsung agar admin sales bisa cepat mencari dengan <span class="font-semibold text-gray-700 dark:text-gray-200">Ctrl + F</span>, dan bulan bisa digeser satu per satu di setiap kartu.</p>
        </div>

        <x-common.component-card title="Filter Kalender" desc="Filter ringan tetap tersedia, tapi semua kartu tetap tampil agar mudah dicari langsung dari browser.">
            <form method="GET" action="{{ route('calendar') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="xl:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Cari Villa / Unit / Lokasi / Brand</label>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Contoh: Kamela, Unit A, Cisarua" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Brand</label>
                    <select name="brand_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                        <option value="">Semua brand</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" @selected((string) ($filters['brand_id'] ?? '') === (string) $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-3">
                    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Terapkan</button>
                    <a href="{{ route('calendar') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Reset</a>
                </div>
            </form>
        </x-common.component-card>

        <div class="rounded-2xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-300">
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded bg-error-500"></span>Tanggal terisi booking</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded bg-success-100 ring-1 ring-success-300 dark:bg-success-500/20 dark:ring-success-700"></span>Tanggal tersedia</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded bg-gray-100 ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700"></span>Di luar bulan aktif</span>
            </div>
        </div>

        @if ($cards->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-300 px-6 py-16 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                Tidak ada villa atau unit aktif yang cocok dengan filter ini.
            </div>
        @else
            <div class="grid grid-cols-1 gap-6">
                @foreach ($cards as $card)
                    <div
                        x-data="calendarCard(@js([
                            'initialMonth' => $card['initial_month'],
                            'bookings' => $card['bookings'],
                            'showBookingBaseUrl' => url('/bookings'),
                        ]))"
                        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"
                    >
                        <div class="flex flex-col lg:flex-row">
                            <div class="border-b border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-900/50 lg:w-[500px] lg:shrink-0 lg:border-b-0 lg:border-r">
                                <div class="w-full max-w-[450px]">
                                    <div class="mb-3 flex items-center justify-between gap-3">
                                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">{{ $card['subtitle'] }}</div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="prevMonth()" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.04]">&lt;</button>
                                            <div class="min-w-[180px] text-center text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-200" x-text="monthLabel"></div>
                                            <button type="button" @click="nextMonth()" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.04]">&gt;</button>
                                        </div>
                                    </div>

                                    <div class="overflow-hidden rounded-2xl border-2 border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-950/40">
                                        <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-100 text-center text-xs font-semibold uppercase tracking-wide text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                            @foreach ($weekdayLabels as $label)
                                                <div class="px-2 py-3.5">{{ $label }}</div>
                                            @endforeach
                                        </div>

                                    <template x-for="(week, weekIndex) in weeks" :key="`week-${weekIndex}`">
                                        <div class="grid grid-cols-7 border-b border-gray-200 last:border-b-0 dark:border-gray-700">
                                            <template x-for="day in week" :key="day.date">
                                                <button
                                                    type="button"
                                                    @click="openBooking(day)"
                                                    class="relative block h-[64px] w-full border-r border-gray-200 p-2.5 text-left text-sm last:border-r-0 dark:border-gray-700"
                                                    :class="dayClasses(day)"
                                                    :title="dayTitle(day)"
                                                >
                                                    <div class="flex items-start justify-between gap-1">
                                                        <span class="font-semibold" :class="{ 'underline underline-offset-2': day.is_today }" x-text="day.day"></span>
                                                        <template x-if="day.booking">
                                                            <span class="rounded bg-white/20 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide" x-text="dayBadge(day)"></span>
                                                        </template>
                                                    </div>
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                    </div>
                                </div>
                            </div>

                            <div class="min-w-0 flex-1 p-5">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <h3 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $card['title'] }}</h3>
                                        <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $card['search_blob'] }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <x-ui.badge color="light">Booking: {{ $card['booking_count'] }}</x-ui.badge>
                                        <x-ui.badge color="info">Terisi: <span x-text="occupancyDays"></span> hari</x-ui.badge>
                                    </div>
                                </div>

                                <div class="mt-5 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                                    <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/40">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Brand</div>
                                        <div class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $card['brands'] }}</div>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/40">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Lokasi</div>
                                        <div class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $card['location'] }}</div>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/40">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Kapasitas</div>
                                        <div class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $card['capacity'] }} orang</div>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/40">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Unit</div>
                                        <div class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $card['unit']->unit_name }}</div>
                                    </div>
                                </div>

                                <div class="mt-5 rounded-xl border border-brand-100 bg-brand-50/70 px-4 py-3 text-sm text-brand-700 dark:border-brand-900/40 dark:bg-brand-500/10 dark:text-brand-300">
                                    Klik tanggal merah untuk membuka detail booking yang mengisi tanggal tersebut.
                                </div>

                                <div class="mt-5 flex flex-wrap gap-3">
                                    <a href="{{ route('bookings.list', $card['villa']) }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Lihat Booking</a>
                                    <a href="{{ route('bookings.create', $card['villa']) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Buat Booking</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
