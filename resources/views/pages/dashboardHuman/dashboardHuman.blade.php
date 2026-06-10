@extends('layouts.app')
@section('title', 'User Dashboard')

@push('styles')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <style>
        /* :root {
                                --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
                                --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                                --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
                                --orange-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
                                --card-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
                                --card-hover-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
                            } */
        :root {
            /* Deep Indigo → Royal Blue */
            --primary-gradient: linear-gradient(135deg, #25316D 0%, #3E497A 100%);

            /* Emerald → Dark Teal */
            --success-gradient: linear-gradient(135deg, #0A8A6A 0%, #096C57 100%);

            /* Gold → Amber (lebih premium, bukan kuning norak) */
            --warning-gradient: linear-gradient(135deg, #C7A845 0%, #A8862A 100%);

            /* Steel Blue → Slate Cyan (soft, tidak neon) */
            --info-gradient: linear-gradient(135deg, #4A7BA7 0%, #3F8DAE 100%);

            /* Soft shadow */
            --card-shadow: 0 2px 12px rgba(0, 0, 0, 0.12);
            --card-hover-shadow: 0 8px 24px rgba(0, 0, 0, 0.20);
        }

        /* ========== Personal Profile Card ========== */
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

        /* ========== Attendance Clock Card ========== */
        .clock-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: var(--card-shadow);
            text-align: center;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .clock-display {
            font-size: 3.5rem;
            font-weight: 700;
            color: #344767;
            margin: 24px 0;
            font-family: 'Courier New', monospace;
        }

        .clock-date {
            font-size: 1.1rem;
            color: #64748b;
            margin-bottom: 24px;
        }

        .clock-in-btn {
            background: var(--success-gradient);
            border: none;
            color: white;
            padding: 16px 48px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(17, 153, 142, 0.3);
        }

        .clock-in-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(17, 153, 142, 0.4);
            color: white;
        }

        .clock-out-btn {
            background: var(--warning-gradient);
            border: none;
            color: white;
            padding: 16px 48px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(245, 87, 108, 0.3);
        }

        .clock-out-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(245, 87, 108, 0.4);
            color: white;
        }

        .clock-status {
            margin-top: 20px;
            font-size: 0.9rem;
            color: #64748b;
        }

        .clock-status strong {
            color: #344767;
        }

        /* ========== Quick Stats Mini Cards ========== */
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

        .mini-stat-card.primary {
            border-left-color: #667eea;
        }

        .mini-stat-card.success {
            border-left-color: #11998e;
        }

        .mini-stat-card.warning {
            border-left-color: #f59e0b;
        }

        .mini-stat-card.danger {
            border-left-color: #f5576c;
        }

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

        .mini-stat-icon.primary {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        .mini-stat-icon.success {
            background: rgba(17, 153, 142, 0.1);
            color: #11998e;
        }

        .mini-stat-icon.warning {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .mini-stat-icon.danger {
            background: rgba(245, 87, 108, 0.1);
            color: #f5576c;
        }

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

        /* ========== Leave Balance Card ========== */
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

        .leave-type-icon.annual {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        .leave-type-icon.sick {
            background: rgba(245, 87, 108, 0.1);
            color: #f5576c;
        }

        .leave-type-icon.casual {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

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

        .leave-progress {
            margin-top: 8px;
        }

        .progress {
            height: 8px;
            border-radius: 10px;
            background: #f1f3f5;
        }

        .progress-bar {
            border-radius: 10px;
        }

        /* ========== My Submissions Card ========== */
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
            justify-content: between;
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

        .submission-type-badge.annual-leave {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        .submission-type-badge.sick-leave {
            background: rgba(245, 87, 108, 0.1);
            color: #f5576c;
        }

        .submission-type-badge.overtime {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .submission-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .submission-status.pending {
            background: rgba(255, 171, 0, 0.15);
            color: #f59e0b;
        }

        .submission-status.approved {
            background: rgba(56, 239, 125, 0.15);
            color: #11998e;
        }

        .submission-status.rejected {
            background: rgba(245, 87, 108, 0.15);
            color: #f5576c;
        }

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

        /* ========== Announcements Card ========== */
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

        /* ========== Attendance History Card ========== */
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
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
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

        /* ========== Empty State ========== */
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

        /* ========== Modal Improvements ========== */
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

        .modal-body {
            padding: 24px;
        }

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

        /* ========== Buttons ========== */
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

        /* ========== Responsive Design ========== */
        @media (max-width: 768px) {
            .profile-header-card {
                padding: 24px;
            }

            .profile-info h2 {
                font-size: 1.5rem;
            }

            .profile-meta {
                flex-direction: column;
                gap: 12px;
            }

            .clock-display {
                font-size: 2.5rem;
            }

            .clock-in-btn,
            .clock-out-btn {
                padding: 12px 32px;
                font-size: 1rem;
            }

            .calendar-grid {
                gap: 4px;
            }

            .calendar-day {
                font-size: 0.75rem;
            }

            .mini-stat-card {
                margin-bottom: 16px;
            }
        }

        /* ========== Animations ========== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        /* ===== Select2 Custom Style untuk Match Modal ===== */
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

        .select2-container--default .select2-selection--single:focus,
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .select2-dropdown {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #667eea;
        }

        /* z-index biar di atas modal */
        .select2-container--open {
            z-index: 99999;
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <!-- Profile Header -->
            <div class="profile-header-card animate-fade-in-up">
                <div class="profile-content">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center gap-4">
                                <img src="{{ asset('img/avatar/avatar-1.png') }}" alt="Profile"
                                    class="profile-avatar-large">
                                <div class="profile-info">
                                    <h2>{{ Auth::user()->employee->employee_name ?? 'Edwin Sirait' }}</h2>
                                    <div class="profile-meta">
                                        <div class="profile-meta-item">
                                            <i class="fas fa-briefcase"></i>
                                            <span>{{ Auth::user()->employee->position->name ?? 'Edwin Sirait' }} </span>
                                            {{-- <span>{{ $employee->position ?? 'Software Engineer' }}</span> --}}
                                        </div>
                                        <div class="profile-meta-item">
                                            <i class="fas fa-building"></i>
                                            <span>{{ Auth::user()->employee->department->department_name ?? 'Edwin Sirait' }}</span>
                                            {{-- <span>{{ $employee->department ?? 'Engineering' }}</span> --}}
                                        </div>
                                        <div class="profile-meta-item">
                                            <i class="fas fa-id-badge"></i>
                                            <span>{{ Auth::user()->employee->employee_pengenal ?? 'Edwin Sirait' }}</span>
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
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card primary">
                        <div class="mini-stat-icon primary">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="mini-stat-value">22</div>
                        {{-- <div class="mini-stat-value">{{ $attendanceData->present ?? 22 }}</div> --}}
                        <div class="mini-stat-label">Days Present</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card success">
                        <div class="mini-stat-icon success">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="mini-stat-value">95%</div>
                        {{-- <div class="mini-stat-value">{{ $attendanceData->rate ?? 95 }}%</div> --}}
                        <div class="mini-stat-label">Attendance Rate</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card warning">
                        <div class="mini-stat-icon warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="mini-stat-value">2</div>
                        {{-- <div class="mini-stat-value">{{ $attendanceData->late ?? 2 }}</div> --}}
                        <div class="mini-stat-label">Times Late</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card danger">
                        <div class="mini-stat-icon danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="mini-stat-value">1</div>
                        {{-- <div class="mini-stat-value">{{ $attendanceData->absent ?? 1 }}</div> --}}
                        <div class="mini-stat-label">Days Absent</div>
                    </div>
                </div>
            </div>


            <div class="col-lg-12 col-6 mb-4">
                <div class="announcements-card">
                    <div class="announcements-header">
                        <h4>
                            <i class="fas fa-bullhorn me-2"></i>
                            Company Announcements
                        </h4>
                    </div>
                    <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">

                        @forelse ($announcements as $announcement)
                            <div class="announcement-item" data-toggle="modal" data-target="#announcementModal"
                                data-id="{{ $announcement->id }}" data-title="{{ $announcement->title }}"
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
            <br>
            <div class="row">
                <div class="col-lg-4 col-12 mb-4">
                    <div class="leave-balance-card mt-1">
                        <div class="leave-balance-header">
                            <h4>
                                <i class="fas fa-umbrella-beach me-2"></i>
                                Leave Balance -
                                {{ Auth::user()->employee->employee_name ?? Auth::user()->employee->employee_name }}
                            </h4>
                        </div>
                        <div class="leave-balance-body">
                            <div class="leave-item">
                                <div class="leave-type">
                                    <div class="leave-type-icon annual">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <div>
                                        <div class="leave-type-name">Annual Leave</div>
                                        <div class="leave-type-period">
                                            {{ Auth::user()->employee->created_at ?? Auth::user()->employee->created_at }}
                                        </div>
                                    </div>
                                </div>
                                <div class="leave-days">
                                    <div class="leave-days-value">12</div>
                                    <div class="leave-days-label">of 14 days</div>
                                </div>
                            </div>
                            <div class="leave-progress">
                                <div class="progress">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 85%"
                                        {{-- style="width: {{ $leaveBalance->annual->percentage ?? 85 }}%" --}} aria-valuenow= "85" {{-- aria-valuenow="{{ $leaveBalance->annual->percentage ?? 85 }}"  --}} aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>

                            <div class="leave-item mt-3">
                                <div class="leave-type">
                                    <div class="leave-type-icon sick">
                                        <i class="fas fa-hospital"></i>
                                    </div>
                                    <div>
                                        <div class="leave-type-name">Sick Leave</div>
                                        <div class="leave-type-period">2024</div>
                                    </div>
                                </div>
                                <div class="leave-days">
                                    {{-- <div class="leave-days-value">{{ $leaveBalance->sick->remaining ?? 5 }}</div> --}}
                                    <div class="leave-days-value">5</div>
                                    <div class="leave-days-label">of 7 days</div>
                                    {{-- <div class="leave-days-label">of {{ $leaveBalance->sick->total ?? 7 }} days</div> --}}
                                </div>
                            </div>
                            <div class="leave-progress">
                                <div class="progress">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 71%"
                                        {{-- style="width: {{ $leaveBalance->sick->percentage ?? 71 }}%" --}} aria-valuenow="71" {{-- aria-valuenow="{{ $leaveBalance->sick->percentage ?? 71 }}"  --}} aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>

                            <div class="leave-item mt-3">
                                <div class="leave-type">
                                    <div class="leave-type-icon casual">
                                        <i class="fas fa-coffee"></i>
                                    </div>
                                    <div>
                                        <div class="leave-type-name">Casual Leave</div>
                                        <div class="leave-type-period">2024</div>
                                    </div>
                                </div>
                                <div class="leave-days">
                                    <div class="leave-days-value">3</div>
                                    {{-- <div class="leave-days-value">{{ $leaveBalance->casual->remaining ?? 3 }}</div> --}}
                                    <div class="leave-days-label">of 5 days</div>
                                    {{-- <div class="leave-days-label">of {{ $leaveBalance->casual->total ?? 5 }} days</div> --}}
                                </div>
                            </div>
                            <div class="leave-progress">
                                <div class="progress">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 60%"
                                        {{-- style="width: {{ $leaveBalance->casual->percentage ?? 60 }}%" --}} aria-valuenow="60" {{-- aria-valuenow="{{ $leaveBalance->casual->percentage ?? 60 }}"  --}} aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Submissions -->
                {{-- <div class="col-lg-8 col-12 mb-4">
                    <div class="submissions-card">
                        <div class="submissions-header">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <h4>
                                    <i class="fas fa-file-alt me-2"></i>
                                    My Submissions
                                </h4>
                                <button type="button" class="btn btn-primary btn-sm" id="newSubmissionBtn"
                                    data-toggle="modal" data-target="#requestLeaveModal">
                                    <i class="fas fa-plus me-1"></i>
                                    New Request
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <!-- Submission Item 1 -->
                            <div class="submission-item">
                                <div class="submission-header-row">
                                    <span class="submission-type-badge annual-leave">
                                        <i class="fas fa-umbrella-beach me-1"></i>
                                        Annual Leave
                                    </span>
                                    <span class="submission-status pending">
                                        <i class="fas fa-clock me-1"></i>
                                        Pending
                                    </span>
                                </div>
                                <div class="submission-meta">
                                    <span>
                                        <i class="fas fa-calendar"></i>
                                        Dec 20, 2024 - Dec 24, 2024
                                    </span>
                                    <span>
                                        <i class="fas fa-hourglass-half"></i>
                                        5 days
                                    </span>
                                    <span>
                                        <i class="fas fa-clock"></i>
                                        2 days ago
                                    </span>
                                </div>
                                <div class="submission-notes">
                                    <i class="fas fa-sticky-note me-2"></i>
                                    <strong>Note:</strong> Family vacation to Bali
                                </div>
                            </div>

                            <!-- Submission Item 2 -->
                            <div class="submission-item">
                                <div class="submission-header-row">
                                    <span class="submission-type-badge overtime">
                                        <i class="fas fa-clock me-1"></i>
                                        Overtime
                                    </span>
                                    <span class="submission-status approved">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Approved
                                    </span>
                                </div>
                                <div class="submission-meta">
                                    <span>
                                        <i class="fas fa-calendar"></i>
                                        Nov 28, 2024
                                    </span>
                                    <span>
                                        <i class="fas fa-hourglass-half"></i>
                                        4 hours
                                    </span>
                                    <span>
                                        <i class="fas fa-clock"></i>
                                        5 days ago
                                    </span>
                                </div>
                                <div class="submission-notes">
                                    <i class="fas fa-sticky-note me-2"></i>
                                    <strong>Note:</strong> Project deadline completion
                                </div>
                            </div>

                            <!-- Submission Item 3 -->
                            <div class="submission-item">
                                <div class="submission-header-row">
                                    <span class="submission-type-badge sick-leave">
                                        <i class="fas fa-hospital me-1"></i>
                                        Sick Leave
                                    </span>
                                    <span class="submission-status approved">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Approved
                                    </span>
                                </div>
                                <div class="submission-meta">
                                    <span>
                                        <i class="fas fa-calendar"></i>
                                        Nov 15, 2024 - Nov 16, 2024
                                    </span>
                                    <span>
                                        <i class="fas fa-hourglass-half"></i>
                                        2 days
                                    </span>
                                    <span>
                                        <i class="fas fa-clock"></i>
                                        2 weeks ago
                                    </span>
                                </div>
                                <div class="submission-notes">
                                    <i class="fas fa-sticky-note me-2"></i>
                                    <strong>Note:</strong> Medical checkup and recovery
                                </div>
                            </div>

                            <!-- Submission Item 4 -->
                            <div class="submission-item">
                                <div class="submission-header-row">
                                    <span class="submission-type-badge annual-leave">
                                        <i class="fas fa-umbrella-beach me-1"></i>
                                        Annual Leave
                                    </span>
                                    <span class="submission-status rejected">
                                        <i class="fas fa-times-circle me-1"></i>
                                        Rejected
                                    </span>
                                </div>
                                <div class="submission-meta">
                                    <span>
                                        <i class="fas fa-calendar"></i>
                                        Nov 10, 2024 - Nov 12, 2024
                                    </span>
                                    <span>
                                        <i class="fas fa-hourglass-half"></i>
                                        3 days
                                    </span>
                                    <span>
                                        <i class="fas fa-clock"></i>
                                        3 weeks ago
                                    </span>
                                </div>
                                <div class="submission-notes">
                                    <i class="fas fa-sticky-note me-2"></i>
                                    <strong>Rejection reason:</strong> Peak season - insufficient coverage
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light text-center">
                            <a href="#" class="text-decoration-none">
                                View All Submissions
                                <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div> --}}
                {{-- <div class="col-lg-8 col-12 mb-4">
                    <div class="submissions-card">
                        <div class="submissions-header">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <h4>
                                    <i class="fas fa-file-alt me-2"></i>
                                    My Submissions
                                </h4>
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                    data-target="#requestLeaveModal">
                                    <i class="fas fa-plus me-1"></i>
                                    New Request
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            @forelse ($submissions as $submission)
                                @php
                                    // Tipe cuti dari relasi leavebalance -> leaves
                                    $leaveType = optional($submission->leavebalance)->leaves;
                                    $typeName = optional($leaveType)->name ?? 'Leave';
                                    $typeSlug = strtolower(str_replace(' ', '-', $typeName)); // untuk class CSS

                                    // Hitung durasi
                                    $start = \Carbon\Carbon::parse($submission->start_date);
                                    $end = \Carbon\Carbon::parse($submission->end_date);
                                    $duration = $start->diffInDays($end) + 1;

                                    // Icon per tipe
                                    $typeIcons = [
                                        'annual leave' => 'fa-umbrella-beach',
                                        'sick leave' => 'fa-hospital',
                                        'overtime' => 'fa-clock',
                                    ];
                                    $icon = $typeIcons[strtolower($typeName)] ?? 'fa-file-alt';

                                    // Status config
                                    $statusConfig = [
                                        'pending' => ['class' => 'pending', 'icon' => 'fa-clock', 'label' => 'Pending'],
                                        'approved' => [
                                            'class' => 'approved',
                                            'icon' => 'fa-check-circle',
                                            'label' => 'Approved',
                                        ],
                                        'rejected' => [
                                            'class' => 'rejected',
                                            'icon' => 'fa-times-circle',
                                            'label' => 'Rejected',
                                        ],
                                    ];
                                    $status = $statusConfig[$submission->status] ?? $statusConfig['pending'];
                                @endphp

                                <div class="submission-item">
                                    <div class="submission-header-row">
                                        <span class="submission-type-badge {{ $typeSlug }}">
                                            <i class="fas {{ $icon }} me-1"></i>
                                            {{ $typeName }}
                                        </span>
                                        <span class="submission-status {{ $status['class'] }}">
                                            <i class="fas {{ $status['icon'] }} me-1"></i>
                                            {{ $status['label'] }}
                                        </span>
                                    </div>

                                    <div class="submission-meta">
                                        <span>
                                            <i class="fas fa-calendar"></i>
                                            {{ $start->format('M d, Y') }}
                                            @if ($start->ne($end))
                                                - {{ $end->format('M d, Y') }}
                                            @endif
                                        </span>
                                        <span>
                                            <i class="fas fa-hourglass-half"></i>
                                            {{ $duration }} {{ $duration > 1 ? 'days' : 'day' }}
                                        </span>
                                        <span>
                                            <i class="fas fa-clock"></i>
                                            {{ $submission->created_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    <div class="submission-notes">
                                        <i class="fas fa-sticky-note me-2"></i>
                                        @if ($submission->status === 'rejected' && $submission->approver_reason)
                                            <strong>Rejection reason:</strong> {{ $submission->approver_reason }}
                                        @else
                                            <strong>Note:</strong>
                                            {{ $submission->employee_reason ?? '-' }}
                                        @endif
                                    </div>
                                </div>

                            @empty
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    No submissions yet.
                                </div>
                            @endforelse
                        </div>

                        <div class="card-footer bg-light text-center">

                        </div>
                    </div>
                </div> --}}
                <div class="col-lg-8 col-12 mb-4">
    <div class="submissions-card">
        <div class="submissions-header">
            <div class="d-flex justify-content-between align-items-center w-100">
                <h4>
                    <i class="fas fa-file-alt me-2"></i>
                    My Submissions
                </h4>
                <button type="button" class="btn btn-primary btn-sm"
                    data-toggle="modal" data-target="#requestLeaveModal">
                    <i class="fas fa-plus me-1"></i>
                    New Request
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            @forelse ($submissions as $s)
                <div class="submission-item">

                    {{-- Header: Tipe & Status --}}
                    <div class="submission-header-row">
                        <span class="submission-type-badge {{ $s->type_slug }}">
                            <i class="fas {{ $s->type_icon }} me-1"></i>
                            {{ $s->type_name }}
                        </span>
                        <span class="submission-status {{ $s->status_class }}">
                            <i class="fas {{ $s->status_icon }} me-1"></i>
                            {{ $s->status_label }}
                        </span>
                    </div>

                    {{-- Meta: Tanggal, Durasi, Waktu Pengajuan --}}
                    <div class="submission-meta">
                        <span>
                            <i class="fas fa-calendar"></i>
                            {{ $s->start }}
                            @unless ($s->is_same_day)
                                - {{ $s->end }}
                            @endunless
                        </span>
                        <span>
                            <i class="fas fa-hourglass-half"></i>
                            {{ $s->duration_label }}
                        </span>
                        <span>
                            <i class="fas fa-clock"></i>
                            {{ $s->posted_ago }}
                        </span>
                    </div>

                    {{-- Approver (jika sudah diproses) --}}
                    @if ($s->approver_name && $s->status !== 'pending')
                        <div class="submission-approver">
                            <i class="fas fa-user-check me-1"></i>
                            <small class="text-muted">
                                {{ $s->status === 'approved' ? 'Approved' : 'Reviewed' }} by
                                <strong>{{ $s->approver_name }}</strong>
                            </small>
                        </div>
                    @endif

                    {{-- Notes / Rejection Reason --}}
                    <div class="submission-notes">
                        <i class="fas fa-sticky-note me-2"></i>
                        @if ($s->status === 'rejected' && $s->reject_reason)
                            <strong>Rejection reason:</strong> {{ $s->reject_reason }}
                        @else
                            <strong>Note:</strong> {{ $s->note }}
                        @endif
                    </div>

                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    No submissions yet.
                </div>
            @endforelse
        </div>

        <div class="card-footer bg-light text-center">
            {{-- <a href="{{ route('leave-requests.index') }}" class="text-decoration-none">
                View All Submissions
                <i class="fas fa-arrow-right ms-2"></i>
            </a> --}}
        </div>
    </div>
</div>
            </div>

            <!-- Announcements & Attendance History Row -->
            <div class="row">
                <div class="col-lg-12 col-12 mb-4">
                    <div class="attendance-history-card">
                        <div class="attendance-history-header">
                            <h4>
                                <i class="fas fa-calendar-check me-2"></i>
                                Attendance History
                            </h4>
                        </div>
                        <div class="attendance-calendar">
                            <div class="calendar-month">
                                <i class="fas fa-chevron-left" style="cursor: pointer;"></i>
                                <span class="mx-4">December 2024</span>
                                <i class="fas fa-chevron-right" style="cursor: pointer;"></i>
                            </div>

                            <div class="calendar-grid">
                                <!-- Day Headers -->
                                <div class="calendar-day-header">Sun</div>
                                <div class="calendar-day-header">Mon</div>
                                <div class="calendar-day-header">Tue</div>
                                <div class="calendar-day-header">Wed</div>
                                <div class="calendar-day-header">Thu</div>
                                <div class="calendar-day-header">Fri</div>
                                <div class="calendar-day-header">Sat</div>

                                <!-- Week 1 -->
                                <div class="calendar-day weekend">1</div>
                                <div class="calendar-day present">2</div>
                                <div class="calendar-day present">3</div>
                                <div class="calendar-day present">4</div>
                                <div class="calendar-day present">5</div>
                                <div class="calendar-day present">6</div>
                                <div class="calendar-day weekend">7</div>

                                <!-- Week 2 -->
                                <div class="calendar-day weekend">8</div>
                                <div class="calendar-day present">9</div>
                                <div class="calendar-day present">10</div>
                                <div class="calendar-day present">11</div>
                                <div class="calendar-day present">12</div>
                                <div class="calendar-day present">13</div>
                                <div class="calendar-day weekend">14</div>

                                <!-- Week 3 -->
                                <div class="calendar-day weekend">15</div>
                                <div class="calendar-day absent">16</div>
                                <div class="calendar-day present">17</div>
                                <div class="calendar-day present">18</div>
                                <div class="calendar-day present">19</div>
                                <div class="calendar-day leave">20</div>
                                <div class="calendar-day weekend">21</div>

                                <!-- Week 4 -->
                                <div class="calendar-day weekend">22</div>
                                <div class="calendar-day leave">23</div>
                                <div class="calendar-day leave">24</div>
                                <div class="calendar-day leave">25</div>
                                <div class="calendar-day leave">26</div>
                                <div class="calendar-day present">27</div>
                                <div class="calendar-day weekend">28</div>

                                <!-- Week 5 -->
                                <div class="calendar-day weekend">29</div>
                                <div class="calendar-day present">30</div>
                                <div class="calendar-day today">31</div>
                                <div class="calendar-day empty"></div>
                                <div class="calendar-day empty"></div>
                                <div class="calendar-day empty"></div>
                                <div class="calendar-day empty"></div>
                            </div>

                            <!-- Calendar Legend -->
                            <div class="calendar-legend">
                                <div class="legend-item">
                                    <div class="legend-color" style="background: rgba(56, 239, 125, 0.15);"></div>
                                    <span>Present</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background: rgba(245, 87, 108, 0.15);"></div>
                                    <span>Absent</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background: rgba(255, 171, 0, 0.15);"></div>
                                    <span>Late</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background: #f8f9fa;"></div>
                                    <span>Weekend</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- ═════════════════════════════════════════════════════════════
          MODAL DIUBAH: Request Leave Modal (DINAMIS DARI DB)
         - action: route('Leaverequest.store') (bukan Submissions.store)
         - Dropdown jenis cuti dari $leaveBalances
         - Field name: leave_balance_id, start_date, end_date, employee_reason
         ═════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="requestLeaveModal" tabindex="-1" aria-labelledby="requestLeaveLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form id="leaveRequestForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="requestLeaveLabel">
                            <i class="fas fa-paper-plane me-2"></i>
                            Apply Leave
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <!-- Jenis Cuti (DINAMIS dari leave_balances_tables) -->
                        <div class="mb-4">
                            <label class="form-label" for="leave_balance_id">
                                <i class="fas fa-clipboard-list me-1"></i> Type Leave
                            </label>
                            <select name="leave_balance_id" id="leave_balance_id" class="form-control" required>
                                <option value="">-- Select type of leave --</option>
                                @forelse(($leaveBalances ?? []) as $balance)
                                    <option value="{{ $balance->id }}" data-days="{{ $balance->balance_days }}"
                                        data-name="{{ $balance->leaves->name ?? 'Leave' }}">
                                        {{ $balance->leaves->name ?? 'Leave' }}
                                        — Sisa: {{ $balance->balance_days }} days
                                    </option>
                                @empty
                                    <option value="" disabled>No leave balance available</option>
                                @endforelse
                            </select>
                        </div>

                        <!-- Available Balance Info (auto-update) -->
                        <div class="alert alert-info mb-4" id="leaveBalanceInfo" style="display:none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Balance Available:</strong>
                                    <span id="availableBalance">- days</span>
                                </div>
                                <div>
                                    <strong>Leave Type:</strong>
                                    <span id="selectedLeaveType">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Date Range -->
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

                        <!-- Duration Display -->
                        <div class="alert alert-light border mb-4" id="durationInfo">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-hourglass-half me-2"></i><strong>Duration:</strong></span>
                                <span id="calculatedDuration" class="text-primary font-weight-bold">0 days</span>
                            </div>
                        </div>

                        <!-- Warning kalau durasi > saldo -->
                        <div class="alert alert-danger mb-4" id="balanceWarning" style="display:none;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Insufficient leave balance!</strong>
                            The requested duration exceeds your remaining leave days.
                        </div>

                        <!-- Alasan -->
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
    <div class="modal fade" id="announcementModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
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

{{-- ═════════════════════════════════════════════════════════════
       modal pengajuan cuti
     ═════════════════════════════════════════════════════════════ --}}
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
          @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}'
            });
        @endif
    </script>
    <script>
        // Isi modal saat announcement diklik
        $('#announcementModal').on('show.bs.modal', function(e) {
            const trigger = $(e.relatedTarget);
            $('#modalTitle').text(trigger.data('title'));
            $('#modalAuthor').text(trigger.data('author'));
            $('#modalDate').text(trigger.data('date'));
            $('#modalContent').html(trigger.data('content'));
        });
    </script>


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

            // ✅ Destroy Select2 saat modal ditutup
            $('#requestLeaveModal').on('hidden.bs.modal', function() {
                $('#leave_balance_id').select2('destroy');
            });

            // ✅ Satu saja, tidak perlu dua
            $('#leave_balance_id').on('select2:select select2:clear', function() {
                this.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
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

            // ── Saat pilih jenis cuti → tampilkan saldo tersedia ──
            leaveSelect?.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                const days = parseInt(opt.dataset.days ?? 0);
                const name = opt.dataset.name ?? '-';

                if (this.value) {
                    maxDays = days;
                    availableBalance.textContent = days + ' days';
                    selectedType.textContent = name;
                    balanceInfo.style.display = 'block';
                } else {
                    balanceInfo.style.display = 'none';
                    maxDays = 0;
                }
                calculateDuration();
            });

            // ── Hitung durasi otomatis ──
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
                calculateDuration();
            });
            endDate?.addEventListener('change', calculateDuration);

            // ── Submit via AJAX ──
            form?.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!leaveSelect.value) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Select the type of leave',
                        text: 'Please select the type of leave first.',
                    });
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
                    submitBtn.innerHTML =
                        '<i class="fas fa-spinner fa-spin me-1"></i> Submitting...';

                    const payload = {
                        leave_balance_id: document.getElementById('leave_balance_id').value,
                        start_date: document.getElementById('start_date').value,
                        end_date: document.getElementById('end_date').value,
                        employee_reason: document.getElementById('employee_reason').value,
                    };

                    fetch("{{ route('Leaverequest.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(payload),
                        })
                        .then(async (res) => {
                            const data = await res.json();
                            return {
                                ok: res.ok,
                                data
                            };
                        })
                        .then(({
                            ok,
                            data
                        }) => {
                            if (ok && data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: data.message ??
                                        'Leave request submitted successfully.',
                                    confirmButtonText: 'OK',
                                }).then(() => {
                                    $('#requestLeaveModal').modal('hide');
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Failed',
                                    text: data.message ??
                                        'An error occurred while submitting the request.',
                                });
                                submitBtn.disabled = false;
                                submitBtn.innerHTML =
                                    '<i class="fas fa-paper-plane me-1"></i> Submit Leave Request';
                            }
                        })
                        .catch((err) => {
                            console.error(err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed',
                                text: 'An error occurred while submitting the request.',
                            });
                            submitBtn.disabled = false;
                            submitBtn.innerHTML =
                                '<i class="fas fa-paper-plane me-1"></i> Submit Leave Request';
                        });
                });
            });
        });
    </script>
@endpush
