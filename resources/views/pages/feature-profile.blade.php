@extends('layouts.app')
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
    top: 50%; /* sejajarkan tengah */
    right: 0.75rem;
    transform: translateY(-50%); /* pastikan tengah vertikal */
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
                                                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                                                            name="password"
                                                            placeholder="Leave blank to keep current password"
                                                            aria-describedby="password-addon" maxlength="12"
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
@endpush
