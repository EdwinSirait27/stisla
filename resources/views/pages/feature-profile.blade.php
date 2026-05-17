@extends('layouts.app')
@section('title', 'Profile')
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .section-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
            gap: 16px;
        }

        .section-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-header-icon {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3b82f6;
        }

        .section-header h1 {
            font-size: 1.25rem;
            font-weight: 500;
            color: #1e293b;
            margin: 0 0 2px;
            letter-spacing: -0.2px;
        }

        .section-header-subtitle {
            font-size: 0.75rem;
            color: #94a3b8;
            margin: 0;
        }

        .section-header-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .breadcrumb-item {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .breadcrumb-item a {
            color: #3b82f6;
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: #1e293b;
            font-weight: 500;
        }

        .breadcrumb-sep {
            font-size: 0.7rem;
            color: #cbd5e1;
        }

        .profile-card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .07), 0 1px 2px rgba(0, 0, 0, .04);
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

        .profile-tag-dept {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .profile-tag-status {
            background: #f0fdf4;
            color: #16a34a;
        }

        .profile-tag-grade {
            background: #fffbeb;
            color: #92400e;
        }

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

        .profile-section-label-kk {
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

        .profile-section-label-kk::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #f1f5f9;
        }

        .profile-section-label-ktp {
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

        .profile-section-label-ktp::after {
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
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .12);
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

        .photo-upload-wrap-kk {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .photo-upload-wrap-ktp {
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


        .photo-thumb-kk {
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

        .photo-thumb-kk img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-thumb-kk i {
            color: #cbd5e1;
            font-size: 1.4rem;
        }


        .photo-thumb-ktp {
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

        .photo-thumb-ktp img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-thumb-ktp i {
            color: #cbd5e1;
            font-size: 1.4rem;
        }



        .photo-upload-hint {
            font-size: .72rem;
            color: #94a3b8;
            margin-bottom: 6px;
        }

        .photo-upload-hint-kk {
            font-size: .72rem;
            color: #94a3b8;
            margin-bottom: 6px;
        }

        .photo-upload-hint-ktp {
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

        .photo-upload-btn-kk {
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

        .photo-upload-btn-kk:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            background: #eff6ff;
        }

        .photo-upload-btn-ktp {
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

        .photo-upload-btn-ktp:hover {
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
            background: #000000;
            color: #000001;
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

        .request-box {
            background: linear-gradient(to right, rgba(245, 158, 11, 0.1), rgba(249, 115, 22, 0.1));
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .request-content {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .request-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(245, 158, 11, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .icon-svg {
            width: 20px;
            height: 20px;
            color: #fbbf24;
        }

        .request-text h3 {
            font-size: 14px;
            font-weight: 600;
            color: #fbbf24;
            margin-bottom: 4px;
        }

        .request-text p {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
            margin: 0 0 4px 0;
        }

        .signature-wrapper {
            width: 100%;
        }

        #signature-pad {
            width: 100%;
            height: 260px;
            border: 1px dashed #d1d5db;
            border-radius: 12px;
            background: #fff;
            cursor: crosshair;
        }

        .signature-preview-wrapper {
            width: 100%;
            min-height: 220px;
            border: 1px dashed #d1d5db;
            border-radius: 12px;
            padding: 1.5rem;
            background: #fff;

            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .signature-preview-image {
            width: 100%;
            max-width: 900px;
            max-height: 350px;
            object-fit: contain;
        }

        .image-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            padding-top: 60px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background: rgba(0, 0, 0, 0.85);
        }

        .image-modal-content {
            margin: auto;
            display: block;
            max-width: 85%;
            max-height: 85vh;
            border-radius: 12px;
            animation: zoomIn .2s ease;
        }

        .close-modal {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }

        .image-modal-kk {
            display: none;
            position: fixed;
            z-index: 9999;
            padding-top: 60px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background: rgba(0, 0, 0, 0.85);
        }

        .image-modal-content-kk {
            margin: auto;
            display: block;
            max-width: 85%;
            max-height: 85vh;
            border-radius: 12px;
            animation: zoomIn .2s ease;
        }

        .close-modal-kk {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }

        .image-modal-ktp {
            display: none;
            position: fixed;
            z-index: 9999;
            padding-top: 60px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background: rgba(0, 0, 0, 0.85);
        }

        .image-modal-content-ktp {
            margin: auto;
            display: block;
            max-width: 85%;
            max-height: 85vh;
            border-radius: 12px;
            animation: zoomIn .2s ease;
        }

        .close-modal-ktp {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }

        .photo-thumb img {
            cursor: pointer;
        }

        .photo-thumb-kk img {
            cursor: pointer;
        }

        .photo-thumb-ktp img {
            cursor: pointer;
        }

        @keyframes zoomIn {
            from {
                transform: scale(.8);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <div class="section-header-left">
                    <div class="section-header-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4" />
                            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
                        </svg>
                    </div>
                    <div>
                        <h1>Profile</h1>
                        <p class="section-header-subtitle">Kelola informasi akun Anda</p>
                    </div>
                </div>
                <nav class="section-header-breadcrumb">
                    <div class="breadcrumb-item">
                        <a href="{{ route(getDashboardRoute()) }}">Dashboard</a>
                    </div>
                    <span class="breadcrumb-sep">›</span>
                    <div class="breadcrumb-item active">Profile</div>
                </nav>
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
                                                src="{{ route('useremployee.photo', basename($user->employee->photos)) }}">
                                        @else
                                            {{ collect(explode(' ', $user->employee->employee_name ?? ($user->username ?? 'U')))->take(2)->map(fn($w) => strtoupper($w[0]))->implode('') }}
                                        @endif
                                    </div>

                                    <div class="profile-avatar-verified">
                                        <i class="fas fa-check" style="font-size:.5rem"></i>
                                    </div>

                                </div>

                                <div class="profile-hero-info">
                                    <div class="profile-hero-name">
                                        {{ $user->employee->employee_name ?? ($user->username ?? '-') }}
                                    </div>
                                    <div class="profile-hero-sub">
                                        NIP : {{ $user->employee->employee_pengenal ?? '' }}

                                    </div>
                                    <div class="profile-hero-tags">
                                        @if (!empty($user->employee->department->department_name))
                                            <span class="profile-tag profile-tag-dept">
                                                Department : {{ $user->employee->department->department_name }}
                                            </span>
                                        @endif
                                        @if (!empty($user->employee->status_employee))
                                            <span class="profile-tag profile-tag-status">
                                                Status : {{ $user->employee->status_employee }}
                                            </span>
                                        @endif
                                        @if (!empty($user->employee->grading->grading_name))
                                            <span class="profile-tag profile-tag-grade">
                                                Grading : {{ $user->employee->grading->grading_name }}
                                            </span>
                                        @endif
                                        @if (!empty($user->employee->group->group_name))
                                            <span class="profile-tag profile-tag-grade">
                                                Grouping : {{ $user->employee->group->group_name }}
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
                                        <div class="profile-section-label">Social Insurance</div>
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
                                                <label for="email"><i class="fas fa-envelope"></i> Email *</label>
                                                <input type="email" id="email" name="email"
                                                    class="form-control @error('email') is-invalid @enderror"
                                                    value="{{ old('email', $user->employee->email ?? '') }}"
                                                    placeholder="Enter email" required>
                                                @error('email')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="field-group">
                                                <label for="telp_number"><i class="fas fa-phone"></i> Phone Number
                                                    *</label>
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


                                    <div>
                                        <div class="profile-section-label">Profile Photo *</div>
                                        <div class="photo-upload-wrap">
                                            <div class="photo-thumb">
                                                @if (!empty($user->employee->photos))
                                                    <img id="preview-image"
                                                        src="{{ route('useremployee.photo', basename($user->employee->photos)) }}"
                                                        alt="Profile photo" onclick="openImageModal(this.src)">
                                                @else
                                                    <img id="preview-image" src="https://via.placeholder.com/56"
                                                        alt="No photo" style="display:none">

                                                    <i class="fas fa-user" id="photo-placeholder"
                                                        onclick="document.getElementById('photos').click()"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="photo-upload-hint">JPG, PNG or WEBP — max 512 KB</div>
                                                <label for="photos" class="photo-upload-btn">
                                                    <i class="fas fa-arrow-up-from-bracket" style="font-size:.7rem"></i>
                                                    Upload photo
                                                </label>
                                                <input type="file" name="photos" id="photos"
                                                    class="d-none @error('photos') is-invalid @enderror" accept="image/*"
                                                    onchange="previewProfilePhoto(event)">
                                                @error('photos')
                                                    <div class="text-danger mt-1" style="font-size:.72rem">
                                                        {{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="profile-section-label-kk">KK Photo *</div>
                                        <div class="photo-upload-wrap-kk">
                                            <div class="photo-thumb-kk">
                                                @if (!empty($user->employee->kk_photos))
                                                    <img id="preview-image-kk"
                                                        src="{{ route('useremployeekk.photo', basename($user->employee->kk_photos)) }}"
                                                        alt="Profile kk photo" onclick="openImageModalkk(this.src)">
                                                @else
                                                    <img id="preview-image-kk" src="https://via.placeholder.com/56"
                                                        alt="No photo" style="display:none">

                                                    <i class="fas fa-user" id="photo-placeholder-kk"
                                                        onclick="document.getElementById('kk_photos').click()"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="photo-upload-hint-kk">JPG, PNG or WEBP — max 512 KB</div>
                                                <label for="kk_photos" class="photo-upload-btn-kk">
                                                    <i class="fas fa-arrow-up-from-bracket" style="font-size:.7rem"></i>
                                                    Upload kk Photo
                                                </label>
                                                <input type="file" name="kk_photos" id="kk_photos"
                                                    class="d-none @error('kk_photos') is-invalid @enderror"
                                                    accept="image/*" onchange="previewProfilePhotokk(event)">
                                                @error('kk_photos')
                                                    <div class="text-danger mt-1" style="font-size:.72rem">
                                                        {{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="profile-section-label-ktp">KTP Photo *</div>
                                        <div class="photo-upload-wrap-ktp">
                                            <div class="photo-thumb-ktp">
                                                @if (!empty($user->employee->ktp_photos))
                                                    <img id="preview-image-ktp"
                                                        src="{{ route('useremployeektp.photo', basename($user->employee->ktp_photos)) }}"
                                                        alt="Profile ktp photo" onclick="openImageModalktp(this.src)">
                                                @else
                                                    <img id="preview-image-ktp" src="https://via.placeholder.com/56"
                                                        alt="No photo" style="display:none">

                                                    <i class="fas fa-user" id="photo-placeholder-ktp"
                                                        onclick="document.getElementById('ktp_photos').click()"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="photo-upload-hint-ktp">JPG, PNG or WEBP — max 512 KB</div>
                                                <label for="ktp_photos" class="photo-upload-btn-ktp">
                                                    <i class="fas fa-arrow-up-from-bracket" style="font-size:.7rem"></i>
                                                    Upload KTP Photo
                                                </label>
                                                <input type="file" name="ktp_photos" id="ktp_photos"
                                                    class="d-none @error('ktp_photos') is-invalid @enderror"
                                                    accept="image/*" onchange="previewProfilePhotoktp(event)">
                                                @error('ktp_photos')
                                                    <div class="text-danger mt-1" style="font-size:.72rem">
                                                        {{ $message }}</div>
                                                @enderror

                                            </div>
                                        </div>
                                    </div>
                                    {{-- <div>
    <div class="profile-section-label">Signature</div>
    <p class="text-muted small mb-3">Please create or update your signature</p>

    @if (!empty22($user->employee->signature))
        <div class="border rounded p-3 bg-light mb-3">
            <p class="text-muted small mb-2">Current signature</p>
            <div class="d-flex justify-content-center">
                <img src="{{ route('useremployeesignature.photo', basename($user->employee->signature)) }}"
                    class="img-fluid" style="height: 96px; object-fit: contain;">
            </div>
        </div>
    @else
        <form method="POST" action="{{ route('save.signature') }}" id="form-signature">
            @csrf

            <div class="border rounded p-3 bg-light mb-3" style="border-style: dashed !important;">
                <p class="text-muted small mb-2">Draw your signature below</p>
                <canvas id="signature-pad" class="w-100 bg-white rounded"
                    style="height: 160px; cursor: crosshair;"></canvas>
            </div>

            <input type="hidden" name="signature" id="signature-input">

            @error('signature')
                <div class="text-danger mt-1 mb-2" style="font-size:.72rem">{{ $message }}</div>
            @enderror

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-light border" id="clear-signature">
                    <i class="fas fa-eraser"></i> Clear
                </button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-floppy-disk"></i> Save
                </button>
            </div>
        </form>
    @endif
</div> --}}
                                    <div>
                                        <div class="profile-section-label-ktp">Documents Pengantar Pembukaan Payroll</div>
                                        <div class="photo-upload-wrap-ktp">
                                            @if ($user->employee && $user->employee->documents && $user->employee->documents->isNotEmpty())
                                                @foreach ($user->employee->documents as $doc)
                                                    <a href="{{ route('profile.documents.download', $doc->id) }}"
                                                        class="photo-upload-btn-ktp mt-1"
                                                        style="display:inline-block; text-decoration:none;">
                                                        <i class="fas fa-file-pdf" style="font-size:.7rem"></i>
                                                        {{ $doc->document_number }}
                                                    </a>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <div class="profile-section-label-ktp">SK</div>
                                        <div class="photo-upload-wrap-ktp">
                                            
                                            @if ($user->employee && $user->employee->skletters && $user->employee->skletters->isNotEmpty())
                                                {{-- @foreach ($user->employee->skletters as $skletter) --}}
                                                @foreach ($user->Employee->skletters->where('status', 'Draft') as $skletter)
                                                {{-- @foreach ($user->Employee->skletters->where('status', 'Approved Managing Director') as $skletter) --}}
                                                    <a href="{{ route('my-sk-letter.download', $skletter->id) }}"
                                                        class="photo-upload-btn-ktp mt-1"
                                                        style="display:inline-block; text-decoration:none;">
                                                        <i class="fas fa-file-pdf" style="font-size:.7rem"></i>
                                                        {{ $skletter->sk_number }}
                                                    </a>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    <div id="imagePreviewModal" class="image-modal" onclick="closeImageModal()">
                                        <span class="close-modal">&times;</span>
                                        <img class="image-modal-content" id="modalPreviewImage">
                                    </div>
                                    <div id="imagePreviewModalkk" class="image-modal-kk" onclick="closeImageModalkk()">
                                        <span class="close-modal-kk">&times;</span>
                                        <img class="image-modal-content-kk" id="modalPreviewImagekk">
                                    </div>
                                    <div id="imagePreviewModalktp" class="image-modal-ktp"
                                        onclick="closeImageModalktp()">
                                        <span class="close-modal-ktp">&times;</span>
                                        <img class="image-modal-content-ktp" id="modalPreviewImagektp">
                                    </div>
                                </div>
                                <div class="profile-footer">
                                    {{-- <a href="{{ url()->previous() }}" class="btn btn-back">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </a> --}}
                                    <a href="{{ route(getDashboardRoute()) }}"class="btn btn-back"><i
                                            class="fas fa-arrow-left"></i> Back</a>

                                    <button type="submit" class="btn btn-save">
                                        <i class="fas fa-floppy-disk"></i> Save changes
                                    </button>
                                </div>
                            </form>

                            <div class="profile-body" style="border-top: 1px solid #f1f5f9;">
                                <div>
                                    <div class="profile-section-label">Signature</div>
                                    <p class="text-muted small mb-3">Please create your signature</p>

                                    @if (!empty($user->employee->signature))
                                        <div class="border rounded p-3 bg-light mb-3">
                                            <p class="text-muted small mb-2">Current signature</p>
                                            <div class="d-flex justify-content-center">
                                                <img src="{{ route('useremployeesignature.photo', basename($user->employee->signature)) }}"
                                                    class="img-fluid" style="height: 96px; object-fit: contain;">
                                            </div>
                                        </div>
                                    @else
                                        <form method="POST" action="{{ route('save.signature') }}" id="form-signature">
                                            @csrf

                                            <div class="border rounded p-3 bg-light mb-3" style="border-style: dashed !important;">
                                                <p class="text-muted small mb-2">Draw your signature below</p>
                                                <canvas id="signature-pad" class="w-100 bg-white rounded"
                                                    style="height: 160px; cursor: crosshair;"></canvas>
                                            </div>

                                            <input type="hidden" name="signature" id="signature-input">

                                            @error('signature')
                                                <div class="text-danger mt-1 mb-2" style="font-size:.72rem">{{ $message }}</div>
                                            @enderror

                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-light border" id="clear-signature">
                                                    <i class="fas fa-eraser"></i> Clear
                                                </button>
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-floppy-disk"></i> Save Signature
                                                </button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            </div>










                        </div>
                        <br>
                        <div class="request-box">
                            <div class="request-content">
                                <div class="request-icon">
                                    <svg class="icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </div>
                                <div class="request-text">
                                    <h3>Information</h3>
                                    <p>
                                        - Email, phone number, profile photo, KTP, and KK can all be changed. However, email
                                        and phone number must be requested and HR will be responsible for changing them.
                                    </p>
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
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

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
            const file = event.target.files[0];
            if (!file) return;

            const url = URL.createObjectURL(file);
            const img = document.getElementById('preview-image');
            const ph = document.getElementById('photo-placeholder');

            img.src = url;
            img.style.display = 'block';

            if (ph) ph.style.display = 'none';

            // tambahkan onclick setelah upload
            img.setAttribute('onclick', 'openImageModal(this.src)');
        }

        function openImageModal(src) {
            document.getElementById('imagePreviewModal').style.display = 'block';
            document.getElementById('modalPreviewImage').src = src;
        }

        function closeImageModal() {
            document.getElementById('imagePreviewModal').style.display = 'none';
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
    <script>
        const canvas = document.getElementById('signature-pad');

if (canvas) {
    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
    }
    resizeCanvas();
    window.addEventListener("resize", resizeCanvas);

    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255,255,255)',
        penColor: 'black',
        minWidth: 2.5,
        maxWidth: 4.5,
        velocityFilterWeight: 0.7
    });

    document.getElementById('clear-signature')
        .addEventListener('click', function () {
            signaturePad.clear();
        });

    document.getElementById('form-signature')
        .addEventListener('submit', function (e) {
            if (signaturePad.isEmpty()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Signature kosong',
                    text: 'Silakan buat tanda tangan terlebih dahulu.',
                    confirmButtonColor: '#1d4ed8'
                });
                return;
            }
            document.getElementById('signature-input').value = signaturePad.toDataURL('image/png');
        });
}
    </script>
    <script>
        function previewProfilePhotokk(event) {
            const file = event.target.files[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            const img = document.getElementById('preview-image-kk');
            const ph = document.getElementById('photo-placeholder-kk');
            img.src = url;
            img.style.display = 'block';
            if (ph) ph.style.display = 'none';
            img.setAttribute('onclick', 'openImageModalkk(this.src)');
        }

        function openImageModalkk(src) {
            document.getElementById('imagePreviewModalkk').style.display = 'block';
            document.getElementById('modalPreviewImagekk').src = src;
        }

        function closeImageModalkk() {
            document.getElementById('imagePreviewModalkk').style.display = 'none';
        }
    </script>
    <script>
        function previewProfilePhotoktp(event) {
            const file = event.target.files[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            const img = document.getElementById('preview-image-ktp');
            const ph = document.getElementById('photo-placeholder-ktp');
            img.src = url;
            img.style.display = 'block';
            if (ph) ph.style.display = 'none';
            img.setAttribute('onclick', 'openImageModalktp(this.src)');
        }

        function openImageModalktp(src) {
            document.getElementById('imagePreviewModalktp').style.display = 'block';
            document.getElementById('modalPreviewImagektp').src = src;
        }

        function closeImageModalktp() {
            document.getElementById('imagePreviewModalktp').style.display = 'none';
        }
    </script>
@endpush



{{-- "6281237175549"
"6282394782341"
"6282266530435"
"6285198463291"
"6281337548427"
"6287877913577"
"6287778168094"
"6285720347075"
"6285954121600"
"6285624686506"
"6283843555715"
"6281910694461"
"6281386028903"
"6285536905364"
"6282189483074"
"6282279597426"
"6287860596428"
"6285738325380"
"6285737284198"
"6287758333577"
"6285704140937"
"6285846312969"
"6285737603616"
"6281237170837"
"6285214147524"
"6281770896570"
"6285240994098"
"6282146672433"
"6287759544677"
"62895429356906"
"6282340787479"
"6285806035597"
"6285333762217"
"6281936916113"
"6287789043904"
"6281339987508"
"6287762462489"
"6287860077700"
"62895413421700"
"6283835100452"
"6285238865318"
"6282259060894"
"6281337414324"
"6281883424187"


"6282110212708"
"6281368672999"
"6285931994691"
"6285339200763"
"62895370085191"
"6285138924512"
"6281288376059"
"6282266086472"
"6281236060419"
"6283114084288"
"6285169994045"
"6281366735646"
"6285394587318"
"6281939466447"
"6289639157500"
"6289624130101"
"6281338494136"
"62881026801037"
"6281214425112"
"6287865776554"
"6285934887598"
"6287833875982"
"6285847374055"
"6281937606287"
"6281252044056"
"6287849355402"
"6281353411995"
"6285702659148"
"6281237092565"
"6282349889044"
"6283857853203"
"6281239607379"
"6289669737837"
"6289526750868"
"6282272501805"
"6285964334535"
"6285955286810"
"6287798797086"
"6289999999999"
"6285858958911"
"6281946257317"
"6281338227347"
"6287861870777"
"6281249816232"
"6281315465818"
"6282264112016"
"6281238814110"
"6281999921265"
"6281215100938"
"6287762234492"
"6282235357685"
"6287890683764"
"6282232122977"
"6285954558924"
"6282364135269"
"6287833271991"
"6282147189223"
"6285789213133"
"6287888079492"
"6285337206845"
"6285806322957"
"6287889188044"
"62895394015731"
"6282247615701"
"62895413615111"
"6282213206756"
"6281246769190"
"6285163705066"
"6282340571575"
"6287771440393"
"62895329837859"
"6285738348743"
"6283149016162"
"6283119421404"
"6289670115464"
"628139957857" --}}