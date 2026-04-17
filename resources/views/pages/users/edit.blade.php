@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit User" />

    <x-common.component-card title="Edit User Internal" desc="Perbarui data akun staff dan role aksesnya.">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')
            @include('pages.users.partials.form', [
                'submitLabel' => 'Simpan Perubahan',
            ])
        </form>
    </x-common.component-card>
@endsection
