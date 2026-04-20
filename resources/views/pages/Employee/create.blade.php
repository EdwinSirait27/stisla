{{-- @extends('layouts.app')
@section('title', 'Create Employee')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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

        /* Additional CSS for improved styling */
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
                <h1>Create Employee</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('pages.Employee') }}">Employees</a></div>
                    <div class="breadcrumb-item">Create Employees</div>
                </div>
            </div>
            <div class="section-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0 px-3">
                                    <h6 class="mb-0">{{ __('Create Employee') }}</h6>
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
                                    <form id="employee-create" action="{{ route('Employee.store') }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="employee_name" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Full Name') }}
                                                    </label>
                                                    <div>
                                                        <input type="text"
                                                            class="form-control @error('employee_name') is-invalid @enderror"
                                                            id="employee_name" name="employee_name"
                                                            value="{{ old('employee_name') }}" required
                                                            placeholder="Cristopher Edwin Sirait, S.Kom.">
                                                        @error('employee_name')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="nik" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('NIK') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control @error('nik') is-invalid @enderror"
                                                            value="{{ old('nik', $employee->Employee->nik ?? '') }}"
                                                            type="number" id="nik" name="nik"
                                                            value="{{ old('nik') }}" aria-describedby="info-nik"
                                                            maxlength="30" placeholder="Insert NIK" required>
                                                        @error('nik')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="religion" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Religion') }}
                                                    </label>
                                                    <div>
                                                        <select name="religion"
                                                            class="form-control select2 @error('religion') is-invalid @enderror"
                                                            required>
                                                            <option value="">Choose Religion</option>
                                                            @foreach ($status_religion as $value)
                                                                <option value="{{ $value }}"
                                                                    {{ old('religion') == $value ? 'selected' : '' }}>
                                                                    {{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('religion')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="gender" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Gender') }}
                                                    </label>
                                                    <div>
                                                        <select name="gender"
                                                            class="form-control select2 @error('gender') is-invalid @enderror"
                                                            required>
                                                            <option value="">Choose Gender</option>
                                                            @foreach ($status_gender as $value)
                                                                <option value="{{ $value }}"
                                                                    {{ old('gender') == $value ? 'selected' : '' }}>
                                                                    {{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('gender')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="date_of_birth" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Date of Birth') }}
                                                    </label>
                                                    <div>
                                                        <input
                                                            class="form-control @error('date_of_birth') is-invalid @enderror"
                                                            value="{{ old('date_of_birth', $employee->Employee->date_of_birth ?? '') }}"
                                                            type="date" id="date_of_birth" name="date_of_birth"
                                                            value="{{ old('date_of_birth') }}"
                                                            aria-describedby="info-date_of_birth" required>
                                                        @error('date_of_birth')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="place_of_birth" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Place of Birth') }}
                                                    </label>
                                                    <div>
                                                        <input
                                                            class="form-control @error('place_of_birth') is-invalid @enderror"
                                                            value="{{ old('place_of_birth', $employee->Employee->place_of_birth ?? '') }}"
                                                            type="text" id="place_of_birth" name="place_of_birth"
                                                            value="{{ old('place_of_birth') }}"
                                                            aria-describedby="info-place_of_birth"
                                                            placeholder="Place of Birth" required>
                                                        @error('place_of_birth')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="biological_mother_name" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Mothers Name') }}
                                                    </label>
                                                    <div>
                                                        <input
                                                            class="form-control @error('biological_mother_name') is-invalid @enderror"
                                                            value="{{ old('biological_mother_name', $employee->Employee->biological_mother_name ?? '') }}"
                                                            type="text" id="biological_mother_name"
                                                            name="biological_mother_name"
                                                            value="{{ old('biological_mother_name') }}"
                                                            aria-describedby="info-biological_mother_name"
                                                            placeholder="Eva" required>
                                                        @error('biological_mother_name')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="current_address" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Current Address') }}
                                                    </label>
                                                    <div>
                                                        <input
                                                            class="form-control @error('current_address') is-invalid @enderror"
                                                            value="{{ old('current_address', $employee->Employee->current_address ?? '') }}"
                                                            type="text" id="current_address" name="current_address"
                                                            value="{{ old('current_address') }}"
                                                            aria-describedby="info-current_address"
                                                            placeholder="jalan dip no.9 sumerta" required>
                                                        @error('current_address')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="id_card_address" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Id Card Address') }}
                                                    </label>
                                                    <div>
                                                        <input
                                                            class="form-control @error('id_card_address') is-invalid @enderror"
                                                            value="{{ old('id_card_address', $employee->Employee->id_card_address ?? '') }}"
                                                            type="text" id="id_card_address" name="id_card_address"
                                                            value="{{ old('id_card_address') }}"
                                                            aria-describedby="info-id_card_address"
                                                            placeholder="Id Card Address" required>
                                                        @error('id_card_address')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="last_education" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Last Education') }}
                                                    </label>
                                                    <div>
                                                        <select name="last_education"
                                                            class="form-control select2 @error('last_education') is-invalid @enderror"
                                                            required>
                                                            <option value="">Choose Education</option>
                                                            @foreach ($status_last_education as $value)
                                                                <option value="{{ $value }}"
                                                                    {{ old('last_education') == $value ? 'selected' : '' }}>
                                                                    {{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('last_education')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="institution" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Institution') }}
                                                    </label>
                                                    <div>
                                                        <input
                                                            class="form-control @error('institution') is-invalid @enderror"
                                                            value="{{ old('institution', $employee->Employee->institution ?? '') }}"
                                                            type="text" id="institution" name="institution"
                                                            value="{{ old('institution') }}"
                                                            aria-describedby="info-institution"
                                                            placeholder="Udayana University" required>
                                                        @error('institution')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="marriage" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Status Marriage') }}
                                                    </label>
                                                    <div>
                                                        <select name="marriage"
                                                            class="form-control select2 @error('marriage') is-invalid @enderror"required>
                                                            <option value="">Choose Status Marriage</option>
                                                            @foreach ($status_marriage as $value)
                                                                <option value="{{ $value }}"
                                                                    {{ old('marriage') == $value ? 'selected' : '' }}>
                                                                    {{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('marriage')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="child" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Child') }}
                                                    </label>
                                                    <div>
                                                        <select name="child"
                                                            class="form-control select2 @error('child') is-invalid @enderror"
                                                            required>
                                                            <option value="">How Many?</option>
                                                            @foreach ($status_child as $value)
                                                                <option value="{{ $value }}"
                                                                    {{ old('child') == $value ? 'selected' : '' }}>
                                                                    {{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('child')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="emergency_contact_name" class="form-control-label">
                                                        <i class="fas fa-id-card"></i>
                                                        {{ __('Emergency Contact Name & Number') }}
                                                    </label>
                                                    <div>
                                                        <input
                                                            class="form-control @error('emergency_contact_name') is-invalid @enderror"
                                                            value="{{ old('emergency_contact_name', $employee->Employee->emergency_contact_name ?? '') }}"
                                                            type="text" id="emergency_contact_name"
                                                            name="emergency_contact_name"
                                                            value="{{ old('emergency_contact_name') }}"
                                                            aria-describedby="info-emergency_contact_name"
                                                            placeholder="(ibu) 081248124xxx)" required>
                                                        @error('emergency_contact_name')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="email" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Email') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control @error('email') is-invalid @enderror"
                                                            value="{{ old('email', $employee->Employee->email ?? '') }}"
                                                            type="email" id="email" name="email"
                                                            value="{{ old('email') }}" aria-describedby="info-email"
                                                            placeholder="drummer@gmail.com" required>
                                                        @error('email')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="telp_number" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Telephone Number') }}
                                                    </label>
                                                    <div>
                                                        <input
                                                            class="form-control @error('telp_number') is-invalid @enderror"
                                                            value="{{ old('telp_number', $employee->Employee->telp_number ?? '') }}"
                                                            type="number" id="telp_number" name="telp_number"
                                                            aria-describedby="info-telp_number" maxlength="30"
                                                            placeholder="Insert Telephone Number" required>
                                                        @error('telp_number')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="bpjs_kes" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('BPJS Kes') }}
                                                    </label>
                                                    <div>
                                                        <input
                                                            class="form-control @error('bpjs_kes') is-invalid @enderror"
                                                            value="{{ old('bpjs_kes', $employee->Employee->bpjs_kes ?? '') }}"
                                                            type="number" id="bpjs_kes" name="bpjs_kes"
                                                            value="{{ old('bpjs_kes') }}"
                                                            aria-describedby="info-bpjs_kes" placeholder="746842xxx"
                                                            required>
                                                        @error('bpjs_kes')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="bpjs_ket" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('BPJS Ket') }}
                                                    </label>
                                                    <div>
                                                        <input
                                                            class="form-control @error('bpjs_ket') is-invalid @enderror"
                                                            value="{{ old('bpjs_ket', $employee->Employee->bpjs_ket ?? '') }}"
                                                            type="number" id="bpjs_ket" name="bpjs_ket"
                                                            value="{{ old('bpjs_ket') }}"
                                                            aria-describedby="info-bpjs_ket" placeholder="0239493xxx"
                                                            required>
                                                        @error('bpjs_ket')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="banks_id" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Bank Name') }}
                                                    </label>
                                                    <div>
                                                        <select name="banks_id"
                                                            class="form-control select2 @error('banks_id') is-invalid @enderror"required>
                                                            <option value="">Choose Banks</option>
                                                            @foreach ($banks as $key => $value)
                                                                <option value="{{ $key }}"
                                                                    {{ old('banks_id') == $key ? 'selected' : '' }}>
                                                                    {{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('banks_id')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="bank_account_number" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Bank Account Number') }}
                                                    </label>
                                                    <div>
                                                        <input
                                                            class="form-control @error('bank_account_number') is-invalid @enderror"
                                                            value="{{ old('bank_account_number', $employee->Employee->bank_account_number ?? '') }}"
                                                            type="text" id="bank_account_number"
                                                            name="bank_account_number"
                                                            value="{{ old('bank_account_number') }}"
                                                            aria-describedby="info-bank_account_number"
                                                            placeholder="bank account number">
                                                        @error('bank_account_number')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="company_id" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Companies Name') }}
                                                    </label>
                                                    <div>
                                                        <select name="company_id"
                                                            class="form-control select2 @error('company_id') is-invalid @enderror"required>
                                                            <option value="">Choose Companies</option>
                                                            @foreach ($companys as $key => $value)
                                                                <option value="{{ $key }}"
                                                                    {{ old('company_id') == $key ? 'selected' : '' }}>
                                                                    {{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('company_id')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="department_id" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Department') }}
                                                    </label>
                                                    <div>
                                                        <select name="department_id"
                                                            class="form-control select2 @error('department_id') is-invalid @enderror"
                                                            required>
                                                            <option value="">Choose Departments</option>
                                                            @foreach ($departments as $key => $value)
                                                                <option value="{{ $key }}"
                                                                    {{ old('department_id') == $key ? 'selected' : '' }}>
                                                                    {{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('department_id')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="position_id" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Position') }}
                                                    </label>
                                                    <div>
                                                        <select name="position_id"
                                                            class="form-control select2 @error('position_id') is-invalid @enderror"
                                                            required>
                                                            <option value="">Choose Position</option>
                                                            @foreach ($positions as $key => $value)
                                                                <option value="{{ $key }}"
                                                                    {{ old('position_id') == $key ? 'selected' : '' }}>
                                                                    {{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('position_id')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="store_id" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Location') }}
                                                    </label>
                                                    <div>
                                                        <select name="store_id"
                                                            class="form-control select2 @error('store_id') is-invalid @enderror"required>
                                                            <option value="">Choose Location</option>
                                                            @foreach ($stores as $key => $value)
                                                                <option value="{{ $key }}"
                                                                    {{ old('store_id') == $key ? 'selected' : '' }}>
                                                                    {{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('store_id')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="status_employee" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Status Employee') }}
                                                    </label>
                                                    <div>
                                                        <select name="status_employee"
                                                            class="form-control select2 @error('status_employee') is-invalid @enderror"required>
                                                            <option value="">Choose Status Employee</option>
                                                            @foreach ($status_employee as $value)
                                                                <option value="{{ $value }}"
                                                                    {{ old('status_employee') == $value ? 'selected' : '' }}>
                                                                    {{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('status_employee')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="join_date" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Join Date') }}
                                                    </label>
                                                    <div>
                                                        <input
                                                            class="form-control @error('join_date') is-invalid @enderror"
                                                            value="{{ old('join_date', $employee->Employee->join_date ?? '') }}"
                                                            type="date" id="join_date" name="join_date"
                                                            value="{{ old('join_date') }}"
                                                            aria-describedby="info-join_date" required>
                                                        @error('join_date')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="npwp" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('NPWP') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control @error('npwp') is-invalid @enderror"
                                                            value="{{ old('npwp', $employee->Employee->npwp ?? '') }}"
                                                            type="text" id="npwp" name="npwp"
                                                            value="{{ old('npwp') }}" aria-describedby="info-npwp"
                                                            placeholder="83.283.23.43.2.234" required>
                                                        @error('npwp')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check mt-2">
                                                    <input type="checkbox" name="is_manager" id="is_manager"
                                                        value="1"
                                                        class="form-check-input @error('is_manager') is-invalid @enderror"
                                                        {{ old('is_manager') ? 'checked' : '' }}>
                                                    <label for="is_manager" class="form-check-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Is Manager') }}
                                                    </label>
                                                    @error('is_manager')
                                                        <span class="invalid-feedback d-block" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                         <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="company_email" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Company Email') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control @error('company_email') is-invalid @enderror"
                                                            value="{{ old('company_email', $employee->Employee->company_email ?? '') }}"
                                                            type="email" id="company_email" name="company_email"
                                                            value="{{ old('company_email') }}" aria-describedby="info-company_email"
                                                            placeholder="drummer@gmail.com" nullable>
                                                        @error('company_email')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="photos" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Images') }}
                                                    </label>
                                                    <div class="mb-2">
                                                        @if (!empty($employee->Employee?->photos))
                                                            <img id="preview-image"
                                                                src="{{ asset('storage/employeephotos/' . $employee->Employee->photos) }}"
                                                                alt="Preview" class="img-thumbnail" width="150"
                                                                style="cursor:pointer" onclick="showImageSwal(this.src)">
                                                        @else
                                                            <img id="preview-image" src="https://via.placeholder.com/150"
                                                                alt="Preview" class="img-thumbnail" width="150"
                                                                style="cursor:pointer" onclick="showImageSwal(this.src)">
                                                        @endif
                                                        <input type="file" name="photos" id="photos"
                                                            class="form-control @error('photos') is-invalid @enderror"
                                                            accept="image/*" onchange="previewImage(event)">

                                                        @error('photos')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="alert alert-secondary mt-4" role="alert">
                                            <span class="text-dark">
                                                <strong>Important Note:</strong> <br>
                                                - please use English to get used to it.<br>
                                                - Before creating data, please check first whether there is already similar
                                                or
                                                identical data to avoid double input.<br>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-end mt-4">
                                            <a href="{{ route('pages.Employee') }}" class="btn btn-secondary">
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
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
        function previewImage(event) {
            const input = event.target;
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('preview-image').src = reader.result;
            };
            if (input.files && input.files[0]) {
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
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
                    document.getElementById('employee-create').submit();
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
@endpush --}}
@extends('layouts.app')
@section('title', 'Create Employee')

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        /* ─── Page header ────────────────────────────────────── */
        .section-header h1 {
            font-size: 1.35rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        /* ─── Card shell ─────────────────────────────────────── */
        .emp-form-card {
            border: none;
            border-radius: .625rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.07);
            background: #fff;
            overflow: hidden;
        }

        /* ─── Card header ────────────────────────────────────── */
        .emp-form-header {
            padding: .875rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            background: #f8fafc;
            display: flex;
            align-items: center;
            gap: .625rem;
        }

        .emp-form-header-icon {
            width: 30px;
            height: 30px;
            background: #eff6ff;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            color: #1d4ed8;
            flex-shrink: 0;
        }

        .emp-form-header-title {
            font-size: .9rem;
            font-weight: 600;
            color: #334155;
            flex: 1;
        }

        .emp-name-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eff6ff;
            border-radius: 20px;
            padding: .2rem .625rem .2rem .25rem;
        }

        .emp-name-pill-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #1d4ed8;
            color: #fff;
            font-size: .6rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .emp-name-pill span {
            font-size: .75rem;
            font-weight: 600;
            color: #1e40af;
        }

        /* ─── Section groups ─────────────────────────────────── */
        .form-body {
            padding: 1.5rem;
        }

        .form-section {
            margin-bottom: 1.5rem;
        }

        .form-section-label {
            font-size: .67rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #94a3b8;
            margin-bottom: .875rem;
            display: flex;
            align-items: center;
            gap: .625rem;
        }

        .form-section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #f1f5f9;
        }

        /* ─── Field grid ─────────────────────────────────────── */
        .field-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: .75rem;
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: .3rem;
        }

        .field-group label {
            font-size: .72rem;
            font-weight: 600;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 0;
        }

        .field-group label i {
            font-size: .67rem;
            color: #94a3b8;
            width: 12px;
            text-align: center;
        }

        .field-group .form-control {
            height: 38px;
            font-size: .825rem;
            border: 1px solid #e2e8f0;
            border-radius: .5rem;
            color: #1e293b;
            padding: 0 .75rem;
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
        }

        .field-group .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,.1);
            outline: none;
        }

        .field-group .form-control.is-invalid {
            border-color: #ef4444;
        }

        .field-group select.form-control {
            height: 38px;
            cursor: pointer;
        }

        .field-group .invalid-feedback {
            font-size: .7rem;
            color: #dc2626;
            margin-top: .2rem;
        }

        /* Select2 override */
        .field-group .select2-container .select2-selection--single {
            height: 38px !important;
            border-color: #e2e8f0 !important;
            border-radius: .5rem !important;
        }

        .field-group .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
            font-size: .825rem;
            color: #1e293b;
        }

        .field-group .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }

        /* ─── Conditional status fields ──────────────────────── */
        .conditional-section {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: .5rem;
            padding: .875rem 1rem;
            margin-top: .875rem;
            display: none;
        }

        .conditional-section.visible {
            display: block;
        }

        .conditional-section-label {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #92400e;
            margin-bottom: .625rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .conditional-section-label i {
            font-size: .65rem;
        }

        /* ─── Photo upload ───────────────────────────────────── */
        .photo-upload-wrap {
            display: flex;
            align-items: center;
            gap: .875rem;
        }

        .photo-thumb {
            width: 60px;
            height: 60px;
            border-radius: .5rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            cursor: pointer;
        }

        .photo-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-thumb i {
            color: #cbd5e1;
            font-size: 1.5rem;
        }

        .photo-upload-hint {
            font-size: .7rem;
            color: #94a3b8;
            margin-bottom: 5px;
        }

        .photo-upload-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 32px;
            padding: 0 12px;
            border: 1px dashed #cbd5e1;
            border-radius: .5rem;
            background: #f8fafc;
            color: #64748b;
            font-size: .775rem;
            font-weight: 500;
            cursor: pointer;
            transition: all .15s;
        }

        .photo-upload-btn:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            background: #eff6ff;
        }

        /* ─── Alerts ─────────────────────────────────────────── */
        .alert-danger-custom {
            padding: .75rem 1rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: .5rem;
            font-size: .8rem;
            color: #991b1b;
            margin-bottom: 1.25rem;
        }

        .alert-danger-custom ul { margin: 0; padding-left: 1rem; }

        .alert-success-custom {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: .625rem 1rem;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: .5rem;
            font-size: .8rem;
            color: #166534;
            margin-bottom: 1.25rem;
        }

        /* ─── Note box ───────────────────────────────────────── */
        .note-box {
            display: flex;
            align-items: flex-start;
            gap: .625rem;
            padding: .75rem 1rem;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: .5rem;
            font-size: .78rem;
            color: #64748b;
            margin-top: 1.25rem;
        }

        .note-box i {
            font-size: .75rem;
            color: #94a3b8;
            margin-top: .15rem;
            flex-shrink: 0;
        }

        /* ─── Card footer ────────────────────────────────────── */
        .emp-form-footer {
            padding: .875rem 1.25rem;
            border-top: 1px solid #f1f5f9;
            background: #fafafa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .emp-form-footer .btn {
            height: 38px;
            font-size: .825rem;
            font-weight: 500;
            padding: 0 1.125rem;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border-radius: .5rem;
        }

        .btn-back {
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #475569;
        }

        .btn-back:hover { background: #f8fafc; color: #1e293b; }

        .btn-save {
            background: #1d4ed8;
            border: none;
            color: #fff;
        }

        .btn-save:hover { background: #1e40af; color: #fff; }

        /* ─── Responsive ─────────────────────────────────────── */
        @media (max-width: 576px) {
            .form-body { padding: 1rem; }
            .emp-form-footer { padding: .75rem 1rem; }
            .field-grid { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div>
                <div style="font-size:.72rem;color:#94a3b8;margin-bottom:3px">
                    Dashboard /
                    <a href="{{ route('pages.Employee') }}" style="color:#64748b;text-decoration:none">Employees</a> /
                    <span style="color:#1e293b">Create</span>
                </div>
                <h1>Create employee</h1>
            </div>
            <div class="section-header-breadcrumb" style="display:none">{{-- hidden, handled above --}}</div>
        </div>

        <div class="section-body">
            <div class="emp-form-card">

                {{-- Card header --}}
                <div class="emp-form-header">
                    <div class="emp-form-header-icon">
                        <i class="fas fa-user-pen"></i>
                    </div>
                    <span class="emp-form-header-title">Create employee data</span>
                    <div class="emp-name-pill">
                        <div class="emp-name-pill-avatar">
                            {{-- {{ collect(explode(' ', $employee->employee->employee_name ?? 'U'))
                                ->take(2)->map(fn($w) => strtoupper($w[0]))->implode('') }} --}}
                        </div>
                        {{-- <span>{{ $employee->employee->employee_name ?? 'Employee' }}</span> --}}
                    </div>
                </div>

               <form id="employee-create" action="{{ route('Employee.store') }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf

                    <div class="form-body">

                        {{-- Flash messages --}}
                        @if ($errors->any())
                            <div class="alert-danger-custom">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert-success-custom">
                                <i class="fas fa-check-circle"></i>
                                {{ session('success') }}
                            </div>
                        @endif

                        {{-- ── Section 1: Personal information ── --}}
                        <div class="form-section">
                            <div class="form-section-label">Personal information</div>
                            <div class="field-grid">
                                <div class="field-group">
                                    <label><i class="fas fa-user"></i> Employee Name</label>
                                    <input type="text" name="employee_name" id="employee_name"
                                        class="form-control @error('employee_name') is-invalid @enderror"
                                        value="{{ old('employee_name') }}"
                                        placeholder="Employee Name" required>
                                    @error('employee_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-id-card"></i> NIK</label>
                                    <input type="number" name="nik" id="nik"
                                        class="form-control @error('nik') is-invalid @enderror"
                                        value="{{ old('nik') }}"
                                        placeholder="NIK" maxlength="30" required>
                                    @error('nik')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-mosque"></i> Religion</label>
                                    <select name="religion" class="form-control @error('religion') is-invalid @enderror" required>
                                        <option value="">-- Choose religion --</option>
                                        @foreach ($status_religion as $value)
                                            <option value="{{ $value }}"
                                                {{ old('religion') == $value ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('religion')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-venus-mars"></i> Gender</label>
                                    <select name="gender" class="form-control @error('gender') is-invalid @enderror" required>
                                        <option value="">-- Choose gender --</option>
                                        @foreach ($status_gender as $value)
                                            <option value="{{ $value }}"
                                                {{ old('gender') == $value ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('gender')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-map-marker-alt"></i> Place of birth</label>
                                    <input type="text" name="place_of_birth"
                                        class="form-control @error('place_of_birth') is-invalid @enderror"
                                        value="{{ old('place_of_birth') }}"
                                        placeholder="Place of birth" required>
                                    @error('place_of_birth')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-calendar"></i> Date of birth</label>
                                    <input type="date" name="date_of_birth"
                                        class="form-control @error('date_of_birth') is-invalid @enderror"
                                        value="{{ old('date_of_birth') }}"
                                        required>
                                    @error('date_of_birth')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-user"></i> Biological mother name</label>
                                    <input type="text" name="biological_mother_name"
                                        class="form-control @error('biological_mother_name') is-invalid @enderror"
                                        value="{{ old('biological_mother_name') }}"
                                        placeholder="Biological mother name" required>
                                    @error('biological_mother_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-ring"></i> Marital status</label>
                                    <select name="marriage" class="form-control @error('marriage') is-invalid @enderror" required>
                                        <option value="">-- Choose marital status --</option>
                                        @foreach ($status_marriage as $value)
                                            <option value="{{ $value }}"
                                                {{ old('marriage') == $value ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('marriage')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-baby"></i> Child</label>
                                    <select name="child" class="form-control @error('child') is-invalid @enderror" required>
                                        <option value="">-- Number of children --</option>
                                        @foreach ($status_child as $value)
                                            <option value="{{ $value }}"
                                                {{ old('child') == $value ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('child')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-phone-volume"></i> Emergency contact</label>
                                    <input type="text" name="emergency_contact_name"
                                        class="form-control @error('emergency_contact_name') is-invalid @enderror"
                                        value="{{ old('emergency_contact_name') }}"
                                        placeholder="e.g. (Mother) 081234567890" required>
                                    @error('emergency_contact_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-home"></i> Current address</label>
                                    <input type="text" name="current_address"
                                        class="form-control @error('current_address') is-invalid @enderror"
                                        value="{{ old('current_address') }}"
                                        placeholder="Current address" required>
                                    @error('current_address')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-id-card"></i> ID card address</label>
                                    <input type="text" name="id_card_address"
                                        class="form-control @error('id_card_address') is-invalid @enderror"
                                        value="{{ old('id_card_address') }}"
                                        placeholder="ID card address" required>
                                    @error('id_card_address')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-graduation-cap"></i> Last education</label>
                                    <select name="last_education" class="form-control @error('last_education') is-invalid @enderror" required>
                                        <option value="">-- Choose education --</option>
                                        @foreach ($status_last_education as $value)
                                            <option value="{{ $value }}"
                                                {{ old('last_education') == $value ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('last_education')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-university"></i> Institution</label>
                                    <input type="text" name="institution"
                                        class="form-control @error('institution') is-invalid @enderror"
                                        value="{{ old('institution') }}"
                                        placeholder="Institution" required>
                                    @error('institution')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        {{-- ── Section 2: Employment ── --}}
                        <div class="form-section">
                            <div class="form-section-label">Employment</div>
                            <div class="field-grid">

                                <div class="field-group">
                                    <label><i class="fas fa-briefcase"></i>Company</label>
                                    <select name="company_id" id="company_id"
                                        class="form-control select2 @error('company_id') is-invalid @enderror">
                                        <option value="">-- Choose Company --</option>
                                         @foreach ($companys as $key => $value)
                                                                <option value="{{ $key }}"
                                                                    {{ old('company_id') == $key ? 'selected' : '' }}>
                                                                    {{ $value }}</option>
                                                            @endforeach
                                    </select>
                                    @error('company_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="field-group">
                                    <label><i class="fas fa-briefcase"></i>Department</label>
                                    <select name="department_id" id="department_id"
                                        class="form-control select2 @error('department_id') is-invalid @enderror">
                                        <option value="">-- Choose Department --</option>
                                        @foreach ($departments as $key => $value)
                                                                <option value="{{ $key }}"
                                                                    {{ old('department_id') == $key ? 'selected' : '' }}>
                                                                    {{ $value }}</option>
                                                            @endforeach
                                    </select>
                                    @error('department_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="field-group">
                                    <label><i class="fas fa-briefcase"></i> Position</label>
                                    <select name="position_id" id="position_id"
                                        class="form-control select2 @error('position_id') is-invalid @enderror">
                                        <option value="">-- Choose position --</option>
                                        {{-- @foreach ($positions as $position)
                                            <option value="{{ $position->id }}"
                                                {{ old('position_id') == $position->id ? 'selected' : '' }}>
                                                {{ $position->name }}
                                            </option>
                                        @endforeach --}}
                                         @foreach ($positions as $key => $value)
                                                                <option value="{{ $key }}"
                                                                    {{ old('position_id') == $key ? 'selected' : '' }}>
                                                                    {{ $value }}</option>
                                                            @endforeach
                                    </select>
                                    @error('position_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-store"></i> Location</label>
                                    <select name="store_id" id="store_id"
                                        class="form-control select2 @error('store_id') is-invalid @enderror">
                                        <option value="">-- Choose location --</option>
                                        {{-- @foreach ($stores as $store)
                                            <option value="{{ $store->id }}"
                                                {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                                {{ $store->name }}
                                            </option>
                                        @endforeach --}}
                                         @foreach ($stores as $key => $value)
                                                                <option value="{{ $key }}"
                                                                    {{ old('store_id') == $key ? 'selected' : '' }}>
                                                                    {{ $value }}</option>
                                                            @endforeach
                                        
                                    </select>
                                    @error('store_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-circle-dot"></i> Status employee</label>
                                    <select name="status_employee" class="form-control @error('status_employee') is-invalid @enderror" required>
                                        <option value="">-- Choose status --</option>
                                        @foreach ($status_employee as $value)
                                            <option value="{{ $value }}"
                                                {{ old('status_employee') == $value ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status_employee')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-calendar-check"></i> Join date</label>
                                    <input type="date" name="join_date"
                                        class="form-control @error('join_date') is-invalid @enderror"
                                        value="{{ old('join_date', isset($join_date) ? $join_date->format('Y-m-d') : '') }}">
                                    @error('join_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                             

                               

                                

                                <div class="field-group">
                                    <label><i class="fas fa-fingerprint"></i> Pin fingerspot</label>
                                    <input type="text" name="pin" id="pin"
                                        class="form-control @error('pin') is-invalid @enderror"
                                        value="{{ old('pin') }}"
                                        placeholder="Pin fingerspot"
                                        style="font-family:monospace">
                                    @error('pin')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                            </div>

                            
                        </div>

                        {{-- ── Section 3: Documents & identity numbers ── --}}
                        <div class="form-section">
                            <div class="form-section-label">Documents & identity numbers</div>
                            <div class="field-grid">

                                <div class="field-group">
                                    <label><i class="fas fa-file-invoice"></i> NPWP</label>
                                    <input type="text" name="npwp"
                                        class="form-control @error('npwp') is-invalid @enderror"
                                        value="{{ old('npwp') }}"
                                        placeholder="NPWP" required>
                                    @error('npwp')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-heart-pulse"></i> BPJS Kesehatan</label>
                                    <input type="text" name="bpjs_kes"
                                        class="form-control @error('bpjs_kes') is-invalid @enderror"
                                        value="{{ old('bpjs_kes') }}"
                                        placeholder="BPJS Kesehatan" required>
                                    @error('bpjs_kes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-shield-halved"></i> BPJS Ketenagakerjaan</label>
                                    <input type="text" name="bpjs_ket"
                                        class="form-control @error('bpjs_ket') is-invalid @enderror"
                                        value="{{ old('bpjs_ket') }}"
                                        placeholder="BPJS Ketenagakerjaan" required>
                                    @error('bpjs_ket')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        {{-- ── Section 4: Bank & contact ── --}}
                        <div class="form-section">
                            <div class="form-section-label">Bank & contact</div>
                            <div class="field-grid">

                                <div class="field-group">
                                    <label><i class="fas fa-building-columns"></i> Bank name</label>
                                   <select name="banks_id" class="form-control @error('banks_id') is-invalid @enderror">
   @foreach ($banks as $key => $value)
                                                                <option value="{{ $key }}"
                                                                    {{ old('banks_id') == $key ? 'selected' : '' }}>
                                                                    {{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                    @error('banks_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-credit-card"></i> Bank account number</label>
                                    <input type="text" name="bank_account_number"
                                        class="form-control @error('bank_account_number') is-invalid @enderror"
                                        value="{{ old('bank_account_number') }}"
                                        placeholder="Bank account number"
                                        style="font-family:monospace">
                                    @error('bank_account_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-envelope"></i> Personal email</label>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}"
                                        placeholder="Personal email" required>
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                               

                                <div class="field-group">
                                    <label><i class="fas fa-phone"></i> Phone number</label>
                                    <input type="number" name="telp_number"
                                        class="form-control @error('telp_number') is-invalid @enderror"
                                        value="{{ old('telp_number') }}"
                                        placeholder="Phone number" required>
                                    @error('telp_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        {{-- ── Section 5: Profile photo ── --}}
                        <div class="form-section">
                            <div class="form-section-label">Profile photo</div>
                            <div class="photo-upload-wrap">
                                {{-- <div class="photo-thumb" onclick="showImageSwal(document.getElementById('preview-image').src)">
                                    @if (!empty($employee->Employee->photos))
                                        <img id="preview-image"
                                             src="{{ asset('storage/' . $employee->Employee->photos) }}"
                                             alt="Preview">
                                    @else
                                        <img id="preview-image" src="https://via.placeholder.com/60"
                                             alt="No photo" style="display:none">
                                        <i class="fas fa-user" id="photo-placeholder"></i>
                                    @endif
                                </div> --}}
                                <div class="photo-thumb" onclick="showImageSwal(document.getElementById('preview-image').src)">
    <img id="preview-image" src="https://via.placeholder.com/60" alt="No photo">
    <i class="fas fa-user" id="photo-placeholder"></i>
</div>
                                <div>
                                    <div class="photo-upload-hint">
                                        JPG, PNG, or WEBP — max 2 MB. Click image to preview full size.
                                    </div>
                                    <label for="photos" class="photo-upload-btn">
                                        <i class="fas fa-arrow-up-from-bracket" style="font-size:.7rem"></i>
                                        Upload new photo
                                    </label>
                                    <input type="file" name="photos" id="photos"
                                        class="d-none @error('photos') is-invalid @enderror"
                                        accept="image/*" onchange="previewPhoto(event)">
                                    @error('photos')
                                        <div class="invalid-feedback d-block mt-1" style="font-size:.7rem">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Note --}}
                        <div class="note-box">
                            <i class="fas fa-circle-info"></i>
                            <div>
                                Leave <strong>Status</strong> unchanged if the employee is still active.
                                End date and reason fields appear automatically when status is set to
                                <em>Resign</em> or <em>On Leave</em>.
                                Please double-check for duplicate records before saving.
                            </div>
                        </div>

                    </div>{{-- /.form-body --}}

                    <div class="emp-form-footer">
                        <a href="{{ route('pages.Employee') }}" class="btn btn-back">
                            <i class="fas fa-arrow-left"></i> Back to employees
                        </a>
                        <button type="submit" class="btn btn-save">
                            <i class="fas fa-floppy-disk"></i> Save changes
                        </button>
                    </div>
                </form>

            </div>{{-- /.emp-form-card --}}
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    /* ── Photo preview ── */
    function previewPhoto(event) {
        const file = event.target.files[0];
        if (!file) return;
        const url  = URL.createObjectURL(file);
        const img  = document.getElementById('preview-image');
        const ph   = document.getElementById('photo-placeholder');
        img.src    = url;
        img.style.display = 'block';
        if (ph) ph.style.display = 'none';
    }

    function showImageSwal(src) {
        if (!src || src.includes('placeholder')) return;
        Swal.fire({ imageUrl: src, imageAlt: 'Employee photo', showConfirmButton: false });
    }

    $(function () {
        /* ── Select2 ── */
        $('.select2').select2({ width: '100%' });

        /* ── Conditional status fields ── */
        const INACTIVE_STATUSES = ['Resign', 'On Leave'];

        function toggleConditionalFields() {
            const val = $('#status').val();
            const $section = $('#conditional-status-fields');
            if (INACTIVE_STATUSES.includes(val)) {
                $section.addClass('visible');
            } else {
                $section.removeClass('visible');
              
            }
        }

        toggleConditionalFields();
        $('#status').on('change', toggleConditionalFields);

        /* ── Session flash ── */
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#1d4ed8',
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}',
                confirmButtonColor: '#dc2626'
            });
        @endif
    });
    </script>
@endpush