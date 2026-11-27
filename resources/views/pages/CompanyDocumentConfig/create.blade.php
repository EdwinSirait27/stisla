@extends('layouts.app')
@section('title', 'Tambah pengaturan dokumen')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.Documenttype._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Dokumen</li><li><a href="{{ route('documentconfig.index') }}">Pengaturan per perusahaan</a></li><li aria-current="page">Tambah</li></ol>
                    <h1 class="pr-title">Tambah pengaturan dokumen</h1>
                    <p class="pr-lede">Aktifkan jenis dokumen untuk satu perusahaan.</p>
                </div>
            </header>

            @include('pages.CompanyDocumentConfig._form', [
                'formId'      => 'docconfig-create',
                'submitId'    => 'create-btn',
                'action'      => route('documentconfig.store'),
                'method'      => 'POST',
                'submitLabel' => 'Simpan pengaturan',
            ])
        </section>
    </div>
@endsection
