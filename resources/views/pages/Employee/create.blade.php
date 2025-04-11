@extends('layouts.app')
@section('title', 'Create Employee')
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

                                    <form action="{{ route('Employee.store') }}" method="POST">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="employee_name" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Full Name') }}
                                                    </label>
                                                    <div>
                                                        <input type="text" class="form-control" id="employee_name"
                                                            name="employee_name" value="{{ old('employee_name') }}" required
                                                            placeholder="Fill Full Name">
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
                                                        <input class="form-control"
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
                                                    <label for="position_id" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Position') }}
                                                    </label>
                                                    <div>
                                                        <select name="position_id" class="form-control" required>
                                                            <option value="">-- Choose Position --</option>
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
                                                        <i class="fas fa-id-card"></i> {{ __('Store') }}
                                                    </label>
                                                    <div>
                                                        <select name="store_id" class="form-control"required>
                                                            <option value="">-- Choose Store --</option>
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
                                                    <label for="department_id" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Department') }}
                                                    </label>
                                                    <div>
                                                        <select name="department_id" class="form-control"required>
                                                            <option value="">-- Choose Department --</option>
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
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="status_employee" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Status Employee') }}
                                                    </label>
                                                    <div>
                                                        <select name="status_employee" class="form-control"required>
                                                            <option value="">-- Choose Status Employee --</option>
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
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="join_date" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Join Date') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control"
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
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="marriage" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Status Marriage') }}
                                                    </label>
                                                    <div>
                                                        <select name="marriage" class="form-control"required>
                                                            <option value="">-- Choose Status Marriage --</option>
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
                                                        <select name="child" class="form-control" required>
                                                            <option value="">-- Choose Child --</option>
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
                                                    <label for="telp_number" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Telephone Number') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control"
                                                            value="{{ old('telp_number', $employee->Employee->telp_number ?? '') }}"
                                                            type="number" id="telp_number" name="telp_number"
                                                            value="{{ old('telp_number') }}"
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
                                                    <label for="gender" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Gender') }}
                                                    </label>
                                                    <div>
                                                        <select name="gender" class="form-control" required>
                                                            <option value="">-- Choose Gender --</option>
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
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="date_of_birth" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Date of Birth') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control"
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
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="place_of_birth" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Place of Birth') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control"
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
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="biological_mother_name" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Mothers Name') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control"
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
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="religion" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Religion') }}
                                                    </label>
                                                    <div>
                                                        <select name="religion" class="form-control" required>
                                                            <option value="">-- Choose Religion --</option>
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
                                                    <label for="current_address" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Current Address') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control"
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
                                                    <label for="status_last_education" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Id Card Address') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control"
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
                                                        <select name="last_education" class="form-control" required>
                                                            <option value="">-- Choose Education --</option>
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
                                                        <input class="form-control"
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
                                                        <label for="emergency_contact" class="form-control-label">
                                                            <i class="fas fa-id-card"></i>
                                                            {{ __('Emergency Contact Name & Number') }}
                                                        </label>
                                                        <div>
                                                            <input class="form-control"
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
                                                        <label for="npwp" class="form-control-label">
                                                            <i class="fas fa-id-card"></i> {{ __('NPWP') }}
                                                        </label>
                                                        <div>
                                                            <input class="form-control"
                                                                value="{{ old('npwp', $employee->Employee->npwp ?? '') }}"
                                                                type="number" id="npwp" name="npwp"
                                                                value="{{ old('npwp') }}" aria-describedby="info-npwp"
                                                                placeholder="0239493249" required>
                                                            @error('npwp')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="bpjs_kes" class="form-control-label">
                                                            <i class="fas fa-id-card"></i> {{ __('BPJS Kes') }}
                                                        </label>
                                                        <div>
                                                            <input class="form-control"
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
                                                </div>
                                                <div class="row mt-3">

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="bpjs_ket" class="form-control-label">
                                                            <i class="fas fa-id-card"></i> {{ __('BPJS KET') }}
                                                        </label>
                                                        <div>
                                                            <input class="form-control"
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

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="email" class="form-control-label">
                                                            <i class="fas fa-id-card"></i> {{ __('Email') }}
                                                        </label>
                                                        <div>
                                                            <input class="form-control"
                                                                value="{{ old('email', $employee->Employee->email ?? '') }}"
                                                                type="email" id="email" name="email"
                                                                value="{{ old('email') }}"
                                                                aria-describedby="info-email"
                                                                placeholder="drummer@gmail.com" required>
                                                            @error('email')
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
                                                        <label for="salary" class="form-control-label">
                                                            <i class="fas fa-id-card"></i> {{ __('Salary') }}
                                                        </label>
                                                        <div>
                                                            <input class="form-control"
                                                                value="{{ old('salary', $employee->Employee->salary ?? '') }}"
                                                                type="number" id="salary" name="salary"
                                                                value="{{ old('salary') }}"
                                                                aria-describedby="info-salary" placeholder="0239493xxx"
                                                                step="0.01" min="0" max="9999999999.99"
                                                                required>
                                                            @error('salary')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="house_allowance" class="form-control-label">
                                                            <i class="fas fa-id-card"></i> {{ __('House Allowance') }}
                                                        </label>
                                                        <div>
                                                            <input class="form-control"
                                                                value="{{ old('house_allowance', $employee->Employee->house_allowance ?? '') }}"
                                                                type="number" id="house_allowance"
                                                                name="house_allowance"
                                                                value="{{ old('house_allowance') }}"
                                                                aria-describedby="info-house_allowance"
                                                                placeholder="700000" step="0.01" min="0"
                                                                max="9999999999.99" required>
                                                            @error('house_allowance')
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
                                                        <label for="meal_allowance" class="form-control-label">
                                                            <i class="fas fa-id-card"></i> {{ __('Meal Allowance') }}
                                                        </label>
                                                        <div>
                                                            <input class="form-control"
                                                                value="{{ old('meal_allowance', $employee->Employee->meal_allowance ?? '') }}"
                                                                type="number" id="meal_allowance" name="meal_allowance"
                                                                value="{{ old('meal_allowance') }}"
                                                                aria-describedby="info-meal_allowance"
                                                                placeholder="700000" step="0.01" min="0"
                                                                max="9999999999.99" required>
                                                            @error('meal_allowance')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="transport_allowance" class="form-control-label">
                                                            <i class="fas fa-id-card"></i>
                                                            {{ __('Transport Allowance') }}
                                                        </label>
                                                        <div>
                                                            <input class="form-control"
                                                                value="{{ old('transport_allowance', $employee->Employee->transport_allowance ?? '') }}"
                                                                type="number" id="transport_allowance"
                                                                name="transport_allowance"
                                                                value="{{ old('transport_allowance') }}"
                                                                aria-describedby="info-transport_allowance"
                                                                placeholder="700000" step="0.01" min="0"
                                                                max="9999999999.99" required>
                                                            @error('transport_allowance')
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
                                                        <label for="notes" class="form-control-label">
                                                            <i class="fas fa-id-card"></i> {{ __('Notes') }}
                                                        </label>
                                                        <div>
                                                            <input class="form-control"
                                                                value="{{ old('notes', $employee->Employee->notes ?? '') }}"
                                                                type="text" id="notes" name="notes"
                                                                value="{{ old('notes') }}"
                                                                aria-describedby="info-notes" placeholder="notes">
                                                            @error('notes')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                            @enderror
                                                        </div>
                                                </div>
                                                </div>
                                            </div>



                                            {{-- <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="role" class="form-control-label">
                                                        <i class="fas fa-shield-alt"></i> {{ __('Role') }}
                                                    </label>
                                                    <div class="@error('role') border border-danger rounded-3 @enderror">
                                                        <select class="form-control" name="role" id="role" required>
                                                            <option value="" disabled selected>Choose Role</option>
                                                            @foreach ($roles as $roleName => $displayName)
                                                                <option value="{{ $roleName }}" {{ old('role') == $roleName ? 'selected' : '' }}>
                                                                    {{ $displayName }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('role')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div> --}}

                                            <div class="alert alert-secondary mt-4" role="alert">
                                                <span class="text-dark">
                                                    <strong>Important Note:</strong> <br>
                                                    - Fill all the input okay.<br>
                                                </span>
                                            </div>

                                            <div class="d-flex justify-content-end mt-4">
                                                <a href="{{ route('pages.Employee') }}" class="btn btn-secondary">
                                                    <i class="fas fa-times"></i> {{ __('Cancel') }}
                                                </a>
                                                <button type="submit" class="btn bg-primary">
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
