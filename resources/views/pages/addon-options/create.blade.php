@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Buat Opsi Add-on" />
    @include('pages.addon-options.partials.form', ['pageTitle' => 'Buat Opsi Add-on', 'pageDescription' => 'Tambahkan varian harga untuk kategori add-on ini.', 'formAction' => route('addon-options.store', $addon), 'submitLabel' => 'Simpan Opsi', 'addon' => $addon, 'addonOption' => $addonOption])
@endsection
