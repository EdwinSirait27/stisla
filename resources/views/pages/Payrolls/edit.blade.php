@extends('layouts.app')
@section('title', 'Update Payrolls')
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
                <h1>Update Payrolls {{ $payroll->employee->employee_name }}</h1>
                <div class="section-header-breadcrumb">
                    {{-- <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div> --}}
                    <div class="breadcrumb-item"><a href="{{ route('pages.Payrolls') }}">Departments</a></div>
                    <div class="breadcrumb-item">Update Payrolls {{ $payroll->employee->employee_name }}</div>
                </div>
            </div>

            <div class="section-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0 px-3">
                                    <h6 class="mb-0">{{ __('Update Payrolls') }} {{ $payroll->employee->employee_name }}
                                    </h6>
                                </div>
                                <div class="card-body pt-4 p-3">
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
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

                                    <form id="payroll" action="{{ route('Payrolls.update', $hashedId) }}" method="POST">
                                        @csrf
                                        @method('PUT')

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="attendance" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Attendance') }}
                                                    </label>
                                                    <div>
                                                        <input type="number" class="form-control" id="attendance"
                                                            name="attendance"
                                                            value="{{ old('attendance', $payroll->attendance) }}"
                                                            placeholder="input employee's attendance" required>
                                                        @error('attendance')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror




                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="daily_allowance" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Daily Allowance') }}
                                                    </label>
                                                    <div>
                                                        <input type="number" class="form-control" id="daily_allowance"
                                                            name="daily_allowance"
                                                            value="{{ old('daily_allowance', $payroll->daily_allowance) }}"
                                                            placeholder="input employee's daily allowance" required>
                                                        @error('daily_allowance')
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
                                                    <label for="overtime" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Overtime') }}
                                                    </label>
                                                    <div>
                                                        <input type="number" class="form-control" id="overtime"
                                                            name="overtime"
                                                            value="{{ old('overtime', $payroll->overtime) }}"
                                                            placeholder="input 0 if the employee does't have overtime"
                                                            required>
                                                        @error('overtime')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="bonus" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Bonuses') }}
                                                    </label>
                                                    <div>
                                                        <input type="number" class="form-control" id="bonus"
                                                            name="bonus" value="{{ old('bonus', $payroll->bonus) }}"
                                                            placeholder="input 0 if the employee dont have bonuses"
                                                            required>
                                                        @error('bonus')
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
                                                    <label for="house_allowance" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('House Allowance') }}
                                                    </label>
                                                    <div>

                                                        <input type="number" class="form-control" id="house_allowance"
                                                            name="house_allowance"
                                                            value="{{ old('house_allowance', $payroll->house_allowance) }}"
                                                            placeholder="input 0 if the employee dont have house allowance"
                                                            required>
                                                        @error('house_allowance')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror


                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="meal_allowance" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Meal Allowance') }}
                                                    </label>
                                                    <div>

                                                        <input type="number" class="form-control" id="meal_allowance"
                                                            name="meal_allowance"
                                                            value="{{ old('meal_allowance', $payroll->meal_allowance) }}"
                                                            placeholder="input 0 if the employee dont have meal allowance"
                                                            required>
                                                        @error('meal_allowance')
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
                                                    <label for="transport_allowance" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Transport Allowance') }}
                                                    </label>
                                                    <div>

                                                        <input type="number" class="form-control"
                                                            id="transport_allowance" name="transport_allowance"
                                                            value="{{ old('transport_allowance', $payroll->transport_allowance) }}"
                                                            placeholder="input 0 if the employee dont have transport allowance"
                                                            required>
                                                        @error('transport_allowance')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror

                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="mesh" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Mesh') }}
                                                    </label>
                                                    <div>

                                                        <input type="number" class="form-control" id="mesh"
                                                            name="mesh"
                                                            value="{{ old('mesh', $payroll->mesh) }}"
                                                            placeholder="input 0 if the employee dont have mesh"
                                                            required>
                                                        @error('mesh')
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
                                                    <label for="punishment" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Punishment') }}
                                                    </label>
                                                    <div>
                                                        <input type="number" class="form-control" id="punishment"
                                                            name="punishment"
                                                            value="{{ old('punishment', $payroll->punishment) }}"
                                                            placeholder="input 0 if the employee doesn't have punishment">
                                                        @error('punishment')
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
                                                        <i class="fas fa-user"></i> {{ __('BPJS Ketenagakerjaan') }}
                                                    </label>
                                                    <div>
                                                        <input type="number" class="form-control" id="bpjs_ket"
                                                            name="bpjs_ket"
                                                            value="{{ old('bpjs_ket', $payroll->bpjs_ket) }}"
                                                            placeholder="input 0 if the employee doesn't have BPJS Ketenagakerjaan">
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
                                                        <label for="bpjs_kes" class="form-control-label">
                                                            <i class="fas fa-user"></i> {{ __('BPJS Kesehatan') }}
                                                        </label>
                                                        <div>
                                                            <input type="number" class="form-control" id="bpjs_kes"
                                                                name="bpjs_kes"
                                                                value="{{ old('bpjs_kes', $payroll->bpjs_kes) }}"
                                                                placeholder="input 0 if the employee doesn't have bpjs kesehatan">
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
                                                            <i class="fas fa-user"></i> {{ __('BPJS Ketenagakerjaan') }}
                                                        </label>
                                                        <div>
                                                            <input type="number" class="form-control" id="bpjs_ket"
                                                                name="bpjs_ket"
                                                                value="{{ old('bpjs_ket', $payroll->bpjs_ket) }}"
                                                                placeholder="input 0 if the employee doesn't have bpjs ketenagakerjaan">
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
                                                            <label for="late_fine" class="form-control-label">
                                                                <i class="fas fa-user"></i> {{ __('Late Fine') }}
                                                            </label>
                                                            <div>
                                                                <input type="number" class="form-control" id="late_fine"
                                                                    name="late_fine"
                                                                    value="{{ old('late_fine', $payroll->late_fine) }}"
                                                                    placeholder="input 0 if the employee doesn't have late fine">
                                                                @error('late_fine')
                                                                    <span class="invalid-feedback" role="alert">
                                                                        <strong>{{ $message }}</strong>
                                                                    </span>
                                                                @enderror
        
                                                            </div>
                                                        </div>
                                                    </div>
                                            
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="information" class="form-control-label">
                                                                <i class="fas fa-user"></i> {{ __('Information') }}
                                                            </label>
                                                            <div>
                                                                <input type="text" class="form-control" id="information"
                                                                    name="information"
                                                                    value="{{ old('information', $payroll->information) }}"
                                                                    placeholder="penjelasan salary">
                                                                @error('information')
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
                                                - if there is no bonus, you can just input 0, it applies to all forms.<br>
                                               

                                            </span>
                                        </div>

                                        <div class="d-flex justify-content-end mt-4">
                                            <a href="{{ route('pages.Payrolls') }}" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> {{ __('Cancel') }}
                                            </a>
                                            <button type="button" id="submitButton" class="btn bg-primary">
                                                <i class="fas fa-save"></i> {{ __('Update') }}
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
        document.addEventListener('DOMContentLoaded', function() {
            // Menambahkan event listener ke tombol setelah DOM sepenuhnya dimuat
            document.getElementById('submitButton').addEventListener('click', function(e) {
                e.preventDefault(); // Mencegah tombol submit langsung

                // Menampilkan SweetAlert2
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to proceed with the update? If you are not sure, you can check again, okay :)',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Update it!',
                    cancelButtonText: 'No, Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('payroll').submit(); // Submit form
                    } else if (result.isDismissed) {
                        Swal.fire('Cancelled', 'The process has been cancelled.',
                        'error'); // Menampilkan pesan jika dibatalkan
                    }
                });
            });
        });

        // Menampilkan notifikasi jika ada session success/error
        @if (session('success'))
            Swal.fire({
                title: 'Success!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonText: 'OK'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                title: 'Error!',
                text: "{{ session('error') }}",
                icon: 'error',
                confirmButtonText: 'OK'
            });
        @endif
    </script>
