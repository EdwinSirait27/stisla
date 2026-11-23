@extends('layouts.app')
@section('title', 'Ubah bank ' . $bank->name)
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.Bank._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Master data</li><li><a href="{{ route('pages.Bank') }}">Bank</a></li><li aria-current="page">Ubah</li></ol>
                    <h1 class="pr-title">Ubah bank {{ $bank->name }}</h1>
                    <p class="pr-lede">Terakhir diubah {{ $bank->updated_at?->timezone(config('app.timezone'))->locale('id')->translatedFormat('d F Y, H:i') ?? '-' }}.</p>
                </div>
            </header>

            @include('pages.Bank._form', [
                'formId'      => 'banks-edit',
                'submitId'    => 'edit-btn',
                'action'      => route('Bank.update', $hashedId),
                'method'      => 'PUT',
                'submitLabel' => 'Simpan perubahan',
                'value'       => old('name', $bank->name),
            ])
        </section>
    </div>
@endsection
