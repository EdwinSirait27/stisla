@extends('layouts.app')
@section('title', 'Create Structuresnew')
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
            <h1>Create Structures</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('pages.Structuresnew') }}">Structure</a></div>
                <div class="breadcrumb-item">Create Structures</div>
            </div>
        </div>

        <div class="section-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header pb-0 px-3">
                                <h6 class="mb-0">{{ __('Create Structures') }}</h6>
                            </div>
                            <div class="card-body pt-4 p-3">

                                {{-- Alert Error --}}
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                {{-- Alert Success --}}
                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" id="alert-success" role="alert">
                                        <span class="alert-text">{{ session('success') }}</span>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                            <i class="fa fa-close" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                @endif

                                <form id="departments-create" action="{{ route('Structuresnew.store') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        {{-- Company --}}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="company_id" class="form-control-label">
                                                    <i class="fas fa-id-card"></i> {{ __('Company') }}
                                                </label>
                                                <select name="company_id" class="form-control select2 @error('company_id') is-invalid @enderror" required>
                                                    <option value="">Choose Company</option>
                                                    @foreach ($companys as $key => $value)
                                                        <option value="{{ $key }}" {{ old('company_id') == $key ? 'selected' : '' }}>
                                                            {{ $value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('company_id')
                                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Department --}}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="department_id" class="form-control-label">
                                                    <i class="fas fa-id-card"></i> {{ __('Department') }}
                                                </label>
                                                <select name="department_id" class="form-control select2 @error('department_id') is-invalid @enderror" required>
                                                    <option value="">Choose Department</option>
                                                    @foreach ($departments as $key => $value)
                                                        <option value="{{ $key }}" {{ old('department_id') == $key ? 'selected' : '' }}>
                                                            {{ $value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('department_id')
                                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Location --}}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="store_id" class="form-control-label">
                                                    <i class="fas fa-id-card"></i> {{ __('Location') }}
                                                </label>
                                                <select name="store_id" class="form-control select2 @error('store_id') is-invalid @enderror" required>
                                                    <option value="">Choose Location</option>
                                                    @foreach ($stores as $key => $value)
                                                        <option value="{{ $key }}" {{ old('store_id') == $key ? 'selected' : '' }}>
                                                            {{ $value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('store_id')
                                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Position --}}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="position_id" class="form-control-label">
                                                    <i class="fas fa-id-card"></i> {{ __('Position') }}
                                                </label>
                                                <select name="position_id" class="form-control select2 @error('position_id') is-invalid @enderror" required>
                                                    <option value="">Choose Position</option>
                                                    @foreach ($positions as $key => $value)
                                                        <option value="{{ $key }}" {{ old('position_id') == $key ? 'selected' : '' }}>
                                                            {{ $value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('position_id')
                                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Hierarchy --}}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="parent_id" class="form-control-label">
                                                    <i class="fas fa-id-card"></i> {{ __('Direct Superior') }}
                                                </label>
                                                <select name="parent_id" class="form-control select2 @error('parent_id') is-invalid @enderror">
                                                    <option value="">Choose Superior</option>
                                                    @foreach ($parents as $key => $value)
                                                        <option value="{{ $key }}" {{ old('parent_id') == $key ? 'selected' : '' }}>
                                                            {{ $value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('parent_id')
                                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Is Manager --}}
                                        <div class="col-md-6">
                                            <div class="form-check mt-2">
                                                <input type="checkbox" name="is_manager" id="is_manager" value="1"
                                                    class="form-check-input @error('is_manager') is-invalid @enderror"
                                                    {{ old('is_manager') ? 'checked' : '' }}>
                                                <label for="is_manager" class="form-check-label">
                                                    <i class="fas fa-id-card"></i> {{ __('Is Manager?') }}
                                                </label>
                                                @error('is_manager')
                                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Role Summary --}}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="role_summary" class="form-control-label">
                                                    <i class="fas fa-id-card"></i> {{ __('Role Summary') }}
                                                </label>
                                                <textarea id="role_summary" name="role_summary" class="form-control @error('role_summary') is-invalid @enderror" rows="8" required>{{ old('role_summary') }}</textarea>
                                                @error('role_summary')
                                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Key Responsibility --}}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="key_respon" class="form-control-label">
                                                    <i class="fas fa-id-card"></i> {{ __('Key Responsibility') }}
                                                </label>
                                                <textarea id="key_respon" name="key_respon" class="form-control @error('key_respon') is-invalid @enderror" rows="8" required>{{ old('key_respon') }}</textarea>
                                                @error('key_respon')
                                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Qualifications --}}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="qualifications" class="form-control-label">
                                                    <i class="fas fa-id-card"></i> {{ __('Qualifications') }}
                                                </label>
                                                <textarea id="qualifications" name="qualifications" class="form-control @error('qualifications') is-invalid @enderror" rows="8" required>{{ old('qualifications') }}</textarea>
                                                @error('qualifications')
                                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Salary --}}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="salary_id" class="form-control-label">
                                                    <i class="fas fa-id-card"></i> {{ __('Salary') }}
                                                </label>
                                                <select name="salary_id" class="form-control select2 @error('salary_id') is-invalid @enderror" required>
                                                    <option value="">Choose Salary</option>
                                                    @foreach ($salaries as $key => $value)
                                                        <option value="{{ $key }}" {{ old('salary_id') == $key ? 'selected' : '' }}>
                                                            {{ $value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('salary_id')
                                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Type --}}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="type" class="form-control-label">
                                                    <i class="fas fa-list"></i> {{ __('Type') }}
                                                </label>
                                                <div>
                                                    @foreach ($types as $type)
                                                        <div class="form-check">
                                                            <input class="form-check-input @error('type') is-invalid @enderror"
                                                                type="checkbox" name="type[]"
                                                                id="type_{{ $type }}" value="{{ $type }}"
                                                                {{ is_array(old('type')) && in_array($type, old('type')) ? 'checked' : '' }} required>
                                                            <label class="form-check-label" for="type_{{ $type }}">
                                                                {{ ucfirst($type) }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @error('type')
                                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                @enderror
                                            </div>
                                        </div>

                                      
                                    </div>

                                    {{-- Note --}}
                                    <div class="alert alert-secondary mt-4" role="alert">
                                        <span class="text-dark">
                                            <strong>Important Note:</strong> <br>
                                            - Column Superior and Is Manager can be empty.
                                        </span>
                                    </div>

                                    {{-- Buttons --}}
                                    <div class="d-flex justify-content-end mt-4">
                                        <a href="{{ route('pages.Structuresnew') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> {{ __('Cancel') }}
                                        </a>
                                        <button type="submit" id="create-btn" class="btn bg-primary">
                                            <i class="fas fa-save"></i> {{ __('Create') }}
                                        </button>
                                    </div>
                                </form>

                            </div> {{-- end card-body --}}
                        </div> {{-- end card --}}
                    </div> {{-- end col --}}
                </div> {{-- end row --}}
            </div> {{-- end container-fluid --}}
        </div> {{-- end section-body --}}
    </section>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('node_modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('node_modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
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
                    // Jika pengguna mengkonfirmasi, submit form
                    document.getElementById('departments-create').submit();
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
      <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
    <script>
        tinymce.init({
            selector: '#role_summary',
            plugins: 'lists link image table code',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code preview',
            menubar: false,
            height: 300,
            license_key: 'gpl'
        });
        tinymce.init({
            selector: '#key_respon',
            plugins: 'lists link image table code',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code preview',
            menubar: false,
            height: 300,
            license_key: 'gpl'
        });
        tinymce.init({
            selector: '#qualifications',
            plugins: 'lists link image table code',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code preview',
            menubar: false,
            height: 300,
            license_key: 'gpl'
        });
    </script>
@endpush
