@section('title', 'Show Structures')
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
                <h1>Detail Structures</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('pages.Structuresnew') }}">Structure</a></div>
                    <div class="breadcrumb-item">Detail Structures {{ $structure->position->name }}</div>
                </div>
            </div>

            <div class="section-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0 px-3">
                                    <h6 class="mb-0">{{ __('Detail Structures') }} - {{ $structure->position->name }}</h6>
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
                                                    <th width="25%">Company</th>
                                                    <td>{{ $structure->company->name ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Department</th>
                                                    <td>{{ $structure->department->department_name ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Location</th>
                                                    <td>{{ $structure->store->name ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Position</th>
                                                    <td>{{ $structure->position->name ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Hierarchy</th>
                                                    <td>{{ $structure->parent->position->name ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Is Manager?</th>
                                                    <td>
                                                        @if ($structure->is_manager)
                                                            <span class="badge bg-success">Yes</span>
                                                        @else
                                                            <span class="badge bg-danger">No</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Role Summary</th>
                                                    <td>{!! $structure->role_summary ?? '<em>Empty</em>' !!}</td>

                                                </tr>
                                                <tr>
                                                    <th>Key Responsibility</th>
                                                    <td>{!! $structure->key_respon ?? '<em>Empty</em>' !!}</td>


                                                </tr>
                                                <tr>
                                                    <th>Qualifications</th>
                                                    <td>{!! $structure->qualifications ?? '<em>Empty</em>' !!}</td>


                                                </tr>
                                                @role('HeadHR')
                                                <tr>
                                                    <th>Salary</th>
                                                    <td>{{ $structure->salary->salary_start ?? '-' }} to
                                                        {{ $structure->salary->salary_end ?? '-' }}</td>
                                                </tr>
@endrole
                                                <tr>
                                                    <th>Type</th>
                                                    <td>
                                                        @forelse ($structure->type_badges as $badge)
                                                            <span
                                                                class="badge bg-{{ $badge['color'] }}">{{ $badge['name'] }}</span>
                                                        @empty
                                                            <span class="text-muted">(empty)</span>
                                                        @endforelse
                                                    </td>
                                                </tr>




                                                <tr>
                                                    <th>Work</th>
                                                    <td>{{ $structure->position->name ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Created on date</th>
                                                    <td>{{ $structure->created_at->format('d M Y, H:i') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Edited on date</th>
                                                    <td>{{ $structure->updated_at->format('d M Y, H:i') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-flex justify-content-end mt-4">
                                        <a href="{{ route('pages.Structuresnew') }}" class="btn btn-secondary">
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
