@extends('layouts.app')
@section('title', 'Profile')
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    /* ─── Page ───────────────────────────────────────────── */
    .section-header h1 {
        font-size: 1.4rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }

    /* ─── Card shell ─────────────────────────────────────── */
    .profile-card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 1px 2px rgba(0,0,0,.04);
        background: #fff;
        overflow: hidden;
    }

    /* ─── Hero header ────────────────────────────────────── */
    .profile-hero {
        padding: 24px 24px 0;
        display: flex;
        align-items: flex-end;
        gap: 18px;
        border-bottom: 1px solid #f1f5f9;
    }

    .profile-avatar-wrap {
        position: relative;
        flex-shrink: 0;
        margin-bottom: 15px;
    }

    .profile-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 1.4rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #fff;
        box-shadow: 0 0 0 1px #e2e8f0;
        letter-spacing: .5px;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .profile-avatar-verified {
        position: absolute;
        bottom: 1px;
        right: 1px;
        width: 20px;
        height: 20px;
        background: #f0fdf4;
        border: 2px solid #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .55rem;
        color: #16a34a;
    }

    .profile-hero-info {
        flex: 1;
        padding-bottom: 16px;
    }

    .profile-hero-name {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.3;
    }

    .profile-hero-sub {
        font-size: .78rem;
        color: #64748b;
        margin-top: 3px;
    }

    .profile-hero-tags {
        display: flex;
        gap: 6px;
        margin-top: 10px;
        flex-wrap: wrap;
    }

    .profile-tag {
        display: inline-flex;
        align-items: center;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: .7rem;
        font-weight: 600;
        letter-spacing: .2px;
    }

    .profile-tag-dept   { background: #eff6ff; color: #1d4ed8; }
    .profile-tag-status { background: #f0fdf4; color: #16a34a; }
    .profile-tag-grade  { background: #fffbeb; color: #92400e; }

    /* ─── Section groups ─────────────────────────────────── */
    .profile-body {
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .profile-section-label {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: #94a3b8;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .profile-section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #f1f5f9;
    }

    /* ─── Field grid ─────────────────────────────────────── */
    .field-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 12px;
    }

    .field-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .field-group label {
        font-size: .72rem;
        font-weight: 600;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 5px;
        margin: 0;
    }

    .field-group label i {
        font-size: .68rem;
        color: #94a3b8;
        width: 12px;
        text-align: center;
    }

    /* read-only field (display only) */
    .field-readonly {
        height: 36px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: .5rem;
        padding: 0 .75rem;
        font-size: .825rem;
        color: #475569;
        display: flex;
        align-items: center;
    }

    /* editable form-control override */
    .field-group .form-control {
        height: 36px;
        font-size: .825rem;
        border-color: #e2e8f0;
        border-radius: .5rem;
        color: #1e293b;
        padding: 0 .75rem;
        background: #fff;
    }

    .field-group .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,.12);
    }

    .field-group .form-control.is-invalid {
        border-color: #ef4444;
    }

    .field-group .invalid-feedback {
        font-size: .72rem;
    }

    /* ─── Photo upload ───────────────────────────────────── */
    .photo-upload-wrap {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .photo-thumb {
        width: 56px;
        height: 56px;
        border-radius: .5rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        cursor: pointer;
    }

    .photo-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .photo-thumb i {
        color: #cbd5e1;
        font-size: 1.4rem;
    }

    .photo-upload-hint {
        font-size: .72rem;
        color: #94a3b8;
        margin-bottom: 6px;
    }

    .photo-upload-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 32px;
        padding: 0 12px;
        border: 1px dashed #cbd5e1;
        border-radius: .5rem;
        background: #f8fafc;
        color: #64748b;
        font-size: .775rem;
        font-weight: 500;
        cursor: pointer;
        transition: all .2s;
    }

    .photo-upload-btn:hover {
        border-color: #3b82f6;
        color: #3b82f6;
        background: #eff6ff;
    }

    /* ─── Alert ──────────────────────────────────────────── */
    .alert-success-custom {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 9px 14px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: .5rem;
        font-size: .8rem;
        color: #166534;
        margin-bottom: 20px;
    }

    .alert-danger-custom {
        padding: 9px 14px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: .5rem;
        font-size: .8rem;
        color: #991b1b;
        margin-bottom: 20px;
    }

    .alert-danger-custom ul {
        margin: 0;
        padding-left: 16px;
    }

    /* ─── Footer ─────────────────────────────────────────── */
    .profile-footer {
        padding: 14px 24px;
        border-top: 1px solid #f1f5f9;
        background: #fafafa;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .profile-footer .btn {
        height: 36px;
        font-size: .825rem;
        font-weight: 500;
        padding: 0 1rem;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        border-radius: .5rem;
    }
    .password-footer {
        padding: 14px 24px;
        border-top: 1px solid #f1f5f9;
        background: #ffffff;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .password-footer .btn {
        height: 36px;
        font-size: .825rem;
        font-weight: 500;
        padding: 0 1rem;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        border-radius: .5rem;
    }

    .btn-back {
        background: #fff;
        border: 1px solid #e2e8f0;
        color: #475569;
    }

    .btn-back:hover {
        background: #f8fafc;
        color: #1e293b;
    }

    .btn-save {
        background: #1d4ed8;
        border: none;
        color: #fff;
    }

    .btn-save:hover {
        background: #1e40af;
        color: #fff;
    }

    /* ─── Responsive ─────────────────────────────────────── */
    @media (max-width: 576px) {
        .profile-hero {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
            padding: 16px 16px 0;
        }

        .profile-body {
            padding: 16px;
        }

        .profile-footer {
            padding: 12px 16px;
        }

        .field-grid {
            grid-template-columns: 1fr;
        }
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
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">

                    <div class="profile-card">

                        {{-- ── Hero ── --}}
                        <div class="profile-hero">
                            <div class="profile-avatar-wrap">
                                <div class="profile-avatar">
                                    @if (!empty($user->employee->photos))
                                              <img alt="image"
                    src="{{ Auth::user()->employee->photos
                                    ? asset('storage/' . Auth::user()->employee->photos)
                                    : asset('img/avatar/avatar-1.png') }}">
                                    @else
                                        {{-- initials fallback --}}
                                        {{ collect(explode(' ', $user->employee->employee_name ?? $user->username ?? 'U'))
                                              ->take(2)->map(fn($w) => strtoupper($w[0]))->implode('') }}
                                    @endif
                                </div>
                                
                                <div class="profile-avatar-verified">
                                    <i class="fas fa-check" style="font-size:.5rem"></i>
                                </div>
                                
                            </div>

                            <div class="profile-hero-info">
                                <div class="profile-hero-name">
                                    {{ $user->employee->employee_name ?? $user->username ?? '-' }}
                                </div>
                                <div class="profile-hero-sub">
                                    NIP : {{ $user->username ?? '' }}
                                    @if(!empty($user->employee->email))
                                        Email&nbsp;:&nbsp;{{ $user->employee->email }}
                                    @endif
                                </div>
                                <div class="profile-hero-tags">
                                    @if(!empty($user->employee->department->department_name))
                                        <span class="profile-tag profile-tag-dept">
                                            Department : {{ $user->employee->department->department_name }}
                                        </span>
                                    @endif
                                    @if(!empty($user->employee->status_employee))
                                        <span class="profile-tag profile-tag-status">
                                            Status : {{ $user->employee->status_employee }}
                                        </span>
                                    @endif
                                    @if(!empty($user->employee->grading->grading_name))
                                        <span class="profile-tag profile-tag-grade">
                                            Grading : {{ $user->employee->grading->grading_name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                             <div class="password-footer">
    <a href="{{ route('pages.change-password') }}" class="btn btn-lock">
        <i class="fas fa-key"></i> Change Password
    </a>
</div>
                        </div>
                        
                        {{-- ── /Hero ── --}}

                        <form action="{{ route('feature-profile.update') }}" method="POST"
                              enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="profile-body">

                                {{-- Flash messages --}}
                                @if (session('status') || session('success'))
                                    <div class="alert-success-custom">
                                        <i class="fas fa-check-circle"></i>
                                        {{ session('status') ?? session('success') }}
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert-danger-custom">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                {{-- ── Section: Account ── --}}
                                <div>
                                    <div class="profile-section-label">Account</div>
                                    <div class="field-grid">
                                        <div class="field-group">
                                            <label><i class="fas fa-user"></i> Username</label>
                                            <div class="field-readonly">
                                                {{ $user->username ?? '-' }}
                                            </div>
                                        </div>
                                         
                                        <div class="field-group">
                                            <label><i class="fas fa-user-tie"></i> Employee Name</label>
                                            <div class="field-readonly">
                                                {{ $user->employee->employee_name ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── Section: Identity ── --}}
                                <div>
                                    <div class="profile-section-label">Identity</div>
                                    <div class="field-grid">
                                        <div class="field-group">
                                            <label><i class="fas fa-id-card"></i> NIK</label>
                                            <div class="field-readonly">
                                                {{ $user->employee->nik ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="field-group">
                                            <label><i class="fas fa-file-invoice"></i> NPWP</label>
                                            <div class="field-readonly">
                                                {{ $user->employee->npwp ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="field-group">
                                            <label><i class="fas fa-map-marker-alt"></i> Place of birth</label>
                                            <div class="field-readonly">
                                                {{ $user->employee->place_of_birth ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="field-group">
                                            <label><i class="fas fa-calendar"></i> Date of birth</label>
                                            <div class="field-readonly">
                                                {{ $user->employee->date_of_birth
                                                    ? \Carbon\Carbon::parse($user->employee->date_of_birth)->format('d F Y')
                                                    : '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── Section: Employment ── --}}
                                <div>
                                    <div class="profile-section-label">Employment</div>
                                    <div class="field-grid">
                                        <div class="field-group">
                                            <label><i class="fas fa-building"></i> Company</label>
                                            <div class="field-readonly">
                                                {{ $user->employee->company->name ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="field-group">
                                            <label><i class="fas fa-sitemap"></i> Department</label>
                                            <div class="field-readonly">
                                                {{ $user->employee->department->department_name ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="field-group">
                                            <label><i class="fas fa-briefcase"></i> Position</label>
                                            <div class="field-readonly">
                                                {{ $user->employee->position->name ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="field-group">
                                            <label><i class="fas fa-store"></i> Location</label>
                                            <div class="field-readonly">
                                                {{ $user->employee->store->name ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="field-group">
                                            <label><i class="fas fa-layer-group"></i> Grading</label>
                                            <div class="field-readonly">
                                                {{ $user->employee->grading->grading_name ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="field-group">
                                            <label><i class="fas fa-circle-dot"></i> Employee status</label>
                                            <div class="field-readonly">
                                                {{ $user->employee->status_employee ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── Section: BPJS ── --}}
                                <div>
                                    <div class="profile-section-label">BPJS</div>
                                    <div class="field-grid">
                                        <div class="field-group">
                                            <label><i class="fas fa-heart-pulse"></i> BPJS Kesehatan</label>
                                            <div class="field-readonly">
                                                {{ $user->employee->bpjs_kes ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="field-group">
                                            <label><i class="fas fa-shield-halved"></i> BPJS Ketenagakerjaan</label>
                                            <div class="field-readonly">
                                                {{ $user->employee->bpjs_ket ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── Section: Contact (editable) ── --}}
                                <div>
                                    <div class="profile-section-label">Contact</div>
                                    <div class="field-grid">
                                        <div class="field-group">
                                            <label for="email"><i class="fas fa-envelope"></i> Email</label>
                                            <input type="email" id="email" name="email"
                                                class="form-control @error('email') is-invalid @enderror"
                                                value="{{ old('email', $user->employee->email ?? '') }}"
                                                placeholder="Enter email" required>
                                            @error('email')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="field-group">
                                            <label for="telp_number"><i class="fas fa-phone"></i> Phone number</label>
                                            <input type="tel" id="telp_number" name="telp_number"
                                                class="form-control @error('telp_number') is-invalid @enderror"
                                                value="{{ old('telp_number', $user->employee->telp_number ?? '') }}"
                                                placeholder="Enter phone number" required>
                                            @error('telp_number')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- ── Section: Photo ── --}}
                                <div>
                                    <div class="profile-section-label">Profile photo</div>
                                    <div class="photo-upload-wrap">
                                        <div class="photo-thumb" onclick="document.getElementById('photos').click()">
                                            @if (!empty($user->employee->photos))
                                                <img id="preview-image"
                                                     src="{{ asset('storage/' . $user->employee->photos) }}"
                                                     alt="Profile photo">
                                            @else
                                                <img id="preview-image"
                                                     src="https://via.placeholder.com/56"
                                                     alt="No photo" style="display:none">
                                                <i class="fas fa-user" id="photo-placeholder"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="photo-upload-hint">JPG, PNG or WEBP — max 2 MB</div>
                                            <label for="photos" class="photo-upload-btn">
                                                <i class="fas fa-arrow-up-from-bracket" style="font-size:.7rem"></i>
                                                Upload photo
                                            </label>
                                            <input type="file" name="photos" id="photos"
                                                class="d-none @error('photos') is-invalid @enderror"
                                                accept="image/*" onchange="previewProfilePhoto(event)">
                                            @error('photos')
                                                <div class="text-danger mt-1" style="font-size:.72rem">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                            </div>{{-- /.profile-body --}}
  <div class="hint-bar">
                        <div class="hint-item">
                            <i class="text-secondary"></i>Information : For email, telephone number and photo, they can be changed but for email and telephone number, it is a request so HR will replace it.
                        </div>
                      
                    </div>
                            <div class="profile-footer">
                                <a href="{{ url()->previous() }}" class="btn btn-back">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                                <button type="submit" class="btn btn-save">
                                    <i class="fas fa-floppy-disk"></i> Save changes
                                </button>
                            </div>
                            
                        </form>

                    </div>{{-- /.profile-card --}}
                    

                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
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
                                        </script>
<script>
    function previewProfilePhoto(event) {
        const file  = event.target.files[0];
        if (!file) return;
        const url   = URL.createObjectURL(file);
        const img   = document.getElementById('preview-image');
        const ph    = document.getElementById('photo-placeholder');
        img.src     = url;
        img.style.display = 'block';
        if (ph) ph.style.display = 'none';
    }

    @if (session('success') || session('status'))
        Swal.fire({
            icon: 'success',
            title: 'Saved',
            text: '{{ session('success') ?? session('status') }}',
            confirmButtonColor: '#1d4ed8',
            timer: 3000,
            timerProgressBar: true
        });
    @endif

    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
            confirmButtonColor: '#dc2626'
        });
    @endif
</script>
@endpush
{{-- @extends('layouts.app')
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
@endpush --}}

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