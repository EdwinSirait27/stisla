{{-- @extends('layouts.app')
@section('title', 'Profile')
@push('style')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.css') }}">
    <link rel="stylesheet" href="{{ asset('library/bootstrap-social/assets/css/bootstrap.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 2.5rem;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            /* sejajarkan tengah */
            right: 0.75rem;
            transform: translateY(-50%);
            /* pastikan tengah vertikal */
            cursor: pointer;
            color: #6c757d;
            font-size: 1.2rem;
            z-index: 10;
        }
    </style>
@endpush
@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Profile</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
                    <div class="breadcrumb-item">Profile</div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Hai, {{ Auth::user()->employee->employee_name }}</h2>
                <p class="section-lead">
                    Bring out your morning spirit
                </p>
                <div class="row mt-sm-4">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <form action="{{ route('feature-profile.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="card-body">
                                    <h4>Edit Your Profile</h4>
                                    @if (session('status'))
                                        <div class="alert alert-success">
                                            {{ session('status') }}
                                        </div>
                                    @endif
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="password" class="form-control-label">
                                                    <i class="fas fa-lock"></i> {{ __('Password') }}
                                                </label>
                                                <div class="input-group">
                                                    <input type="password"
                                                        class="form-control @error('password') is-invalid @enderror"
                                                        id="password" name="password"
                                                        placeholder="Leave blank to keep current password"
                                                        aria-describedby="password-addon" minlength="8" maxlength="20"
                                                        oninput="this.value = this.value.replace(/\s/g, '');" />

                                                    <div class="input-group-append">
                                                        <span class="input-group-text" onclick="togglePassword()"
                                                            style="cursor: pointer;">
                                                            <i id="eyeIcon" class="fa fa-eye"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    Password must contain at least 1 uppercase letter, 1 lowercase letter, 1
                                                    number, and 1 symbol, and must not contain spaces. minimum length 8
                                                    characters anda Max 12
                                                    characters.
                                                </small>
                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
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
                                    </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>

                        </form>
                    </div>
                </div>
            </div>
    </div>
    </section>
    </div>
@endsection
@push('scripts')
    <!-- JS Libraies -->
    <script src="{{ asset('library/summernote/dist/summernote-bs4.js') }}"></script>
    <script src="{{ asset('library/jquery.pwstrength/jquery.pwstrength.min.js') }}"></script>
    <script src="{{ asset('js/page/features-profile.js') }}"></script>
    <!-- Page Specific JS File -->
@endpush --}}
@extends('layouts.app')
@section('title', 'Profile')
@push('style')
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.css') }}">
    <link rel="stylesheet" href="{{ asset('library/bootstrap-social/assets/css/bootstrap.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .password-wrapper {
            position: relative;
        }
        .password-wrapper input {
            padding-right: 2.5rem;
        }
        .toggle-password {
            position: absolute;
            top: 50%;
            right: 0.75rem;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            font-size: 1.2rem;
            z-index: 10;
        }
    </style>
