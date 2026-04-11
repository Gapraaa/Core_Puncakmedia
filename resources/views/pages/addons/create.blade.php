@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Buat Add-on" />
    @include('pages.addons.partials.form', ['pageTitle' => 'Buat Add-on', 'pageDescription' => 'Tambahkan add-on untuk biaya tambahan booking.', 'formAction' => route('addons.store'), 'submitLabel' => 'Simpan Add-on', 'addon' => $addon])
@endsection
