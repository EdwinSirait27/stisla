{{-- @extends('layouts.app')
@section('title', 'Create User')
@push('style')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
@endpush
@section('title', 'Edit User')
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
</style>
<div>
    <div class="container-fluid">

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('dashboardAdmin.update', $hashedId) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="container-fluid py-4">
                <div class="card">
                    <div class="card-header pb-0 px-3">
                        <h6 class="mb-0">{{ __('Update User') }}</h6>
                    </div>
                    <div class="card-body pt-4 p-3">

                        @if ($errors->any())
                            <div class="mt-3  alert alert-primary alert-dismissible fade show" role="alert">
                                @foreach ($errors->all() as $error)
                                    <span class="alert-text text-white">
                                        {{ $error }}</span>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                        <i class="fa fa-close" aria-hidden="true"></i>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="m-3  alert alert-success alert-dismissible fade show" id="alert-success"
                                role="alert">
                                <span class="alert-text text-white">
                                    {{ session('success') }}</span>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                    <i class="fa fa-close" aria-hidden="true"></i>
                                </button>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username" class="form-control-label">
                                        <i class="fas fa-lock"></i> {{ __('Username') }}
                                    </label>
                                    <div>
                                        <input type="text" class="form-control" id="username" name="username"
                                            value="{{ old('username', $user->username) }}" required
                                            oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')">
                                        @error('username')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password" class="form-control-label">
                                        <i class="fas fa-lock"></i> {{ __('Password') }}
                                    </label>
                                    <div>
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Password" aria-describedby="password-addon" maxlength="12"
                                            oninput="this.value = this.value.replace(/[^a-zA-Z0-9_-]/g, '');" />
                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-control-label">
                                        <i class="fas fa-lock"></i> {{ __('Full Name') }}
                                    </label>
                                    <div>
                                        <input class="form-control" value="{{ old('name', $user->name ?? '') }}"
                                            type="text" id="name" name="name" aria-describedby="info-name"
                                            maxlength="255">
                                        @error('name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_type" class="form-control-label">{{ __('Access Rights') }}</label>
                                    <div class="@error('user_type') border border-danger rounded-3 @enderror">
                                        <select class="form-control" name="user_type" id="user_type" required>
                                            <option value="" disabled
                                                {{ old('user_type', $user->user_type ?? '') == '' ? 'selected' : '' }}>
                                                Select Access Rights</option>
                                            <option value="Admin"
                                                {{ old('user_type', $user->user_type ?? '') == 'Admin' ? 'selected' : '' }}>
                                                Admin</option>
                                            <option value="Manager"
                                                {{ old('user_type', $user->user_type ?? '') == 'Manager' ? 'selected' : '' }}>
                                                Manager</option>
                                            <option value="Kasir"
                                                {{ old('user_type', $user->user_type ?? '') == 'Kasir' ? 'selected' : '' }}>
                                                Kasir</option>
                                        </select>
                                        @error('user_type')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror

                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    @php
                                        $roles = ['Admin', 'Manager', 'Kasir'];
                                        $selectedRoles = old('role', explode(',', $user->role ?? ''));
                                    @endphp

                                    <label for="role" class="form-control-label">{{ __('Role') }}</label>
                                    <div class="@error('role') border border-danger rounded-3 @enderror">
                                        @foreach ($roles as $role)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="role[]"
                                                    id="role_{{ $role }}" value="{{ $role }}"
                                                    {{ in_array($role, $selectedRoles) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="role_{{ $role }}">
                                                    {{ $role }}
                                                </label>
                                            </div>
                                        @endforeach
                                        @error('role')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror


                                    </div>
                                </div>
                            </div>


                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone" class="form-control-label">
                                        <i class="fas fa-lock"></i> {{ __('Phone Number') }}
                                    </label>
                                    <div>
                                        <input class="form-control" value="{{ old('phone', $user->phone ?? '') }}"
                                            type="text" id="phone" name="phone"
                                            aria-describedby="info-phone" maxlength="13">
                                        @error('phone')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn bg-gradient-dark btn-md mt-4 mb-4">
                                    {{ __('Update') }}
                                </button>
                                <a href="{{ route('pages.dashboardAdmin') }}" class="btn btn-secondary mt-4 mb-4">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
        </form>
        <div class="alert alert-secondary mx-4" role="alert">
            <span class="text-white">
                <strong>Keterangan</strong> <br>
            </span>
            <span class="text-white">-
                <strong> Jika sudah ada Username yang sudah terdaftar, maka tidak bisa menginputkan data kembali
                </strong> <br>
                <br>

            </span>
        </div>
    </div>
</div>
</div>
</div> --}}
@extends('layouts.app')
@section('title', 'Update User')
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
                <h1>Update User</h1>
                <div class="section-header-breadcrumb">
                    {{-- <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div> --}}
                    <div class="breadcrumb-item"><a href="{{ route('pages.dashboardAdmin') }}">Users</a></div>
                    <div class="breadcrumb-item">Update User</div>
                </div>
            </div>

            <div class="section-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0 px-3">
                                    <h6 class="mb-0">{{ __('Update User') }}</h6>
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
                                        <div class="alert alert-success alert-dismissible fade show" id="alert-success" role="alert">
                                            <span class="alert-text">
                                                {{ session('success') }}
                                            </span>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                                <i class="fa fa-close" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    @endif

                                    <form action="{{ route('dashboardAdmin.update', $hashedId) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="username" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Username') }}
                                                    </label>
                                                    <div>
                                                        <input type="text" class="form-control" id="username" name="username"
                                                            value="{{ old('username', $user->username) }}" required
                                                            oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')">
                                                        @error('username')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="password" class="form-control-label">
                                                        <i class="fas fa-lock"></i> {{ __('Password') }}
                                                    </label>
                                                    <div>
                                                        <input type="password" class="form-control" id="password" name="password"
                                                            placeholder="Leave blank to keep current password" aria-describedby="password-addon" 
                                                            maxlength="12"
                                                            oninput="this.value = this.value.replace(/[^a-zA-Z0-9_-]/g, '');" />
                                                        <small class="text-muted">Only letters, numbers, underscore and dash allowed. Max 12 characters.</small>
                                                        @error('password')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div> --}}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="password" class="form-control-label">
                                                    <i class="fas fa-lock"></i> {{ __('Password') }}
                                                </label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="password" name="password"
                                                    placeholder="Leave blank to keep current password" aria-describedby="password-addon" 
                                                    maxlength="12"
                                                    oninput="this.value = this.value.replace(/[^a-zA-Z0-9_-]/g, '');" />
                                                    <div class="input-group-append">
                                                        <span class="input-group-text" onclick="togglePassword()" style="cursor: pointer;">
                                                            <i id="eyeIcon" class="fa fa-eye"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    Only letters, numbers, underscore, and dash allowed. Max 12 characters.
                                                </small>
                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                        
                                        <script>
                                            function togglePassword() {
                                                let passwordInput = document.getElementById('password');
                                                let eyeIcon = document.getElementById('eyeIcon');
                                        
                                                if (passwordInput.type === "password") {
                                                    passwordInput.type = "text";
                                                    eyeIcon.classList.replace("fa-eye", "fa-eye-slash");
                                                } else {
                                                    passwordInput.type = "password";
                                                    eyeIcon.classList.replace("fa-eye-slash", "fa-eye");
                                                }
                                            }
                                        </script>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Full Name') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control" value="{{ old('name', $user->name ?? '') }}"
                                                            type="text" id="name" name="name" aria-describedby="info-name"
                                                            maxlength="255">
                                                        @error('name')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="user_type" class="form-control-label">
                                                        <i class="fas fa-shield-alt"></i> {{ __('Access Rights') }}
                                                    </label>
                                                    <div class="@error('user_type') border border-danger rounded-3 @enderror">
                                                        <select class="form-control" name="user_type" id="user_type" required>
                                                            <option value="" disabled
                                                                {{ old('user_type', $user->user_type ?? '') == '' ? 'selected' : '' }}>
                                                                Select Access Rights</option>
                                                            <option value="Admin"
                                                                {{ old('user_type', $user->user_type ?? '') == 'Admin' ? 'selected' : '' }}>
                                                                Admin</option>
                                                            <option value="Manager"
                                                                {{ old('user_type', $user->user_type ?? '') == 'Manager' ? 'selected' : '' }}>
                                                                Manager</option>
                                                            <option value="Kasir"
                                                                {{ old('user_type', $user->user_type ?? '') == 'Kasir' ? 'selected' : '' }}>
                                                                Kasir</option>
                                                        </select>
                                                        @error('user_type')
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
                                                    <label for="role" class="form-control-label">
                                                        <i class="fas fa-user-tag"></i> {{ __('Role') }}
                                                    </label>
                                                    <div class="@error('role') border border-danger rounded-3 p-3 @enderror">
                                                        @php
                                                            $roles = ['Admin', 'Manager', 'Kasir'];
                                                            $selectedRoles = old('role', explode(',', $user->role ?? ''));
                                                        @endphp

                                                        @foreach ($roles as $role)
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="role[]"
                                                                    id="role_{{ $role }}" value="{{ $role }}"
                                                                    {{ in_array($role, $selectedRoles) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="role_{{ $role }}">
                                                                    {{ $role }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                        @error('role')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="phone" class="form-control-label">
                                                        <i class="fas fa-phone"></i> {{ __('Phone Number') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control" value="{{ old('phone', $user->phone ?? '') }}"
                                                            type="text" id="phone" name="phone"
                                                            aria-describedby="info-phone" maxlength="13"
                                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                                        <small class="text-muted">Numbers only. Max 13 digits.</small>
                                                        @error('phone')
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
                                                - If a username is already registered, you cannot register it again.<br>
                                                - Leave the password field empty if you don't want to change it.
                                            </span>
                                        </div>

                                        <div class="d-flex justify-content-end mt-4">
                                            <a href="{{ route('pages.dashboardAdmin') }}" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> {{ __('Cancel') }}
                                            </a>
                                            <button type="submit" class="btn bg-gradient-dark">
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
