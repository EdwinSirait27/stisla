@section('title', 'Show Team Employee')
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
@extends('layouts.app')

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Details Team</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('pages.Team') }}">Detail Team</a></div>
                    <div class="breadcrumb-item">Detail Employee
                        {{ $employee->Employee->employee_name }}</div>
                </div>
            </div>
            <div class="section-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0 px-3">
                                    <h6 class="mb-0">{{ __('Detail Employee') }} {{ $employee->Employee->employee_name }}
                                    </h6>
                                </div>

                                <div class="card-body pt-4 p-3">
                                    @if (session('success'))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            {{ session('success') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close"></button>
                                        </div>
                                    @endif

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <tbody>
                                                <tr>
                                                    <th width="25%">Employee Name</th>
                                                    <td>{{ $employee->Employee->employee_name ?? 'empty' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>NIP</th>
                                                    <td>{{ $employee->Employee->employee_pengenal ?? 'empty' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Religion</th>
                                                    <td>{{ $employee->Employee->religion ?? 'empty' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Gender</th>
                                                    <td>{{ $employee->Employee->gender ?? 'empty' }}</td>
                                                </tr>
                                                {{-- <tr>
                                                    <th>Date of Birth</th>
                                                    <td>{{ $employee->Employee->date_of_birth ?? 'empty' }}
                                                    </td>
                                                </tr> --}}
                                                <tr>
                                                    <th>Current Address</th>
                                                    <td>{{ $employee->Employee->current_address ?? 'empty' }}
                                                    </td>
                                                </tr>
                                                {{-- <tr>
                                                    <th>ID Card Address</th>
                                                    <td>{{ $employee->Employee->id_card_address ?? 'empty' }}
                                                    </td>
                                                </tr> --}}
                                                <tr>
                                                    <th>Emergency Contact Name</th>
                                                    <td>{{ $employee->Employee->emergency_contact_name ?? 'empty' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Email</th>
                                                    <td>{{ $employee->Employee->email ?? 'empty' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Telephone Number</th>
                                                    <td>{{ $employee->Employee->telp_number ?? 'empty' }}
                                                    </td>
                                                </tr>
                                                {{-- <tr>
                                                    <th>Company</th>
                                                    <td>{{ $employee->Employee->company->name ?? 'empty' }}
                                                    </td>
                                                </tr> --}}
                                                <tr>
                                                    <th>Department</th>
                                                    <td>{{ $employee->Employee->department->department_name ?? 'empty' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Location</th>
                                                    <td>{{ $employee->Employee->structuresnew->submissionposition->store->name ?? 'empty' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Position</th>
                                                    <td>{{ $employee->Employee->structuresnew->submissionposition->positionRelation->name ?? 'empty' }}
                                                    </td>
                                                </tr>

                                                {{-- <tr>
                                                    <th>Grading</th>
                                                    <td>{{ $employee->Employee->grading->grading_name ?? 'empty' }}
                                                    </td>
                                                </tr> --}}
                                                @if (!is_null($isManager))
                                                    <tr>
                                                        <th>Is Manager</th>
                                                        <td>
                                                            @if ($isManager)
                                                                <span class="badge bg-success">Yes</span>
                                                            @else
                                                                <span class="badge bg-danger">No</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif

                                                <tr>
                                                    <th>Employee Status</th>
                                                    <td>{{ $employee->Employee->status_employee ?? 'empty' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Join Date</th>
                                                    <td>{{ $employee->Employee->join_date ?? 'empty' }}
                                                    </td>
                                                </tr>

                                                
                                                <tr>
                                                    <th>Status</th>
                                                    <td>{{ $employee->Employee->status ?? 'empty' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Employee Pin Fingerprint</th>
                                                    <td>{{ $employee->Employee->pin ?? 'empty' }}
                                                    </td>
                                                </tr>
                                                </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end mt-4">
                                        <a href="{{ route('pages.Team') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> {{ __('Back') }}
                                        </a>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
