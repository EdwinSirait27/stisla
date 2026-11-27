@extends('layouts.app')
@section('title', 'Ubah SK')
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
                    <ol class="pr-crumbs"><li><a href="{{ route('SkLetters') }}">SK</a></li><li><a href="{{ route('SkLetters.show', $skLetter->id) }}">{{ $skLetter->sk_number }}</a></li><li aria-current="page">Ubah</li></ol>
                    <h1 class="pr-title">Ubah SK {{ $skLetter->sk_number }}</h1>
                    <p class="pr-lede">SK masih Draft, jadi semua isinya bisa diubah. Setelah disimpan, ajukan lagi dari halaman detail.</p>
                </div>
            </header>
            @include('pages.SkLetters._form')
        </section>
    </div>
@endsection
