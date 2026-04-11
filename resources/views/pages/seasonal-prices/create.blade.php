@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Buat Harga Musiman" />
    @include('pages.seasonal-prices.partials.form', ['pageTitle' => 'Buat Harga Musiman', 'pageDescription' => 'Tambahkan override harga pada rentang tanggal tertentu.', 'formAction' => route('seasonal-prices.store'), 'submitLabel' => 'Simpan Harga Musiman', 'seasonalPrice' => $seasonalPrice, 'villaUnits' => $villaUnits])
@endsection
