@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Harga High Season" />
    @include('pages.seasonal-prices.partials.form', ['pageTitle' => 'Edit Harga High Season', 'pageDescription' => 'Perbarui override harga high season.', 'formAction' => route('seasonal-prices.update', $seasonalPrice), 'submitLabel' => 'Perbarui Harga High Season', 'httpMethod' => 'PUT', 'seasonalPrice' => $seasonalPrice, 'villaUnits' => $villaUnits, 'selectedVilla' => $selectedVilla, 'selectedVillaUnit' => $selectedVillaUnit])
@endsection
