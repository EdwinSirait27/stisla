@extends('layouts.app')
@section('title', 'Ubah pengaturan dokumen')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.Documenttype._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Dokumen</li><li><a href="{{ route('documentconfig.index') }}">Pengaturan per perusahaan</a></li><li aria-current="page">Ubah</li></ol>
                    <h1 class="pr-title">Ubah pengaturan {{ $config->documenttypes?->nickname }} untuk {{ $config->company?->nickname }}</h1>
                    <p class="pr-lede">{{ $config->documenttypes?->document_name }}{{ $config->bank_name ? ", " . $config->bank_name : "" }}.</p>
                </div>
            </header>

            @include('pages.CompanyDocumentConfig._form', [
                'formId'      => 'docconfig-edit',
                'submitId'    => 'edit-btn',
                'action'      => route('documentconfig.update', $hashedId),
                'method'      => 'PUT',
                'submitLabel' => 'Simpan perubahan',
            ])
        </section>
    </div>
@endsection
