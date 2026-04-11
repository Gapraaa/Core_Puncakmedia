@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Add-on" />
    @include('pages.addons.partials.form', ['pageTitle' => 'Edit Add-on', 'pageDescription' => 'Perbarui add-on biaya tambahan booking.', 'formAction' => route('addons.update', $addon), 'submitLabel' => 'Perbarui Add-on', 'httpMethod' => 'PUT', 'addon' => $addon])
@endsection
