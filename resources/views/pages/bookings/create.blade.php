@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Buat Booking" />
    @include('pages.bookings.partials.form', ['brands' => $brands, 'villas' => $villas, 'villaUnits' => $villaUnits, 'vouchers' => $vouchers, 'addons' => $addons])
@endsection
