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

        .info-box {
            background: linear-gradient(to right, rgba(59, 130, 246, 0.08), rgba(29, 78, 216, 0.06));
            border: 1px solid rgba(59, 130, 246, 0.25);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .info-content {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(59, 130, 246, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .icon-svg {
            width: 20px;
            height: 20px;
            color: #1d4ed8;
        }

        .info-text h3 {
            font-size: 14px;
            font-weight: 600;
            color: #1d4ed8;
            margin-bottom: 4px;
        }

        .info-text p {
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
            margin: 0 0 4px 0;
        }

        /* ─── Document / SK chips ────────────────────────────── */
        .doc-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .doc-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px 8px 8px;
            border: 1px solid #e2e8f0;
            border-radius: .5rem;
            background: #f8fafc;
            color: #334155;
            font-size: .8rem;
            font-weight: 500;
            text-decoration: none;
            max-width: 100%;
            transition: all .15s;
        }

        .doc-chip:hover {
            border-color: #3b82f6;
            background: #eff6ff;
            color: #1d4ed8;
            text-decoration: none;
        }

        .doc-chip-icon {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            background: #fee2e2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
            flex-shrink: 0;
        }

        .doc-chip-icon-sk {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .doc-chip-name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 220px;
        }

        .doc-chip-download {
            font-size: .68rem;
            color: #cbd5e1;
        }

        .doc-chip:hover .doc-chip-download {
            color: #3b82f6;
        }

        .doc-empty {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 14px;
            border: 1px dashed #e2e8f0;
            border-radius: .5rem;
            background: #f8fafc;
            color: #94a3b8;
            font-size: .8rem;
            width: 100%;
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
                        <div class="info-box">
                            <div class="info-content">
                                <div class="info-icon">
                                    <svg class="icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="info-text">
                                    <h3>Information</h3>
                                    <p>
                                        - Email, phone number, profile photo, KTP, and KK can all be changed. However, email
                                        and phone number must be requested and HR will be responsible for changing them.<br>
                                        - To change Signature, please contact HR Admin.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                {{-- Switch Active Role --}}
                                @if (!empty(Auth::user()->all_roles_hrx) && count(Auth::user()->all_roles_hrx) > 1)
                                    <form action="{{ route('profile.switchRole') }}" method="POST" class="mb-3">
                                        @csrf
                                        @method('PUT')

                                        <div class="profile-section-label">Active Role</div>
                                        <div class="field-grid">
                                            <div class="field-group">
                                                <label><i class="fas fa-shield-alt"></i> Switch Role</label>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <select name="active_role_hrx" id="active_role_hrx"
                                                        class="form-control @error('active_role_hrx') is-invalid @enderror">
                                                        @foreach (Auth::user()->all_roles_hrx as $role)
                                                            <option value="{{ $role }}"
                                                                {{ Auth::user()->active_role_hrx === $role ? 'selected' : '' }}>
                                                                {{ $role }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    {{-- <button type="submit" class="btn btn-primary btn-sm text-nowrap"> --}}
                                                    <button type="submit" class="btn btn-primary btn-sm text-nowrap ms-6">
                                                        <i class="fas fa-sync-alt"></i> Switch
                                                    </button>
                                                </div>
                                                @error('active_role_hrx')
                                                    <span class="text-danger small">{{ $message }}</span>
                                                @enderror
                                                <small class="text-muted">
                                                    Active Role :
                                                    <strong>{{ Auth::user()->active_role_hrx ?? '-' }}</strong>
                                                </small>
                                            </div>
                                        </div>
                                    </form>
                                @endif
                                <form action="{{ route('feature-profile.update') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <div class="profile-fields">
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
                                    <br>
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
                                                <label><i class="fas fa-id-card"></i> KK Number</label>
                                                <div class="field-readonly">
                                                    {{ $user->employee->kk_number ?? '-' }}
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
                                    <br>
                                    <div>
                                        <div class="profile-section-label">Employment</div>
                                        <div class="field-grid">
                                            <div class="field-group">
                                                <label><i class="fas fa-building"></i> Company</label>
                                                <div class="field-readonly">
                                                    {{-- {{ $user->Employee->company->pluck('name')->join(', ') }} --}}
                                                    {{ $user->Employee->company->name }}

                                                    {{-- {{ $user->employee->company->name ?? '-' }} --}}
                                                </div>
                                            </div>
                                            <div class="field-group">
                                                <label><i class="fas fa-sitemap"></i> Department</label>
                                                <div class="field-readonly">
                                                    {{-- {{ $user->Employee->department->first()->department_name ?? '-'}} --}}
                                                    {{ $user->Employee->department->pluck('department_name')->implode(', ') ?: '-' }}
                                                    {{-- {{ $department->department_name ?? '-' }} --}}
                                                </div>
                                            </div>

                                            <div class="field-group">
                                                <label><i class="fas fa-briefcase"></i> Position</label>
                                                <div class="field-readonly">
                                                    {{ $user->Employee->position->pluck('name')->implode(', ') ?: '-' }}
                                                </div>
                                            </div>
                                            <div class="field-group">
                                                <label><i class="fas fa-store"></i> Location</label>
                                                <div class="field-readonly">
                                                    {{-- {{ $user->Employee->store->first()->name ?? '-'}} --}}
                                                    {{ $user->Employee->store->pluck('name')->implode(', ') ?: '-' }}
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
                                    <br>
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
                                    <br>
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

                                    <br>
                                    <div>
                                        <div class="profile-section-label">Profile Photo *</div>
                                        <div class="photo-upload-wrap">
                                            <div class="photo-thumb">
                                                @if (!empty($user->employee->photos))
                                                    <img id="preview-image"
                                                        src="{{ route('useremployee.photo', basename($user->employee->photos)) }}"
                                                        alt="Profile Photo" onclick="openImageModal(this.src)">
                                                @else
                                                    <img id="preview-image" src="https://via.placeholder.com/56"
                                                        alt="No photo" style="display:none">

                                                    <i class="fas fa-user" id="photo-placeholder"
                                                        onclick="document.getElementById('photos').click()"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="photo-upload-hint">JPG, PNG or WEBP — max 2048 KB</div>
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
                                    <br>
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
                                    <br>
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
                                    <br>
                                    @php
                                        $documents = $user->employee?->documents ?? collect();
                                        $skLetters = $user->employee?->skletters?->where('status', 'Draft') ?? collect();
                                    @endphp
                                    <div>
                                        <div class="profile-section-label"><i class="fas fa-folder-open"></i> Documents</div>
                                        <div class="doc-list">
                                            @forelse ($documents as $doc)
                                                <a href="{{ route('profile.documents.download', $doc->id) }}" class="doc-chip">
                                                    <span class="doc-chip-icon"><i class="fas fa-file-pdf"></i></span>
                                                    <span class="doc-chip-name">{{ $doc->document_number }}</span>
                                                    <i class="fas fa-arrow-down doc-chip-download"></i>
                                                </a>
                                            @empty
                                                <div class="doc-empty"><i class="fas fa-folder-open"></i> No documents uploaded yet</div>
                                            @endforelse
                                        </div>
                                    </div>
                                    <br>
                                    <div>
                                        <div class="profile-section-label"><i class="fas fa-file-signature"></i> SK</div>
                                        <div class="doc-list">
                                            @forelse ($skLetters as $skletter)
                                                <a href="{{ route('my-sk-letter.download', $skletter->id) }}" class="doc-chip doc-chip-sk">
                                                    <span class="doc-chip-icon doc-chip-icon-sk"><i class="fas fa-file-pdf"></i></span>
                                                    <span class="doc-chip-name">{{ $skletter->sk_number }}</span>
                                                    <i class="fas fa-arrow-down doc-chip-download"></i>
                                                </a>
                                            @empty
                                                <div class="doc-empty"><i class="fas fa-file-circle-xmark"></i> No SK letters available</div>
                                            @endforelse
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
                                    <a href="{{ route(getDashboardRoute()) }}" class="btn btn-back"><i
                                            class="fas fa-arrow-left"></i> Back</a>

                                    <button type="submit" class="btn btn-save">
                                        <i class="fas fa-floppy-disk"></i> Save changes
                                    </button>
                                </div>
                            </form>
                            </div>

                            <div class="profile-body" style="border-top: 1px solid #f1f5f9;">
                                <div>
                                    <div class="profile-section-label">
                                        <i class="fas fa-signature"></i> Signature
                                        @if (!empty($user->employee->signature))
                                            <span class="profile-tag profile-tag-status">Signed</span>
                                        @else
                                            <span class="profile-tag" style="background:#fef2f2;color:#991b1b;">Not signed</span>
                                        @endif
                                    </div>
                                    <p class="text-muted small mb-3">Please create your signature</p>

                                    @if (!empty($user->employee->signature))
                                        <div class="signature-preview-wrapper">
                                            <img src="{{ route('useremployeesignature.photo', basename($user->employee->signature)) }}"
                                                class="signature-preview-image" alt="Signature">
                                            <p class="text-muted small mt-2 mb-0">This is your saved signature.</p>
                                        </div>
                                    @else
                                        <form method="POST" action="{{ route('save.signature') }}" id="form-signature"
                                            enctype="multipart/form-data">
                                            @csrf

                                            {{-- Tab Navigation --}}
                                            <div class="d-flex gap-2 mb-3">
                                                <button type="button" class="btn btn-sm btn-primary" id="tab-draw"
                                                    onclick="switchTab('draw')">
                                                    <i class="fas fa-pen"></i> Draw
                                                </button>
                                                <button type="button" class="btn btn-sm btn-light border"
                                                    id="tab-import" onclick="switchTab('import')">
                                                    <i class="fas fa-upload"></i> Import File
                                                </button>
                                            </div>

                                            {{-- Tab Draw --}}
                                            <div id="section-draw">
                                                <div class="border rounded p-3 bg-light mb-3"
                                                    style="border-style: dashed !important;">
                                                    <p class="text-muted small mb-2">Draw your signature below</p>
                                                    {{-- <canvas id="signature-pad" class="w-100 bg-white rounded"
                                                        style="height: 160px; cursor: crosshair;"></canvas> --}}
                                                        {{-- <canvas id="signature-pad" class="w-100 rounded" style="height: 160px; cursor: crosshair; background: transparent;"></canvas> --}}
                                                        <canvas id="signature-pad" class="w-100 rounded" style="height: 200px; cursor: crosshair;"></canvas>
                                                </div>

                                                <input type="hidden" name="signature" id="signature-input">

                                                @error('signature')
                                                    <div class="text-danger mt-1 mb-2" style="font-size:.72rem">
                                                        {{ $message }}</div>
                                                @enderror

                                                <div class="d-flex gap-2 mb-3">
                                                    <button type="button" class="btn btn-sm btn-light border"
                                                        id="clear-signature">
                                                        <i class="fas fa-eraser"></i> Clear
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Tab Import --}}
                                            <div id="section-import" style="display:none;">
                                                <div class="border rounded p-3 bg-light mb-3"
                                                    style="border-style: dashed !important;">
                                                    <p class="text-muted small mb-2">Upload signature image (JPG, PNG, WEBP
                                                        — max 512KB)</p>

                                                    <div class="photo-upload-wrap">
                                                        <div class="photo-thumb">
                                                            <img id="preview-signature-import" src=""
                                                                alt=""
                                                                style="display:none; height:96px; object-fit:contain;">
                                                            <i class="fas fa-file-image" id="signature-import-placeholder"
                                                                style="font-size:2rem; color:#cbd5e1;"></i>
                                                        </div>
                                                        <div>
                                                            <label for="signature_file" class="photo-upload-btn">
                                                                <i class="fas fa-arrow-up-from-bracket"
                                                                    style="font-size:.7rem"></i>
                                                                Choose file
                                                            </label>
                                                            <input type="file" name="signature_file"
                                                                id="signature_file"
                                                                class="d-none @error('signature_file') is-invalid @enderror"
                                                                accept=".jpg,.jpeg,.png,.webp"
                                                                onchange="previewSignatureImport(event)">
                                                            <div class="photo-upload-hint mt-1" id="signature-file-name">
                                                                No file chosen</div>
                                                        </div>
                                                    </div>

                                                    @error('signature_file')
                                                        <div class="text-danger mt-1" style="font-size:.72rem">
                                                            {{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-floppy-disk"></i> Save Signature
                                            </button>
                                        </form>
                                    @endif
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
        // ─── Password Toggle ───────────────────────────────────────────────
        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('eyeIcon');
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            icon.classList.replace(isHidden ? 'fa-eye' : 'fa-eye-slash', isHidden ? 'fa-eye-slash' : 'fa-eye');
        }

        // ─── Image Modal Helpers ───────────────────────────────────────────
        function openImageModal(src)    { document.getElementById('imagePreviewModal').style.display    = 'block'; document.getElementById('modalPreviewImage').src    = src; }
        function closeImageModal()      { document.getElementById('imagePreviewModal').style.display    = 'none'; }
        function openImageModalkk(src)  { document.getElementById('imagePreviewModalkk').style.display  = 'block'; document.getElementById('modalPreviewImagekk').src  = src; }
        function closeImageModalkk()    { document.getElementById('imagePreviewModalkk').style.display  = 'none'; }
        function openImageModalktp(src) { document.getElementById('imagePreviewModalktp').style.display = 'block'; document.getElementById('modalPreviewImagektp').src = src; }
        function closeImageModalktp()   { document.getElementById('imagePreviewModalktp').style.display = 'none'; }

        // ─── Photo Preview Helpers ─────────────────────────────────────────
        function previewPhoto(fileInput, imgId, placeholderId, modalFn) {
            const file = fileInput.files[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            const img = document.getElementById(imgId);
            const ph  = document.getElementById(placeholderId);
            img.src = url;
            img.style.display = 'block';
            if (ph) ph.style.display = 'none';
            img.setAttribute('onclick', `${modalFn}(this.src)`);
        }

        function previewProfilePhoto(event)    { previewPhoto(event.target, 'preview-image',     'photo-placeholder',     'openImageModal'); }
        function previewProfilePhotokk(event)  { previewPhoto(event.target, 'preview-image-kk',  'photo-placeholder-kk',  'openImageModalkk'); }
        function previewProfilePhotoktp(event) { previewPhoto(event.target, 'preview-image-ktp', 'photo-placeholder-ktp', 'openImageModalktp'); }

        // ─── SweetAlert Session Flash ──────────────────────────────────────
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

        // ─── Signature Pad ─────────────────────────────────────────────────
        const canvas = document.getElementById('signature-pad');
        let signaturePad = null;

        if (canvas) {
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const data  = signaturePad ? signaturePad.toData() : null;

                canvas.width  = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;

                const ctx = canvas.getContext('2d');
                ctx.scale(ratio, ratio);
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (signaturePad && data) signaturePad.fromData(data);
            }

            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);

            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(0,0,0,0)',
                penColor: 'black',
                minWidth: 4,
                maxWidth: 7,
                velocityFilterWeight: 0.7
            });

            document.getElementById('clear-signature').addEventListener('click', () => signaturePad.clear());
        }

        // ─── Signature Export (crop + scale ke 800x300) ────────────────────
        function exportSignatureAsPNG() {
            const imgData     = canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height);
            const { data, width, height } = imgData;

            let minX = width, minY = height, maxX = 0, maxY = 0;

            for (let y = 0; y < height; y++) {
                for (let x = 0; x < width; x++) {
                    if (data[(y * width + x) * 4 + 3] > 10) {
                        if (x < minX) minX = x;
                        if (x > maxX) maxX = x;
                        if (y < minY) minY = y;
                        if (y > maxY) maxY = y;
                    }
                }
            }

            const padding    = 20;
            minX = Math.max(0, minX - padding);
            minY = Math.max(0, minY - padding);
            maxX = Math.min(width,  maxX + padding);
            maxY = Math.min(height, maxY + padding);

            const cropW = maxX - minX;
            const cropH = maxY - minY;

            const targetW = 800;
            const targetH = 300;
            const scale   = Math.min(targetW / cropW, targetH / cropH, 2);

            const drawW   = cropW * scale;
            const drawH   = cropH * scale;
            const offsetX = (targetW - drawW) / 2;
            const offsetY = (targetH - drawH) / 2;

            const exportCanvas    = document.createElement('canvas');
            exportCanvas.width    = targetW;
            exportCanvas.height   = targetH;
            const exportCtx       = exportCanvas.getContext('2d');
            exportCtx.clearRect(0, 0, targetW, targetH);
            exportCtx.drawImage(canvas, minX, minY, cropW, cropH, offsetX, offsetY, drawW, drawH);

            return exportCanvas.toDataURL('image/png');
        }

        // ─── Tab Switch ────────────────────────────────────────────────────
        function switchTab(tab) {
            const isDrawTab    = tab === 'draw';
            const drawSection  = document.getElementById('section-draw');
            const importSection = document.getElementById('section-import');
            const tabDraw      = document.getElementById('tab-draw');
            const tabImport    = document.getElementById('tab-import');

            drawSection.style.display   = isDrawTab ? 'block' : 'none';
            importSection.style.display = isDrawTab ? 'none'  : 'block';

            tabDraw.classList.toggle('btn-primary', isDrawTab);
            tabDraw.classList.toggle('btn-light',  !isDrawTab);
            tabImport.classList.toggle('btn-primary', !isDrawTab);
            tabImport.classList.toggle('btn-light',    isDrawTab);

            if (isDrawTab) {
                document.getElementById('signature_file').value = '';
                document.getElementById('preview-signature-import').style.display = 'none';
                document.getElementById('signature-import-placeholder').style.display = 'inline';
                document.getElementById('signature-file-name').textContent = 'No file chosen';
            } else {
                document.getElementById('signature-input').value = '';
                if (signaturePad) signaturePad.clear();
            }
        }

        // ─── Import File Preview ───────────────────────────────────────────
        function previewSignatureImport(event) {
            const file = event.target.files[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            document.getElementById('preview-signature-import').src          = url;
            document.getElementById('preview-signature-import').style.display = 'block';
            document.getElementById('signature-import-placeholder').style.display = 'none';
            document.getElementById('signature-file-name').textContent        = file.name;
        }

        // ─── Form Submit ───────────────────────────────────────────────────
        const formSignature = document.getElementById('form-signature');
        if (formSignature) {
            formSignature.addEventListener('submit', function (e) {
                const isDrawTab = document.getElementById('section-draw').style.display !== 'none';

                if (isDrawTab) {
                    if (!signaturePad || signaturePad.isEmpty()) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Signature kosong',
                            text: 'Silakan buat tanda tangan terlebih dahulu.',
                            confirmButtonColor: '#1d4ed8'
                        });
                        return;
                    }
                    document.getElementById('signature-input').value = exportSignatureAsPNG();
                    document.getElementById('signature_file').value  = '';
                } else {
                    if (!document.getElementById('signature_file').files.length) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'File kosong',
                            text: 'Silakan pilih file signature terlebih dahulu.',
                            confirmButtonColor: '#1d4ed8'
                        });
                        return;
                    }
                    document.getElementById('signature-input').value = '';
                }
            });
        }
    </script>
@endpush