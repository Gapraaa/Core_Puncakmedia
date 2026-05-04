@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Buat Villa" />

    @include('pages.villas.partials.form', [
        'pageTitle' => 'Buat Villa',
        'pageDescription' => 'Tambahkan data villa baru dengan section form berbasis TailAdmin.',
        'formAction' => route('villas.store'),
        'submitLabel' => 'Simpan Villa',
        'villa' => $villa,
    ])

    <div class="mt-6 rounded-2xl border border-dashed border-gray-300 bg-gray-50/70 px-5 py-4 text-sm text-gray-600 dark:border-gray-700 dark:bg-white/[0.02] dark:text-gray-300">
        Gallery dan cover gambar bisa diatur setelah villa berhasil disimpan.
    </div>
@endsection
