@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Tambah User" />

    <x-common.component-card title="Form User Internal" desc="Buat akun staff baru dan tentukan role aksesnya.">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            @include('pages.users.partials.form', [
                'submitLabel' => 'Simpan User',
            ])
        </form>
    </x-common.component-card>
@endsection
