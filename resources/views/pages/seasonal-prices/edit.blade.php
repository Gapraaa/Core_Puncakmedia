@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Harga Musiman" />
    @include('pages.seasonal-prices.partials.form', ['pageTitle' => 'Edit Harga Musiman', 'pageDescription' => 'Perbarui override harga musiman.', 'formAction' => route('seasonal-prices.update', $seasonalPrice), 'submitLabel' => 'Perbarui Harga Musiman', 'httpMethod' => 'PUT', 'seasonalPrice' => $seasonalPrice, 'villaUnits' => $villaUnits])
@endsection
