@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Buat Brand" />

    @include('pages.brands.partials.form', [
        'pageTitle' => 'Buat Brand',
        'pageDescription' => 'Tambahkan brand baru untuk operasional Core PMS.',
        'formAction' => route('brands.store'),
        'submitLabel' => 'Simpan Brand',
        'brand' => $brand,
    ])
@endsection
