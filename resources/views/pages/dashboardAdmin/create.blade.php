@extends('layouts.app')
@section('title', 'Create User')
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
                <h1>Create User</h1>
                <div class="section-header-breadcrumb">
                    {{-- <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div> --}}
                    <div class="breadcrumb-item"><a href="{{ route('pages.dashboardAdmin') }}">Users</a></div>
                    <div class="breadcrumb-item">Create User</div>
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

                                    <form action="{{ route('dashboardAdmin.store') }}" method="POST">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="username" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Username') }}
                                                    </label>
                                                    <div>
                                                        <input type="text" class="form-control" id="username"
                                                            name="username" value="{{ old('username') }}" required
                                                            oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')"
                                                            placeholder="Fill Username">
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
                                                            maxlength="12 required"
                                                            oninput="this.value = this.value.replace(/[^a-zA-Z0-9_-]/g, '');" />
                                                            <div class="input-group-append">
                                                                <span class="input-group-text" onclick="togglePassword()" style="cursor: pointer;">
                                                                    <i id="eyeIcon" class="fa fa-eye"></i>
                                                                </span>
                                                            </div>
                                                        <small class="text-muted">Only letters, numbers, underscore and dash allowed. Max 12 characters.</small>
                                                        @error('password')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <script>
                                            function togglePassword() {
                                                let passwordInput = document.getElementById('password');
                                                let eyeIcon = document.getElementById('eyeIcon');
                                        
                                                if (passwordInput.type === "password") {
                                                    passwordInput.type = "text";
                                                    eyeIcon.classList.remove("fa-eye");
                                                    eyeIcon.classList.add("fa-eye-slash");
                                                } else {
                                                    passwordInput.type = "password";
                                                    eyeIcon.classList.remove("fa-eye-slash");
                                                    eyeIcon.classList.add("fa-eye");
                                                }
                                            }
                                        </script> --}}
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="password" class="form-control-label">
                                                        <i class="fas fa-lock"></i> {{ __('Password') }}
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control" id="password"
                                                            name="password"
                                                            placeholder="Leave blank to keep current password"
                                                            aria-describedby="password-addon" maxlength="12" required
                                                            oninput="this.value = this.value.replace(/[^a-zA-Z0-9_-]/g, '');" />
                                                        <div class="input-group-append">
                                                            <span class="input-group-text" onclick="togglePassword()"
                                                                style="cursor: pointer;">
                                                                <i id="eyeIcon" class="fa fa-eye"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">
                                                        Only letters, numbers, underscore, and dash allowed. Max 12
                                                        characters.
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
                                                    <label for="device_lan_mac" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Mac Lan') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control"
                                                            value="{{ old('device_lan_mac', $user->Terms->device_lan_mac ?? '') }}"
                                                            type="text" id="device_lan_mac" name="device_lan_mac"
                                                            value="{{ old('device_lan_mac') }}"aria-describedby="info-device_lan_mac"
                                                            maxlength="255"  placeholder="Insert Mac Lan">
                                                        @error('device_lan_mac')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="device_wifi_mac" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Mac Wifi') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control"
                                                            value="{{ old('device_wifi_mac', $user->Terms->device_wifi_mac ?? '') }}"
                                                            type="text" id="device_wifi_mac" name="device_wifi_mac"
                                                            value="{{ old('device_wifi_mac') }}"aria-describedby="info-device_wifi_mac"
                                                            maxlength="255"  placeholder="Insert Mac Lan">
                                                        @error('device_wifi_mac')
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
                                                        <i class="fas fa-shield-alt"></i> {{ __('Roles') }}
                                                    </label>
                                                    <div class="@error('role') border border-danger rounded-3 @enderror">
                                                        <select class="form-control" name="role" id="role" required>
                                                            <option value="" disabled selected>Select Role </option>
                                                            @foreach($Roles as $role)
                                                                <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
                                                                    {{ $role }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        
                                                        @error('role')
                                                            <div class="text-danger small mt-1">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="permissions" class="form-control-label">
                                                        <i class="fas fa-shield-alt"></i> {{ __('Permissions') }}
                                                    </label>
                                                    <div class="@error('permissions') border border-danger rounded-3 @enderror">
                                                        @foreach($permissions as $permission)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="permissions[]" id="permission-{{ $permission->id }}" value="{{ $permission->name }}" {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="permission-{{ $permission->id }}">
                                                                {{ $permission->name }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                        
                                                        @error('permissions')
                                                            <div class="text-danger small mt-1">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            </div>


                                            {{-- <div class="form-group row">
                                                <label class="col-md-4 col-form-label text-md-right">Permissions</label>
                                                <div class="col-md-6">
                                                    @foreach($permissions as $permission)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="permissions[]" id="permission-{{ $permission->id }}" value="{{ $permission->name }}" {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="permission-{{ $permission->id }}">
                                                                {{ $permission->name }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                    @error('permissions')
                                                        <span class="invalid-feedback d-block" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div> --}}









                                        {{-- <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="status" class="form-control-label">
                                                        <i class="fas fa-shield-alt"></i> {{ __('Status') }}
                                                    </label>
                                                    <div class="@error('status') border border-danger rounded-3 @enderror">
                                                        <select class="form-control" name="status" id="status"
                                                            required>
                                                            <option value="" disabled
                                                                {{ $selectedStatusType == '' ? 'selected' : '' }}>Choose
                                                                Status</option>
                                                            @foreach ($userStatus as $status)
                                                                <option value="{{ $status }}"
                                                                    {{ $selectedStatusType == $status ? 'selected' : '' }}>
                                                                    {{ $status }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('status')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                        </div> --}}

                                        {{-- <div class="form-group">
                                            <div class="d-block">
                                                <label class="text-muted" for="password" class="control-label">Password</label>
                                            </div>
                                            <div class="input-group">
                                                <input id="password" type="password" class="form-control" name="password"
                                                    tabindex="2" required placeholder="Password">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text" onclick="togglePassword()" style="cursor: pointer;">
                                                            <i id="eyeIcon" class="fa fa-eye"></i>
                                                        </span>
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        Please fill your password
                                                    </div>
                                            </div>
                                           
                                        </div>
                                        <script>
                                            function togglePassword() {
                                                let passwordInput = document.getElementById('password');
                                                let eyeIcon = document.getElementById('eyeIcon');
                                        
                                                if (passwordInput.type === "password") {
                                                    passwordInput.type = "text";
                                                    eyeIcon.classList.remove("fa-eye");
                                                    eyeIcon.classList.add("fa-eye-slash");
                                                } else {
                                                    passwordInput.type = "password";
                                                    eyeIcon.classList.remove("fa-eye-slash");
                                                    eyeIcon.classList.add("fa-eye");
                                                }
                                            }
                                        </script> --}}







                                        {{-- <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Full Name') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control"
                                                            value="{{ old('name', $user->name ?? '') }}" type="text"
                                                            id="name" name="name"
                                                            value="{{ old('name') }}"aria-describedby="info-name"
                                                            maxlength="255" required placeholder="Fill Full Name">
                                                        @error('name')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div> --}}

                                        {{-- <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="user_type" class="form-control-label">
                                                        <i class="fas fa-shield-alt"></i> {{ __('Access Rights') }}
                                                    </label>
                                                    <div class="@error('user_type') border border-danger rounded-3 @enderror">
                                                        <select class="form-control" name="user_type" id="user_type" required>
                                                            <option value="" disabled {{ old('user_type' ?? '') == '' ? 'selected' : '' }}>Choose Access Rights</option>
                                                            <option value="Admin" {{ old('user_type' ?? '') == 'Admin' ? 'selected' : '' }}>Admin</option>
                                                            <option value="Manager" {{ old('user_type' ?? '') == 'Manager' ? 'selected' : '' }}>Manager</option>
                                                            <option value="Kasir" {{ old('user_type' ?? '') == 'Kasir' ? 'selected' : '' }}>Kasir</option>
                                                            
                                                        </select>
                                                        @error('user_type')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div> --}}
                                        {{-- <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="user_type" class="form-control-label">
                                                        <i class="fas fa-shield-alt"></i> {{ __('Access Rights') }}
                                                    </label>
                                                    <div
                                                        class="@error('user_type') border border-danger rounded-3 @enderror">
                                                        <select class="form-control" name="user_type" id="user_type"
                                                            required>
                                                            <option value="" disabled
                                                                {{ $selectedUserType == '' ? 'selected' : '' }}>Choose
                                                                Access Rights</option>
                                                            @foreach ($userTypes as $type)
                                                                <option value="{{ $type }}"
                                                                    {{ $selectedUserType == $type ? 'selected' : '' }}>
                                                                    {{ $type }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('user_type')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div> --}}


                                        {{-- <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="role" class="form-control-label">
                                                        <i class="fas fa-user-tag"></i> {{ __('Role') }}
                                                    </label>
                                                    <div
                                                        class="@error('role') border border-danger rounded-3 p-3 @enderror">
                                                     

                                                        @foreach ($roles as $role)
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="role[]" id="role_{{ $role }}"
                                                                    value="{{ $role }}"
                                                                    {{ in_array($role, $selectedRoles) ? 'checked' : '' }}>
                                                                <label class="form-check-label"
                                                                    for="role_{{ $role }}">
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
                                            </div> --}}

                                        {{-- <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="phone" class="form-control-label">
                                                        <i class="fas fa-phone"></i> {{ __('Phone Number') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control" value="{{ old('phone') }}"
                                                            type="text" id="phone" name="phone"
                                                            aria-describedby="info-phone" maxlength="13" required
                                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                            placeholder="Fill Phone Number">
                                                        <small class="text-muted">Numbers only. Max 13 digits.</small>
                                                        @error('phone')
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
                                                - If a username is already registered, you cannot register it again.<br>
                                            </span>
                                        </div>

                                        <div class="d-flex justify-content-end mt-4">
                                            <a href="{{ route('pages.dashboardAdmin') }}" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> {{ __('Cancel') }}
                                            </a>
                                            <button type="submit" class="btn bg-gradient-dark">
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
