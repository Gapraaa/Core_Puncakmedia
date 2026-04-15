@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Buat Harga High Season" />
    @include('pages.seasonal-prices.partials.form', ['pageTitle' => 'Buat Harga High Season', 'pageDescription' => 'Tambahkan override harga high season pada rentang tanggal tertentu.', 'formAction' => route('seasonal-prices.store'), 'submitLabel' => 'Simpan Harga High Season', 'seasonalPrice' => $seasonalPrice, 'villaUnits' => $villaUnits, 'selectedVilla' => $selectedVilla, 'selectedVillaUnit' => $selectedVillaUnit])
@endsection
