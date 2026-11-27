@extends('layouts.app')
@section('title', 'Buat kontrak')
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
                    <ol class="pr-crumbs"><li><a href="{{ route('contract') }}">Kontrak</a></li><li aria-current="page">Buat</li></ol>
                    <h1 class="pr-title">Buat kontrak</h1>
                    <p class="pr-lede">Pilih karyawan; penempatan dan gaji terakhirnya terisi otomatis. Satu karyawan hanya bisa punya satu kontrak berlaku.</p>
                </div>
            </header>
            @include('pages.contract._form')
        </section>
    </div>
@endsection
