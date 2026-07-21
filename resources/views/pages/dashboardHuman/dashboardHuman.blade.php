@extends('layouts.app')
@section('title', 'User Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #25316D 0%, #3E497A 100%);
            --success-gradient: linear-gradient(135deg, #0A8A6A 0%, #096C57 100%);
            --warning-gradient: linear-gradient(135deg, #C7A845 0%, #A8862A 100%);
            --info-gradient: linear-gradient(135deg, #4A7BA7 0%, #3F8DAE 100%);
            --orange-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --card-shadow: 0 2px 12px rgba(0, 0, 0, 0.12);
            --card-hover-shadow: 0 8px 24px rgba(0, 0, 0, 0.20);
        }

        .profile-header-card {
            background: var(--primary-gradient);
            border-radius: 20px;
            padding: 40px;
            color: white;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
        }

        .profile-header-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .profile-header-card::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .profile-content {
            position: relative;
            z-index: 1;
        }

        .profile-avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.3);
            object-fit: cover;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .profile-info h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .profile-meta {
            display: flex;
            gap: 24px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .profile-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            opacity: 0.95;
        }

        .profile-meta-item i {
            font-size: 1.1rem;
        }

        .mini-stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 4px solid transparent;
            height: 100%;
        }

        .mini-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--card-hover-shadow);
        }

        .mini-stat-card.primary { border-left-color: #667eea; }
        .mini-stat-card.success { border-left-color: #11998e; }
        .mini-stat-card.warning { border-left-color: #f59e0b; }
        .mini-stat-card.danger  { border-left-color: #f5576c; }

        .mini-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 12px;
        }

        .mini-stat-icon.primary { background: rgba(102, 126, 234, 0.1); color: #667eea; }
        .mini-stat-icon.success { background: rgba(17, 153, 142, 0.1); color: #11998e; }
        .mini-stat-icon.warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .mini-stat-icon.danger  { background: rgba(245, 87, 108, 0.1); color: #f5576c; }

        .mini-stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #344767;
            margin: 8px 0;
        }

        .mini-stat-label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
        }

        .calendar-month {
        text-align: center;
        font-weight: 600;
        color: #344767;
        margin-bottom: 20px;
        font-size: 1.1rem;
        }

        .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
        }

        .calendar-day-header {
            text-align: center;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            padding: 8px;
        }

        .calendar-day {
            aspect-ratio: 1;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
            padding: 4px;
            text-align: center;
        }

        .calendar-day-number {
            font-size: 0.95rem;
            font-weight: 600;
        }

        .calendar-day-label {
            font-size: 0.65rem;
            font-weight: 500;
            line-height: 1.1;
            opacity: 0.85;
        }

        .calendar-day-remark {
            font-size: 0.6rem;
            font-weight: 400;
            line-height: 1.1;
            font-style: italic;
            opacity: 0.75;
        }

        .calendar-day.empty {
            background: transparent;
            cursor: default;
        }

        .calendar-day.present {
            background: rgba(56, 239, 125, 0.15);
            color: #11998e;
        }

        .calendar-day.absent {
            background: rgba(245, 87, 108, 0.15);
            color: #f5576c;
        }

        .calendar-day.leave {
            background: rgba(255, 171, 0, 0.15);
            color: #f59e0b;
        }

        .calendar-day.weekend {
            background: #f8f9fa;
            color: #94a3b8;
        }

        .calendar-day.today {
            border: 2px solid #667eea;
            font-weight: 700;
        }

        .calendar-day:hover:not(.empty) {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .calendar-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 24px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
            color: #64748b;
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
        }

        .leave-balance-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .leave-balance-header {
            background: var(--info-gradient);
            color: white;
            padding: 24px;
        }

        .leave-balance-header h4 {
            margin: 0;
            font-weight: 600;
            color: white;
        }

        .leave-balance-body {
            padding: 24px;
        }

        .leave-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #f1f3f5;
        }

        .leave-item:last-child {
            border-bottom: none;
        }

        .leave-type {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .leave-type-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .leave-type-icon.annual { background: rgba(102, 126, 234, 0.1); color: #667eea; }
        .leave-type-icon.sick   { background: rgba(245, 87, 108, 0.1); color: #f5576c; }
        .leave-type-icon.casual { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }

        .leave-type-name {
            font-weight: 600;
            color: #344767;
            margin-bottom: 4px;
        }

        .leave-type-period {
            font-size: 0.8rem;
            color: #64748b;
        }

        .leave-days {
            text-align: right;
        }

        .leave-days-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #344767;
        }

        .leave-days-label {
            font-size: 0.75rem;
            color: #64748b;
        }

        .submissions-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .submissions-header {
            background: white;
            border-bottom: 2px solid #f1f3f5;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .submissions-header h4 {
            margin: 0;
            font-weight: 600;
            color: #344767;
        }

        .submission-item {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f3f5;
            transition: background-color 0.2s;
        }

        .submission-item:hover {
            background-color: #f8f9fa;
        }

        .submission-item:last-child {
            border-bottom: none;
        }

        .submission-header-row {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }

        .submission-type-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .submission-type-badge.annual-leave { background: rgba(102, 126, 234, 0.1); color: #667eea; }
        .submission-type-badge.sick-leave   { background: rgba(245, 87, 108, 0.1); color: #f5576c; }
        .submission-type-badge.overtime     { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }

        .submission-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .submission-status.pending  { background: rgba(255, 171, 0, 0.15); color: #f59e0b; }
        .submission-status.approved { background: rgba(56, 239, 125, 0.15); color: #11998e; }
        .submission-status.rejected { background: rgba(245, 87, 108, 0.15); color: #f5576c; }

        .submission-meta {
            display: flex;
            gap: 20px;
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 8px;
        }

        .submission-meta i {
            width: 14px;
        }

        .submission-notes {
            font-size: 0.9rem;
            color: #344767;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed #e9ecef;
        }

        .announcements-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .announcements-header {
            background: var(--orange-gradient);
            color: white;
            padding: 20px 24px;
        }

        .announcements-header h4 {
            margin: 0;
            font-weight: 600;
            color: rgb(0, 0, 0);
        }

        .announcement-item {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f3f5;
            transition: all 0.2s;
            cursor: pointer;
        }

        .announcement-item:hover {
            background-color: #f8f9fa;
        }

        .announcement-item:last-child {
            border-bottom: none;
        }

        .announcement-title {
            font-weight: 600;
            color: #344767;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .announcement-badge-new {
            background: #f5576c;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .announcement-excerpt {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .announcement-date {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .attendance-history-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .attendance-history-header {
            background: white;
            border-bottom: 2px solid #f1f3f5;
            padding: 20px 24px;
        }

        .attendance-history-header h4 {
            margin: 0;
            font-weight: 600;
            color: #344767;
        }

        .attendance-calendar {
            padding: 24px;
        }

        .empty-state {
            text-align: center;
            padding: 48px 24px;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 16px;
        }

        .empty-state h6 {
            color: #64748b;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #94a3b8;
            font-size: 0.875rem;
            margin: 0;
        }

        .modal-content {
            border-radius: 16px;
            border: none;
        }

        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 20px 24px;
        }

        .modal-header .modal-title {
            font-weight: 600;
            color: white;
        }

        .modal-header .close {
            color: white;
            opacity: 0.9;
        }

        .modal-body { padding: 24px; }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .form-label {
            font-weight: 600;
            color: #344767;
            margin-bottom: 8px;
            font-size: 0.875rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 10px 14px;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 10px 20px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        @media (max-width: 768px) {
            .profile-header-card { padding: 24px; }
            .profile-info h2 { font-size: 1.5rem; }
            .profile-meta { flex-direction: column; gap: 12px; }
            .mini-stat-card { margin-bottom: 16px; }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out;
        }

        .select2-container--default .select2-selection--single {
            height: 44px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 6px 14px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px;
            color: #344767;
            font-size: 0.95rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px;
        }

        .select2-dropdown {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #667eea;
        }

        .select2-container--open {
            z-index: 99999;
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">

            {{-- Profile Header --}}
            <div class="profile-header-card animate-fade-in-up">
                <div class="profile-content">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center gap-4">
                                <img src="{{ Auth::user()->employee->photos
                                    ? route('useremployee.photo', basename(Auth::user()->employee->photos))
                                    : asset('img/avatar/avatar-1.png') }}"
                                    alt="Profile" class="profile-avatar-large no-drag">
                                <div class="profile-info">
                                    <h2>{{ Auth::user()->employee->employee_name ?? '-' }}</h2>
                                    <div class="profile-meta">
                                        <div class="profile-meta-item">
                                            <i class="fas fa-briefcase"></i>
                                            <span>{{ Auth::user()->employee->position->first()?->name ?? '-' }}</span>
                                        </div>
                                        <div class="profile-meta-item">
                                            <i class="fas fa-building"></i>
                                            <span>{{ Auth::user()->employee->department->first()?->department_name ?? '-' }}</span>
                                        </div>
                                        <div class="profile-meta-item">
                                            <i class="fas fa-id-badge"></i>
                                            <span>{{ Auth::user()->employee->employee_pengenal ?? '-' }}</span>
                                        </div>
                                        <div class="profile-meta-item">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>{{ now()->format('l, F d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mini Stat Cards --}}
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card primary">
                        <div class="mini-stat-icon primary"><i class="fas fa-calendar-check"></i></div>
                        <div class="mini-stat-value">22</div>
                        <div class="mini-stat-label">Days Present</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card success">
                        <div class="mini-stat-icon success"><i class="fas fa-percentage"></i></div>
                        <div class="mini-stat-value">95%</div>
                        <div class="mini-stat-label">Attendance Rate</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card warning">
                        <div class="mini-stat-icon warning"><i class="fas fa-clock"></i></div>
                        <div class="mini-stat-value">2</div>
                        <div class="mini-stat-label">Times Late</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card danger">
                        <div class="mini-stat-icon danger"><i class="fas fa-times-circle"></i></div>
                        <div class="mini-stat-value">1</div>
                        <div class="mini-stat-label">Days Absent</div>
                    </div>
                </div>
            </div>

            {{-- Company Announcements --}}
            <div class="col-lg-12 col-12 mb-4 px-0">
                <div class="announcements-card">
                    <div class="announcements-header">
                        <h4><i class="fas fa-bullhorn me-2"></i> Company Announcements</h4>
                    </div>
                    <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                        @forelse ($announcements as $announcement)
                            <div class="announcement-item" data-toggle="modal" data-target="#announcementModal"
                                data-id="{{ $announcement->id }}"
                                data-title="{{ $announcement->title }}"
                                data-content="{{ $announcement->content }}"
                                data-author="{{ $announcement->user->employee->employee_name ?? 'Admin' }}"
                                data-date="{{ $announcement->publish_date ?? $announcement->created_at->format('d M Y') }}">
                                <div class="announcement-title">
                                    <i class="fas fa-bullhorn text-primary"></i>
                                    {{ $announcement->title }}
                                    @if ($announcement->created_at->diffInDays(now()) <= 3)
                                        <span class="announcement-badge-new">New</span>
                                    @endif
                                </div>
                                <div class="announcement-excerpt">
                                    {{ Str::limit(strip_tags($announcement->content), 120) }}
                                </div>
                                <div class="announcement-date">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    {{ $announcement->created_at->diffForHumans() }}
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>No announcements available.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Leave Balance + My Submissions --}}
            <div class="row">

                {{-- Leave Balance (Annual Leave dari DB) --}}
                <div class="col-lg-4 col-12 mb-4">
                    <div class="leave-balance-card mt-1">
                        <div class="leave-balance-header">
                            <h4>
                                <i class="fas fa-umbrella-beach me-2"></i>
                                Leave Balance - {{ Auth::user()->employee->employee_name ?? '-' }}
                            </h4>
                        </div>
                        <div class="leave-balance-body">
                            <div class="leave-item">
                                <div class="leave-type">
                                    <div class="leave-type-icon annual">
                                        <i class="fas fa-umbrella-beach"></i>
                                    </div>
                                    <div>
                                        <div class="leave-type-name">Annual Leave</div>
                                        <div class="leave-type-period">{{ $annualLeave->year ?? date('Y') }}</div>
                                    </div>
                                </div>
                                <div class="leave-days">
                                    <div class="leave-days-value">
                                        {{ rtrim(rtrim(number_format($displayBalance, 2), '0'), '.') }}
                                    </div>
                                    <div class="leave-days-label">days remaining</div>
                                </div>
                            </div>

                            @unless ($annualLeave)
                                <div class="alert alert-warning mt-3 mb-0" style="font-size: 0.85rem;">
                                    <i class="fas fa-info-circle me-2"></i>
                                    @if ($isNewbie)
                                        Anda belum genap 1 tahun berada di perusahaan ini, sehingga saldo cuti belum tersedia.
                                    @else
                                        Saldo cuti tahunan belum tersedia untuk periode ini.
                                    @endif
                                </div>
                            @endunless
                        </div>
                    </div>
                </div>

                {{-- My Submissions --}}
                <div class="col-lg-8 col-12 mb-4">
                    <div class="submissions-card">
                        <div class="submissions-header">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <h4><i class="fas fa-file-alt me-2"></i> My Submissions</h4>
                                @if ($hasPending)
                                    <button type="button" class="btn btn-secondary btn-sm" disabled
                                        title="Anda masih memiliki pengajuan yang menunggu persetujuan">
                                        <i class="fas fa-clock me-1"></i> Pending Approval
                                    </button>
                                @else
                                    <button type="button" class="btn btn-primary btn-sm" id="newSubmissionBtn"
                                        data-toggle="modal" data-target="#requestLeaveModal">
                                        <i class="fas fa-plus me-1"></i> New Request
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @forelse ($submissions as $sub)
                                <div class="submission-item">
                                    <div class="submission-header-row">
                                        <span class="submission-type-badge annual-leave">
                                            <i class="fas fa-umbrella-beach me-1"></i>
                                            {{ $sub['leave_name'] }}
                                        </span>
                                        <span class="submission-status {{ $sub['statusClass'] }}">
                                            <i class="fas {{ $sub['statusIcon'] }} me-1"></i>
                                            {{ $sub['status'] }}
                                        </span>
                                    </div>
                                    <div class="submission-meta">
                                        <span><i class="fas fa-calendar"></i> {{ $sub['dateLabel'] }}</span>
                                        <span><i class="fas fa-hourglass-half"></i> {{ $sub['totalDays'] }} days</span>
                                        <span><i class="fas fa-clock"></i> {{ $sub['ago'] }}</span>
                                    </div>
                                    @if ($sub['employeeReason'])
                                        <div class="submission-notes">
                                            <i class="fas fa-sticky-note me-2"></i>
                                            <strong>Note:</strong> {{ $sub['employeeReason'] }}
                                        </div>
                                    @endif
                                    @if (!empty($sub['approverReason']))
                                        <div class="submission-notes"
                                            style="border-top: 1px dashed #e9ecef; margin-top: 6px; padding-top: 6px;">
                                            <i class="fas {{ $sub['isRejected'] ? 'fa-times-circle' : 'fa-user-check' }} me-2"></i>
                                            <strong>{{ $sub['isRejected'] ? 'Rejection reason:' : 'Approver:' }}</strong>
                                            {{ $sub['approverReason'] }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h6>No submissions yet</h6>
                                    <p>You haven't submitted any leave requests.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Schedule History (Calendar dari DB) --}}
            <div class="row">
                <div class="col-lg-12 col-12 mb-4">
                    <div class="attendance-history-card">
                        <div class="attendance-history-header">
                            <h4><i class="fas fa-calendar-check me-2"></i> Schedule History</h4>
                        </div>
                        <div class="attendance-calendar" id="calendarContainer">
                            @include('pages.Dashboard.calendar')
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </div>

    {{-- Request Leave Modal --}}
    <div class="modal fade" id="requestLeaveModal" tabindex="-1" aria-labelledby="requestLeaveLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form id="leaveRequestForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="requestLeaveLabel">
                            <i class="fas fa-paper-plane me-2"></i> Apply Leave
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label" for="leave_balance_id">
                                <i class="fas fa-clipboard-list me-1"></i> Type Leave
                            </label>
                            <select name="leave_balance_id" id="leave_balance_id" class="form-control" required>
                                <option value="">-- Select type of leave --</option>
                               @forelse(($leaveBalances ?? []) as $balance)
                                    <option value="{{ $balance->id }}"
                                        data-days="{{ $balance->balance_days }}"
                                        data-name="{{ $balance->leaves->name ?? 'Leave' }}"
                                        data-fixed-days="{{ $balance->leaves->fixed_days ?? '' }}"
                                        data-require-attachment="{{ ($balance->leaves->require_attachment ?? false) ? '1' : '0' }}">
                                        {{ $balance->leaves->name ?? 'Leave' }} — Sisa:
                                        {{ $balance->balance_days }} days
                                    </option>
                                @empty
                                    <option value="" disabled>No leave balance available</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="alert alert-info mb-4" id="leaveBalanceInfo" style="display:none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div><strong>Balance Available:</strong> <span id="availableBalance">- days</span></div>
                                <div><strong>Leave Type:</strong> <span id="selectedLeaveType">-</span></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="start_date">
                                    <i class="fas fa-calendar-alt me-1"></i> Start Date
                                </label>
                                <input type="date" name="start_date" id="start_date" class="form-control"
                                    min="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="end_date">
                                    <i class="fas fa-calendar-check me-1"></i> End Date
                                </label>
                                <input type="date" name="end_date" id="end_date" class="form-control"
                                    min="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="alert alert-light border mb-4" id="durationInfo">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-hourglass-half me-2"></i><strong>Duration:</strong></span>
                                <span id="calculatedDuration" class="text-primary font-weight-bold">0 days</span>
                            </div>
                        </div>

                        <div class="alert alert-danger mb-4" id="balanceWarning" style="display:none;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Insufficient leave balance!</strong>
                            The requested duration exceeds your remaining leave days.
                        </div>

                        <div class="mb-4" id="attachmentGroup" style="display:none;">
                            <label class="form-label" for="attachment">
                                <i class="fas fa-paperclip me-1"></i> Lampiran Bukti
                                <span class="text-danger" id="attachmentRequiredMark">*</span>
                            </label>
                            <input type="file" name="attachment" id="attachment" class="form-control"
                                accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted" id="attachmentHint">
                                Wajib untuk jenis cuti ini. Format: JPG, PNG, atau PDF. Maks 5MB.
                            </small>
                        </div>

                        <div class="mb-4" id="attachmentGroup" style="display:none;">
                            <label class="form-label" for="attachment">
                                <i class="fas fa-paperclip me-1"></i> Lampiran Bukti
                                <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="attachment" id="attachment" class="form-control"
                                accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted">
                                Wajib untuk jenis cuti ini. Format: JPG, PNG, atau PDF. Maks 5MB.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="employee_reason">
                                <i class="fas fa-sticky-note me-1"></i> Reason for Application
                            </label>
                            <textarea name="employee_reason" id="employee_reason" class="form-control" rows="4"
                                placeholder="Write the reason for your leave application..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitLeaveBtn">
                            <i class="fas fa-paper-plane me-1"></i> Apply Leave
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Announcement Preview Modal --}}
    <div class="modal fade" id="announcementModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-bullhorn me-2"></i>
                        <span id="modalTitle"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        <i class="fas fa-user me-1"></i> <span id="modalAuthor"></span>
                        &nbsp;|&nbsp;
                        <i class="fas fa-calendar-alt me-1"></i> <span id="modalDate"></span>
                    </p>
                    <div id="modalContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        @if (session('success'))
            Swal.fire({ icon: 'success', title: 'Success', text: '{{ session('success') }}', timer: 3000, showConfirmButton: false });
        @endif
        @if (session('error'))
            Swal.fire({ icon: 'error', title: 'Error', text: '{{ session('error') }}' });
        @endif
    </script>

    {{-- Calendar AJAX loader --}}
    <script>
        function loadCalendar(month, year) {
            const container = document.getElementById('calendarContainer');
            container.style.opacity = '0.5';

            fetch(`{{ url('/dashboardHuman') }}?month=${month}&year=${year}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.text())
                .then(html => {
                    container.innerHTML = html;
                    container.style.opacity = '1';
                })
                .catch(() => {
                    window.location.href = `{{ url('/dashboardHuman') }}?month=${month}&year=${year}`;
                });
        }
    </script>

    {{-- Announcement modal filler --}}
    <script>
        $('#announcementModal').on('show.bs.modal', function(e) {
            const trigger = $(e.relatedTarget);
            $('#modalTitle').text(trigger.data('title'));
            $('#modalAuthor').text(trigger.data('author'));
            $('#modalDate').text(trigger.data('date'));
            $('#modalContent').html(trigger.data('content'));
        });
    </script>

    {{-- Request Leave modal logic --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#requestLeaveModal').on('shown.bs.modal', function() {
                $('#leave_balance_id').select2({
                    dropdownParent: $('#requestLeaveModal'),
                    placeholder: '-- select the type of leave --',
                    allowClear: true,
                    width: '100%',
                });
            });

            $('#requestLeaveModal').on('hidden.bs.modal', function() {
                $('#leave_balance_id').select2('destroy');
            });

            $('#leave_balance_id').on('select2:select select2:clear', function() {
                this.dispatchEvent(new Event('change', { bubbles: true }));
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const leaveSelect = document.getElementById('leave_balance_id');
            const balanceInfo = document.getElementById('leaveBalanceInfo');
            const availableBalance = document.getElementById('availableBalance');
            const selectedType = document.getElementById('selectedLeaveType');
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            const durationDisplay = document.getElementById('calculatedDuration');
            const balanceWarning = document.getElementById('balanceWarning');
            const submitBtn = document.getElementById('submitLeaveBtn');
            const form = document.getElementById('leaveRequestForm');

            let maxDays = 0;
            let fixedDays = 0;              // durasi dikunci (0 = tidak dikunci)
            let requireAttachment = false; // jenis cuti ini wajib lampiran?
            const attachmentGroup = document.getElementById('attachmentGroup');
            const attachmentInput = document.getElementById('attachment');

            function applyFixedDuration() {
                if (fixedDays > 0 && startDate.value) {
                    const s = new Date(startDate.value);
                    s.setDate(s.getDate() + (fixedDays - 1));
                    endDate.value = s.toISOString().slice(0, 10);
                }
            }

            leaveSelect?.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                const days = parseInt(opt.dataset.days ?? 0);
                const name = opt.dataset.name ?? '-';
                fixedDays = parseInt(opt.dataset.fixedDays || '0') || 0;
                requireAttachment = (opt.dataset.requireAttachment === '1');

                if (this.value) {
                    maxDays = days;
                    availableBalance.textContent = days + ' days';
                    selectedType.textContent = name;
                    balanceInfo.style.display = 'block';

                    attachmentGroup.style.display = requireAttachment ? 'block' : 'none';

                    if (fixedDays > 0) {
                        endDate.readOnly = true;
                        endDate.style.backgroundColor = '#e9ecef';
                        applyFixedDuration();
                    } else {
                        endDate.readOnly = false;
                        endDate.style.backgroundColor = '';
                    }
                } else {
                    balanceInfo.style.display = 'none';
                    attachmentGroup.style.display = 'none';
                    endDate.readOnly = false;
                    endDate.style.backgroundColor = '';
                    maxDays = 0;
                    fixedDays = 0;
                    requireAttachment = false;
                }
                calculateDuration();
            });

            // Hitung end_date otomatis untuk cuti berdurasi tetap
            function applyFixedDuration() {
                if (fixedDays > 0 && startDate.value) {
                    const s = new Date(startDate.value);
                    s.setDate(s.getDate() + (fixedDays - 1));
                    endDate.value = s.toISOString().slice(0, 10);
                }
            }

            function calculateDuration() {
                const start = startDate.value;
                const end = endDate.value;

                if (!start || !end) {
                    durationDisplay.textContent = '0 days';
                    balanceWarning.style.display = 'none';
                    submitBtn.disabled = false;
                    return;
                }

                const startObj = new Date(start);
                const endObj = new Date(end);

                if (endObj < startObj) {
                    durationDisplay.textContent = 'End date cannot be before start date';
                    durationDisplay.classList.add('text-danger');
                    durationDisplay.classList.remove('text-primary');
                    submitBtn.disabled = true;
                    return;
                }

                const diffTime = endObj - startObj;
                const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;

                durationDisplay.textContent = diffDays + ' days';
                durationDisplay.classList.remove('text-danger');
                durationDisplay.classList.add('text-primary');

                if (maxDays > 0 && diffDays > maxDays) {
                    balanceWarning.style.display = 'block';
                    submitBtn.disabled = true;
                } else {
                    balanceWarning.style.display = 'none';
                    submitBtn.disabled = false;
                }
            }

           startDate?.addEventListener('change', function() {
                if (endDate) endDate.min = this.value;
                if (fixedDays > 0) applyFixedDuration();
                calculateDuration();
            });
            endDate?.addEventListener('change', calculateDuration);

            form?.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!leaveSelect.value) {
                    Swal.fire({ icon: 'warning', title: 'Select the type of leave', text: 'Please select the type of leave first.' });
                    return;
                }

                Swal.fire({
                    title: 'Submit Leave Request?',
                    text: 'Please ensure the data is correct before submitting.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Submit',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#1976D2',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Submitting...';

                    // Guard: lampiran wajib tapi belum dipilih
                    if (requireAttachment && attachmentInput && !attachmentInput.files.length) {
                        Swal.fire({ icon: 'warning', title: 'Lampiran wajib', text: 'Jenis cuti ini memerlukan lampiran bukti.' });
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                        return;
                    }

                    const formData = new FormData();
                    formData.append('leave_balance_id', document.getElementById('leave_balance_id').value);
                    formData.append('start_date', document.getElementById('start_date').value);
                    formData.append('end_date', document.getElementById('end_date').value);
                    formData.append('employee_reason', document.getElementById('employee_reason').value);
                    if (attachmentInput && attachmentInput.files.length) {
                        formData.append('attachment', attachmentInput.files[0]);
                    }

                    fetch("{{ route('Leaverequest.store') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: formData,
                        })
                        .then(async (res) => {
                            const data = await res.json();
                            return { ok: res.ok, data };
                        })
                        .then(({ ok, data }) => {
                            if (ok && data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: data.message ?? 'Leave request submitted successfully.',
                                    confirmButtonText: 'OK',
                                }).then(() => {
                                    $('#requestLeaveModal').modal('hide');
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({ icon: 'error', title: 'Failed', text: data.message ?? 'An error occurred while submitting the request.' });
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Apply Leave';
                            }
                        })
                        .catch((err) => {
                            console.error(err);
                            Swal.fire({ icon: 'error', title: 'Failed', text: 'An error occurred while submitting the request.' });
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Apply Leave';
                        });
                });
            });
        });
    </script>
@endpush