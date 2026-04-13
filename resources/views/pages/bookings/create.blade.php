@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Buat Booking - {{ $villa->name }}" />
    @include('pages.bookings.partials.form', ['villa' => $villa, 'brands' => $brands, 'villaUnits' => $villaUnits, 'vouchers' => $vouchers, 'addons' => $addons])
@endsection
