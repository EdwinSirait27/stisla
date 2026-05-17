@extends('layouts.app')
@section('title', 'Change Password')
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
                <h1>Change Password</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
                    <div class="breadcrumb-item">Change Password</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row mt-sm-4 justify-content-center">
                    <div class="col-12 col-md-10 col-lg-8">
                        <div class="card card-modern">
                            <div class="card-header">
                                <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i> Change your Password</h4>
                            </div>

                            <form action="{{ route('change-password.update') }}" method="POST">
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
                                        <div class="col-md-12 mb-3">
                                            <label for="password"><i class="fas fa-lock"></i> Password</label>
                                            <div class="input-group">
                                                <input type="password"
                                                    class="form-control @error('password') is-invalid @enderror"
                                                    id="password" name="password"
                                                    placeholder="Leave blank to keep current password" minlength="8"
                                                    maxlength="20" oninput="this.value = this.value.replace(/\s/g, '');">
                                                <span class="input-group-text" onclick="togglePassword()"
                                                    style="cursor:pointer;">
                                                    <i id="eyeIcon" class="fa fa-eye"></i>
                                                </span>
                                                @error('password')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <small class="text-muted">
                                                Password must contain at least 1 uppercase letter, 1 lowercase letter,
                                                1 number, and 1 symbol. (8-20 chars) for exmpl. mJmRet@il123
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex justify-content-between">
                                    <a href="{{ route('pages.feature-profile') }}" class="btn btn-modern btn-modern-secondary">
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