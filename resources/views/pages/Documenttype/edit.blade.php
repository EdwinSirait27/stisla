@extends('layouts.app')
@section('title', 'Ubah jenis dokumen')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.Documenttype._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Dokumen</li><li><a href="{{ route('documenttype.index') }}">Jenis dokumen</a></li><li aria-current="page">Ubah</li></ol>
                    <h1 class="pr-title">Ubah {{ $type->document_name }}</h1>
                    <p class="pr-lede">Singkatan {{ $type->nickname }}, template {{ $type->view_name ?: "belum dipilih" }}.</p>
                </div>
            </header>

            @include('pages.Documenttype._form', [
                'formId'      => 'doctype-edit',
                'submitId'    => 'edit-btn',
                'action'      => route('documenttype.update', $hashedId),
                'method'      => 'PUT',
                'submitLabel' => 'Simpan perubahan',
                'type'        => $type,
            ])
        </section>
    </div>
@endsection
