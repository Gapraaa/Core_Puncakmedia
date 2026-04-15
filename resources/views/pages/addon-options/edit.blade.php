@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Opsi Add-on" />
    @include('pages.addon-options.partials.form', ['pageTitle' => 'Edit Opsi Add-on', 'pageDescription' => 'Perbarui varian harga untuk kategori add-on ini.', 'formAction' => route('addon-options.update', [$addon, $addonOption]), 'submitLabel' => 'Perbarui Opsi', 'httpMethod' => 'PUT', 'addon' => $addon, 'addonOption' => $addonOption])
@endsection
