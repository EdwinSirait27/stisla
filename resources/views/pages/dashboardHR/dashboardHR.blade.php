{{-- @extends('layouts.app')
@section('title', 'HR Dashboard')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
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

        /* ========== Card Metrics ========== */
        .metric-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .metric-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        }

        .metric-card .card-icon {
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 20px;
        }

        .metric-card .card-icon i {
            font-size: 2rem;
        }

        /* ========== Chart Section ========== */
        .chart-card {
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .date-filter-container {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .date-filter-container input[type="date"] {
            min-width: 140px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 4px 6px;
            font-size: 0.875rem;
        }

        .date-filter-container .btn {
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
        }

        /* ========== Submission Card ========== */
        .submission-card {
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .submission-list-item {
            padding: 16px;
            border-radius: 8px;
            transition: background-color 0.2s;
            margin-bottom: 12px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .submission-list-item:hover {
            background-color: #f8f9fa;
        }

        .leave-summary-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
        }

        .leave-summary-box h6 {
            color: white;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .leave-stat {
            text-align: center;
        }

        .leave-stat small {
            display: block;
            opacity: 0.9;
            font-size: 0.75rem;
            margin-bottom: 4px;
        }

        .leave-stat h6 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        /* ========== Announcement Section ========== */
        .announcement-card {
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        /* ========== Modal Improvements ========== */
        .modal-content {
            border-radius: 16px;
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, #242424 0%, #282829 100%);
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

        .form-control,
        .select2 {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 10px 14px;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* ========== Info Box ========== */
        .info-box {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 12px;
            padding: 20px;
            color: white;
        }

        .info-box p {
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .info-box strong {
            font-weight: 600;
        }

        /* ========== Employee Checkbox List ========== */
        .employee-checkbox-container {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px;
            background: #f8f9fa;
        }

        .form-check {
            padding: 8px 12px;
            border-radius: 6px;
            transition: background-color 0.2s;
        }

        .form-check:hover {
            background-color: white;
        }

        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        /* ========== Buttons ========== */
        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 10px 20px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #000000 0%, #0c0c0c 100%);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-lg {
            padding: 12px 32px;
            font-size: 1rem;
        }

        /* ========== Alert ========== */
        .alert-info-custom {
            background: linear-gradient(135deg, #000000 0%, #2a2b2c 100%);
            border: none;
            border-radius: 12px;
            padding: 16px;
            color: #1e293b;
        }

        .alert-info-custom strong {
            display: block;
            margin-bottom: 8px;
            font-size: 1rem;
        }

        /* ========== Preview Modal ========== */
        .preview-modal .modal-content {
            border-radius: 20px;
        }

        .preview-modal .modal-header {
            background: white;
            border-bottom: 2px solid #f1f3f5;
        }

        .preview-modal .modal-title {
            color: #344767;
        }

        .preview-table {
            font-size: 0.9rem;
        }

        .preview-table th {
            color: #64748b;
            font-weight: 600;
        }

        .preview-table td {
            color: #344767;
        }

        /* ========== Responsive ========== */
        @media (max-width: 768px) {
            .date-filter-container {
                flex-direction: column;
                width: 100%;
            }

            .date-filter-container input[type="date"],
            .date-filter-container .btn {
                width: 100%;
            }

            .metric-card {
                margin-bottom: 16px;
            }

            .leave-summary-box .d-flex {
                flex-direction: column;
                gap: 16px;
            }
        }

        /* ========== Accessibility ========== */
        .btn:focus,
        .form-control:focus,
        .form-check-input:focus {
            outline: 3px solid rgba(102, 126, 234, 0.3);
            outline-offset: 2px;
        }

        /* ========== Loading State ========== */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            z-index: 10;
        }

        .welcome-banner {
            background: var(--primary-gradient);
            border-radius: 16px;
            padding: 32px;
            color: white;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
        }

        .welcome-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .welcome-banner h2 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .welcome-banner p {
            font-size: 1rem;
            opacity: 0.95;
            margin-bottom: 0;
            position: relative;
            z-index: 1;
        }

        .welcome-banner .date-info {
            position: relative;
            z-index: 1;
            margin-top: 16px;
            font-size: 0.9rem;
            opacity: 0.9;
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

        /* ========== Quick Stats Cards ========== */
        .quick-stats {
            margin-bottom: 16px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--card-hover-shadow);
        }

        .stat-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.primary {
            background: var(--primary-gradient);
        }

        .stat-icon.success {
            background: var(--success-gradient);
        }

        .stat-icon.warning {
            background: var(--warning-gradient);
        }

        .stat-icon.info {
            background: var(--info-gradient);
        }

        .stat-content h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            color: #344767;
        }

        .stat-content p {
            margin: 4px 0 0 0;
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .stat-trend {
            display: inline-flex;
            align-items: center;
            font-size: 0.8rem;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 8px;
        }

        .stat-trend.up {
            background: rgba(56, 239, 125, 0.15);
            color: #11998e;
        }

        .stat-trend.down {
            background: rgba(245, 87, 108, 0.15);
            color: #f5576c;
        }
           img.no-drag {
        -webkit-user-drag: none;
        user-select: none;
    }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <!-- Header -->

            <div class="profile-header-card animate-fade-in-up">
                <div class="profile-content">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center gap-4">
                                <img src="{{ Auth::user()->employee->photos
                                    ? asset('storage/' . Auth::user()->employee->photos)
                                    : asset('img/avatar/avatar-1.png') }}"
                                    alt="Profile" class="profile-avatar-large no-drag">


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
            </div>

            <div class="section-body">
                <div class="quick-stats">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 col-12 mb-4">
                            <div onclick="window.location='{{ route('pages.Employee') }}';" class="stat-card" role="button"
                                title="show employees" aria-label="View all employees">
                                <div class="stat-card-header">
                                    <div class="stat-icon primary">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                                <div class="stat-content">
                                    <h3>{{ $totalEmployees ?? 0 }}</h3>
                                    <p>Employees</p>
                                    <span class="stat-trend up" title="employees who are still pending from last week">
                                        <i class="fas fa-arrow-up me-1"></i>
                                        {{ $totalEmployeespending }} Pending
                                    </span>
                                    <span class="stat-trend down" style="margin-left: 8px;"title="employees who resigned last week">
                                        <i class="fas fa-arrow-down me-1"></i>
                                        {{ $totalEmployeesinactive }} Resigned
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-12 mb-4">
                            <div onclick="window.location='{{ route('pages.Fingerprints') }}';" class="stat-card" role="button"
                                title="show attendance" aria-label="View all fingerprints">
                                <div class="stat-card-header">
                                    <div class="stat-icon success">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                </div>
                                <div class="stat-content">
                                    <h3>{{ $presentToday ?? 0 }}</h3>
                                    <p>Present Today</p>
                                    @if ($trend >= 0)
                                        <span class="stat-trend up" title="Employees who were present yesterday">
                                            <i class="fas fa-arrow-up me-1"></i>
                                            {{ $trend }} Employees
                                        </span>
                                    @else
                                        <span class="stat-trend down" title="Employees who were present yesterday">
                                            <i class="fas fa-arrow-down me-1"></i>
                                            {{ $presentYesterday }} Employees
                                        </span>
                                    @endif

                                </div>
                            </div>
                        </div>


                        <div class="col-lg-3 col-md-6 col-12 mb-4">
                            <div class="stat-card">
                                <div class="stat-card-header">
                                    <div class="stat-icon warning">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                </div>
                                <div class="stat-content">
                                    <h3>{{ $pendingApprovals ?? 0 }}</h3>
                                    <p>All Approvals</p>
                                    @if (($pendingApprovals ?? 0) > 0)
                                        <span class="stat-trend down">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            Needs Action
                                        </span>
                                    @else
                                        <span class="stat-trend up">
                                            <i class="fas fa-check-circle me-1"> </i> All Clear </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12 mb-4">
                            <div class="stat-card">
                                <div class="stat-card-header">
                                    <div class="stat-icon info">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                </div>
                                <div class="stat-content">
                                    <h3>{{ $onLeave ?? 0 }}</h3>
                                    <p>On Leave</p>
                                    <span class="badge-primary-soft mt-2">
                                        This Week
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-12">
                        <div class="card announcement-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">
                                    <i class="fas fa-bullhorn me-2"></i>
                                    Announcements
                                </h4>
                                <button id="btn-announcement" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i>
                                    New Announcement
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="users-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Title</th>
                                                <th class="text-center">Publish Date</th>
                                                <th class="text-center">End Date</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>




                <!-- Chart & Submissions Row -->
                <div class="row">
                    <div class="col-lg-8 col-12 mb-4">
                        <div class="card chart-card">
                            <div class="card-header">
                                <h4>
                                    <i class="fas fa-calendar-check me-2"></i>
                                    Monthly Attendance Rate
                                </h4>
                                <div class="card-header-action">
                                    <div class="date-filter-container d-flex align-items-center gap-2">
                                        <input type="date" id="startDate" class="form-control date-input"
                                            aria-label="Start Date" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                        <input type="date" id="endDate" class="form-control date-input"
                                            aria-label="End Date" value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                                        <button id="filterButton" class="btn btn-primary">
                                            <i class="fas fa-filter me-1"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="attendanceChart" height="180"></canvas>
                                <div class="alert alert-info-custom mt-4" role="alert">
                                    <strong><i class="fas fa-info-circle me-2"></i> Chart Information</strong>
                                    <span class="d-block mt-1">X-axis represents dates, Y-axis shows total employee
                                        attendance.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <style>
                        /* Ukuran input tanggal diperkecil */
                        .date-input {
                            width: 130px !important;
                            padding: 4px 6px;
                            font-size: 0.9rem;
                        }

                        /* Supaya rapat tapi tetap rapi */
                        .date-filter-container {
                            display: flex;
                            align-items: center;
                            gap: 8px;
                        }
                    </style>


                    <!-- Submissions List -->
                    <div class="col-lg-4 col-12 mb-4">
                        <div class="card submission-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">
                                    <i class="fas fa-file-alt me-2"></i>
                                    Submissions
                                </h4>
                               
                            </div>

                            <div class="card-body">
                                <!-- Annual Leave Summary -->
                                @if ($selectedType === 'Annual Leave' && isset($leaveData))
                                    <div class="leave-summary-box">
                                        <h6><i class="fas fa-umbrella-beach me-2"></i>Annual Leave Summary</h6>
                                        <div class="d-flex justify-content-between">
                                            <div class="leave-stat">
                                                <small>Total</small>
                                                <h6>{{ $leaveData['total'] }}</h6>
                                            </div>
                                            <div class="leave-stat">
                                                <small>Pending</small>
                                                <h6>{{ $leaveData['pending'] }}</h6>
                                            </div>
                                            <div class="leave-stat">
                                                <small>Remaining</small>
                                                <h6>{{ $leaveData['remaining'] }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Pending Submissions List -->
                                <div class="submissions-list">
                                    @forelse($pendingSubmissions as $submission)
                                        <div class="submission-list-item">
                                            <div class="d-flex align-items-center">
                                                <img class="rounded-circle me-3 no-drag" width="48" height="48"
                                                    src="{{ asset('img/avatar/avatar-' . rand(1, 5) . '.png') }}"
                                                    alt="{{ $submission->employee->employee_name }}">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                        <h6 class="mb-0">{{ $submission->employee->employee_name }}</h6>
                                                        <small
                                                            class="text-muted">{{ $submission->created_at->diffForHumans() }}</small>
                                                    </div>
                                                    <span class="text-small text-muted">
                                                        <i class="fas fa-tag me-1"></i>
                                                        {{ ucfirst($submission->type) }} -
                                                        {{ $submission->formattedDuration }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">No pending submissions yet</p>
                                        </div>
                                    @endforelse
                                </div>

                                <!-- View All Button -->
                                <div class="text-center pt-3 mt-3 border-top">
                                    <a href="#" class="btn btn-primary btn-lg btn-block">
                                        <i class="fas fa-list me-2"></i>
                                        View All Submissions
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Announcements Table -->

            </div>
        </section>
    </div>

    <!-- Preview Modal -->
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

    <!-- Create Submission Modal -->
    <div class="modal fade" id="createSubmissionModal" tabindex="-1" aria-labelledby="createSubmissionLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('Submissions.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createSubmissionLabel">
                            <i class="fas fa-plus-circle me-2"></i>
                            Create New Submission
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Type Selection -->
                        <div class="mb-4">
                            <label class="form-label" for="type">
                                <i class="fas fa-clipboard-list me-1"></i> Type
                            </label>
                            <select name="type" id="type"
                                class="form-control select2 @error('type') is-invalid @enderror" required>
                                <option value="">Choose submission type</option>
                                @foreach ($types as $value)
                                    @if ($value === 'Overtime' && !$canCreateOvertime)
                                        @continue
                                    @endif
                                    <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Overtime Type -->
                        <div class="mb-4" id="statusDiv" style="display: none;">
                            <label class="form-label" for="status_submissions">
                                <i class="fas fa-clock me-1"></i> Overtime Type
                            </label>
                            <select name="status_submissions" id="status_submissions"
                                class="form-control select2 @error('status_submissions') is-invalid @enderror">
                                <option value="">Choose overtime type</option>
                                @foreach ($statussubmissions as $value)
                                    <option value="{{ $value }}"
                                        {{ old('status_submissions') == $value ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Annual Leave Info -->
                        <div class="mb-4" id="annualLeaveInfo" style="display: none;">
                            <label class="form-label">
                                <i class="fas fa-info-circle me-1"></i> Annual Leave Balance
                            </label>
                            <div class="info-box">
                                <p><strong>Total Leave:</strong> <span id="total">{{ $employee->total ?? 0 }}</span>
                                    days</p>
                                <p><strong>Pending Leave:</strong> <span
                                        id="pending">{{ $employee->pending ?? 0 }}</span> days</p>
                                <p class="mb-0"><strong>Remaining Leave:</strong> <span
                                        id="remaining">{{ $employee->remaining ?? 0 }}</span> days</p>
                            </div>
                        </div>

                        <!-- Employee Selection -->
                        @if ($canCreateOvertime)
                            <div class="mb-4" id="employeeList" style="display: none;">
                                <label class="form-label">
                                    <i class="fas fa-users me-1"></i> Select Employee(s)
                                </label>
                                <div class="employee-checkbox-container">
                                    @foreach ($managedEmployees as $emp)
                                        <div class="form-check">
                                            <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}"
                                                class="form-check-input" id="emp_{{ $emp->id }}">
                                            <label for="emp_{{ $emp->id }}" class="form-check-label">
                                                {{ $emp->employee_name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    <i class="fas fa-info-circle me-1"></i>
                                    You can select yourself or employees in your department
                                </small>
                            </div>
                        @endif

                        <!-- Date Range -->
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="leave_date_from">
                                    <i class="fas fa-calendar-alt me-1"></i> Start Date
                                </label>
                                <input type="date" name="leave_date_from" id="leave_date_from" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="leave_date_to">
                                    <i class="fas fa-calendar-check me-1"></i> End Date
                                </label>
                                <input type="date" name="leave_date_to" id="leave_date_to" class="form-control"
                                    required>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label class="form-label" for="notes">
                                <i class="fas fa-sticky-note me-1"></i> Notes
                            </label>
                            <textarea name="notes" id="notes" class="form-control" rows="3"
                                placeholder="Enter additional notes or reasons..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('library/chart.js/dist/Chart.min.js') }}"></script>
    <script src="{{ asset('library/jqvmap/dist/jquery.vmap.min.js') }}"></script>
    <script src="{{ asset('library/jqvmap/dist/maps/jquery.vmap.world.js') }}"></script>
    <script src="{{ asset('library/summernote/dist/summernote-bs4.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            function toggleFields(type) {
                if (type === 'Overtime') {
                    $('#leave_date_from').attr('type', 'datetime-local');
                    $('#leave_date_to').attr('type', 'datetime-local');
                    $('#statusDiv').show();
                    $('#annualLeaveInfo').hide();
                    @if ($canCreateOvertime)
                        $('#employeeList').show();
                    @endif
                } else if (type === 'Annual Leave') {
                    $('#leave_date_from').attr('type', 'date');
                    $('#leave_date_to').attr('type', 'date');
                    $('#statusDiv').hide();
                    $('#annualLeaveInfo').show();
                    @if ($canCreateOvertime)
                        $('#employeeList').hide();
                    @endif
                } else {
                    $('#leave_date_from').attr('type', 'date');
                    $('#leave_date_to').attr('type', 'date');
                    $('#statusDiv').hide();
                    $('#annualLeaveInfo').hide();
                    @if ($canCreateOvertime)
                        $('#employeeList').hide();
                    @endif
                }
            }
            toggleFields($('#type').val());
            $('#type').on('change', function() {
                const type = $(this).val();
                toggleFields(type);
            });
        });
    </script>
    <script>
        let ctx = document.getElementById('attendanceChart').getContext('2d');
        let attendanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Attendance Percentage (%)',
                    data: [],
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Persentase Kehadiran'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Tanggal Scan'
                        }
                    }
                }
            }
        });

        function loadChartData(startDate, endDate) {
            fetch(`{{ route('dashboardHR.data') }}?start_date=${startDate}&end_date=${endDate}`)
                .then(res => res.json())
                .then(data => {
                    const labels = data.data.map(item => item.date);
                    const percentages = data.data.map(item => item.percentage);
                    attendanceChart.data.labels = labels;
                    attendanceChart.data.datasets[0].data = percentages;
                    attendanceChart.update();
                });
        }
        document.addEventListener("DOMContentLoaded", function() {
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;
            loadChartData(start, end);
        });
        document.getElementById('filterButton').addEventListener('click', function() {
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;
            loadChartData(start, end);
        });
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
    <script>
        jQuery(document).ready(function($) {
            var table = $('#users-table').DataTable({
                processing: true,
                autoWidth: false,
                serverSide: true,
                ajax: {
                    url: '{{ route('announcements.announcements') }}',
                    type: 'GET'
                },
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                },
                columns: [{
                        data: 'title',
                        name: 'title',
                        className: 'text-center'
                    },
                    {
                        data: 'publish_date',
                        name: 'publish_date',
                        className: 'text-center',
                        render: function(data) {
                            if (!data) return '-';
                            let date = new Date(data);
                            let day = String(date.getDate()).padStart(2, '0');
                            let monthNames = [
                                "January", "February", "March", "April", "May", "June",
                                "July", "August", "September", "October", "November", "December"
                            ];
                            let month = monthNames[date.getMonth()];
                            let year = date.getFullYear();
                            return `${day} ${month} ${year}`;
                        }
                    },
                    {
                        data: 'end_date',
                        name: 'end_date',
                        className: 'text-center',
                        render: function(data) {
                            if (!data) return 'Continuesly';
                            let date = new Date(data);
                            let day = String(date.getDate()).padStart(2, '0');
                            let monthNames = [
                                "January", "February", "March", "April", "May", "June",
                                "July", "August", "September", "October", "November", "December"
                            ];
                            let month = monthNames[date.getMonth()];
                            let year = date.getFullYear();
                            return `${day} ${month} ${year}`;
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
            });
        });
        $(document).on('click', '.preview-btn', function() {
            let title = $(this).data('title');
            let content = $(this).data('content');
            let date = $(this).data('date');
            let enddate = $(this).data('enddate');
            let employee = $(this).data('employee');
            $('#previewTitle').text(title);
            $('#previewEmployee').text(employee);
            $('#previewDate').text(date);
            $('#previewEndDate').text(enddate);
            $('#previewContent').html(content);
            $('#previewModal').modal('show');
        });
    </script>
    <script>
        document.getElementById('btn-announcement').addEventListener('click', function() {
            Swal.fire({
                title: 'Make an Announcement',
                html: `
                <form id="announcementForm" action="{{ route('dashboardHR.store') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3 text-start">
                        <label for="title">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="form-group mb-3 text-start">
                        <label for="content">Announcement Contents</label>
                        <textarea id="editor" name="content" class="form-control"></textarea>
                    </div>

                    <div class="form-group mb-3 text-start">
                        <label for="publish_date">Publish Date</label>
                        <input type="date" name="publish_date" class="form-control" required>
                    </div>

                    <div class="form-group mb-3 text-start">
                        <label for="end_date">End Date</label>
                        <input type="date" name="end_date" class="form-control">
                    </div>
                </form>
            `,
                showCancelButton: true,
                confirmButtonText: 'Save',
                cancelButtonText: 'Cancel',
                focusConfirm: false,

                didOpen: () => {
                    if (tinymce.get('editor')) {
                        tinymce.get('editor').remove();
                    }
                    tinymce.init({
                        selector: '#editor',
                        plugins: 'lists link image table code',
                        toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
                        menubar: false,
                        height: 300,
                        license_key: 'gpl'
                    });
                },
                willClose: () => {
                    if (tinymce.get('editor')) {
                        tinymce.get('editor').remove();
                    }
                },
                preConfirm: () => {
                    tinymce.triggerSave();
                    let title = document.querySelector('input[name="title"]').value.trim();
                    let content = document.querySelector('textarea[name="content"]').value.trim();
                    let publish_date = document.querySelector('input[name="publish_date"]').value;

                    if (!title) {
                        Swal.showValidationMessage('Title is required');
                        return false;
                    }
                    if (!content) {
                        Swal.showValidationMessage('Announcement content is required');
                        return false;
                    }
                    if (!publish_date) {
                        Swal.showValidationMessage('Publish date is required');
                        return false;
                    }

                    document.getElementById('announcementForm').submit();
                }
            });
        });
    </script>
    <script>
        $(document).on('hidden.bs.modal', function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        });
    </script>
    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#type').select2({
                    theme: 'bootstrap4',
                    placeholder: '-- Select Type --',
                    width: '100%'
                });
                $('#createSubmissionModal').on('shown.bs.modal', function() {
                    $('#type').select2({
                        dropdownParent: $('#createSubmissionModal')
                    });
                });
            });
        </script>
    @endpush
