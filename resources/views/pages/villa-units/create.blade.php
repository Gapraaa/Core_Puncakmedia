@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Buat Villa Unit" />
    @include('pages.villa-units.partials.form', ['pageTitle' => 'Buat Villa Unit', 'pageDescription' => 'Tambahkan unit villa dan harga dasar harian.', 'formAction' => route('villa-units.store'), 'submitLabel' => 'Simpan Villa Unit', 'villaUnit' => $villaUnit, 'villas' => $villas])
@endsection
