@extends('layouts.app')
@section('title', 'Tambah jenis dokumen')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.Documenttype._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Dokumen</li><li><a href="{{ route('documenttype.index') }}">Jenis dokumen</a></li><li aria-current="page">Tambah</li></ol>
                    <h1 class="pr-title">Tambah jenis dokumen</h1>
                    <p class="pr-lede">Setelah disimpan, aktifkan untuk perusahaan supaya bisa diterbitkan.</p>
                </div>
            </header>

            @include('pages.Documenttype._form', [
                'formId'      => 'doctype-create',
                'submitId'    => 'create-btn',
                'action'      => route('documenttype.store'),
                'method'      => 'POST',
                'submitLabel' => 'Tambah jenis dokumen',
                'type'        => null,
            ])
        </section>
    </div>
@endsection
