@extends('layouts.app')
@section('title', 'Tambah komponen')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.payrollcomponents._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Payroll</li><li><a href="{{ route('payrollcomponents') }}">Komponen payroll</a></li><li aria-current="page">Tambah</li></ol>
                    <h1 class="pr-title">Tambah komponen payroll</h1>
                    <p class="pr-lede">Setelah disimpan, komponen bisa dipakai di payroll.</p>
                </div>
            </header>

            @include('pages.payrollcomponents._form', [
                'formId'      => 'employee-create',
                'submitId'    => 'create-btn',
                'action'      => route('payrollcomponents.store'),
                'method'      => 'POST',
                'submitLabel' => 'Tambah komponen',
                'component'   => null,
            ])
        </section>
    </div>
@endsection
