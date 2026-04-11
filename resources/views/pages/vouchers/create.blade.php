@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Buat Voucher" />
    @include('pages.vouchers.partials.form', ['pageTitle' => 'Buat Voucher', 'pageDescription' => 'Tambahkan kode voucher baru untuk diskon booking.', 'formAction' => route('vouchers.store'), 'submitLabel' => 'Simpan Voucher', 'voucher' => $voucher])
@endsection
