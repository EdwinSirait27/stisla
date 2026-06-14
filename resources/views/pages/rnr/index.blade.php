@extends('layouts.app')
@section('title', 'Roles and Responsibility')
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

        .profile-tag-company {
            background: #eff6ff;
            color: #e7ac08;
        }

        .profile-tag-position {
            background: #eff6ff;
            color: #00c3ff;
        }

        .profile-tag-location {
            background: #eff6ff;
            color: #9d0ddb;
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
                        <h1>Roles and Responsibility</h1>
                        <p class="section-header-subtitle">Your Roles and Responsibility</p>
                    </div>
                </div>
                <nav class="section-header-breadcrumb">
                    <div class="breadcrumb-item">
                        <a href="{{ route(getDashboardRoute()) }}">Dashboard</a>
                    </div>
                    <span class="breadcrumb-sep">›</span>
                    <div class="breadcrumb-item active">Roles and Responsibility</div>
                </nav>
            </div>

            <div class="section-body">
                <div class="row justify-content-center">
                    <div class="col-12 col-xl-10">

                        <div class="profile-card">

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
                                            <span class="profile-tag profile-tag-company">
                                                Company : {{ $user->employee->company->name }}
                                            </span>
                                        @endif
                                        @if (!empty($user->employee->department->department_name))
                                            <span class="profile-tag profile-tag-location">
                                                Location : {{ $user->employee->store->name }}
                                            </span>
                                        @endif
                                        @if (!empty($user->employee->department->department_name))
                                            <span class="profile-tag profile-tag-dept">
                                                Department : {{ $user->employee->department->department_name }}
                                            </span>
                                        @endif
                                        @if (!empty($user->employee->position->name))
                                            <span class="profile-tag profile-tag-position">
                                                Position : {{ $user->employee->position->name }}
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
                            </div>


                            <div class="profile-body">

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
                              
                                {{-- ganti bagian ini saja --}}
                                <div>
                                    <div class="profile-section-label">
                                        Roles & Responsibility
                                    </div>

                                    @php $employee = $user->employee; @endphp

                                    @if (!$employee || $employee->position->isEmpty())
                                        <div
                                            style="padding: 12px 16px; background: #fefce8; border: 1px solid #fef08a; border-radius: 8px; font-size: .825rem; color: #854d0e;">
                                            <i class="fas fa-exclamation-circle mr-2"></i> Belum ada posisi yang di-assign.
                                        </div>
                                    @else
                                        <div class="d-flex flex-column gap-3">
                                            @foreach ($employee->position as $position)
                                                <div
                                                    style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">

                                                    {{-- Position Header --}}
                                                    <div
                                                        style="background: #eff6ff; padding: 10px 16px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #e2e8f0;">
                                                        <i class="fas fa-briefcase"
                                                            style="color: #3b82f6; font-size: .8rem;"></i>
                                                        <span
                                                            style="font-weight: 600; font-size: .875rem; color: #1e293b;">{{ $position->name }}</span>
                                                        @if ($position->pivot->is_primary)
                                                            <span
                                                                style="margin-left: auto; background: #dbeafe; color: #1d4ed8; border-radius: 20px; padding: 2px 10px; font-size: .68rem; font-weight: 600;">
                                                                Primary
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div style="padding: 16px;">

                                                        {{-- Role Summary --}}
                                                        <div class="field-group mb-3">
                                                            <label>
                                                                <i class="fas fa-user-tag"></i> Role Summary
                                                            </label>
                                                            <div
                                                                style="background: #f8fafc; border: 1px solid #f1f5f9; border-left: 3px solid #3b82f6; border-radius: 0 8px 8px 0; padding: 10px 14px; font-size: .825rem; color: #475569; line-height: 1.6; min-height: 40px;">
                                                                {{ $position->role_summary ?? '-' }}
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            {{-- Key Responsibilities --}}
                                                            <div class="col-md-6">
                                                                <div class="field-group">
                                                                    <label>
                                                                        <i class="fas fa-clipboard-check"></i> Key
                                                                        Responsibilities
                                                                    </label>
                                                                    @if ($position->responsibilities->isEmpty())
                                                                        <p
                                                                            style="font-size: .78rem; color: #94a3b8; font-style: italic; margin: 8px 0 0;">
                                                                            Belum ada data.</p>
                                                                    @else
                                                                        <ul
                                                                            style="list-style: none; padding: 0; margin: 8px 0 0;">
                                                                            @foreach ($position->responsibilities as $item)
                                                                                <li
                                                                                    style="display: flex; align-items: flex-start; gap: 8px; padding: 5px 0; font-size: .825rem; color: #475569; border-bottom: 1px solid #f1f5f9;">
                                                                                    <span
                                                                                        style="width: 6px; height: 6px; border-radius: 50%; background: #3b82f6; flex-shrink: 0; margin-top: 6px;"></span>
                                                                                    <span>{{ $item->description }}</span>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            {{-- Qualifications --}}
                                                            <div class="col-md-6">
                                                                <div class="field-group">
                                                                    <label>
                                                                        <i class="fas fa-graduation-cap"></i> Qualifications
                                                                    </label>
                                                                    @if ($position->qualifications->isEmpty())
                                                                        <p
                                                                            style="font-size: .78rem; color: #94a3b8; font-style: italic; margin: 8px 0 0;">
                                                                            Belum ada data.</p>
                                                                    @else
                                                                        <ul
                                                                            style="list-style: none; padding: 0; margin: 8px 0 0;">
                                                                            @foreach ($position->qualifications as $item)
                                                                                <li
                                                                                    style="display: flex; align-items: flex-start; gap: 8px; padding: 5px 0; font-size: .825rem; color: #475569; border-bottom: 1px solid #f1f5f9;">
                                                                                    <span
                                                                                        style="width: 6px; height: 6px; border-radius: 50%; background: #6777ef; flex-shrink: 0; margin-top: 6px;"></span>
                                                                                    <span>{{ $item->description }}</span>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="profile-footer">
                                <a href="{{ route(getDashboardRoute()) }}"class="btn btn-back"><i
                                        class="fas fa-arrow-left"></i> Back</a>
                            </div>
                        </div>
                        <br>


                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection