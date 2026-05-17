{{-- @extends('layouts.app')
@section('title', 'Create SK Letters')
@push('style')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <style>
        .avatar {
            position: relative;
        }

        .iframe-container {
            position: relative;
            overflow: hidden;
            padding-top: 56.25%;
            /* Aspect ratio 16:9 */
        }

        .iframe-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
            transition: all 0.3s ease;
            border: 1px solid #d1d1d1;
        }

        .form-control:focus {
            border-color: #6777ef;
            box-shadow: 0 0 0 0.2rem rgba(103, 119, 239, 0.25);
        }

        .form-control-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #34395e;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control-label i {
            color: #6777ef;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 4px 25px 0 rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 10px 30px 0 rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #f9f9f9;
            padding: 20px;
        }

        .card-header h6 {
            font-weight: 700;
            font-size: 16px;
            color: #34395e;
        }

        .card-body {
            padding: 30px;
        }

        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-left: 10px;
        }

        .btn-secondary {
            background-color: #cdd3d8;
            border-color: #cdd3d8;
            color: #34395e;
        }

        .btn-secondary:hover {
            background-color: #b9bfc4;
            border-color: #b9bfc4;
        }

        .bg-gradient-dark {
            background: linear-gradient(310deg, #2dce89, #2dcec7);
            border: none;
        }

        .bg-gradient-dark:hover {
            background: linear-gradient(310deg, #26b179, #26b1a9);
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .alert-secondary {
            background-color: #f8f9fa;
            border-color: #f1f2f3;
        }

        .alert-secondary .text-white {
            color: #6c757d !important;
        }

        .form-check {
            padding-left: 30px;
            margin-bottom: 10px;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            margin-top: 3px;
            margin-left: -30px;
            cursor: pointer;
        }

        .form-check-label {
            cursor: pointer;
        }

        .invalid-feedback {
            display: block;
            margin-top: 5px;
            font-size: 13px;
            color: #fc544b;
        }

        .alert-danger {
            background-color: #ffdede;
            border-color: #ffd0d0;
            color: #dc3545;
        }

        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        select.form-control {
            height: 42px;
        }
    </style>
@endpush
@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Create SK Letters</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('SkLetters') }}">SK Letters</a></div>
                    <div class="breadcrumb-item">Create SK Letters</div>
                </div>
            </div>
            <div class="section-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0 px-3">
                                    <h6 class="mb-0">{{ __('Create Position') }}</h6>
                                </div>
                                <div class="card-body pt-4 p-3">
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    @if (session('success'))
                                        <div class="alert alert-success alert-dismissible fade show" id="alert-success"
                                            role="alert">
                                            <span class="alert-text">
                                                {{ session('success') }}
                                            </span>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close">
                                                <i class="fa fa-close" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    @endif
                                    <form id="position-create" action="{{ route('SkLetters.store') }}" method="POST">
                                        @csrf

                                        <div class="row">

                                            <!-- Company -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Company Name') }}
                                                    </label>

                                                    <select name="company_id"
                                                        class="form-control select2 @error('company_id') is-invalid @enderror"
                                                        required>
                                                        <option value="">Choose Company</option>
                                                        @foreach ($companies as $key => $value)
                                                            <option value="{{ $key }}"
                                                                {{ old('company_id') == $key ? 'selected' : '' }}>
                                                                {{ $value }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    @error('company_id')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('SK Types') }}
                                                    </label>

                                                    <select name="sk_type_id"
                                                        class="form-control select2 @error('sk_type_id') is-invalid @enderror"
                                                        required>
                                                        <option value="">Choose SK Types</option>
                                                        @foreach ($sktypes as $key => $value)
                                                            <option value="{{ $key }}"
                                                                {{ old('sk_type_id') == $key ? 'selected' : '' }}>
                                                                {{ $value }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    @error('sk_type_id')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Effective Date') }}
                                                    </label>

                                                     <input type="date" name="effective_date"
                                        class="form-control @error('effective_date') is-invalid @enderror"
                                        value="{{ old('effective_date', isset($effective_date) ? $effective_date->format('Y-m-d') : '') }}">

                                                    @error('effective_date')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Inactive Date') }}
                                                    </label>

                                                     <input type="date" name="inactive_date"
                                        class="form-control @error('inactive_date') is-invalid @enderror"
                                        value="{{ old('inactive_date', isset($inactive_date) ? $inactive_date->format('Y-m-d') : '') }}">

                                                    @error('inactive_date')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>


                                            <!-- Divider -->
                                            <div class="col-12">
                                                <hr>
                                                <h5 class="mb-3">Employees</h5>
                                            </div>

                                            <!-- Dynamic Employee -->
                                            <div class="col-12">
                                                <div id="employee-wrapper"></div>

                                                <button type="button" class="btn btn-primary btn-sm mt-2"
                                                    id="add-employee">
                                                    <i class="fas fa-plus"></i> Add Employee
                                                </button>
                                            </div>

                                        </div>

                                        <!-- Template -->
                                        <div id="employee-template" class="d-none">
                                            <div class="employee-item border rounded p-3 mb-3">
                                                <div class="row align-items-end">

                                                    <!-- Employee -->
                                                    <div class="col-md-4">
                                                        <div class="form-group mb-2">
                                                            <label>Choose Employee</label>
                                                            <select name="employees[__INDEX__][employee_id]"
                                                                class="form-control select2">
                                                                @foreach ($employees as $emp)
                                                                    <option value="{{ $emp->id }}">
                                                                        {{ $emp->employee_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <!-- Position -->
                                                    <div class="col-md-4">
                                                        <div class="form-group mb-2">
                                                            <label>Position</label>
                                                            <select name="employees[__INDEX__][position_id]"
                                                                class="form-control select2" required>
                                                                <option value="">Choose Position</option>
                                                                @foreach ($positions as $key => $value)
                                                                    <option value="{{ $key }}">
                                                                        {{ $value }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <!-- Salary -->
                                                    <div class="col-md-3">
                                                        <div class="form-group mb-2">
                                                            <label>Salary</label>
                                                            <input type="number" name="employees[__INDEX__][basic_salary]"
                                                                class="form-control" placeholder="Input salary">
                                                        </div>
                                                    </div>

                                                    <!-- Remove -->
                                                    <div class="col-md-1 text-right">
                                                        <button type="button" class="btn btn-danger btn-sm remove mt-4">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="sk_type_id" class="form-control-label">
                                                    <i class="fas fa-user"></i> {{ __('Sk Type') }}
                                                </label>
                                                <div>
                                                    <select name="sk_type_id"
                                                        class="form-control select2 @error('sk_type_id') is-invalid @enderror"required>
                                                        <option value="">Choose SK Types</option>
                                                        @foreach ($sktypes as $key => $value)
                                                            <option value="{{ $key }}"
                                                                {{ old('sk_type_id') == $key ? 'selected' : '' }}>
                                                                {{ $value }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('sk_type_id')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="alert alert-secondary mt-4" role="alert">
                                            <span class="text-dark">
                                                <strong>Important Note:</strong> <br>

                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-end mt-4">
                                            <a href="{{ route('SkLetters') }}" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> {{ __('Cancel') }}
                                            </a>
                                            <button type="submit" id="create-btn" class="btn bg-primary">
                                                <i class="fas fa-save"></i> {{ __('Create') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('node_modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js') }}"></script>
    <script>
        document.getElementById('create-btn').addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are You Sure?',
                text: "Make sure the data you entered is correct!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Assign!',
                cancelButtonText: 'Abort'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('position-create').submit();
                }
            });
        });
    </script>
    <script>
        @if (session('success'))
            Swal.fire({
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonText: 'OK'
            });
        @endif
        @if (session('error'))
            Swal.fire({
                title: 'Gagal!',
                text: "{{ session('error') }}",
                icon: 'error',
                confirmButtonText: 'OK'
            });
        @endif
    </script>
    <script>
        let empIndex = 0;

        document.getElementById('add-employee').onclick = function() {
            let template = document.getElementById('employee-template').innerHTML;
            template = template.replace(/__INDEX__/g, empIndex++);
            document.getElementById('employee-wrapper').insertAdjacentHTML('beforeend', template);
        };

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove')) {
                e.target.closest('.employee-item').remove();
            }
        });

        // MENIMBANG
        document.getElementById('add-menimbang').onclick = function() {
            let template = document.getElementById('menimbang-template').innerHTML;
            document.getElementById('menimbang-wrapper').insertAdjacentHTML('beforeend', template);
        };

        // MENGINGAT
        document.getElementById('add-mengingat').onclick = function() {
            let template = document.getElementById('mengingat-template').innerHTML;
            document.getElementById('mengingat-wrapper').insertAdjacentHTML('beforeend', template);
        };
    </script>
