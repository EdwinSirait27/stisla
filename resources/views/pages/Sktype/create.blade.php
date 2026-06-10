@extends('layouts.app')
@section('title', 'Create Position')
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
                <h1>Create Sktype</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('pages.Sktype') }}">SK Types</a></div>
                    <div class="breadcrumb-item">Create SK Type</div>
                </div>
            </div>
            <div class="section-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0 px-3">
                                    <h6 class="mb-0">{{ __('Create SK Type') }}</h6>
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
                                    <form id="position-create" action="{{ route('Sktype.store') }}" method="POST">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="sk_name" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('SK Type Name') }}
                                                    </label>
                                                    <div>
                                                        <input type="text"
                                                            class="form-control @error('sk_name') is-invalid @enderror"
                                                            id="sk_name" name="sk_name" value="{{ old('sk_name') }}"
                                                            required placeholder="Fill SK Type Name">
                                                        @error('sk_name')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="nickname" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Nickname') }}
                                                    </label>
                                                    <div>
                                                        <input type="text"
                                                            class="form-control @error('nickname') is-invalid @enderror"
                                                            id="nickname" name="nickname" value="{{ old('nickname') }}"
                                                            required placeholder="exmpl OPR with capslock">
                                                        @error('nickname')
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
                                                    <label for="categories" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Company NPWP') }}
                                                    </label>
                                                    <div>
                                                        <select name="categories" id="categories"
                                                            class="select2 form-control" required>
                                                            <option value="">Choose categories</option>
                                                            @foreach ($categories as $value)
                                                                <option value="{{ $value }}"
                                                                    {{ old('categories') == $value ? 'selected' : '' }}>
                                                                    {{ $value }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('categories')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                        </div>
                                        <hr>

                                        {{-- Affects Section --}}
                                        <div class="mb-3">
                                            <label class="font-weight-bold">Affects Configuration</label>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">

                                                    <label class="form-control-label d-block">
                                                        <i class="fas fa-money-bill-wave"></i> Generates Contract
                                                    </label>


                                                    <div class="custom-control custom-switch">
                                                        <input type="hidden" name="generates_contract" value="0">

                                                        <input type="checkbox"
                                                            class="custom-control-input @error('generates_contract') is-invalid @enderror"
                                                            id="generates_contract" name="generates_contract" value="1"
                                                            {{ old('generates_contract') ? 'checked' : '' }}>

                                                        <label class="custom-control-label" for="generates_contract">
                                                            No / Yes
                                                        </label>

                                                        @error('generates_contract')
                                                            <span class="invalid-feedback d-block">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-control-label d-block">
                                                        <i class="fas fa-money-bill-wave"></i> Affects Position
                                                    </label>
                                                    <div class="custom-control custom-switch">
                                                        <input type="hidden" name="affects_position" value="0">

                                                        <input type="checkbox"
                                                            class="custom-control-input @error('affects_position') is-invalid @enderror"
                                                            id="affects_position" name="affects_position" value="1"
                                                            {{ old('affects_position') ? 'checked' : '' }}>

                                                        <label class="custom-control-label" for="affects_position">
                                                            No / Yes
                                                        </label>

                                                        @error('affects_position')
                                                            <span class="invalid-feedback d-block">
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
                                                    <label class="form-control-label d-block">
                                                        <i class="fas fa-money-bill-wave"></i> Affects Status
                                                    </label>
                                                    <div class="custom-control custom-switch">
                                                        <input type="hidden" name="affects_status" value="0">

                                                        <input type="checkbox"
                                                            class="custom-control-input @error('affects_status') is-invalid @enderror"
                                                            id="affects_status" name="affects_status" value="1"
                                                            {{ old('affects_status') ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="affects_status">
                                                            No / Yes
                                                        </label>
                                                        @error('affects_status')
                                                            <span class="invalid-feedback d-block">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                             <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-control-label d-block">
                                                        <i class="fas fa-money-bill-wave"></i> Affects Salaries
                                                    </label>
                                                    <div class="custom-control custom-switch">
                                                        <input type="hidden" name="affects_salary" value="0">

                                                        <input type="checkbox"
                                                            class="custom-control-input @error('affects_salary') is-invalid @enderror"
                                                            id="affects_salary" name="affects_salary" value="1"
                                                            {{ old('affects_salary') ? 'checked' : '' }}>

                                                        <label class="custom-control-label" for="affects_salary">
                                                            No / Yes
                                                        </label>

                                                        @error('affects_salary')
                                                            <span class="invalid-feedback d-block">
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
                                                - If a sk type name is already registered, you cannot register it again.
                                                <br> - please use English to get used to it.
                                                <br> - Before creating data, please check first whether there is already
                                                similar or identical data to avoid double input.
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-end mt-4">
                                            <a href="{{ route('pages.Sktype') }}" class="btn btn-secondary">
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
@endpush
