@extends('layouts.app')
@section('title', 'Tambah bank')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.Bank._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Master data</li><li><a href="{{ route('pages.Bank') }}">Bank</a></li><li aria-current="page">Tambah</li></ol>
                    <h1 class="pr-title">Tambah bank</h1>
                    <p class="pr-lede">Bank baru langsung bisa dipilih di data rekening karyawan.</p>
                </div>
            </header>

            @include('pages.Bank._form', [
                'formId'      => 'banks-create',
                'submitId'    => 'create-btn',
                'action'      => route('Bank.store'),
                'method'      => 'POST',
                'submitLabel' => 'Tambah bank',
                'value'       => old('name'),
            ])
        </section>
    </div>
@endsection