@endpush --}}
@extends('layouts.app')
@section('title', 'Create SK Letter')

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

    <style>
        .section-card {
            border-left: 3px solid #6777ef;
            padding-left: 15px;
            margin-bottom: 10px;
        }
        .section-card-title {
            font-weight: 700;
            font-size: 13px;
            color: #6777ef;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
        }
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
        .salary-fields {
            display: none;
        }
    </style>
@endpush

@section('main')
<div class="main-content">
<section class="section">

    <div class="section-header">
        <h1>Create SK Letter</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('SkLetters') }}">SK Letters</a></div>
            <div class="breadcrumb-item">Create</div>
        </div>
    </div>

    <div class="section-body">
    <form id="sk-form" action="{{ route('SkLetters.store') }}" method="POST">
    @csrf

    <div class="row">

        {{-- ========================= --}}
        {{-- KOLOM KIRI --}}
        {{-- ========================= --}}
        <div class="col-lg-8">

            {{-- Card: Informasi SK --}}
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-file-alt mr-2 text-primary"></i>Informasi SK</h4>
                </div>
                <div class="card-body">
                    <div class="row">

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
                                            {{ old('sk_type_id') == $key ? 'selected' : '' }}>
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
                                    Publishing Company<span class="text-danger">*</span>
                                </label>
                                <select name="company_id"
                                    class="form-control select2 @error('company_id') is-invalid @enderror"
                                    required>
                                    <option value="">Choose Company</option>
                                    @foreach($companies as $key => $value)
                                        <option value="{{ $key }}"
                                            {{ old('company_id') == $key ? 'selected' : '' }}>
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
                                    value="{{ old('title') }}"
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
                                    value="{{ old('effective_date', date('Y-m-d')) }}"
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
                                    value="{{ old('inactive_date') }}">
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
                                    value="{{ old('location', 'Denpasar') }}"
                                    placeholder="cth: Denpasar">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        {{-- <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Approver HR</label>
                                <select name="approver_1"
                                    class="form-control select2 @error('approver_1') is-invalid @enderror">
                                    <option value="">-- Pilih --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}"
                                            {{ old('approver_1') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->employee_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('approver_1')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div> --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Approver Director</label>
                                <select name="approver_2"
                                    class="form-control select2 @error('approver_2') is-invalid @enderror">
                                    <option value="">Choose</option>
                                    @foreach($employees_approver_2 as $empapp2)
                                        <option value="{{ $empapp2->id }}"
                                            {{ old('approver_2') == $empapp2->id ? 'selected' : '' }}>
                                            {{ $empapp2->employee_name }}
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
                                    @foreach($employees_approver_3 as $empapp3)
                                        <option value="{{ $empapp3->id }}"
                                            {{ old('approver_3') == $empapp3->id ? 'selected' : '' }}>
                                            {{ $empapp3->employee_name }}
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
                                    placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Card: Karyawan --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-users mr-2 text-primary"></i>Employee Data</h4>
                    <button type="button" class="btn btn-primary btn-sm" id="add-employee">
                        <i class="fas fa-plus mr-1"></i> Add Employees
                    </button>
                </div>
                <div class="card-body">
                    @error('employees')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                    <div id="employee-wrapper">
                        {{-- Employee rows will be added here --}}
                    </div>
                    <div id="empty-employee" class="text-center text-muted py-3">
                        <i class="fas fa-user-plus fa-2x mb-2 d-block"></i>
                       There are no employees yet. Click "Add Employee" to add one.
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
                        <label class="form-control-label">Text establish</label>
                        <textarea name="menetapkan_text" id="menetapkan_text"
                            class="form-control summernote @error('menetapkan_text') is-invalid @enderror">{{ old('menetapkan_text') }}</textarea>
                        @error('menetapkan_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                    <div id="keputusan-wrapper"></div>
                    <div id="empty-keputusan" class="text-center text-muted py-3">
                        <i class="fas fa-list fa-2x mb-2 d-block"></i>
                        There are no decision points yet.
                    </div>
                </div>
            </div>

        </div>

        {{-- ========================= --}}
        {{-- KOLOM KANAN --}}
        {{-- ========================= --}}
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
                    <div id="menimbang-wrapper"></div>
                    <div id="empty-menimbang" class="text-center text-muted py-3">
                        <small>There are no points to consider yet.</small>
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
                    <div id="mengingat-wrapper"></div>
                    <div id="empty-mengingat" class="text-center text-muted py-3">
                        <small>There are no points to consider yet.</small>
                    </div>
                </div>
            </div>

            {{-- Card: Action --}}
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" id="btn-submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save mr-1"></i> Save SK
                        </button>
                        <a href="{{ route('SkLetters') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </a>
                    </div>
                    <hr>
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        SK akan tersimpan dengan status <strong>Draft</strong>.
                        Nomor SK akan dibuat otomatis oleh sistem.
                    </small>
                </div>
            </div>

        </div>
    </div>

    </form>
    </div>
</section>
</div>

{{-- ========================= --}}
{{-- TEMPLATES --}}
{{-- ========================= --}}

{{-- Template: Employee Row --}}
<template id="employee-template">
    <div class="employee-item" data-index="__INDEX__">
        <div class="item-number">Employee <span class="num"> __NUM__ </span></div>
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
                                {{ $s->submissionposition->positionRelation->name ?? '-' }} - {{ $s->submissionposition->company->name ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            {{-- <div class="col-md-4 salary-field">
                <div class="form-group">
                    <label>Gaji Pokok</label>
                    <input type="number" name="employees[__INDEX__][basic_salary]"
                        class="form-control" placeholder="0" min="0">
                </div>
            </div> --}}
            <div class="col-md-4 salary-field">
    <div class="form-group">
        <label>Gaji Pokok</label>

        <input type="text"
            name="employees[__INDEX__][basic_salary]"
            class="form-control rupiah"
            placeholder="0">
    </div>
</div>
            <div class="col-md-4 salary-field">
                <div class="form-group">
                    <label>Tunjangan Jabatan</label>
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
                    <label>Catatan</label>
                    <input type="text" name="employees[__INDEX__][notes]"
                        class="form-control" placeholder="Catatan untuk karyawan ini...">
                </div>
            </div>
        </div>
    </div>
</template>

{{-- Template: Menimbang --}}
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

{{-- Template: Mengingat --}}
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

{{-- Template: Keputusan --}}
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
    $(document).on('input', '.rupiah', function () {

        let value = $(this).val().replace(/[^,\d]/g, '');

        let split = value.split(',');
        let sisa = split[0].length % 3;

        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;

        $(this).val(rupiah);
    });
</script>
<script>
    
$(document).ready(function () {

    // ─── Summernote ───
    $('#menetapkan_text').summernote({
        height: 150,
        toolbar: [
            ['style', ['bold', 'italic', 'underline']],
            ['para', ['ul', 'ol']],
            ['insert', ['link']],
            ['view', ['fullscreen', 'codeview']]
        ]
    });

    // ─── Select2 global ───
    $('.select2').select2({ width: '100%' });

    // ─── SK Type change → tampilkan/sembunyikan salary fields ───
    // Data flag dari backend
    const skTypeFlags = @json($skTypeFlags);

    $('#sk_type_id').on('change', function () {
        const id = $(this).val();
        const flag = skTypeFlags[id] ?? {};
        if (flag.affects_salary) {
            $('.salary-field').show();
        } else {
            $('.salary-field').hide();
            $('.salary-field input').val('');
        }
    });

    // ─── Employee ───
    let empIndex = 0;

    function updateEmptyEmployee() {
        const count = $('#employee-wrapper .employee-item').length;
        $('#empty-employee').toggle(count === 0);
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

        // Sembunyikan salary jika sk_type tidak affects_salary
        const skTypeId = $('#sk_type_id').val();
        const flag = skTypeFlags[skTypeId] ?? {};
        if (!flag.affects_salary) {
            $item.find('.salary-field').hide();
        }

        $('#employee-wrapper').append($item);
        initSelect2InEmployee($item);
        empIndex++;
        updateEmptyEmployee();
    });

    $(document).on('click', '.remove-employee', function () {
        $(this).closest('.employee-item').remove();
        updateEmptyEmployee();
    });

    updateEmptyEmployee();

    // ─── Menimbang ───
    function updateEmptyMenimbang() {
        const count = $('#menimbang-wrapper .menimbang-item').length;
        $('#empty-menimbang').toggle(count === 0);
    }

    $('#add-menimbang').on('click', function () {
        const html = document.getElementById('menimbang-template').innerHTML;
        $('#menimbang-wrapper').append(html);
        updateEmptyMenimbang();
    });

    $(document).on('click', '.remove-menimbang', function () {
        $(this).closest('.menimbang-item').remove();
        updateEmptyMenimbang();
    });

    updateEmptyMenimbang();

    // ─── Mengingat ───
    function updateEmptyMengingat() {
        const count = $('#mengingat-wrapper .mengingat-item').length;
        $('#empty-mengingat').toggle(count === 0);
    }

    $('#add-mengingat').on('click', function () {
        const html = document.getElementById('mengingat-template').innerHTML;
        $('#mengingat-wrapper').append(html);
        updateEmptyMengingat();
    });

    $(document).on('click', '.remove-mengingat', function () {
        $(this).closest('.mengingat-item').remove();
        updateEmptyMengingat();
    });

    updateEmptyMengingat();

    // ─── Keputusan ───
    function updateEmptyKeputusan() {
        const count = $('#keputusan-wrapper .keputusan-item').length;
        $('#empty-keputusan').toggle(count === 0);
    }

    $('#add-keputusan').on('click', function () {
        const html = document.getElementById('keputusan-template').innerHTML;
        $('#keputusan-wrapper').append(html);
        updateEmptyKeputusan();
    });

    $(document).on('click', '.remove-keputusan', function () {
        $(this).closest('.keputusan-item').remove();
        updateEmptyKeputusan();
    });

    updateEmptyKeputusan();

    // ─── Submit dengan konfirmasi ───
    $('#btn-submit').on('click', function () {
        // Validasi minimal 1 karyawan
        if ($('#employee-wrapper .employee-item').length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: 'Minimal harus ada 1 karyawan dalam SK ini.',
            });
            return;
        }

        Swal.fire({
            title: 'Simpan SK?',
            text: 'SK akan disimpan dengan status Draft. Nomor SK dibuat otomatis.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#6777ef',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#sk-form').submit();
            }
        });
    });

    // ─── Session alerts ───
    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}" });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Gagal!', text: "{{ session('error') }}" });
    @endif

});
</script>
@endpush