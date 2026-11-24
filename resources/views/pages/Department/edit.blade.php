@extends('layouts.app')
@section('title', 'Ubah departemen')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.Department._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Master data</li><li><a href="{{ route('pages.Department') }}">Departemen</a></li><li aria-current="page">Ubah</li></ol>
                    <h1 class="pr-title">Ubah departemen {{ $department->department_name }}</h1>
                    <p class="pr-lede">Terakhir diubah {{ $department->updated_at?->timezone(config('app.timezone'))->locale('id')->translatedFormat('d F Y, H:i') ?? '-' }}.</p>
                </div>
            </header>

            @include('pages.Department._form', [
                'formId'      => 'departments-edit',
                'submitId'    => 'edit-btn',
                'action'      => route('Department.update', $hashedId),
                'method'      => 'PUT',
                'submitLabel' => 'Simpan perubahan',
                'name'        => old('department_name', $department->department_name),
                'nickname'    => old('nickname', $department->nickname === '-' ? '' : $department->nickname),
            ])
        </section>
    </div>
@endsection