@endpush --}}
@extends('layouts.app')
@section('title', 'HR Dashboard')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* =====================================================
           ROOT & TOKENS
        ===================================================== */
        :root {
            --brand-900: #0f172a;
            --brand-800: #1e293b;
            --brand-700: #25316D;
            --brand-600: #3E497A;
            --brand-400: #6c7fc4;
            --brand-100: #e8ecf8;
            --brand-50:  #f1f4fb;

            --emerald-600: #059669;
            --emerald-500: #10b981;
            --amber-500:   #f59e0b;
            --amber-600:   #d97706;
            --rose-500:    #f43f5e;
            --rose-600:    #e11d48;
            --sky-500:     #0ea5e9;
            --violet-500:  #8b5cf6;

            --primary-gradient: linear-gradient(135deg, #25316D 0%, #3E497A 100%);
            --success-gradient: linear-gradient(135deg, #059669 0%, #047857 100%);
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --danger-gradient:  linear-gradient(135deg, #f43f5e 0%, #be123c 100%);
            --info-gradient:    linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            --purple-gradient:  linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);

            --surface:     #ffffff;
            /* --surface-2:   #f8fafc; */
            --border:      #e2e8f0;
            --text-primary:#0f172a;
            --text-muted:  #64748b;

            --radius-sm:   8px;
            --radius-md:   12px;
            --radius-lg:   16px;
            --radius-xl:   20px;

            --shadow-sm:   0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
            --shadow-md:   0 4px 16px rgba(0,0,0,.08);
            --shadow-lg:   0 12px 32px rgba(0,0,0,.12);
            --shadow-xl:   0 24px 48px rgba(0,0,0,.16);

            --transition:  all .25s cubic-bezier(.4,0,.2,1);
        }

        /* =====================================================
           BASE
        ===================================================== */
        body, .main-content {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--surface-2);
        }

        img.no-drag { -webkit-user-drag: none; user-select: none; }

        /* =====================================================
           PROFILE HEADER
        ===================================================== */
        .profile-header-card {
            background: var(--primary-gradient);
            border-radius: var(--radius-xl);
            padding: 36px 40px;
            color: white;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .profile-header-card::before {
            content: '';
            position: absolute;
            top: -60%;
            right: -8%;
            width: 380px;
            height: 380px;
            background: rgba(255,255,255,.08);
            border-radius: 50%;
            pointer-events: none;
        }

        .profile-header-card::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: -4%;
            width: 280px;
            height: 280px;
            background: rgba(255,255,255,.05);
            border-radius: 50%;
            pointer-events: none;
        }

        .profile-content { position: relative; z-index: 1; }

        .profile-avatar-large {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,.35);
            object-fit: cover;
            box-shadow: 0 8px 20px rgba(0,0,0,.25);
        }

        .profile-info h2 {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 6px;
            letter-spacing: -.5px;
        }

        .profile-meta {
            display: flex;
            gap: 20px;
            margin-top: 14px;
            flex-wrap: wrap;
        }

        .profile-meta-item {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: .875rem;
            opacity: .9;
            background: rgba(255,255,255,.12);
            padding: 5px 12px;
            border-radius: 20px;
        }

        /* =====================================================
           QUICK ACTIONS
        ===================================================== */
        .quick-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 28px;
        }

        .qa-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: var(--radius-md);
            border: 1.5px solid var(--border);
            background: var(--surface);
            color: var(--text-primary);
            font-weight: 600;
            font-size: .82rem;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        .qa-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: var(--brand-700);
            border-color: var(--brand-400);
            text-decoration: none;
        }

        .qa-btn i {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            color: white;
        }

        .qa-btn .icon-primary  { background: var(--primary-gradient); }
        .qa-btn .icon-success  { background: var(--success-gradient); }
        .qa-btn .icon-warning  { background: var(--warning-gradient); }
        .qa-btn .icon-danger   { background: var(--danger-gradient); }
        .qa-btn .icon-info     { background: var(--info-gradient); }
        .qa-btn .icon-purple   { background: var(--purple-gradient); }

        /* =====================================================
           STAT CARDS
        ===================================================== */
        .stat-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            transition: var(--transition);
            height: 100%;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .stat-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            color: white;
        }

        .stat-icon.primary { background: var(--primary-gradient); }
        .stat-icon.success { background: var(--success-gradient); }
        .stat-icon.warning { background: var(--warning-gradient); }
        .stat-icon.info    { background: var(--info-gradient); }
        .stat-icon.danger  { background: var(--danger-gradient); }
        .stat-icon.purple  { background: var(--purple-gradient); }

        .stat-badge {
            font-size: .72rem;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 20px;
            letter-spacing: .3px;
        }
        .stat-badge.up   { background: rgba(16,185,129,.12); color: var(--emerald-600); }
        .stat-badge.down { background: rgba(244,63,94,.12);  color: var(--rose-600); }
        .stat-badge.neutral { background: rgba(100,116,139,.1); color: var(--text-muted); }

        .stat-content h3 {
            font-size: 2.1rem;
            font-weight: 800;
            margin: 0 0 2px 0;
            color: var(--text-primary);
            letter-spacing: -1px;
        }

        .stat-content p {
            margin: 0;
            color: var(--text-muted);
            font-size: .82rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .stat-footer {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* =====================================================
           ALERT BANNER (contract expiry, birthdays)
        ===================================================== */
        .alert-banner {
            border-radius: var(--radius-md);
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 10px;
            font-size: .875rem;
            font-weight: 500;
            border: none;
        }

        .alert-banner.danger  { background: rgba(244,63,94,.08); color: var(--rose-600); border-left: 4px solid var(--rose-500); }
        .alert-banner.warning { background: rgba(245,158,11,.08); color: var(--amber-600); border-left: 4px solid var(--amber-500); }
        .alert-banner.info    { background: rgba(14,165,233,.08); color: var(--sky-500); border-left: 4px solid var(--sky-500); }
        .alert-banner.success { background: rgba(16,185,129,.08); color: var(--emerald-600); border-left: 4px solid var(--emerald-500); }

        .alert-banner .alert-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .alert-banner.danger  .alert-icon  { background: rgba(244,63,94,.15); }
        .alert-banner.warning .alert-icon  { background: rgba(245,158,11,.15); }
        .alert-banner.info    .alert-icon  { background: rgba(14,165,233,.15); }
        .alert-banner.success .alert-icon  { background: rgba(16,185,129,.15); }

        /* =====================================================
           CARD GENERIC
        ===================================================== */
        .dash-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .dash-card .dash-card-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--surface);
        }

        .dash-card .dash-card-title {
            font-size: .95rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        .dash-card .dash-card-title .title-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            color: white;
        }

        .dash-card .dash-card-body {
            padding: 20px 22px;
        }

        /* =====================================================
           SUBMISSION LIST
        ===================================================== */
        .submission-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 13px 0;
            border-bottom: 1px solid var(--border);
            transition: var(--transition);
        }

        .submission-item:last-child { border-bottom: none; }

        .submission-item:hover { background: var(--surface-2); border-radius: var(--radius-sm); padding-left: 8px; }

        .submission-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border);
            flex-shrink: 0;
        }

        .submission-info { flex: 1; min-width: 0; }
        .submission-name { font-weight: 700; font-size: .875rem; color: var(--text-primary); }
        .submission-meta { font-size: .78rem; color: var(--text-muted); margin-top: 2px; }

        .type-badge {
            font-size: .72rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            white-space: nowrap;
        }

        .type-badge.leave   { background: rgba(139,92,246,.12); color: var(--violet-500); }
        .type-badge.overtime{ background: rgba(245,158,11,.12);  color: var(--amber-600); }
        .type-badge.sick    { background: rgba(244,63,94,.12);   color: var(--rose-600); }
        .type-badge.other   { background: rgba(14,165,233,.12);  color: var(--sky-500); }

        /* =====================================================
           BIRTHDAY LIST
        ===================================================== */
        .birthday-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 0;
            border-bottom: 1px solid var(--border);
        }

        .birthday-item:last-child { border-bottom: none; }

        .birthday-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border);
        }

        .birthday-info { flex: 1; }
        .birthday-name { font-size: .85rem; font-weight: 700; color: var(--text-primary); }
        .birthday-date { font-size: .77rem; color: var(--text-muted); }

        .birthday-today {
            font-size: .72rem;
            font-weight: 700;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
        }

        /* =====================================================
           CONTRACT EXPIRY
        ===================================================== */
        .contract-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 0;
            border-bottom: 1px solid var(--border);
        }

        .contract-item:last-child { border-bottom: none; }

        .contract-days {
            width: 46px;
            height: 46px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        .contract-days.urgent  { background: rgba(244,63,94,.12);  color: var(--rose-600); }
        .contract-days.warning { background: rgba(245,158,11,.12); color: var(--amber-600); }
        .contract-days.ok      { background: rgba(16,185,129,.12); color: var(--emerald-600); }

        /* =====================================================
           PAYROLL CARD
        ===================================================== */
        .payroll-status-card {
            background: var(--primary-gradient);
            border-radius: var(--radius-lg);
            padding: 24px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .payroll-status-card::after {
            content: '';
            position: absolute;
            top: -40%;
            right: -15%;
            width: 220px;
            height: 220px;
            background: rgba(255,255,255,.08);
            border-radius: 50%;
        }

        .payroll-status-card .payroll-label {
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            opacity: .8;
            margin-bottom: 6px;
        }

        .payroll-status-card .payroll-amount {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .payroll-progress {
            margin-top: 16px;
            background: rgba(255,255,255,.2);
            border-radius: 8px;
            height: 8px;
            overflow: hidden;
        }

        .payroll-progress-bar {
            height: 100%;
            background: rgba(255,255,255,.8);
            border-radius: 8px;
            transition: width .8s ease;
        }

        .payroll-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: .78rem;
            opacity: .85;
        }

        /* =====================================================
           HR CALENDAR
        ===================================================== */
        .calendar-event {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }

        .calendar-event:last-child { border-bottom: none; }

        .cal-date {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-sm);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .cal-date .cal-day  { font-size: 1.1rem; font-weight: 800; line-height: 1; }
        .cal-date .cal-mon  { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }

        .cal-date.primary { background: var(--brand-50); color: var(--brand-700); }
        .cal-date.success { background: rgba(16,185,129,.1); color: var(--emerald-600); }
        .cal-date.warning { background: rgba(245,158,11,.1); color: var(--amber-600); }

        .cal-info { flex: 1; }
        .cal-title { font-size: .855rem; font-weight: 700; color: var(--text-primary); }
        .cal-sub   { font-size: .77rem; color: var(--text-muted); }

        /* =====================================================
           LEAVE SUMMARY BOX (inside submissions)
        ===================================================== */
        .leave-summary-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: var(--radius-md);
            padding: 18px;
            color: white;
            margin-bottom: 18px;
        }

        .leave-summary-box h6 {
            color: white;
            font-weight: 700;
            margin-bottom: 14px;
            font-size: .875rem;
        }

        .leave-stat { text-align: center; }
        .leave-stat small { display: block; opacity: .85; font-size: .72rem; margin-bottom: 3px; }
        .leave-stat .leave-num { font-size: 1.6rem; font-weight: 800; }

        /* =====================================================
           DATE FILTER
        ===================================================== */
        .date-input {
            width: 140px !important;
            padding: 7px 10px;
            font-size: .85rem;
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: var(--transition);
        }

        .date-input:focus {
            outline: none;
            border-color: var(--brand-400);
            box-shadow: 0 0 0 3px rgba(108,127,196,.15);
        }

        /* =====================================================
           CHART INFO BOX
        ===================================================== */
        .chart-info-box {
            background: var(--brand-50);
            border-left: 4px solid var(--brand-400);
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
            padding: 12px 16px;
            margin-top: 14px;
            font-size: .82rem;
            color: var(--brand-700);
        }

        /* =====================================================
           ANNOUNCEMENTS TABLE
        ===================================================== */
        #users-table thead th {
            background: var(--surface-2);
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--text-muted);
            border-bottom: 2px solid var(--border);
        }

        #users-table tbody tr:hover { background: var(--brand-50); }

        /* =====================================================
           BUTTONS
        ===================================================== */
        .btn {
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: var(--transition);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37,49,109,.35);
            color: white;
        }

        .btn-sm {
            padding: 6px 16px;
            font-size: .82rem;
        }

        .btn-outline-primary {
            border: 1.5px solid var(--brand-700);
            color: var(--brand-700);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--brand-700);
            color: white;
        }

        /* =====================================================
           MODAL
        ===================================================== */
        .modal-content { border-radius: var(--radius-xl); border: none; box-shadow: var(--shadow-xl); }

        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
            padding: 22px 26px;
        }

        .modal-header .modal-title { font-weight: 700; color: white; }
        .modal-header .close { color: white; opacity: .9; font-size: 1.25rem; }
        .modal-body { padding: 26px; }
        .modal-footer { padding: 16px 26px; border-top: 1px solid var(--border); }

        .form-label { font-weight: 700; color: var(--brand-800); font-size: .85rem; margin-bottom: 7px; }

        .form-control {
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            padding: 10px 14px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: .875rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--brand-400);
            box-shadow: 0 0 0 3px rgba(108,127,196,.15);
            outline: none;
        }

        /* =====================================================
           INFO BOX (Annual Leave in modal)
        ===================================================== */
        .info-box {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: var(--radius-md);
            padding: 18px;
            color: white;
        }

        .info-box p { margin-bottom: 7px; font-size: .875rem; }
        .info-box strong { font-weight: 700; }

        /* =====================================================
           EMPLOYEE CHECKBOX
        ===================================================== */
        .employee-checkbox-container {
            max-height: 200px;
            overflow-y: auto;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 10px;
            background: var(--surface-2);
        }

        .form-check { padding: 8px 12px; border-radius: 6px; transition: var(--transition); }
        .form-check:hover { background: white; }
        .form-check-input:checked { background-color: var(--brand-700); border-color: var(--brand-700); }

        /* =====================================================
           SECTION LABEL
        ===================================================== */
        .section-label {
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: var(--text-muted);
            margin-bottom: 14px;
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */
        @media (max-width: 768px) {
            .profile-header-card { padding: 24px 20px; }
            .profile-info h2 { font-size: 1.35rem; }
            .profile-meta { gap: 10px; }
            .quick-actions { gap: 8px; }
            .qa-btn { font-size: .78rem; padding: 8px 14px; }
            .stat-content h3 { font-size: 1.6rem; }
            .date-input { width: 100% !important; }
        }

        /* =====================================================
           ANIMATE
        ===================================================== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .animate-in {
            animation: fadeInUp .45s ease forwards;
        }

        .animate-in:nth-child(1) { animation-delay: .05s; }
        .animate-in:nth-child(2) { animation-delay: .10s; }
        .animate-in:nth-child(3) { animation-delay: .15s; }
        .animate-in:nth-child(4) { animation-delay: .20s; }

        /* =====================================================
           DONUT LEGEND
        ===================================================== */
        .donut-legend { list-style: none; padding: 0; margin: 0; }
        .donut-legend li {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .82rem;
            color: var(--text-muted);
            padding: 5px 0;
            font-weight: 500;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .legend-val { margin-left: auto; font-weight: 700; color: var(--text-primary); }

        /* =====================================================
           LATE/ABSENT TRACKER
        ===================================================== */
        .tracker-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }

        .tracker-row:last-child { border-bottom: none; }

        .tracker-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border);
        }

        .tracker-info { flex: 1; }
        .tracker-name { font-size: .85rem; font-weight: 700; color: var(--text-primary); }
        .tracker-time { font-size: .75rem; color: var(--text-muted); }

        .tracker-tag {
            font-size: .72rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .tracker-tag.late   { background: rgba(245,158,11,.12); color: var(--amber-600); }
        .tracker-tag.absent { background: rgba(244,63,94,.12);  color: var(--rose-600); }
        .tracker-tag.permit { background: rgba(14,165,233,.12); color: var(--sky-500); }

        /* Scrollable inner list */
        .inner-scroll { max-height: 290px; overflow-y: auto; }
        .inner-scroll::-webkit-scrollbar { width: 4px; }
        .inner-scroll::-webkit-scrollbar-track { background: transparent; }
        .inner-scroll::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
    </style>
@endpush

@section('main')
<div class="main-content">
<section class="section">

    {{-- =========================================================
         PROFILE HEADER
    ========================================================= --}}
    <div class="profile-header-card animate-in">
        <div class="profile-content">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-4">
                        <img src="{{ Auth::user()->employee->photos
                            ? asset('storage/' . Auth::user()->employee->photos)
                            : asset('img/avatar/avatar-1.png') }}"
                            alt="Profile" class="profile-avatar-large no-drag">

                        <div class="profile-info">
                            <h2>{{ Auth::user()->employee->employee_name ?? 'HR User' }}</h2>
                            <div style="font-size:.85rem;opacity:.85;font-weight:500;">
                                {{-- Human Resources Department --}}
                            </div>
                            <div class="profile-meta">
                                <div class="profile-meta-item">
                                    <i class="fas fa-briefcase"></i>
                                    <span>{{ Auth::user()->employee->position->name ?? '-' }}</span>
                                </div>
                                <div class="profile-meta-item">
                                    <i class="fas fa-building"></i>
                                    <span>{{ Auth::user()->employee->department->department_name ?? '-' }}</span>
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

                <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                    <div style="background:rgba(255,255,255,.12);border-radius:12px;padding:16px 20px;display:inline-block;text-align:center;">
                        <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;opacity:.8;margin-bottom:6px;">
                            Today's Attendance Rate
                        </div>
                        <div style="font-size:2.2rem;font-weight:800;letter-spacing:-1px;">
                            {{ $attendanceRateToday ?? 0 }}%
                        </div>
                        <div style="font-size:.78rem;opacity:.8;margin-top:4px;">
                            {{ $presentToday ?? 0 }} / {{ $totalEmployees ?? 0 }} Present
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================
         QUICK ACTIONS BAR
    ========================================================= --}}
    <div class="quick-actions animate-in">
        <a href="{{ route('Employee.create') }}" class="qa-btn">
            <i class="fas fa-user-plus icon-primary"></i> Add Employee
        </a>
        <a href="{{ route('pages.Fingerprints') }}" class="qa-btn">
            <i class="fas fa-fingerprint icon-success"></i> Attendance Log
        </a>
        <a href="#" class="qa-btn" data-toggle="modal" data-target="#createSubmissionModal">
            <i class="fas fa-file-medical icon-warning"></i> New Submission
        </a>
        <a href="#" class="qa-btn" id="btn-announcement-quick">
            <i class="fas fa-bullhorn icon-info"></i> Announcement
        </a>
        <a href="#" class="qa-btn">
            <i class="fas fa-money-bill-wave icon-purple"></i> Payroll
        </a>
        <a href="#" class="qa-btn">
            <i class="fas fa-file-contract icon-danger"></i> Contracts
        </a>
    </div>

    <div class="section-body">

        {{-- =====================================================
             ALERT BANNERS (Contract Expiry + Birthdays Today)
        ===================================================== --}}
        @if(isset($contractsExpiringCount) && $contractsExpiringCount > 0)
        <div class="alert-banner danger animate-in" role="alert">
            <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <strong>{{ $contractsExpiringCount }} employee contract(s)</strong> expiring within 30 days.
                <a href="#" class="ml-2" style="font-weight:700;color:inherit;text-decoration:underline;">Review now →</a>
            </div>
        </div>
        @endif

        @if(isset($birthdaysToday) && count($birthdaysToday) > 0)
        <div class="alert-banner success animate-in" role="alert">
            <div class="alert-icon"><i class="fas fa-birthday-cake"></i></div>
            <div>
                🎉 Today is <strong>{{ collect($birthdaysToday)->pluck('employee_name')->join(', ') }}'s</strong> birthday! Don't forget to wish them.
            </div>
        </div>
        @endif

        @if(isset($pendingApprovals) && $pendingApprovals > 0)
        <div class="alert-banner warning animate-in" role="alert">
            <div class="alert-icon"><i class="fas fa-clock"></i></div>
            <div>
                <strong>{{ $pendingApprovals }} submission(s)</strong> waiting for your approval.
                <a href="#" class="ml-2" style="font-weight:700;color:inherit;text-decoration:underline;">Approve now →</a>
            </div>
        </div>
        @endif

        {{-- =====================================================
             TOP STAT CARDS
        ===================================================== --}}
        <div class="row mb-2">
            {{-- Total Employees --}}
            <div class="col-lg-3 col-md-6 col-12 mb-4 animate-in">
                <div onclick="window.location='{{ route('pages.Employee') }}';" class="stat-card" role="button" aria-label="View all employees">
                    <div class="stat-card-header">
                        <div class="stat-icon primary"><i class="fas fa-users"></i></div>
                        <span class="stat-badge up"><i class="fas fa-arrow-up mr-1"></i> Active</span>
                    </div>
                    <div class="stat-content">
                        <h3>{{ $totalEmployees ?? 0 }}</h3>
                        <p>Total Employees</p>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge up"><i class="fas fa-hourglass-half mr-1"></i> {{ $totalEmployeespending ?? 0 }} Pending</span>
                        <span class="stat-badge down"><i class="fas fa-door-open mr-1"></i> {{ $totalEmployeesinactive ?? 0 }} Resigned</span>
                    </div>
                </div>
            </div>

            {{-- Present Today --}}
            <div class="col-lg-3 col-md-6 col-12 mb-4 animate-in">
                <div onclick="window.location='{{ route('pages.Fingerprints') }}';" class="stat-card" role="button" aria-label="View attendance">
                    <div class="stat-card-header">
                        <div class="stat-icon success"><i class="fas fa-user-check"></i></div>
                        @if(($trend ?? 0) >= 0)
                            <span class="stat-badge up"><i class="fas fa-arrow-up mr-1"></i> +{{ $trend }}</span>
                        @else
                            <span class="stat-badge down"><i class="fas fa-arrow-down mr-1"></i> {{ $trend }}</span>
                        @endif
                    </div>
                    <div class="stat-content">
                        <h3>{{ $presentToday ?? 0 }}</h3>
                        <p>Present Today</p>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge neutral">Yesterday: {{ $presentYesterday ?? 0 }}</span>
                        <span class="stat-badge down"><i class="fas fa-user-times mr-1"></i> {{ $absentToday ?? 0 }} Absent</span>
                    </div>
                </div>
            </div>

            {{-- Late Today --}}
            <div class="col-lg-3 col-md-6 col-12 mb-4 animate-in">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
                        @if(($lateToday ?? 0) > 0)
                            <span class="stat-badge down"><i class="fas fa-exclamation-circle mr-1"></i> Alert</span>
                        @else
                            <span class="stat-badge up"><i class="fas fa-check-circle mr-1"></i> On Time</span>
                        @endif
                    </div>
                    <div class="stat-content">
                        <h3>{{ $lateToday ?? 0 }}</h3>
                        <p>Late Today</p>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge neutral">Pending Approvals: {{ $pendingApprovals ?? 0 }}</span>
                    </div>
                </div>
            </div>

            {{-- On Leave --}}
            <div class="col-lg-3 col-md-6 col-12 mb-4 animate-in">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon info"><i class="fas fa-calendar-check"></i></div>
                        <span class="stat-badge neutral">This Week</span>
                    </div>
                    <div class="stat-content">
                        <h3>{{ $onLeave ?? 0 }}</h3>
                        <p>On Leave</p>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge warning"><i class="fas fa-file-medical mr-1"></i> {{ $onSickLeave ?? 0 }} Sick</span>
                        <span class="stat-badge up"><i class="fas fa-umbrella-beach mr-1"></i> {{ $onAnnualLeave ?? 0 }} Annual</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- =====================================================
             ROW 2 — Attendance Chart + Submissions
        ===================================================== --}}
        <div class="row mb-4">
            {{-- Attendance Chart --}}
            <div class="col-lg-8 col-12 mb-4">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h4 class="dash-card-title">
                            <span class="title-icon" style="background:var(--primary-gradient)"><i class="fas fa-chart-bar"></i></span>
                            Monthly Attendance Rate
                        </h4>
                        <div class="d-flex align-items-center gap-2">
                            <input type="date" id="startDate" class="date-input" aria-label="Start Date"
                                value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                            <input type="date" id="endDate" class="date-input" aria-label="End Date"
                                value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                            <button id="filterButton" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                        </div>
                    </div>
                    <div class="dash-card-body">
                        <canvas id="attendanceChart" height="190"></canvas>
                        <div class="chart-info-box">
                            <i class="fas fa-info-circle mr-2"></i>
                            X-axis: Date &nbsp;|&nbsp; Y-axis: Attendance percentage of total active employees
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pending Submissions --}}
            <div class="col-lg-4 col-12 mb-4">
                <div class="dash-card h-100 d-flex flex-column">
                    <div class="dash-card-header">
                        <h4 class="dash-card-title">
                            <span class="title-icon" style="background:var(--purple-gradient)"><i class="fas fa-file-alt"></i></span>
                            Pending Submissions
                        </h4>
                        <span class="stat-badge down">{{ count($pendingSubmissions) }} new</span>
                    </div>
                    <div class="dash-card-body flex-grow-1 d-flex flex-column">
                        @if($selectedType === 'Annual Leave' && isset($leaveData))
                        <div class="leave-summary-box">
                            <h6><i class="fas fa-umbrella-beach mr-2"></i>Annual Leave Summary</h6>
                            <div class="d-flex justify-content-around">
                                <div class="leave-stat">
                                    <small>Total</small>
                                    <div class="leave-num">{{ $leaveData['total'] }}</div>
                                </div>
                                <div class="leave-stat">
                                    <small>Pending</small>
                                    <div class="leave-num">{{ $leaveData['pending'] }}</div>
                                </div>
                                <div class="leave-stat">
                                    <small>Remaining</small>
                                    <div class="leave-num">{{ $leaveData['remaining'] }}</div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="inner-scroll flex-grow-1">
                            @forelse($pendingSubmissions as $submission)
                            <div class="submission-item">
                                <img class="submission-avatar no-drag"
                                    src="{{ asset('img/avatar/avatar-' . rand(1,5) . '.png') }}"
                                    alt="{{ $submission->employee->employee_name }}">
                                <div class="submission-info">
                                    <div class="submission-name">{{ $submission->employee->employee_name }}</div>
                                    <div class="submission-meta">
                                        <i class="fas fa-clock mr-1"></i>{{ $submission->created_at->diffForHumans() }}
                                        &nbsp;·&nbsp; {{ $submission->formattedDuration }}
                                    </div>
                                </div>
                                @php
                                    $typeClass = match(strtolower($submission->type)) {
                                        'annual leave' => 'leave',
                                        'overtime'     => 'overtime',
                                        'sick leave'   => 'sick',
                                        default        => 'other',
                                    };
                                @endphp
                                <span class="type-badge {{ $typeClass }}">{{ ucfirst($submission->type) }}</span>
                            </div>
                            @empty
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0" style="font-size:.875rem;">No pending submissions</p>
                            </div>
                            @endforelse
                        </div>

                        <div class="mt-3 pt-3" style="border-top:1px solid var(--border)">
                            <a href="#" class="btn btn-primary btn-block">
                                <i class="fas fa-list mr-2"></i>View All Submissions
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- =====================================================
             ROW 3 — Dept Chart + Late Tracker + Birthdays
        ===================================================== --}}
        <div class="row mb-4">
            {{-- Department Distribution --}}
            <div class="col-lg-4 col-md-6 col-12 mb-4">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <h4 class="dash-card-title">
                            <span class="title-icon" style="background:var(--info-gradient)"><i class="fas fa-sitemap"></i></span>
                            Employee by Department
                        </h4>
                    </div>
                    <div class="dash-card-body">
                        <canvas id="deptChart" height="200"></canvas>
                        <ul class="donut-legend mt-3" id="deptLegend">
                            {{-- Populated by JS --}}
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Late / Absent Today --}}
            <div class="col-lg-4 col-md-6 col-12 mb-4">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <h4 class="dash-card-title">
                            <span class="title-icon" style="background:var(--warning-gradient)"><i class="fas fa-user-clock"></i></span>
                            Late & Absent Today
                        </h4>
                        <span class="stat-badge down">{{ ($lateToday ?? 0) + ($absentToday ?? 0) }}</span>
                    </div>
                    <div class="dash-card-body">
                        <div class="inner-scroll">
                            @forelse($lateAbsentList ?? [] as $emp)
                            <div class="tracker-row">
                                <img class="tracker-avatar no-drag"
                                    src="{{ asset('img/avatar/avatar-' . rand(1,5) . '.png') }}"
                                    alt="{{ $emp->employee_name }}">
                                <div class="tracker-info">
                                    <div class="tracker-name">{{ $emp->employee_name }}</div>
                                    <div class="tracker-time">
                                        {{ $emp->department->department_name ?? '-' }}
                                        @if(isset($emp->check_in))
                                        &nbsp;· Check-in {{ \Carbon\Carbon::parse($emp->check_in)->format('H:i') }}
                                        @endif
                                    </div>
                                </div>
                                @php
                                    $tag = $emp->status ?? 'late';
                                @endphp
                                <span class="tracker-tag {{ $tag }}">{{ ucfirst($tag) }}</span>
                            </div>
                            @empty
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle fa-3x mb-3 d-block" style="color:var(--emerald-500)"></i>
                                <p class="text-muted mb-0" style="font-size:.875rem;">Everyone is on time today! 🎉</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Upcoming Birthdays --}}
            <div class="col-lg-4 col-12 mb-4">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <h4 class="dash-card-title">
                            <span class="title-icon" style="background:linear-gradient(135deg,#f093fb,#f5576c)"><i class="fas fa-birthday-cake"></i></span>
                            Upcoming Birthdays
                        </h4>
                    </div>
                    <div class="dash-card-body">
                        <div class="inner-scroll">
                            @forelse($upcomingBirthdays ?? [] as $emp)
                            <div class="birthday-item">
                                <img class="birthday-avatar no-drag"
                                    src="{{ $emp->photos ? asset('storage/'.$emp->photos) : asset('img/avatar/avatar-1.png') }}"
                                    alt="{{ $emp->employee_name }}">
                                <div class="birthday-info">
                                    <div class="birthday-name">{{ $emp->employee_name }}</div>
                                    <div class="birthday-date">
                                        <i class="fas fa-birthday-cake mr-1" style="color:var(--rose-500)"></i>
                                        {{ \Carbon\Carbon::parse($emp->birth_date)->format('d F') }}
                                        &nbsp;· {{ $emp->department->department_name ?? '-' }}
                                    </div>
                                </div>
                                @if(\Carbon\Carbon::parse($emp->birth_date)->format('m-d') === now()->format('m-d'))
                                    <span class="birthday-today">🎂 Today!</span>
                                @else
                                    <span class="stat-badge neutral" style="font-size:.7rem;white-space:nowrap;">
                                        in {{ \Carbon\Carbon::parse($emp->birth_date)->setYear(now()->year)->diffInDays(now()) }} days
                                    </span>
                                @endif
                            </div>
                            @empty
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0" style="font-size:.875rem;">No upcoming birthdays this month</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- =====================================================
             ROW 4 — Payroll Status + Contract Expiry + HR Calendar
        ===================================================== --}}
        <div class="row mb-4">
            {{-- Payroll Status --}}
            <div class="col-lg-4 col-md-6 col-12 mb-4">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <h4 class="dash-card-title">
                            <span class="title-icon" style="background:var(--success-gradient)"><i class="fas fa-money-bill-wave"></i></span>
                            Payroll Status
                        </h4>
                        <span class="stat-badge neutral">{{ now()->format('M Y') }}</span>
                    </div>
                    <div class="dash-card-body">
                        <div class="payroll-status-card mb-3">
                            <div class="payroll-label">Total Payroll This Month</div>
                            <div class="payroll-amount">
                                Rp {{ number_format($totalPayroll ?? 0, 0, ',', '.') }}
                            </div>
                            <div class="payroll-progress">
                                <div class="payroll-progress-bar" style="width:{{ $payrollProgress ?? 0 }}%"></div>
                            </div>
                            <div class="payroll-meta">
                                <span>{{ $payrollProcessed ?? 0 }} Processed</span>
                                <span>{{ $payrollPending ?? 0 }} Pending</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <div class="flex-fill text-center p-3" style="background:var(--surface-2);border-radius:var(--radius-sm);">
                                <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--text-muted);">Processed</div>
                                <div style="font-size:1.5rem;font-weight:800;color:var(--emerald-600);">{{ $payrollProcessed ?? 0 }}</div>
                            </div>
                            <div class="flex-fill text-center p-3" style="background:var(--surface-2);border-radius:var(--radius-sm);">
                                <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--text-muted);">Pending</div>
                                <div style="font-size:1.5rem;font-weight:800;color:var(--amber-600);">{{ $payrollPending ?? 0 }}</div>
                            </div>
                            <div class="flex-fill text-center p-3" style="background:var(--surface-2);border-radius:var(--radius-sm);">
                                <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--text-muted);">Failed</div>
                                <div style="font-size:1.5rem;font-weight:800;color:var(--rose-600);">{{ $payrollFailed ?? 0 }}</div>
                            </div>
                        </div>

                        <a href="#" class="btn btn-outline-primary btn-block mt-3">
                            <i class="fas fa-arrow-right mr-2"></i>Process Payroll
                        </a>
                    </div>
                </div>
            </div>

            {{-- Contract Expiry --}}
            <div class="col-lg-4 col-md-6 col-12 mb-4">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <h4 class="dash-card-title">
                            <span class="title-icon" style="background:var(--danger-gradient)"><i class="fas fa-file-contract"></i></span>
                            Contract Expiry
                        </h4>
                        @if(isset($contractsExpiringCount) && $contractsExpiringCount > 0)
                        <span class="stat-badge down">{{ $contractsExpiringCount }} expiring</span>
                        @endif
                    </div>
                    <div class="dash-card-body">
                        <div class="inner-scroll">
                            @forelse($expiringContracts ?? [] as $contract)
                            @php
                                $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($contract->contract_end), false);
                                $urgencyClass = $daysLeft <= 7 ? 'urgent' : ($daysLeft <= 30 ? 'warning' : 'ok');
                            @endphp
                            <div class="contract-item">
                                <div class="contract-days {{ $urgencyClass }}">
                                    {{ $daysLeft }}<span style="font-size:.6rem;display:block;font-weight:600;">days</span>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:.855rem;font-weight:700;color:var(--text-primary);">{{ $contract->employee_name }}</div>
                                    <div style="font-size:.77rem;color:var(--text-muted);">
                                        Ends {{ \Carbon\Carbon::parse($contract->contract_end)->format('d M Y') }}
                                        &nbsp;· {{ $contract->contract_type ?? 'PKWT' }}
                                    </div>
                                </div>
                                <a href="#" class="btn btn-sm btn-outline-primary">Renew</a>
                            </div>
                            @empty
                            <div class="text-center py-5">
                                <i class="fas fa-file-contract fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0" style="font-size:.875rem;">No contracts expiring soon</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- HR Calendar / Upcoming Events --}}
            <div class="col-lg-4 col-12 mb-4">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <h4 class="dash-card-title">
                            <span class="title-icon" style="background:var(--primary-gradient)"><i class="fas fa-calendar-alt"></i></span>
                            Upcoming Events
                        </h4>
                        <a href="#" class="btn btn-sm btn-outline-primary">+ Add</a>
                    </div>
                    <div class="dash-card-body">
                        <div class="inner-scroll">
                            @forelse($upcomingEvents ?? [] as $event)
                            @php
                                $eventDate = \Carbon\Carbon::parse($event->event_date);
                                $colorClass = $event->type === 'holiday' ? 'success' : ($event->type === 'deadline' ? 'warning' : 'primary');
                            @endphp
                            <div class="calendar-event">
                                <div class="cal-date {{ $colorClass }}">
                                    <span class="cal-day">{{ $eventDate->format('d') }}</span>
                                    <span class="cal-mon">{{ $eventDate->format('M') }}</span>
                                </div>
                                <div class="cal-info">
                                    <div class="cal-title">{{ $event->title }}</div>
                                    <div class="cal-sub">{{ ucfirst($event->type) }} &nbsp;·&nbsp; {{ $event->description ?? '' }}</div>
                                </div>
                            </div>
                            @empty
                            {{-- Static placeholder events when no DB data --}}
                            <div class="calendar-event">
                                <div class="cal-date primary">
                                    <span class="cal-day">{{ now()->addDays(3)->format('d') }}</span>
                                    <span class="cal-mon">{{ now()->addDays(3)->format('M') }}</span>
                                </div>
                                <div class="cal-info">
                                    <div class="cal-title">Monthly Payroll Deadline</div>
                                    <div class="cal-sub">Deadline · Finance & HR</div>
                                </div>
                            </div>
                            <div class="calendar-event">
                                <div class="cal-date success">
                                    <span class="cal-day">{{ now()->addDays(7)->format('d') }}</span>
                                    <span class="cal-mon">{{ now()->addDays(7)->format('M') }}</span>
                                </div>
                                <div class="cal-info">
                                    <div class="cal-title">Performance Review</div>
                                    <div class="cal-sub">HR Event · All Departments</div>
                                </div>
                            </div>
                            <div class="calendar-event">
                                <div class="cal-date warning">
                                    <span class="cal-day">{{ now()->addDays(14)->format('d') }}</span>
                                    <span class="cal-mon">{{ now()->addDays(14)->format('M') }}</span>
                                </div>
                                <div class="cal-info">
                                    <div class="cal-title">New Employee Onboarding</div>
                                    <div class="cal-sub">HR Event · 3 new hires</div>
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- =====================================================
             ANNOUNCEMENTS TABLE
        ===================================================== --}}
        <div class="row">
            <div class="col-12 mb-4">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h4 class="dash-card-title">
                            <span class="title-icon" style="background:var(--primary-gradient)"><i class="fas fa-bullhorn"></i></span>
                            Announcements
                        </h4>
                        <button id="btn-announcement" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i> New Announcement
                        </button>
                    </div>
                    <div class="dash-card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="users-table">
                                <thead>
                                    <tr>
                                        <th class="text-center pl-4">Title</th>
                                        <th class="text-center">Publish Date</th>
                                        <th class="text-center">End Date</th>
                                        <th class="text-center pr-4">Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /section-body --}}
