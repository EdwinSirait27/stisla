@extends('layouts.app')
@section('title', 'Buat berita acara')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.Documenttype._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Dokumen</li><li><a href="{{ route('beritaacara.index') }}">Berita acara</a></li><li aria-current="page">Buat</li></ol>
                    <h1 class="pr-title">Buat berita acara</h1>
                    <p class="pr-lede">Isi berita acara, pilih approver sesuai urutan persetujuan, dan tambahkan tembusan bila perlu.</p>
                </div>
            </header>
            @include('pages.BeritaAcara._form', ['returned' => $returned ?? null])
        </section>
    </div>
@endsection
