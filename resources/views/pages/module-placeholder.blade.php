@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$pageTitle" />

    <div class="grid grid-cols-12 gap-4 md:gap-6">
        <div class="col-span-12 xl:col-span-8">
            <x-common.component-card :title="$pageTitle" desc="Modul ini disiapkan untuk implementasi Phase 1 dengan tetap memakai fondasi dashboard TailAdmin yang ada.">
                <div class="space-y-4">
                    <p class="text-sm leading-6 text-gray-600 dark:text-gray-300">
                        {{ $description }}
                    </p>

                    <div class="rounded-2xl border border-dashed border-brand-300 bg-brand-50/60 px-5 py-4 dark:border-brand-800 dark:bg-brand-500/10">
                        <p class="text-sm font-medium text-brand-700 dark:text-brand-300">
                            Aturan TailAdmin:
                        </p>
                        <p class="mt-2 text-sm text-brand-700/90 dark:text-brand-300/90">
                            Semua UI dalam modul ini akan terus memakai layout, card, tabel, kontrol form, badge, dan pola modal TailAdmin yang sudah tersedia di repository.
                        </p>
                    </div>
                </div>
            </x-common.component-card>
        </div>

        <div class="col-span-12 xl:col-span-4">
            <x-common.component-card title="Langkah Berikutnya" desc="Halaman ini memang disiapkan lebih dulu sebelum data layer tersambung.">
                <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                    <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                        Buat migration dan struktur model untuk modul ini.
                    </div>
                    <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                        Tambahkan validasi dan service logic saat business rules sudah diterapkan.
                    </div>
                    <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                        Ganti placeholder ini dengan halaman list dan form berbasis TailAdmin.
                    </div>
                </div>
            </x-common.component-card>
        </div>
    </div>
@endsection
