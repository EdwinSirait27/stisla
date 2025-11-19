@extends('layouts.app')
@section('title', 'Profile')

@push('style')
<link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.css') }}">
<link rel="stylesheet" href="{{ asset('library/bootstrap-social/assets/css/bootstrap.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    body {
        background: #f5f6fa;
    }

    .card-modern {
        border: none;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease-in-out;
    }

    .card-modern:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
    }

    .form-control {
        border-radius: 12px;
        border: 1px solid #dcdde1;
        transition: all 0.2s ease-in-out;
    }

    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    .form-group label {
        font-weight: 600;
        color: #2f3640;
    }

    .section-header h1 {
        font-weight: 700;
        color: #2c3e50;
    }

    .btn-modern {
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease-in-out;
    }

    .btn-modern-primary {
        background: linear-gradient(135deg, #4e73df, #224abe);
        color: white;
        border: none;
    }

    .btn-modern-primary:hover {
        background: linear-gradient(135deg, #224abe, #4e73df);
    }

    .btn-modern-secondary {
        background: #f1f2f6;
        color: #2f3640;
        border: none;
    }

    .btn-modern-secondary:hover {
        background: #dfe4ea;
    }

    .input-group-text {
        background: #f1f2f6;
        border: none;
    }

    .alert {
        border-radius: 12px;
    }

    small.text-muted {
        font-size: 0.85rem;
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
            <div class="row mt-sm-4 justify-content-center">
                <div class="col-12 col-md-10 col-lg-8">
                    <div class="card card-modern">
                        <div class="card-header">
                            <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i> Edit Your Profile</h4>
                        </div>

                        <form action="{{ route('feature-profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="card-body">

                                @if (session('status'))
                                    <div class="alert alert-success">
                                        {{ session('status') }}
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="username"><i class="fas fa-user"></i> Username</label>
                                        <input type="text"
                                            class="form-control @error('username') is-invalid @enderror"
                                            id="username" name="username"
                                            value="{{ old('username', $user->username ?? '') }}"
                                            placeholder="Enter username" disabled>
                                        @error('username')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="employee_name"><i class="fas fa-user-tie"></i> Employee Name</label>
                                        <input type="text"
                                            class="form-control @error('employee_name') is-invalid @enderror"
                                            id="employee_name" name="employee_name"
                                            value="{{ old('employee_name', $user->employee->employee_name ?? '') }}"
                                            placeholder="Enter employee name" disabled>
                                        @error('employee_name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
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
                                         <div class="col-md-6 mb-3">
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
                                         <div class="col-md-6 mb-3">
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
                                         <div class="col-md-6 mb-3">
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
                                         <div class="col-md-6 mb-3">
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
                                           <div class="col-md-6 mb-3">
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

                                           <div class="col-md-6 mb-3">
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
                                           <div class="col-md-6 mb-3">
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

                                         <div class="col-md-6 mb-3">
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
                                        <div class="col-md-6 mb-3">
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
                                         <div class="col-md-6 mb-3">
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
                                          <div class="col-md-6 mb-3">
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
                                          <div class="col-md-6 mb-3">
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
                                          <div class="col-md-6 mb-3">
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
                                         <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="foto" class="form-control-label">
                                                <i class="fas fa-id-card"></i> {{ __('Images') }}
                                            </label>

                                                {{-- Preview Image --}}
                                                <div class="mb-2">
                                                    @if (!empty($employee->Employee?->foto))
                                                        <img id="preview-image"
                                                            src="{{ asset('storage/employeephotos/' . $employee->Employee->photos) }}"
                                                            alt="Preview" class="img-thumbnail" width="150"
                                                            style="cursor:pointer" onclick="showImageSwal(this.src)">
                                                    @else
                                                        <img id="preview-image" src="https://via.placeholder.com/150"
                                                            alt="Preview" class="img-thumbnail" width="150"
                                                            style="cursor:pointer" onclick="showImageSwal(this.src)">
                                                    @endif
                                             
                                                {{-- File Input --}}
                                                <input type="file" name="photos" id="photos"
                                                    class="form-control @error('photos') is-invalid @enderror"
                                                    accept="image/*" onchange="previewImage(event)">

                                                @error('photos')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                    {{-- <div class="col-md-12 mb-3">
                                        <label for="password"><i class="fas fa-lock"></i> Password</label>
                                        <div class="input-group">
                                            <input type="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                id="password" name="password"
                                                placeholder="Leave blank to keep current password"
                                                minlength="8" maxlength="20"
                                                oninput="this.value = this.value.replace(/\s/g, '');">
                                            <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                                                <i id="eyeIcon" class="fa fa-eye"></i>
                                            </span>
                                            @error('password')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <small class="text-muted">
                                            Password must contain at least 1 uppercase letter, 1 lowercase letter,
                                            1 number, and 1 symbol. (8-20 chars)
                                        </small>
                                    </div> --}}
                                </div>
                            </div>

                            <div class="card-footer d-flex justify-content-between">
                                <a href="{{ url()->previous() }}" class="btn btn-modern btn-modern-secondary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                                <button type="submit" class="btn btn-modern btn-modern-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
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
<script src="{{ asset('library/summernote/dist/summernote-bs4.js') }}"></script>
<script src="{{ asset('library/jquery.pwstrength/jquery.pwstrength.min.js') }}"></script>
<script src="{{ asset('js/page/features-profile.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
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
        confirmButtonColor: '#4e73df'
    });
    @endif

    @if (session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '{{ session('error') }}',
        confirmButtonColor: '#d33'
    });
    @endif
</script>
@endpush

{{-- employee_name dan password
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
                                        </div> --}}