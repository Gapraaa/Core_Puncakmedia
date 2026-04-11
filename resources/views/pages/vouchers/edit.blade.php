@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Voucher" />
    @include('pages.vouchers.partials.form', ['pageTitle' => 'Edit Voucher', 'pageDescription' => 'Perbarui kode voucher dan aturan diskonnya.', 'formAction' => route('vouchers.update', $voucher), 'submitLabel' => 'Perbarui Voucher', 'httpMethod' => 'PUT', 'voucher' => $voucher])
@endsection
