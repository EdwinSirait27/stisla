@extends('layouts.app')
@section('title', 'Buat SK')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    @include('pages.SkLetters._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li><a href="{{ route('SkLetters') }}">SK</a></li><li aria-current="page">Buat</li></ol>
                    <h1 class="pr-title">Buat surat keputusan</h1>
                    <p class="pr-lede">Disimpan sebagai Draft. Setelah diperiksa, ajukan ke approver yang dipilih di bawah.</p>
                </div>
            </header>
            @include('pages.SkLetters._form')
        </section>
    </div>
@endsection
