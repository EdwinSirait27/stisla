@extends('layouts.app')
@section('title', 'Tambah jenis SK')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.Sktype._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li><a href="{{ route('pages.Sktype') }}">Jenis SK</a></li><li aria-current="page">Tambah</li></ol>
                    <h1 class="pr-title">Tambah jenis SK</h1>
                    <p class="pr-lede">Tentukan nama, singkatan untuk nomor SK, dan apa yang diubah sistem pada data karyawan saat SK jenis ini disahkan.</p>
                </div>
            </header>
            @include('pages.Sktype._form', ['sktype' => null])
        </section>
    </div>
@endsection
