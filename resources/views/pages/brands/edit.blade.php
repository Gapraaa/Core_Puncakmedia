@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Brand" />

    @include('pages.brands.partials.form', [
        'pageTitle' => 'Edit Brand',
        'pageDescription' => 'Perbarui detail brand sambil tetap konsisten dengan gaya dasbor TailAdmin.',
        'formAction' => route('brands.update', $brand),
        'submitLabel' => 'Perbarui Brand',
        'httpMethod' => 'PUT',
        'brand' => $brand,
    ])
@endsection
