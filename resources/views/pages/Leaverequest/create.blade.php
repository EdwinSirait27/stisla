@extends('layouts.app')
@section('title', 'Create Leave Request')
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

        #notes {
            height: 200px;
            resize: vertical;
            /* biar masih bisa diubah manual */
        }
    </style>
@endpush
@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Create Leave Request</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">
                        <a href="{{ route('pages.Leaverequest') }}">Leave Request</a>
                    </div>
                    <div class="breadcrumb-item">Create Leave Request</div>
                </div>
            </div>

            <div class="section-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0 px-3">
                                    <h6 class="mb-0">{{ __('Create Leave Request') }}</h6>
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
                                        <div class="alert alert-success alert-dismissible fade show" id="alert-success"
                                            role="alert">
                                            <span class="alert-text">{{ session('success') }}</span>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close">
                                                <i class="fa fa-close" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    @endif
                                    <form id="position-create" action="{{ route('Leaverequest.store') }}" method="POST">
                                        @csrf
                                        <div class="row">

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="position_id" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Position') }}
                                                    </label>
                                                    <select name="position_id"
                                                        class="form-control select2 @error('position_id') is-invalid @enderror"required>
                                                        <option value="" selected disabled>Select Leave</option>
                                                        @foreach ($leaveBalances as $id => $name)
                                                            <option value="{{ $id }}"
                                                                {{ old('leave_balance_id') == $id ? 'selected' : '' }}>
                                                                {{ $name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    
                                                    @error('position_id')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>


                                           

                                            {{-- Role Summary --}}
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="role_summary" class="form-control-label">
                                                        <i class="fas fa-file-alt"></i> {{ __('Role Summary') }}
                                                    </label>
                                                    <textarea id="role_summary" name="role_summary" class="form-control @error('role_summary') is-invalid @enderror"
                                                        rows="8" required>{{ old('role_summary') }}</textarea>
                                                    @error('role_summary')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Key Responsibility --}}
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="key_respon" class="form-control-label">
                                                        <i class="fas fa-tasks"></i> {{ __('Key Responsibility') }}
                                                    </label>
                                                    <textarea id="key_respon" name="key_respon" class="form-control @error('key_respon') is-invalid @enderror"
                                                        rows="8" required>{{ old('key_respon') }}</textarea>
                                                    @error('key_respon')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Qualifications --}}
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="qualifications" class="form-control-label">
                                                        <i class="fas fa-graduation-cap"></i> {{ __('Qualifications') }}
                                                    </label>
                                                    <textarea id="qualifications" name="qualifications" class="form-control @error('qualifications') is-invalid @enderror"
                                                        rows="8" required>{{ old('qualifications') }}</textarea>
                                                    @error('qualifications')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Notes --}}
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="notes" class="form-control-label">
                                                        <i class="fas fa-sticky-note"></i> {{ __('Notes') }}
                                                    </label>
                                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes"
                                                        value="{{ old('notes') }}" placeholder="Message manager to HR department"></textarea>


                                                    @error('notes')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Important Note --}}
                                            <div class="col-12">
                                                <div class="alert alert-secondary mt-4" role="alert">
                                                    <span class="text-dark">
                                                        <strong>Important Note:</strong>
                                                        <br>
                                                        Notes can be blank, but if there is something you want to share with
                                                        HR department, you can fill it in
                                                        <br>
                                                        - Please use English to get used to it.<br>
                                                        - Before creating data, please check first whether there is already
                                                        similar or identical data to avoid double input.
                                                    </span>
                                                </div>
                                            </div>

                                            {{-- Action Buttons --}}
                                            <div class="col-12 d-flex justify-content-end mt-4">
                                                <a href="{{ route('pages.Positionrequest') }}" class="btn btn-secondary">
                                                    <i class="fas fa-times"></i> {{ __('Cancel') }}
                                                </a>
                                                <button type="submit" id="create-btn" class="btn bg-primary ms-2">
                                                    <i class="fas fa-save"></i> {{ __('Create') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div> {{-- card-body --}}
                            </div> {{-- card --}}
                        </div> {{-- col-12 --}}
                    </div> {{-- row --}}
                </div> {{-- container-fluid --}}
            </div> {{-- section-body --}}
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('node_modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
                    document.getElementById('position-create').submit();
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
