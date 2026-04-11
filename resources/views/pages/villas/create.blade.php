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
@endsection
