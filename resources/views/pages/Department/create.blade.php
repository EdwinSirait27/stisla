@extends('layouts.app')
@section('title', 'Tambah departemen')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.Department._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Master data</li><li><a href="{{ route('pages.Department') }}">Departemen</a></li><li aria-current="page">Tambah</li></ol>
                    <h1 class="pr-title">Tambah departemen</h1>
                    <p class="pr-lede">Setelah disimpan, departemen bisa dipilih di data karyawan.</p>
                </div>
            </header>

            @include('pages.Department._form', [
                'formId'      => 'departments-create',
                'submitId'    => 'create-btn',
                'action'      => route('Department.store'),
                'method'      => 'POST',
                'submitLabel' => 'Tambah departemen',
                'name'        => old('department_name'),
                'nickname'    => old('nickname'),
            ])
        </section>
    </div>
@endsection
