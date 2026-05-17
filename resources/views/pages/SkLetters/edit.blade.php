@extends('layouts.app')
@section('title', 'Edit SK Letter')

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .employee-item, .menimbang-item, .mengingat-item, .keputusan-item {
            background: #f8f9ff;
            border: 1px solid #e8ecff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            position: relative;
        }
        .btn-remove {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .item-number {
            font-weight: 700;
            color: #6777ef;
            font-size: 13px;
            margin-bottom: 10px;
        }
        .select2-container .select2-selection--single {
            height: 42px;
            border: 1px solid #d1d1d1;
            border-radius: 8px;
        }
        .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 42px;
            padding-left: 12px;
        }
        .select2-container .select2-selection--single .select2-selection__arrow {
            height: 42px;
        }
    </style>
@endpush

@section('main')
<div class="main-content">
<section class="section">

    <div class="section-header">
        <h1>Edit SK Letter</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('SkLetters') }}">SK Letters</a></div>
            <div class="breadcrumb-item">
                <a href="{{ route('SkLetters.show', $skLetter->id) }}">{{ $skLetter->sk_number }}</a>
            </div>
            <div class="breadcrumb-item">Edit</div>
        </div>
    </div>

    <div class="section-body">
    <form id="sk-form" action="{{ route('SkLetters.update', $skLetter->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">

        {{-- KOLOM KIRI --}}
        <div class="col-lg-8">

            {{-- Card: Informasi SK --}}
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-file-alt mr-2 text-primary"></i>SK Information</h4>
                </div>
                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">
                                    SK Number
                                </label>
                                <input type="text"
                                    class="form-control"
                                    value="{{ $skLetter->sk_number }}"
                                    disabled readonly>
                                <small class="text-muted">Nomor SK tidak dapat diubah.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">
                                    SK Type <span class="text-danger">*</span>
                                </label>
                                <select name="sk_type_id" id="sk_type_id"
                                    class="form-control select2 @error('sk_type_id') is-invalid @enderror"
                                    required>
                                    <option value="">Choose SK Type</option>
                                    @foreach($sktypes as $key => $value)
                                        <option value="{{ $key }}"
                                            {{ old('sk_type_id', $skLetter->sk_type_id) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sk_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">
                                    Publishing Company <span class="text-danger">*</span>
                                </label>
                                <select name="company_id"
                                    class="form-control select2 @error('company_id') is-invalid @enderror"
                                    required>
                                    <option value="">Choose Company</option>
                                    @foreach($companies as $key => $value)
                                        <option value="{{ $key }}"
                                            {{ old('company_id', $skLetter->company_id) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('company_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-control-label">SK Title</label>
                                <input type="text" name="title"
                                    class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title', $skLetter->title) }}"
                                    placeholder="cth: Surat Keputusan Pengangkatan Karyawan">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label">
                                    Effective Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="effective_date"
                                    class="form-control @error('effective_date') is-invalid @enderror"
                                    value="{{ old('effective_date', optional($skLetter->effective_date)->format('Y-m-d')) }}"
                                    required>
                                @error('effective_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label">Inactive Date</label>
                                <input type="date" name="inactive_date"
                                    class="form-control @error('inactive_date') is-invalid @enderror"
                                    value="{{ old('inactive_date', optional($skLetter->inactive_date)->format('Y-m-d')) }}">
                                @error('inactive_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label">Published</label>
                                <input type="text" name="location"
                                    class="form-control @error('location') is-invalid @enderror"
                                    value="{{ old('location', $skLetter->location) }}"
                                    placeholder="cth: Denpasar">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Approver Director</label>
                                <select name="approver_2"
                                    class="form-control select2 @error('approver_2') is-invalid @enderror">
                                    <option value="">Choose</option>
                                    @foreach($employees_approver_2 as $emp)
                                        <option value="{{ $emp->id }}"
                                            {{ old('approver_2', $skLetter->approver_2) == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->employee_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('approver_2')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Approver Managing Director</label>
                                <select name="approver_3"
                                    class="form-control select2 @error('approver_3') is-invalid @enderror">
                                    <option value="">Choose</option>
                                    @foreach($employees_approver_3 as $emp)
                                        <option value="{{ $emp->id }}"
                                            {{ old('approver_3', $skLetter->approver_3) == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->employee_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('approver_3')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-control-label">Notes</label>
                                <textarea name="notes" rows="2"
                                    class="form-control @error('notes') is-invalid @enderror"
                                    placeholder="Catatan tambahan...">{{ old('notes', $skLetter->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Card: Employees --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-users mr-2 text-primary"></i>Employee Data</h4>
                    <button type="button" class="btn btn-primary btn-sm" id="add-employee">
                        <i class="fas fa-plus mr-1"></i> Add Employee
                    </button>
                </div>
                <div class="card-body">
                    @error('employees')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                    <div id="employee-wrapper">
                        {{-- Existing employees --}}
                        @foreach($skLetter->employees as $i => $employee)
                        @php $pivot = $employee->pivot; @endphp
                        <div class="employee-item" data-index="{{ $i }}">
                            <div class="item-number">Employee #{{ $i + 1 }}</div>
                            <button type="button" class="btn btn-danger btn-sm btn-remove remove-employee">
                                <i class="fas fa-times"></i>
                            </button>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Employee <span class="text-danger">*</span></label>
                                        <select name="employees[{{ $i }}][employee_id]"
                                            class="form-control select2-employee" required>
                                            <option value="">Choose Employee</option>
                                            @foreach($employees as $emp)
                                                <option value="{{ $emp->id }}"
                                                    {{ $emp->id == $employee->id ? 'selected' : '' }}>
                                                    {{ $emp->employee_name }} ({{ $emp->employee_pengenal ?? '-' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>New Structure / Position</label>
                                        <select name="employees[{{ $i }}][new_structure_id]"
                                            class="form-control select2-employee">
                                            <option value="">Choose</option>
                                            @foreach($structures as $s)
                                                <option value="{{ $s->id }}"
                                                    {{ $s->id == $pivot->new_structure_id ? 'selected' : '' }}>
                                                    {{ $s->submissionposition->positionRelation->name ?? '-' }}
                                                    - {{ $s->submissionposition->company->name ?? '-' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @if($skLetter->skType->affects_salary)
                                <div class="col-md-4 salary-field">
                                    <div class="form-group">
                                        <label>Basic Salary</label>
                                        <input type="number"
                                            name="employees[{{ $i }}][basic_salary]"
                                            class="form-control"
                                            value="{{ $pivot->basic_salary }}"
                                            placeholder="0" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4 salary-field">
                                    <div class="form-group">
                                        <label>Positional Allowance</label>
                                        <input type="number"
                                            name="employees[{{ $i }}][positional_allowance]"
                                            class="form-control"
                                            value="{{ $pivot->positional_allowance }}"
                                            placeholder="0" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4 salary-field">
                                    <div class="form-group">
                                        <label>Daily Rate</label>
                                        <input type="number"
                                            name="employees[{{ $i }}][daily_rate]"
                                            class="form-control"
                                            value="{{ $pivot->daily_rate }}"
                                            placeholder="0" min="0">
                                    </div>
                                </div>
                                @endif
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <input type="text"
                                            name="employees[{{ $i }}][notes]"
                                            class="form-control"
                                            value="{{ $pivot->notes }}"
                                            placeholder="Catatan...">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div id="empty-employee" class="text-center text-muted py-3"
                        style="{{ $skLetter->employees->count() > 0 ? 'display:none' : '' }}">
                        <i class="fas fa-user-plus fa-2x mb-2 d-block"></i>
                        No employees yet. Click "Add Employee" to add one.
                    </div>
                </div>
            </div>

            {{-- Card: Menetapkan --}}
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-stamp mr-2 text-primary"></i>Establish</h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <textarea name="menetapkan_text" id="menetapkan_text"
                            class="form-control summernote">{{ old('menetapkan_text', $skLetter->menetapkan_text) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Card: Keputusan --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-list-ol mr-2 text-primary"></i>Decision</h4>
                    <button type="button" class="btn btn-primary btn-sm" id="add-keputusan">
                        <i class="fas fa-plus mr-1"></i> Add Point
                    </button>
                </div>
                <div class="card-body">
                    <div id="keputusan-wrapper">
                        @foreach($skLetter->keputusan as $item)
                        <div class="keputusan-item">
                            <button type="button" class="btn btn-danger btn-sm btn-remove remove-keputusan">
                                <i class="fas fa-times"></i>
                            </button>
                            <div class="form-group mb-0">
                                <textarea name="keputusan[]" class="form-control" rows="2">{{ $item->content_keputusan }}</textarea>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div id="empty-keputusan" class="text-center text-muted py-3"
                        style="{{ $skLetter->keputusan->count() > 0 ? 'display:none' : '' }}">
                        <i class="fas fa-list fa-2x mb-2 d-block"></i>
                        No decision points yet.
                    </div>
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN --}}
        <div class="col-lg-4">

            {{-- Card: Menimbang --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-balance-scale mr-2 text-primary"></i>Consider</h4>
                    <button type="button" class="btn btn-primary btn-sm" id="add-menimbang">
                        <i class="fas fa-plus mr-1"></i> Add
                    </button>
                </div>
                <div class="card-body">
                    <div id="menimbang-wrapper">
                        @foreach($skLetter->menimbang as $item)
                        <div class="menimbang-item">
                            <button type="button" class="btn btn-danger btn-sm btn-remove remove-menimbang">
                                <i class="fas fa-times"></i>
                            </button>
                            <div class="form-group mb-0">
                                <textarea name="menimbang[]" class="form-control" rows="2">{{ $item->content_menimbang }}</textarea>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div id="empty-menimbang" class="text-center text-muted py-3"
                        style="{{ $skLetter->menimbang->count() > 0 ? 'display:none' : '' }}">
                        <small>No points to consider yet.</small>
                    </div>
                </div>
            </div>

            {{-- Card: Mengingat --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-book mr-2 text-primary"></i>Considering</h4>
                    <button type="button" class="btn btn-primary btn-sm" id="add-mengingat">
                        <i class="fas fa-plus mr-1"></i> Add
                    </button>
                </div>
                <div class="card-body">
                    <div id="mengingat-wrapper">
                        @foreach($skLetter->mengingat as $item)
                        <div class="mengingat-item">
                            <button type="button" class="btn btn-danger btn-sm btn-remove remove-mengingat">
                                <i class="fas fa-times"></i>
                            </button>
                            <div class="form-group mb-0">
                                <textarea name="mengingat[]" class="form-control" rows="2">{{ $item->content_mengingat }}</textarea>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div id="empty-mengingat" class="text-center text-muted py-3"
                        style="{{ $skLetter->mengingat->count() > 0 ? 'display:none' : '' }}">
                        <small>No points to consider yet.</small>
                    </div>
                </div>
            </div>

            {{-- Card: Action --}}
            <div class="card">
                <div class="card-body">
                    <button type="button" id="btn-submit" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-save mr-1"></i> Update SK
                    </button>
                    <a href="{{ route('SkLetters.show', $skLetter->id) }}"
                       class="btn btn-secondary btn-block">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </a>
                    <hr>
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        SK hanya dapat diedit saat status <strong>Draft</strong>.
                    </small>
                </div>
            </div>

        </div>
    </div>

    </form>
    </div>
</section>
</div>

{{-- Templates (sama dengan create) --}}
<template id="employee-template">
    <div class="employee-item" data-index="__INDEX__">
        <div class="item-number">Employee #<span class="num">__NUM__</span></div>
        <button type="button" class="btn btn-danger btn-sm btn-remove remove-employee">
            <i class="fas fa-times"></i>
        </button>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Employee <span class="text-danger">*</span></label>
                    <select name="employees[__INDEX__][employee_id]"
                        class="form-control select2-employee" required>
                        <option value="">Choose Employee</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">
                                {{ $emp->employee_name }} ({{ $emp->employee_pengenal ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>New Structure / Position</label>
                    <select name="employees[__INDEX__][new_structure_id]"
                        class="form-control select2-employee">
                        <option value="">Choose</option>
                        @foreach($structures as $s)
                            <option value="{{ $s->id }}">
                                {{ $s->submissionposition->positionRelation->name ?? '-' }}
                                - {{ $s->submissionposition->company->name ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4 salary-field">
                <div class="form-group">
                    <label>Basic Salary</label>
                    <input type="number" name="employees[__INDEX__][basic_salary]"
                        class="form-control" placeholder="0" min="0">
                </div>
            </div>
            <div class="col-md-4 salary-field">
                <div class="form-group">
                    <label>Positional Allowance</label>
                    <input type="number" name="employees[__INDEX__][positional_allowance]"
                        class="form-control" placeholder="0" min="0">
                </div>
            </div>
            <div class="col-md-4 salary-field">
                <div class="form-group">
                    <label>Daily Rate</label>
                    <input type="number" name="employees[__INDEX__][daily_rate]"
                        class="form-control" placeholder="0" min="0">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>Notes</label>
                    <input type="text" name="employees[__INDEX__][notes]"
                        class="form-control" placeholder="Catatan...">
                </div>
            </div>
        </div>
    </div>
</template>

<template id="menimbang-template">
    <div class="menimbang-item">
        <button type="button" class="btn btn-danger btn-sm btn-remove remove-menimbang">
            <i class="fas fa-times"></i>
        </button>
        <div class="form-group mb-0">
            <textarea name="menimbang[]" class="form-control" rows="2"
                placeholder="Poin menimbang..."></textarea>
        </div>
    </div>
</template>

<template id="mengingat-template">
    <div class="mengingat-item">
        <button type="button" class="btn btn-danger btn-sm btn-remove remove-mengingat">
            <i class="fas fa-times"></i>
        </button>
        <div class="form-group mb-0">
            <textarea name="mengingat[]" class="form-control" rows="2"
                placeholder="Dasar hukum / peraturan..."></textarea>
        </div>
    </div>
</template>

<template id="keputusan-template">
    <div class="keputusan-item">
        <button type="button" class="btn btn-danger btn-sm btn-remove remove-keputusan">
            <i class="fas fa-times"></i>
        </button>
        <div class="form-group mb-0">
            <textarea name="keputusan[]" class="form-control" rows="2"
                placeholder="Poin keputusan..."></textarea>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script src="{{ asset('library/summernote/dist/summernote-bs4.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const skTypeFlags = @json($skTypeFlags);

$(document).ready(function () {

    // Summernote
    $('#menetapkan_text').summernote({
        height: 150,
        toolbar: [
            ['style', ['bold', 'italic', 'underline']],
            ['para', ['ul', 'ol']],
            ['insert', ['link']],
            ['view', ['fullscreen', 'codeview']]
        ]
    });

    // Select2
    $('.select2').select2({ width: '100%' });

    // Init select2 pada existing employees
    $('#employee-wrapper .employee-item').each(function () {
        $(this).find('.select2-employee').select2({
            width: '100%',
            dropdownParent: $(this)
        });
    });

    // Employee index mulai dari jumlah existing
    let empIndex = {{ $skLetter->employees->count() }};

    function updateEmptyEmployee() {
        $('#empty-employee').toggle($('#employee-wrapper .employee-item').length === 0);
    }

    function initSelect2InEmployee(container) {
        container.find('.select2-employee').select2({
            width: '100%',
            dropdownParent: container
        });
    }

    $('#add-employee').on('click', function () {
        const template = document.getElementById('employee-template');
        let html = template.innerHTML
            .replace(/__INDEX__/g, empIndex)
            .replace(/__NUM__/g, empIndex + 1);
        const $item = $(html);

        const flag = skTypeFlags[$('#sk_type_id').val()] ?? {};
        if (!flag.affects_salary) $item.find('.salary-field').hide();

        $('#employee-wrapper').append($item);
        initSelect2InEmployee($item);
        empIndex++;
        updateEmptyEmployee();
    });

    $(document).on('click', '.remove-employee', function () {
        $(this).closest('.employee-item').remove();
        updateEmptyEmployee();
    });

    // Menimbang
    $('#add-menimbang').on('click', function () {
        $('#menimbang-wrapper').append(document.getElementById('menimbang-template').innerHTML);
        $('#empty-menimbang').hide();
    });
    $(document).on('click', '.remove-menimbang', function () {
        $(this).closest('.menimbang-item').remove();
        if ($('#menimbang-wrapper .menimbang-item').length === 0) $('#empty-menimbang').show();
    });

    // Mengingat
    $('#add-mengingat').on('click', function () {
        $('#mengingat-wrapper').append(document.getElementById('mengingat-template').innerHTML);
        $('#empty-mengingat').hide();
    });
    $(document).on('click', '.remove-mengingat', function () {
        $(this).closest('.mengingat-item').remove();
        if ($('#mengingat-wrapper .mengingat-item').length === 0) $('#empty-mengingat').show();
    });

    // Keputusan
    $('#add-keputusan').on('click', function () {
        $('#keputusan-wrapper').append(document.getElementById('keputusan-template').innerHTML);
        $('#empty-keputusan').hide();
    });
    $(document).on('click', '.remove-keputusan', function () {
        $(this).closest('.keputusan-item').remove();
        if ($('#keputusan-wrapper .keputusan-item').length === 0) $('#empty-keputusan').show();
    });

    // Submit
    $('#btn-submit').on('click', function () {
        if ($('#employee-wrapper .employee-item').length === 0) {
            Swal.fire({ icon: 'warning', title: 'Warning!', text: 'Minimal 1 employee required.' });
            return;
        }
        Swal.fire({
            title: 'Update SK?',
            text: 'Pastikan semua data sudah benar.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#6777ef',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Update!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) $('#sk-form').submit();
        });
    });

    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}" });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Gagal!', text: "{{ session('error') }}" });
    @endif
});
</script>
@endpush