@extends('layouts.app')
@section('title', 'Ubah kontrak')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    @include('pages.contract._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li><a href="{{ route('contract') }}">Kontrak</a></li><li><a href="{{ route('showcontract', $contract->id) }}">{{ $contract->employee?->employee_name }}</a></li><li aria-current="page">Ubah</li></ol>
                    <h1 class="pr-title">Ubah kontrak {{ $contract->employee?->employee_name }}</h1>
                    <p class="pr-lede">Kontrak Draft bisa diubah seluruhnya. Kontrak yang sudah berlaku hanya bisa diganti dokumen dan catatannya.</p>
                </div>
            </header>
            @include('pages.contract._form')
        </section>
    </div>
@endsection
