@extends('layouts.app')
@section('title', 'Edit Position Request List')
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
                <h1>Edit Position Request List</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">
                        <a href="{{ route('pages.Positionreqlist') }}">Position Request</a>
                    </div>
                    <div class="breadcrumb-item">Edit Position Request List</div>
                </div>
            </div>

            <div class="section-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0 px-3">
                                    <h6 class="mb-0">{{ __('Edit Position Request List') }} from {{ $position->submitter->employee_name }} for {{ $position->position_name }}
                                    </h6>
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
                                            <span class="alert-text">{{ session('success') }}</span>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close">
                                                <i class="fa fa-close" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    @endif

                                    <form id="position-edit" action="{{ route('Positionreqlist.update', $hashedId) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="position_name" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Position Name') }}
                                                    </label>
                                                    <input type="text"
                                                        class="form-control @error('position_name') is-invalid @enderror"
                                                        id="position_name" name="position_name"
                                                        value="{{ old('position_name', $position->position_name) }}"
                                                        disabled placeholder="Fill Position Name">
                                                    @error('position_name')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">

                                                <div class="form-group">
                                                    <label for="work_location" class="form-control-label">
                                                        <i class="fas fa-file-alt"></i> {{ __('Work Location') }}
                                                    </label>
                                                    <input type="text"
                                                        class="form-control @error('work_location') is-invalid @enderror"
                                                        id="work_location" name="work_location"
                                                        value="{{ old('work_location', $position->work_location) }}"
                                                        disabled placeholder="Fill Work Location">
                                                    @error('work_location')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                          
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="type" class="form-control-label">
                                                        <i class="fas fa-list"></i> {{ __('Type') }}
                                                    </label>
                                                    <div>
                                                        @foreach ($types as $type)
                                                            <div class="form-check">
                                                                <input
                                                                    class="form-check-input @error('type') is-invalid @enderror"
                                                                    type="checkbox" name="type[]"
                                                                    id="type_{{ $type }}"
                                                                    value="{{ $type }}"
                                                                    {{ in_array($type, explode(',', $position->type)) ? 'checked' : '' }}disabled>
                                                                <label class="form-check-label"
                                                                    for="type_{{ $type }}">
                                                                    {{ $type }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    @error('type')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>



                                            <div class="col-md-6">

                                                <div class="form-group">
                                                    <label for="role_summary" class="form-control-label">
                                                        <i class="fas fa-file-alt"></i> {{ __('Role Summary') }}
                                                    </label>
                                                    <textarea id="role_summary" name="role_summary" class="form-control @error('role_summary') is-invalid @enderror"
                                                        rows="8"disabled>{{ old('role_summary', $position->role_summary) }}</textarea>
                                                    @error('role_summary')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">

                                                <div class="form-group">
                                                    <label for="key_respon" class="form-control-label">
                                                        <i class="fas fa-file-alt"></i> {{ __('Key Responsibility') }}
                                                    </label>
                                                    <textarea id="key_respon" name="key_respon" class="form-control @error('key_respon') is-invalid @enderror"
                                                        rows="8"disabled>{{ old('key_respon', $position->key_respon) }}</textarea>
                                                    @error('key_respon')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="qualifications" class="form-control-label">
                                                        <i class="fas fa-file-alt"></i> {{ __('Qualifications') }}
                                                    </label>
                                                    <textarea id="qualifications" name="qualifications"
                                                        class="form-control @error('qualifications') is-invalid @enderror" rows="8"disabled>{{ old('qualifications', $position->qualifications) }}</textarea>
                                                    @error('qualifications')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6">

                                                <div class="form-group">
                                                    <label for="notes" class="form-control-label">
                                                        <i class="fas fa-file-alt"></i> {{ __('Notes') }}
                                                    </label>
                                                    <input type="text"
                                                        class="form-control @error('notes') is-invalid @enderror"
                                                        id="notes" name="notes"
                                                        value="{{ old('notes', $position->notes) }}"
                                                        placeholder="note from manager requesting additional position"disabled>
                                                    @error('notes')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="alert alert-secondary mt-4" role="alert">
                                                    <span class="text-dark">
                                                        <strong>Important Note:</strong>
                                                        <br> - please use English to get used to it.
                                                        <br> - Before creating data, please check first whether there is
                                                        already
                                                        similar or identical data to avoid double input.
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="col-12 d-flex justify-content-end mt-4">
                                                <a href="{{ route('pages.Positionreqlist') }}" class="btn btn-secondary">
                                                    <i class="fas fa-times"></i> {{ __('Cancel') }}
                                                </a>
                                                <button type="submit" id="edit-btn" class="btn bg-primary ">
                                                    <i class="fas fa-save"></i> {{ __('Update') }}
                                                </button>
                                            </div>
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

        document.getElementById('edit-btn').addEventListener('click', function(e) {
            e.preventDefault(); // Mencegah pengiriman form langsung
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
                    document.getElementById('position-edit').submit();
                }
            });
        });
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
@endpush
