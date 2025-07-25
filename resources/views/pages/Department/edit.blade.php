@extends('layouts.app')
@section('title', 'Update Department')
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
                <h1>Update Departments {{ $department->department_name }}</h1>
                <div class="section-header-breadcrumb">
                    {{-- <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div> --}}
                    <div class="breadcrumb-item"><a href="{{ route('pages.Department') }}">Departments</a></div>
                    <div class="breadcrumb-item">Update Departments {{ $department->department_name }}</div>
                </div>
            </div>

            <div class="section-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0 px-3">
                                    <h6 class="mb-0">{{ __('Update Department') }} {{ $department->department_name }}</h6>
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

                                    <form id="departments-edit" action="{{ route('Department.update', $hashedId) }}" method="POST">
                                        @csrf
                                        @method('PUT')

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="department_name" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Department Name') }}
                                                    </label>
                                                    <div>
                                                        <input type="text" class="form-control @error('department_name') is-invalid @enderror" id="department_name"
                                                            name="department_name" value="{{ old('department_name', $department->department_name) }}"
                                                            placeholder="IT" required>
                                                        @error('department_name')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="manager_id" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Managers Name') }}
                                                    </label>
                                                    {{-- <select name="manager_id" id="manager_id"
                                                        class="form-control @error('manager_id') is-invalid @enderror">
                                                        <option value="">-- Pilih Manager --</option>
                                                        @foreach($managers as $manager)
                                                            <option value="{{ $manager->id }}" {{ (isset($department) && $department->manager_id == $manager->id) ? 'selected' : '' }}>
                                                                {{ $manager->Employee->employee_name ?? $manager->name ?? 'Tanpa Nama' }}
                                                            </option>
                                                        @endforeach
                                                    </select> --}}
                                                      <select name="manager_id" id="manager_id"
                                                            class="form-control select2 @error('manager_id') is-invalid @enderror">
                                                            <option value="">Choose Managers</option>
                                                            @foreach ($managers as $manager)
                                                                                                                              <option value="{{ $manager->id }}" {{ (isset($department) && $department->manager_id == $manager->id) ? 'selected' : '' }}>
                                                       
                                                                    {{ $manager->Employee->employee_name ?? $manager->name ?? 'Tanpa Nama' }}

                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @error('manager_id')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            </div>
                                            


                                        <div class="alert alert-secondary mt-4" role="alert">
                                            <span class="text-dark">
                                                <strong>Important Note:</strong> <br>
                                                - If a Departmnet name is already registered, you cannot register it again.<br>
                                                - If a Departmnet Manager is already registered, you cannot register it again.<br>
                                                
                                            </span>
                                        </div>

                                        <div class="d-flex justify-content-end mt-4">
                                            <a href="{{ route('pages.Department') }}" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> {{ __('Cancel') }}
                                            </a>
                                            <button type="submit" id="edit-btn" class="btn bg-primary">
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
<script src="{{ asset('node_modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
      $(document).ready(function() {
        $('.select2').select2();
    });
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
                      document.getElementById('departments-edit').submit();
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
    {{-- <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="fullName" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Full Name') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control"
                                                            value="{{ old('name', $user->Employee->fullName ?? '') }}"
                                                            type="text" id="fullName" name="fullName"
                                                            aria-describedby="info-fullName"
                                                            maxlength="255"placeholder="Christopher Edwin Sirait, S.Kom.">
                                                        @error('fullName')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div> --}}
    {{-- <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="position" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Position') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control" value="{{ old('position', $user->Employee->position ?? '') }}"
                                                            type="text" id="position" name="position" aria-describedby="info-position"
                                                            maxlength="255" placeholder="Programmer">
                                                        @error('position')
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
                                                    <label for="department_id" class="form-control-label">
                                                        <i class="fas fa-shield-alt"></i> {{ __('Department') }}
                                                    </label>
                                                    <div class="@error('department_id') border border-danger rounded-3 @enderror">
                                                        <select class="form-control" name="department_id" id="department_id" required>
                                                         
                                                                <option value="" disabled {{ $selectedDepartments == '' ? 'selected' : '' }}>Choose Department</option>
                                                                @foreach ($departments as $type)
                                                                    <option value="{{ $type }}" {{ $selectedDepartments == $type ? 'selected' : '' }}>{{ $type }}</option>
                                                                @endforeach
                                                        </select>
                                                        @error('department_id')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div> --}}
    {{-- <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="hireDate" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Hire Date') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control" value="{{ old('hireDate', $user->Employee->hireDate ?? '') }}"
                                                            type="date" id="hireDate" name="hireDate" aria-describedby="info-hireDate"
                                                            required>
                                                        @error('hireDate')
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
                                                <label for="phone" class="form-control-label">
                                                    <i class="fas fa-phone"></i> {{ __('Phone Number') }}
                                                </label>
                                                <div>
                                                    <input class="form-control" value="{{ old('phone', $user->Employee->phone ?? '') }}"
                                                        type="text" id="phone" name="phone"
                                                        aria-describedby="info-phone" maxlength="13" placeholder="088xxxxxxx"
                                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                                    <small class="text-muted">Numbers only. Max 13 digits.</small>
                                                    @error('phone')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div> --}}
    {{-- <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email" class="form-control-label">
                                                    <i class="fas fa-id-card"></i> {{ __('Email') }}
                                                </label>
                                                <div>
                                                    <input class="form-control" value="{{ old('email', $user->Employee->email ?? '') }}"
                                                        type="email" id="email" name="email" aria-describedby="info-email" placeholder="edwin.sirait7994@gmail.com"
                                                        required>
                                                    @error('email')
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
                                                <label for="salary" class="form-control-label">
                                                    <i class="fas fa-dollar"></i> {{ __('Salary') }}
                                                </label>
                                                <div>
                                                    <input class="form-control" value="{{ old('salary', $user->Employee->salary ?? '') }}"
                                                        type="number" id="salary" name="salary"
                                                        aria-describedby="info-salary" placeholder="expl: 3000000.00" required >
                                                    <small class="text-muted">Numbers only.</small>
                                                    @error('salary')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div> --}}



    {{-- <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="status" class="form-control-label">
                                                        <i class="fas fa-shield-alt"></i> {{ __('Status Employee') }}
                                                    </label>
                                                    <div class="@error('status') border border-danger rounded-3 @enderror">
                                                        <select class="form-control" name="status" id="status" required>
                                                         
                                                                <option value="" disabled {{ $selectedStatus == '' ? 'selected' : '' }}>Choose Status</option>
                                                                @foreach ($status as $type)
                                                                    <option value="{{ $type }}" {{ $selectedStatus == $type ? 'selected' : '' }}>{{ $type }}</option>
                                                                @endforeach
                                                        </select>
                                                        @error('status')
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
                                                    <label for="role" class="form-control-label">
                                                        <i class="fas fa-user-tag"></i> {{ __('Role') }}
                                                    </label>
                                                    <div
                                                        class="@error('role') border border-danger rounded-3 p-3 @enderror">


                                                        @foreach ($allRoles as $role)
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
                                            </div>
                                        </div> --}}
    {{-- <div class="row mt-3">

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="user_type" class="form-control-label">
                                                        <i class="fas fa-shield-alt"></i> {{ __('Access Rights') }}
                                                    </label>
                                                    <div
                                                        class="@error('user_type') border border-danger rounded-3 @enderror">
                                                        <select class="form-control" name="user_type" id="user_type"
                                                            required>
                                                            
                                                            <option value="" disabled
                                                                {{ $selectedUser == '' ? 'selected' : '' }}>Choose Access
                                                                Rights</option>
                                                            @foreach ($usertype as $type)
                                                                <option value="{{ $type }}"
                                                                    {{ $selectedUser == $type ? 'selected' : '' }}>
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
                                            </div> --}}
    {{-- <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="roles[]" class="form-control-label">
                                                        <i class="fas fa-shield-alt"></i> {{ __('Roles') }}
                                                    </label>
                                                    <div class="@error('roles[]') border border-danger rounded-3 @enderror">
                                                        <select class="form-control" name="roles[]" id="roles[]"
                                                            required>
                                                            <option value="" disabled
                                                                {{ $selectedRolesType == '' ? 'selected' : '' }}>Choose
                                                                Roles</option>
                                                            @foreach ($Roles as $role)
                                                                <option value="{{ $role }}"
                                                                    {{ $selectedRolesType == $role ? 'selected' : '' }}>
                                                                    {{ $role }}</option>
                                                            @endforeach
                                                        </select>
                                                        
                                                        @error('roles[]')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            </div> --}}