</section>
</div>{{-- /main-content --}}


{{-- =====================================================
     MODALS
===================================================== --}}

{{-- Preview Modal --}}
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">
                    <i class="fas fa-eye mr-2"></i> Announcement Preview
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm mb-4" style="font-size:.875rem;">
                    <tbody>
                        <tr><th style="width:140px;color:var(--text-muted);">Publish Date</th><td id="previewDate" style="font-weight:700;"></td></tr>
                        <tr><th style="color:var(--text-muted);">End Date</th><td id="previewEndDate" style="font-weight:700;"></td></tr>
                        <tr><th style="color:var(--text-muted);">Created By</th><td id="previewEmployee" style="font-weight:700;"></td></tr>
                    </tbody>
                </table>
                <div id="previewContent" style="max-height:400px;overflow-y:auto;line-height:1.8;font-size:.9rem;"></div>
            </div>
            <div class="modal-footer" style="background:var(--surface-2);">
                <small class="text-muted text-center w-100">
                    <i class="fas fa-shield-alt mr-2"></i>Official announcement from HR Department ·
                    <a href="https://wa.me/6281138310552" target="_blank" class="text-success">Contact HR</a>
                </small>
            </div>
        </div>
    </div>
</div>

{{-- Create Submission Modal --}}
<div class="modal fade" id="createSubmissionModal" tabindex="-1" aria-labelledby="createSubmissionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('Submissions.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createSubmissionLabel">
                        <i class="fas fa-plus-circle mr-2"></i> Create New Submission
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    {{-- <div class="mb-4">
                        <label class="form-label" for="type"><i class="fas fa-clipboard-list mr-1"></i> Type</label>
                        <select name="type" id="type" class="form-control select2 @error('type') is-invalid @enderror" required>
                            <option value="">Choose submission type</option>
                            @foreach ($types as $value)
                                @if ($value === 'Overtime' && !$canCreateOvertime)@continue@endif
                                <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div> --}}

                    <div class="mb-4" id="statusDiv" style="display:none;">
                        <label class="form-label" for="status_submissions"><i class="fas fa-clock mr-1"></i> Overtime Type</label>
                        <select name="status_submissions" id="status_submissions" class="form-control select2">
                            <option value="">Choose overtime type</option>
                            @foreach ($statussubmissions as $value)
                                <option value="{{ $value }}" {{ old('status_submissions') == $value ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4" id="annualLeaveInfo" style="display:none;">
                        <label class="form-label"><i class="fas fa-info-circle mr-1"></i> Annual Leave Balance</label>
                        <div class="info-box">
                            <p><strong>Total Leave:</strong> <span id="total">{{ $employee->total ?? 0 }}</span> days</p>
                            <p><strong>Pending Leave:</strong> <span id="pending">{{ $employee->pending ?? 0 }}</span> days</p>
                            <p class="mb-0"><strong>Remaining:</strong> <span id="remaining">{{ $employee->remaining ?? 0 }}</span> days</p>
                        </div>
                    </div>

                    @if ($canCreateOvertime)
                    <div class="mb-4" id="employeeList" style="display:none;">
                        <label class="form-label"><i class="fas fa-users mr-1"></i> Select Employee(s)</label>
                        <div class="employee-checkbox-container">
                            @foreach ($managedEmployees as $emp)
                            <div class="form-check">
                                <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}"
                                    class="form-check-input" id="emp_{{ $emp->id }}">
                                <label for="emp_{{ $emp->id }}" class="form-check-label">{{ $emp->employee_name }}</label>
                            </div>
                            @endforeach
                        </div>
                        <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle mr-1"></i>You can select yourself or employees in your department</small>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label" for="leave_date_from"><i class="fas fa-calendar-alt mr-1"></i> Start Date</label>
                            <input type="date" name="leave_date_from" id="leave_date_from" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label" for="leave_date_to"><i class="fas fa-calendar-check mr-1"></i> End Date</label>
                            <input type="date" name="leave_date_to" id="leave_date_to" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="notes"><i class="fas fa-sticky-note mr-1"></i> Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"
                            placeholder="Enter additional notes or reasons..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane mr-1"></i> Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('library/chart.js/dist/Chart.min.js') }}"></script>
    <script src="{{ asset('library/jqvmap/dist/jquery.vmap.min.js') }}"></script>
    <script src="{{ asset('library/jqvmap/dist/maps/jquery.vmap.world.js') }}"></script>
    <script src="{{ asset('library/summernote/dist/summernote-bs4.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>

    <script>
    /* =====================================================
       SUBMISSION TYPE TOGGLE
    ===================================================== */
    $(document).ready(function () {
        function toggleFields(type) {
            const isOvertime = type === 'Overtime';
            const isAnnual  = type === 'Annual Leave';

            $('#leave_date_from, #leave_date_to').attr('type', isOvertime ? 'datetime-local' : 'date');
            $('#statusDiv').toggle(isOvertime);
            $('#annualLeaveInfo').toggle(isAnnual);
            @if($canCreateOvertime)
            $('#employeeList').toggle(isOvertime);
            @endif
        }

        toggleFields($('#type').val());
        $('#type').on('change', function () { toggleFields($(this).val()); });
    });

    /* =====================================================
       SELECT2
    ===================================================== */
    $(document).ready(function () {
        $('#type').select2({ theme: 'bootstrap4', placeholder: '-- Select Type --', width: '100%' });
        $('#createSubmissionModal').on('shown.bs.modal', function () {
            $('#type').select2({ dropdownParent: $('#createSubmissionModal') });
        });
    });

    /* =====================================================
       ATTENDANCE CHART
    ===================================================== */
    let ctx = document.getElementById('attendanceChart').getContext('2d');
    let attendanceChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Attendance %',
                data: [],
                backgroundColor: 'rgba(37,49,109,0.15)',
                borderColor: 'rgba(37,49,109,0.8)',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.parsed.y + '%'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: { color: 'rgba(0,0,0,.05)' },
                    ticks: { callback: v => v + '%', font: { family: 'Plus Jakarta Sans', size: 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Plus Jakarta Sans', size: 11 } }
                }
            }
        }
    });

    function loadChartData(startDate, endDate) {
        fetch(`{{ route('dashboardHR.data') }}?start_date=${startDate}&end_date=${endDate}`)
            .then(r => r.json())
            .then(data => {
                attendanceChart.data.labels   = data.data.map(i => i.date);
                attendanceChart.data.datasets[0].data = data.data.map(i => i.percentage);
                attendanceChart.update();
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadChartData(document.getElementById('startDate').value, document.getElementById('endDate').value);
    });

    document.getElementById('filterButton').addEventListener('click', function () {
        loadChartData(document.getElementById('startDate').value, document.getElementById('endDate').value);
    });

    /* =====================================================
       DEPARTMENT DONUT CHART
    ===================================================== */
    document.addEventListener('DOMContentLoaded', function () {
        const deptData  = @json($departmentDistribution ?? []);
        const labels    = deptData.map(d => d.name);
        const counts    = deptData.map(d => d.count);
        const palette   = ['#25316D','#10b981','#f59e0b','#f43f5e','#8b5cf6','#0ea5e9','#f97316','#14b8a6'];

        const dctx = document.getElementById('deptChart').getContext('2d');
        new Chart(dctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: palette,
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 6,
                }]
            },
            options: {
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => ` ${c.label}: ${c.parsed} employees` } }
                }
            }
        });

        // Render legend
        const legendEl = document.getElementById('deptLegend');
        if (legendEl && labels.length) {
            const total = counts.reduce((a, b) => a + b, 0);
            labels.forEach((lbl, i) => {
                const pct = total > 0 ? Math.round(counts[i] / total * 100) : 0;
                legendEl.innerHTML += `
                    <li>
                        <span class="legend-dot" style="background:${palette[i % palette.length]}"></span>
                        ${lbl}
                        <span class="legend-val">${counts[i]} <span style="color:var(--text-muted);font-weight:500;">(${pct}%)</span></span>
                    </li>`;
            });
        }
    });

    /* =====================================================
       DATATABLES — ANNOUNCEMENTS
    ===================================================== */
    $(document).ready(function () {
        var table = $('#users-table').DataTable({
            processing: true,
            autoWidth: false,
            serverSide: true,
            ajax: { url: '{{ route('announcements.announcements') }}', type: 'GET' },
            responsive: true,
            lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'All']],
            language: { search: '_INPUT_', searchPlaceholder: 'Search...' },
            columns: [
                { data: 'title',        name: 'title',        className: 'text-center' },
                {
                    data: 'publish_date', name: 'publish_date', className: 'text-center',
                    render: d => {
                        if (!d) return '-';
                        const dt = new Date(d);
                        return `${String(dt.getDate()).padStart(2,'0')} ${['January','February','March','April','May','June','July','August','September','October','November','December'][dt.getMonth()]} ${dt.getFullYear()}`;
                    }
                },
                {
                    data: 'end_date',    name: 'end_date',     className: 'text-center',
                    render: d => {
                        if (!d) return '<span class="stat-badge neutral">Continuesly</span>';
                        const dt = new Date(d);
                        return `${String(dt.getDate()).padStart(2,'0')} ${['January','February','March','April','May','June','July','August','September','October','November','December'][dt.getMonth()]} ${dt.getFullYear()}`;
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ]
        });
    });

    /* =====================================================
       PREVIEW MODAL
    ===================================================== */
    $(document).on('click', '.preview-btn', function () {
        $('#previewEmployee').text($(this).data('employee'));
        $('#previewDate').text($(this).data('date'));
        $('#previewEndDate').text($(this).data('enddate'));
        $('#previewContent').html($(this).data('content'));
        $('#previewModal').modal('show');
    });

    /* =====================================================
       ANNOUNCEMENT MODAL (SweetAlert)
    ===================================================== */
    function openAnnouncementModal() {
        Swal.fire({
            title: 'Make an Announcement',
            html: `
                <form id="announcementForm" action="{{ route('dashboardHR.store') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3 text-start">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group mb-3 text-start">
                        <label class="form-label">Announcement Content</label>
                        <textarea id="editor" name="content" class="form-control"></textarea>
                    </div>
                    <div class="form-group mb-3 text-start">
                        <label class="form-label">Publish Date</label>
                        <input type="date" name="publish_date" class="form-control" required>
                    </div>
                    <div class="form-group mb-3 text-start">
                        <label class="form-label">End Date <span class="text-muted">(optional)</span></label>
                        <input type="date" name="end_date" class="form-control">
                    </div>
                </form>`,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-paper-plane mr-1"></i> Publish',
            cancelButtonText:  'Cancel',
            width: '680px',
            focusConfirm: false,
            didOpen: () => {
                if (tinymce.get('editor')) tinymce.get('editor').remove();
                tinymce.init({
                    selector: '#editor',
                    plugins: 'lists link image table code',
                    toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
                    menubar: false,
                    height: 280,
                    license_key: 'gpl'
                });
            },
            willClose: () => { if (tinymce.get('editor')) tinymce.get('editor').remove(); },
            preConfirm: () => {
                tinymce.triggerSave();
                const title        = document.querySelector('input[name="title"]').value.trim();
                const content      = document.querySelector('textarea[name="content"]').value.trim();
                const publish_date = document.querySelector('input[name="publish_date"]').value;
                if (!title)        { Swal.showValidationMessage('Title is required');            return false; }
                if (!content)      { Swal.showValidationMessage('Content is required');          return false; }
                if (!publish_date) { Swal.showValidationMessage('Publish date is required');     return false; }
                document.getElementById('announcementForm').submit();
            }
        });
    }

    document.getElementById('btn-announcement').addEventListener('click', openAnnouncementModal);
    document.getElementById('btn-announcement-quick').addEventListener('click', function (e) {
        e.preventDefault();
        openAnnouncementModal();
    });

    /* =====================================================
       FLASH MESSAGES
    ===================================================== */
    @if(session('success'))
    Swal.fire({ icon: 'success', title: 'Success', text: '{{ session('success') }}', timer: 3000, showConfirmButton: false });
    @endif
    @if(session('error'))
    Swal.fire({ icon: 'error', title: 'Error', text: '{{ session('error') }}' });
    @endif

    /* =====================================================
       CLEANUP MODAL BACKDROP
    ===================================================== */
    $(document).on('hidden.bs.modal', function () {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });
    </script>
@endpush