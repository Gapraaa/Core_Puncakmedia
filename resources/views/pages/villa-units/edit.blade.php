@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Villa Unit" />
    @include('pages.villa-units.partials.form', ['pageTitle' => 'Edit Villa Unit', 'pageDescription' => 'Perbarui konfigurasi unit villa dan harga dasarnya.', 'formAction' => route('villa-units.update', $villaUnit), 'submitLabel' => 'Perbarui Villa Unit', 'httpMethod' => 'PUT', 'villaUnit' => $villaUnit, 'villas' => $villas])
@endsection
