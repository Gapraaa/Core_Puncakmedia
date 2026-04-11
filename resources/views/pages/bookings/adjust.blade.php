@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Penyesuaian Booking" />

    <x-common.component-card title="Penyesuaian Booking" desc="Tambahkan add-on baru atau extend booking tanpa membuat booking baru.">
        <form method="POST" action="{{ route('bookings.adjustments.store', $booking) }}" class="space-y-6">
            @csrf
            <div class="rounded-xl border border-gray-100 px-4 py-4 text-sm text-gray-600 dark:border-gray-800 dark:text-gray-300">
                Booking: <span class="font-medium text-gray-800 dark:text-white/90">{{ $booking->booking_code }}</span> | {{ $booking->guest_name }} | {{ $booking->villa?->name }} - {{ $booking->villaUnit?->unit_name }}
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Extend Check-out</label>
                <input type="date" name="extend_check_out" value="{{ old('extend_check_out') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                @error('extend_check_out')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-3 block text-sm font-medium text-gray-700 dark:text-gray-300">Tambah Add-ons</label>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    @foreach ($addons as $addon)
                        <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 dark:border-gray-800">
                            <input type="checkbox" name="selected_addons[]" value="{{ $addon->id }}" @checked(collect(old('selected_addons', []))->contains($addon->id)) class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20" />
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $addon->name }} <span class="text-gray-500">({{ $addon->charge_type === 'per_night' ? 'per malam' : 'per stay' }})</span></span>
                        </label>
                    @endforeach
                </div>
                @error('selected_addons')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center justify-end gap-3"><a href="{{ route('bookings.show', $booking) }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Kembali</a><button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Simpan Penyesuaian</button></div>
        </form>
    </x-common.component-card>
@endsection
