@extends('layouts.app')
@section('title', 'Ubah komponen')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.payrollcomponents._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Payroll</li><li><a href="{{ route('payrollcomponents') }}">Komponen payroll</a></li><li aria-current="page">Ubah</li></ol>
                    <h1 class="pr-title">Ubah komponen {{ $payrolls->component_name }}</h1>
                    <p class="pr-lede">Terakhir diubah {{ $payrolls->updated_at?->timezone(config('app.timezone'))->locale('id')->translatedFormat('d F Y, H:i') ?? '-' }}.</p>
                </div>
            </header>

            @include('pages.payrollcomponents._form', [
                'formId'      => 'payrollcomponents-edit',
                'submitId'    => 'edit-btn',
                'action'      => route('updatepayrollcomponents', $payrolls->id),
                'method'      => 'PUT',
                'submitLabel' => 'Simpan perubahan',
                'component'   => $payrolls,
            ])
        </section>
    </div>
@endsection
