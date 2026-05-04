@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Villa" />

    @include('pages.villas.partials.form', [
        'pageTitle' => 'Edit Villa',
        'pageDescription' => 'Perbarui data villa dengan tampilan yang tetap konsisten di dalam TailAdmin.',
        'formAction' => route('villas.update', $villa),
        'submitLabel' => 'Perbarui Villa',
        'httpMethod' => 'PUT',
        'villa' => $villa,
    ])

    <div class="mt-6">
        @include('pages.villas.partials.gallery-manager', [
            'villa' => $villa,
        ])
    </div>
@endsection