@endpush
@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Profile</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
                    <div class="breadcrumb-item">Profile</div>
                </div>
            </div>
            <div class="section-body">
                <div class="row mt-sm-4">
                    <div class="col-12">
                        <div class="card">
                            <form action="{{ route('feature-profile.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="card-body">
                                    <h4>Edit Your Profile</h4>

                                    {{-- Alert --}}
                                    @if (session('status'))
                                        <div class="alert alert-success">
                                            {{ session('status') }}
                                        </div>
                                    @endif
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <div class="row">
                                        {{-- Employee Name --}}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                 <label for="username" class="form-control-label">
                                                    <i class="fas fa-user"></i> {{ __('Username') }}
                                                </label>
                                              <input type="text"
                                                    class="form-control @error('username') is-invalid @enderror"
                                                    id="username" name="username"
                                                    value="{{ old('username', $user->username ?? '') }}"
                                                    placeholder="Enter username" disabled>
                                                @error('username')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Username --}}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="employee_name" class="form-control-label">
                                                    <i class="fas fa-user"></i> {{ __('Employee Name') }}
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('employee_name') is-invalid @enderror"
                                                    id="employee_name" name="employee_name"
                                                    value="{{ old('employee_name', $user->employee->employee_name ?? '') }}"
                                                    placeholder="Enter Employee Name" disabled>
                                                @error('employee_name')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="nik" class="form-control-label">
                                                    <i class="fas fa-user"></i> {{ __('NIK') }}
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('nik') is-invalid @enderror"
                                                    id="nik" name="nik"
                                                    value="{{ old('nik', $user->employee->nik ?? '') }}"
                                                    placeholder="Enter NIK" disabled>
                                                @error('nik')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                         <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="bpjs_ket" class="form-control-label">
                                                    <i class="fas fa-user"></i> {{ __('BPJS Ketenagakerjaan') }}
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('bpjs_ket') is-invalid @enderror"
                                                    id="bpjs_ket" name="bpjs_ket"
                                                    value="{{ old('bpjs_ket', $user->employee->bpjs_ket ?? '') }}"
                                                    placeholder="Enter BPJS Ketenagakerjaan" disabled>
                                                @error('nik')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                         <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="bpjs_kes" class="form-control-label">
                                                    <i class="fas fa-user"></i> {{ __('BPJS Kesehatan') }}
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('bpjs_kes') is-invalid @enderror"
                                                    id="bpjs_kes" name="bpjs_kes"
                                                    value="{{ old('bpjs_kes', $user->employee->bpjs_kes ?? '') }}"
                                                    placeholder="Enter BPJS Kesehatan" disabled>
                                                @error('nik')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                         <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="npwp" class="form-control-label">
                                                    <i class="fas fa-user"></i> {{ __('NPWP') }}
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('npwp') is-invalid @enderror"
                                                    id="npwp" name="npwp"
                                                    value="{{ old('npwp', $user->employee->npwp ?? '') }}"
                                                    placeholder="Enter NPWP" disabled>
                                                @error('nik')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                         <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="place_of_birth" class="form-control-label">
                                                    <i class="fas fa-user"></i> {{ __('Place of Birth') }}
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('place_of_birth') is-invalid @enderror"
                                                    id="place_of_birth" name="place_of_birth"
                                                    value="{{ old('place_of_birth', $user->employee->place_of_birth ?? '') }}"
                                                    placeholder="Enter Place of Birth" disabled>
                                                @error('nik')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                           <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="date_of_birth" class="form-control-label">
                                                    <i class="fas fa-user"></i> {{ __('Date of Birth') }}
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('date_of_birth') is-invalid @enderror"
                                                    id="date_of_birth" name="date_of_birth"
                                                    value="{{ old('date_of_birth', $user->employee->date_of_birth ?? '') }}"
                                                    placeholder="Enter Date" disabled>
                                                @error('nik')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                           <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="status_employee" class="form-control-label">
                                                    <i class="fas fa-user"></i> {{ __('Employee Status') }}
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('status_employee') is-invalid @enderror"
                                                    id="status_employee" name="status_employee"
                                                    value="{{ old('status_employee', $user->employee->status_employee ?? '') }}"
                                                    placeholder="Enter Status" disabled>
                                                @error('nik')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                           <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name" class="form-control-label">
                                                    <i class="fas fa-user"></i> {{ __('Company Name') }}
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('name') is-invalid @enderror"
                                                    id="name" name="name"
                                                    value="{{ old('name', $user->employee->company->name ?? '') }}"
                                                    placeholder="Enter Cmpany Name" disabled>
                                                @error('nik')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                         <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="grading_name" class="form-control-label">
                                                    <i class="fas fa-user"></i> {{ __('Grading') }}
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('grading_name') is-invalid @enderror"
                                                    id="grading_name" name="grading_name"
                                                    value="{{ old('grading_name', $user->employee->grading->grading_name ?? '') }}"
                                                    placeholder="Enter Grading" disabled>
                                                @error('nik')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="department_name" class="form-control-label">
                                                    <i class="fas fa-user"></i> {{ __('Department') }}
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('department_name') is-invalid @enderror"
                                                    id="department_name" name="department_name"
                                                    value="{{ old('department_name', $user->employee->department->department_name ?? '') }}"
                                                    placeholder="Enter Department" disabled>
                                                @error('nik')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                         <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name" class="form-control-label">
                                                    <i class="fas fa-user"></i> {{ __('Position') }}
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('name') is-invalid @enderror"
                                                    id="name" name="name"
                                                    value="{{ old('name', $user->employee->position->name ?? '') }}"
                                                    placeholder="Enter Position" disabled>
                                                @error('nik')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                          <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name" class="form-control-label">
                                                    <i class="fas fa-user"></i> {{ __('Location') }}
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('name') is-invalid @enderror"
                                                    id="name" name="name"
                                                    value="{{ old('name', $user->employee->store->name ?? '') }}"
                                                    placeholder="Enter Location" disabled>
                                                @error('nik')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                          <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email" class="form-control-label">
                                                    <i class="fas fa-user"></i> {{ __('Email') }}
                                                </label>
                                                <input type="email"
                                                    class="form-control @error('email') is-invalid @enderror"
                                                    id="email" name="email"
                                                    value="{{ old('email', $user->employee->email ?? '') }}"
                                                    placeholder="Enter email" required>
                                                @error('email')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                          <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="telp_number" class="form-control-label">
                                                    <i class="fas fa-user"></i> {{ __('Telephone Number') }}
                                                </label>
                                                <input type="number"
                                                    class="form-control @error('telp_number') is-invalid @enderror"
                                                    id="telp_number" name="telp_number"
                                                    value="{{ old('telp_number', $user->employee->telp_number ?? '') }}"
                                                    placeholder="Enter Phone" required>
                                                @error('telp_number')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Password --}}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="password" class="form-control-label">
                                                    <i class="fas fa-lock"></i> {{ __('Password') }}
                                                </label>
                                                <div class="input-group">
                                                    <input type="password"
                                                        class="form-control @error('password') is-invalid @enderror"
                                                        id="password" name="password"
                                                        placeholder="Leave blank to keep current password"
                                                        aria-describedby="password-addon"
                                                        minlength="8" maxlength="20"
                                                        oninput="this.value = this.value.replace(/\s/g, '');">

                                                    <div class="input-group-append">
                                                        <span class="input-group-text" onclick="togglePassword()" style="cursor: pointer;">
                                                            <i id="eyeIcon" class="fa fa-eye"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    Password must contain at least 1 uppercase letter, 1 lowercase letter,
                                                    1 number, and 1 symbol, and must not contain spaces. Min 8 - Max 20 characters.
                                                </small>
                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer d-flex justify-content-between">
                                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <!-- JS Libraies -->
    <script src="{{ asset('library/summernote/dist/summernote-bs4.js') }}"></script>
    <script src="{{ asset('library/jquery.pwstrength/jquery.pwstrength.min.js') }}"></script>
    <script src="{{ asset('js/page/features-profile.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


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
          @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
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
