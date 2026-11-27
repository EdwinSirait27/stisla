@extends('layouts.app')
@section('title', 'Ubah jenis SK')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.Sktype._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li><a href="{{ route('pages.Sktype') }}">Jenis SK</a></li><li aria-current="page">{{ \Illuminate\Support\Str::title($sktype->sk_name) }}</li></ol>
                    <h1 class="pr-title">Ubah jenis SK {{ \Illuminate\Support\Str::title($sktype->sk_name) }}</h1>
                    <p class="pr-lede">Singkatan baru hanya dipakai untuk nomor SK berikutnya; nomor SK yang sudah ada tidak berubah.</p>
                </div>
            </header>
            @include('pages.Sktype._form')
        </section>
    </div>
@endsection
