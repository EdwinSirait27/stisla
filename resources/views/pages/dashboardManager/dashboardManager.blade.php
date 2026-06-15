@extends('layouts.app')
@section('title', 'Employee Dashboard')

@push('styles')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
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

        .calendar-day {
            font-size: 0.75rem;
        }

        .calendar-day {
            flex-direction: column;
        }

        .calendar-day-number {
            font-weight: 600;
        }

        .calendar-day-label {
            font-size: 0.65rem;
            margin-top: 2px;
            opacity: 0.85;
        }

        .calendar-day-remark {
            font-size: 0.6rem;
            opacity: 0.7;
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

        /* Override modal header putih khusus untuk modal reject */
        #rejectLeaveModal .modal-header {
            background: white;
        }

        #rejectLeaveModal .modal-title {
            color: #344767;
        }

        #rejectLeaveModal .btn-close {
            filter: none;
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

        .btn-reject-leave {
            transition: all 0.2s;
        }

        .btn-reject-leave:hover {
            background-color: #D32F2F !important;
            color: #fff !important;
            border-color: #D32F2F !important;
        }

        .btn-approve-leave {
            transition: all 0.2s;
        }

        .btn-approve-leave:hover {
            background-color: #1565C0 !important;
            color: #fff !important;
            border-color: #1565C0 !important;
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
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <!-- Profile Header -->
            {{-- <div class="profile-header-card animate-fade-in-up">
                <div class="profile-content">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center gap-4">
                                <img src="{{ Auth::user()->employee->photos
                                    ? asset('storage/' . Auth::user()->employee->photos)
                                    : asset('img/avatar/avatar-1.png') }}"
                                    alt="Profile" class="profile-avatar-large">
                                <div class="profile-info">
                                    <h2>{{ Auth::user()->employee->employee_name ?? 'Edwin Sirait' }}</h2>
                                    <div class="profile-meta">
                                        <div class="profile-meta-item">
                                            <i class="fas fa-briefcase"></i>
                                            <span>{{ Auth::user()->employee->position->name ?? 'Edwin Sirait' }} </span>
                                        </div>
                                        <div class="profile-meta-item">
                                            <i class="fas fa-building"></i>
                                            <span>{{ Auth::user()->employee->department->department_name ?? 'Edwin Sirait' }}</span>
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
            </div> --}}
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
                                    <h2>{{ Auth::user()->employee->employee_name ?? 'Edwin Sirait' }}</h2>
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

            <!-- Mini Stat Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card primary">
                        <div class="mini-stat-icon primary">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="mini-stat-value">{{ $presentCount ?? 0 }}</div>
                        <div class="mini-stat-label">Days Present</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card success">
                        <div class="mini-stat-icon success">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="mini-stat-value">95%</div>
                        <div class="mini-stat-label">Attendance Rate</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card warning">
                        <div class="mini-stat-icon warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="mini-stat-value">2</div>
                        <div class="mini-stat-label">Times Late</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card danger">
                        <div class="mini-stat-icon danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="mini-stat-value">1</div>
                        <div class="mini-stat-label">Days Absent</div>
                    </div>
                </div>
            </div>

            <!-- Company Announcements -->
            <div class="col-lg-12 col-12 mb-4">
                <div class="announcements-card">
                    <div class="announcements-header">
                        <h4>
                            <i class="fas fa-bullhorn me-2"></i>
                            Company Announcements
                        </h4>
                    </div>
                    <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                        @forelse($announcements as $a)
                            <div class="announcement-item" data-toggle="modal" data-target="#previewModal"
                                data-title="{{ $a->title }}"
                                data-content="{{ str_replace('&nbsp;', ' ', $a->content) }}"
                                data-publish="{{ \Carbon\Carbon::parse($a->publish_date)->format('d M Y') }}"
                                data-end="{{ \Carbon\Carbon::parse($a->end_date)->format('d M Y') }}"
                                {{-- data-employee="{{ $a->user->Employee->department->department_name ?? 'Unknown' }}"> --}}
                                data-employee="{{ $a->user->Employee->department->first()?->department_name ?? 'Unknown' }}">

                                <div class="announcement-title">
                                    <i class="fas fa-star text-warning"></i>
                                    {{ $a->title }}
                                    @if (\Carbon\Carbon::parse($a->publish_date)->greaterThan(now()->subDays(3)))
                                        <span class="announcement-badge-new">New</span>
                                    @endif
                                </div>
                                <div class="announcement-excerpt">
                                    {{ Str::limit(strip_tags($a->content), 120, '...') }}
                                </div>
                                <div class="announcement-date">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Posted {{ \Carbon\Carbon::parse($a->publish_date)->diffForHumans() }}
                                </div>
                            </div>
                        @empty
                            <div class="p-3 text-center text-muted">
                                No announcements found.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Main Content Grid: Leave Balance + Leave Approval -->
            <div class="row">

                <!-- Leave Balance -->
                <div class="col-lg-4 col-12 mb-4">
                    <div class="leave-balance-card">
                        <div class="leave-balance-header">
                            <h4>
                                <i class="fas fa-umbrella-beach me-2"></i>
                                Leave Balance
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

                <!-- Leave Approval -->
                <div class="col-lg-8 col-12 mb-4">
                    <div class="submissions-card">
                        <div class="submissions-header">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <h4>
                                    <i class="fas fa-clipboard-check me-2"></i>
                                    Leave Approval
                                </h4>
                                <span class="badge"
                                    style="background-color:#FFF3CD; color:#856404; font-size:0.75rem; padding:6px 12px; border-radius:6px;">
                                    {{ $pendingLeaves->count() }} Pending
                                </span>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            @forelse($pendingLeaves as $leave)
                                <div class="submission-item leave-approval-item" id="leave-item-{{ $leave['id'] }}">
                                    <div class="d-flex align-items-center">

                                        <!-- Avatar -->
                                        <div class="flex-shrink-0" style="margin-right: 24px;">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                                style="width:44px; height:44px; background-color:{{ $leave['bgColor'] }}; font-size:1rem;">
                                                {{ $leave['initial'] }}
                                            </div>
                                        </div>

                                        <!-- Info -->
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold mb-1" style="font-size:0.95rem; color:#344767;">
                                                {{ $leave['employeeName'] }}
                                            </div>
                                            <div class="d-flex align-items-center flex-wrap"
                                                style="font-size:0.82rem; color:#64748b; gap: 14px;">
                                                <span><i class="fas fa-calendar me-1"></i>{{ $leave['dateLabel'] }}</span>
                                                <span><i class="fas fa-clock me-1"></i>{{ $leave['ago'] }}</span>
                                                <span class="badge fw-semibold"
                                                    style="background-color:{{ $leave['typeBg'] }}; color:{{ $leave['typeText'] }};
                                   font-size:0.7rem; padding:4px 10px; border-radius:6px;">
                                                    {{ $leave['leaveTypeName'] }}
                                                </span>
                                            </div>
                                            @if ($leave['employeeReason'])
                                                <div class="mt-2" style="font-size:0.85rem; color:#344767;">
                                                    <i class="fas fa-sticky-note me-1 text-muted"></i>
                                                    <strong>Note:</strong> {{ $leave['employeeReason'] }}
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Tombol Approve & Reject -->
                                        <div class="d-flex flex-column ms-2" style="gap: 10px;">
                                            <button type="button" class="btn btn-sm fw-semibold btn-approve-leave"
                                                data-id="{{ $leave['id'] }}" data-name="{{ $leave['employeeName'] }}"
                                                data-url="{{ $leave['approveUrl'] }}"
                                                style="background-color:#fff; color:#1976D2; border:1.5px solid #1976D2;
                               border-radius:6px; font-size:0.75rem; padding:5px 14px; white-space:nowrap;">
                                                <i class="fas fa-check me-1"></i> Approve
                                            </button>

                                            <button type="button" class="btn btn-sm fw-semibold btn-reject-leave"
                                                data-id="{{ $leave['id'] }}" data-name="{{ $leave['employeeName'] }}"
                                                data-url="{{ $leave['rejectUrl'] }}"
                                                style="background-color:#fff; color:#D32F2F; border:1.5px solid #D32F2F;
                               border-radius:6px; font-size:0.75rem; padding:5px 14px; white-space:nowrap;">
                                                <i class="fas fa-times me-1"></i> Reject
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 px-4" id="leave-empty-state">
                                    <i class="fas fa-check-circle"
                                        style="font-size:3rem; color:#11998e; opacity:0.5;"></i>
                                    <h6 class="mt-3 mb-1" style="color:#344767;">Semua cuti sudah ditangani</h6>
                                    <p class="text-muted mb-0" style="font-size:0.875rem;">
                                        Tidak ada pengajuan cuti yang menunggu persetujuan.
                                    </p>
                                </div>
                            @endforelse
                        </div>

                        @if ($pendingLeaves->count() > 0)
                            <div class="card-footer bg-light text-center" id="leave-footer">
                                <a href="{{ route('leaverequest.index') }}" class="text-decoration-none">
                                    View All Leave Requests
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Schedule History Row -->
            <div class="row">
                <div class="col-lg-6 col-12 mb-4">
                    <div class="attendance-history-card">
                        <div class="attendance-history-header">
                            <h4>
                                <i class="fas fa-calendar-check me-2"></i>
                                Schedule History
                            </h4>
                        </div>
                        <div class="attendance-calendar">
                            <div id="calendarContainer">
                                @include('pages.dashboardManager.calendar')
                            </div>

                            <div class="calendar-legend">
                                <div class="legend-item">
                                    <div class="legend-color" style="background: rgba(56, 239, 125, 0.15);"></div>
                                    <span>Work</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background: #f8f9fa;"></div>
                                    <span>Off</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background: rgba(255, 171, 0, 0.15);"></div>
                                    <span>Public Holiday</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background: rgba(245, 87, 108, 0.15);"></div>
                                    <span>Leave</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background: rgba(245, 87, 108, 0.15);"></div>
                                    <span>Cuti Melahirkan</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    </div>
    </section>
    </div>

    <!-- ═══════════ Modal Reject Leave ═══════════ -->
    <div class="modal fade" id="rejectLeaveModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-semibold">Tolak Pengajuan Cuti</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3" style="font-size:0.9rem;">
                        Tolak cuti <strong id="rejectEmployeeName"></strong>?
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.85rem;">
                            Alasan Penolakan
                        </label>
                        <textarea id="rejectReason" class="form-control" rows="3" placeholder="Isi alasan penolakan..."
                            style="font-size:0.9rem;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="btnConfirmReject" class="btn btn-sm fw-semibold"
                        style="background-color:#D32F2F; color:#fff;">
                        Tolak Cuti
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════ Request Leave Modal ═══════════ -->
    <div class="modal fade" id="requestLeaveModal" tabindex="-1" aria-labelledby="requestLeaveLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('Submissions.store') }}" method="POST" id="leaveRequestForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="requestLeaveLabel">
                            <i class="fas fa-paper-plane me-2"></i>
                            Request Leave
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label" for="leave_type">
                                <i class="fas fa-clipboard-list me-1"></i> Leave Type
                            </label>
                            <select name="type" id="leave_type" class="form-control" required>
                                <option value="">Choose leave type</option>
                                <option value="Annual Leave">Annual Leave</option>
                                <option value="Sick Leave">Sick Leave</option>
                                <option value="Casual Leave">Casual Leave</option>
                                <option value="Emergency Leave">Emergency Leave</option>
                            </select>
                        </div>

                        <div class="alert alert-info mb-4" id="leaveBalanceInfo">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>Available Balance:</strong>
                                    <span id="availableBalance">12 days</span>
                                </div>
                                <div>
                                    <strong>Total Allocation:</strong>
                                    <span id="totalAllocation">14 days</span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="start_date">
                                    <i class="fas fa-calendar-alt me-1"></i> Start Date
                                </label>
                                <input type="date" name="leave_date_from" id="start_date" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="end_date">
                                    <i class="fas fa-calendar-check me-1"></i> End Date
                                </label>
                                <input type="date" name="leave_date_to" id="end_date" class="form-control" required>
                            </div>
                        </div>

                        <div class="alert alert-light border mb-4" id="durationInfo">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-hourglass-half me-2"></i><strong>Duration:</strong></span>
                                <span id="calculatedDuration" class="text-primary font-weight-bold">0 days</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="leave_reason">
                                <i class="fas fa-sticky-note me-1"></i> Reason
                            </label>
                            <textarea name="notes" id="leave_reason" class="form-control" rows="4"
                                placeholder="Please provide a brief reason for your leave request..." required></textarea>
                        </div>

                        <div class="mb-3" id="emergencyContactDiv" style="display: none;">
                            <label class="form-label" for="emergency_contact">
                                <i class="fas fa-phone me-1"></i> Emergency Contact (Optional)
                            </label>
                            <input type="text" name="emergency_contact" id="emergency_contact" class="form-control"
                                placeholder="Contact number during leave">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ═══════════ Announcement Preview Modal ═══════════ -->
    <div class="modal fade preview-modal" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalLabel">
                        <i class="fas fa-eye me-2"></i>
                        Announcement Preview
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-4">
                    <table class="table table-sm preview-table mb-4">
                        <tbody>
                            <tr>
                                <th style="width: 150px;">Publish Date</th>
                                <td><span id="previewDate" class="fw-semibold"></span></td>
                            </tr>
                            <tr>
                                <th>End Date</th>
                                <td><span id="previewEndDate" class="fw-semibold"></span></td>
                            </tr>
                            <tr>
                                <th>Created By</th>
                                <td><span id="previewEmployee" class="fw-semibold"></span></td>
                            </tr>
                        </tbody>
                    </table>

                    <h5 id="previewTitle" class="fw-bold mb-3 text-center"></h5>

                    <div id="previewContent" style="max-height: 400px; overflow-y: auto; line-height: 1.8;">
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <small class="text-muted text-center w-100">
                        <i class="fas fa-shield-alt me-2"></i>
                        Official announcement from HR Department •
                        <a href="https://wa.me/6281138310552" target="_blank" class="text-success">
                            Contact HR
                        </a>
                    </small>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function loadCalendar(month, year) {
            const container = document.getElementById('calendarContainer');
            container.style.opacity = '0.5';

            fetch(`{{ url('/dashboardManager') }}?month=${month}&year=${year}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.text())
                .then(html => {
                    container.innerHTML = html;
                    container.style.opacity = '1';
                })
                .catch(() => {
                    window.location.href = `{{ url('/dashboardManager') }}?month=${month}&year=${year}`;
                });
        }
    </script>

    <!-- Script announcement preview -->

    <!-- Script announcement preview -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('.announcement-item').on('click', function() {
                $('#previewTitle').text($(this).data('title'));
                $('#previewContent').html($(this).data('content'));
                $('#previewDate').text($(this).data('publish'));
                $('#previewEndDate').text($(this).data('end'));
                $('#previewEmployee').text($(this).data('employee'));
            });
        });
    </script>

    <!-- SweetAlert session success/error -->
    <script>
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
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

    <!-- AJAX Approve / Reject Leave -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

            function removeLeaveItem(id) {
                const item = document.getElementById('leave-item-' + id);
                if (item) {
                    item.style.transition = 'opacity 0.4s';
                    item.style.opacity = '0';
                    setTimeout(() => {
                        item.remove();
                        const remaining = document.querySelectorAll('.leave-approval-item');
                        if (remaining.length === 0) {
                            const footer = document.getElementById('leave-footer');
                            if (footer) footer.remove();
                        }
                    }, 400);
                }
            }

            function sendDecision(url, reason, id, successMsg) {
                return fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            approver_reason: reason
                        }),
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
                                title: 'Berhasil',
                                text: successMsg,
                                timer: 1500,
                                showConfirmButton: false,
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: data.message ?? 'Terjadi kesalahan.',
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan, coba lagi.',
                        });
                    });
            }

            // ── APPROVE ──
            document.querySelectorAll('.btn-approve-leave').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const url = this.dataset.url;

                    Swal.fire({
                        title: 'Setujui Cuti',
                        html: `Setujui pengajuan cuti <strong>${name}</strong>?`,
                        input: 'textarea',
                        inputPlaceholder: 'Tulis alasan persetujuan...',
                        showCancelButton: true,
                        confirmButtonText: 'Setujui',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#1976D2',
                        inputValidator: (value) => {
                            if (!value || !value.trim()) {
                                return 'Alasan persetujuan wajib diisi.';
                            }
                        }
                    }).then((result) => {
                        if (!result.isConfirmed) return;
                        sendDecision(url, result.value.trim(), id,
                            `Cuti ${name} berhasil disetujui.`);
                    });
                });
            });

            // ── REJECT ──
            document.querySelectorAll('.btn-reject-leave').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const url = this.dataset.url;

                    Swal.fire({
                        title: 'Tolak Cuti',
                        html: `Tolak pengajuan cuti <strong>${name}</strong>?`,
                        input: 'textarea',
                        inputPlaceholder: 'Tulis alasan penolakan...',
                        showCancelButton: true,
                        confirmButtonText: 'Tolak',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#D32F2F',
                        inputValidator: (value) => {
                            if (!value || !value.trim()) {
                                return 'Alasan penolakan wajib diisi.';
                            }
                        }
                    }).then((result) => {
                        if (!result.isConfirmed) return;
                        sendDecision(url, result.value.trim(), id,
                            `Cuti ${name} berhasil ditolak.`);
                    });
                });
            });
        });
    </script>
@endpush
