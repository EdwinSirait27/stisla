{{-- @extends('layouts.app')
@section('title', 'HR Manager Dashboard')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">


    <style>
        .metric-card {
            transition: transform 0.2s;
        }

        .metric-card:hover {
            transform: translateY(-5px);
        }

        .progress-sm {
            height: 8px;
        }

        .attendance-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .metric-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .metric-card .card-icon {
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .tooltip-custom {
            position: relative;
        }

        .tooltip-custom:hover::after {
            content: attr(data-title);
            position: absolute;
            bottom: 110%;
            left: 50%;
            transform: translateX(-50%);
            background: #343a40;
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            white-space: nowrap;
            font-size: 13px;
            opacity: 0.9;
            z-index: 999;
        }

        .modal-backdrop {
            z-index: 1040 !important;
        }

        .modal {
            z-index: 1050 !important;
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>HR Manager Dashboard</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
                    <div class="breadcrumb-item">HR Manager</div>
                </div>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12" title="View list of all employees">
                        <div onclick="window.location='{{ route('pages.Employee') }}';" style="cursor: pointer;"
                            title="Lihat daftar semua karyawan" class="card card-statistic-1 metric-card">
                            <div class="card-icon bg-primary">
                                <i class="far fa-user"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Active</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalEmployees ?? null }} Employees
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1 metric-card">
                            <div class="card-icon bg-success">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Hadir Hari Ini</h4>
                                </div>
                                <div class="card-body">
                                    {{ $presentToday ?? null }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1 metric-card">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-user-clock"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Izin/Cuti</h4>
                                </div>
                                <div class="card-body">
                                    {{ $onLeave ?? null }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1 metric-card">
                            <div class="card-icon bg-danger">
                                <i class="fas fa-user-times"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Tidak Hadir</h4>
                                </div>
                                <div class="card-body">
                                    {{ $absent ?? null }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-8 col-md-12 col-12 col-sm-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>
                                    <i class="fas fa-calendar-check me-2"></i>
                                    Monthly Attendance Rate
                                </h4>
                                <div class="card-header-action d-flex gap-2">
                                    <input type="date" id="startDate" class="form-control"
                                        value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                    <input type="date" id="endDate" class="form-control"
                                        value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                                    <button id="filterButton" class="btn btn-primary">
                                        Filter
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="attendanceChart" height="180"></canvas>
                                <div class="alert alert-secondary mt-4" role="alert">
                                    <span class="text-dark">
                                        <strong>Important Note:</strong> <br>
                                        - X-axis means date.<br>
                                        - Y-axis total employee attendance based on the x-axis.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 col-12 col-sm-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>
                                    <i class="fas fa-atom me-2"></i>
                                    List Submission
                                </h4>
                                <button id="btn-submission" class="btn btn-primary btn-sm" data-toggle="modal"
                                    data-target="#createSubmissionModal">
                                    <i class="fas fa-plus me-1"></i>
                                    Create Submission
                                </button>
                            </div>

                            <div class="card-body">
                                @if ($selectedType === 'Annual Leave' && isset($leaveData))
                                    <div class="mb-3 p-3 rounded bg-light border">
                                        <h6 class="text-primary mb-2"><i class="fas fa-umbrella-beach me-2"></i>Annual Leave
                                            Summary</h6>
                                        <div class="d-flex justify-content-between text-center">
                                            <div class="flex-fill">
                                                <small class="text-muted">Total</small>
                                                <h6 class="mb-0 text-dark">{{ $leaveData['total'] }}</h6>
                                            </div>
                                            <div class="flex-fill">
                                                <small class="text-muted">Pending</small>
                                                <h6 class="mb-0 text-warning">{{ $leaveData['pending'] }}</h6>
                                            </div>
                                            <div class="flex-fill">
                                                <small class="text-muted">Remaining</small>
                                                <h6 class="mb-0 text-success">{{ $leaveData['remaining'] }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <ul class="list-unstyled list-unstyled-border">
                                    @forelse($pendingSubmissions as $submission)
                                        <li class="media">
                                            <img class="mr-3 rounded-circle" width="50"
                                                src="{{ asset('img/avatar/avatar-' . rand(1, 4) . '.png') }}"
                                                alt="avatar">
                                            <div class="media-body">
                                                <div class="float-right">
                                                    <small>{{ $submission->created_at->diffForHumans() }}</small>
                                                </div>
                                                <div class="media-title">
                                                    {{ $submission->employee->employee_name }}
                                                </div>
                                                <span class="text-small text-muted">
                                                    {{ ucfirst($submission->type) }} -
                                                    {{ $submission->formattedDuration }}
                                                </span>
                                            </div>
                                        </li>
                                    @empty
                                        <li class="media">
                                            <div class="media-body text-center text-muted">
                                                There is no pending applications yet :)
                                            </div>
                                        </li>
                                    @endforelse
                                </ul>
                                <div class="text-center pt-1 pb-1">
                                    <a href="#" class="btn btn-primary btn-lg btn-round">
                                        View Your Submissions
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-12 col-sm-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>
                                    <i class="fas fa-book me-2"></i>
                                    List of Announcements
                                </h4>
                                <button id="btn-announcement" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i>
                                    Make an Announcement
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
            </div>
        </section>
    </div>
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-3 shadow-lg border-0">
                <div class="modal-header bg-white border-bottom justify-content-center">
                    <h5 class="modal-title fw-bold text-dark mb-0" id="previewTitle">
                        Announcement Preview
                    </h5>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <table class="table table-sm align-middle mb-0">
                            <tbody>
                                <tr>
                                    <th scope="row" class="text-dark" style="width: 130px;">Publish Date</th>
                                    <td style="width: 180px;"><span id="previewDate" class="fw-semibold"></span></td>
                                    <th scope="row" class="text-dark" style="width: 500px; text-align: right;">End
                                        Date</th>
                                    <td style="width: 210px; text-align: right; "><span id="previewEndDate"
                                            class="fw-semibold"></span></td>
                                </tr>
                                <tr>
                                    <th scope="row" class="text-dark" style="width: 130px;">Created By</th>
                                    <td colspan="3"><span id="previewEmployee" class="fw-semibold"></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <div id="previewContent" class="fs-6 text-dark"
                            style="max-height: 450px; overflow-y: auto; line-height: 1.6;"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top justify-content-center text-center">
                    &copy; This is a valid announcement from HR Department.
                    For more information please contact
                    <div class="bullet d-inline-block mx-2"></div>
                    <a href="https://wa.me/6281138310552" target="_blank" rel="noopener noreferrer"
                        style="color:#25D366; text-decoration:none; font-weight:bold;">
                        HR Department
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="createSubmissionModal" tabindex="-1" role="dialog"
        aria-labelledby="createSubmissionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('Submissions.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createSubmissionLabel">
                            <i class="fas fa-plus me-2"></i> Create New Submission
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" id="type"
                                class="form-control select2 @error('type') is-invalid @enderror" required>
                                <option value="">Choose Type</option>
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
                        <div class="mb-3" id="statusDiv" style="display: none;">
                            <label class="form-label">Overtime Type</label>
                            <select name="status_submissions" id="status_submissions"
                                class="form-control select2 @error('status_submissions') is-invalid @enderror">
                                <option value="">Choose Status Submissions</option>
                                @foreach ($statussubmissions as $value)
                                    <option value="{{ $value }}"
                                        {{ old('status_submissions') == $value ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3" id="annualLeaveInfo" style="display: none;">
                            <label class="form-label">Annual Leave Info</label>
                            <div class="border rounded p-3 bg-light">
                                <p class="mb-1"><strong>Total Leave:</strong> <span
                                        id="total">{{ $employee->total ?? 0 }}</span></p>
                                <p class="mb-1"><strong>Pending Leave:</strong> <span
                                        id="pending">{{ $employee->pending ?? 0 }}</span></p>
                                <p class="mb-0"><strong>Remaining Leave:</strong> <span
                                        id="remaining">{{ $employee->remaining ?? 0 }}</span></p>
                            </div>
                        </div>
                        @if ($canCreateOvertime)
                            <div class="mb-3" id="employeeList" style="display: none;">
                                <label class="form-label">Select Employee(s)</label>
                                <div class="border rounded p-2" style="max-height: 180px; overflow-y: auto;">
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
                                <small class="text-muted">Manager dapat memilih dirinya sendiri atau bawahan satu
                                    departemen.</small>
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Leave Date From</label>
                            <input type="date" name="leave_date_from" id="leave_date_from" class="form-control"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Leave Date To</label>
                            <input type="date" name="leave_date_to" id="leave_date_to" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <input type="text" name="notes" id="notes" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection --}}
@extends('layouts.app')
@section('title', 'HR Dashboard')
@push('styles')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <style>
        /* :root {
                        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
                        --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                        --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
            </div>

            <div class="section-body">
                <div class="quick-stats">
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-12 mb-4">
                        <div onclick="window.location='{{ route('pages.Employee') }}';" class="stat-card" role="button" title="show employees" aria-label="View all employees">
                            <div class="stat-card-header">
                                <div class="stat-icon primary">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="stat-content">
                                <h3>{{ $totalEmployees ?? 0 }}</h3>
                                <p>Team Members</p>
                                <span class="stat-trend up">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    {{$totalEmployeespending}} Pending
                                </span>
                                <span class="stat-trend down" style="margin-left: 8px;">
                                    <i class="fas fa-arrow-down me-1"></i>
                                    {{$totalEmployeesinactive}} Inactive
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-12 mb-4">
                        <div class="stat-card">
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
        {{ $trend }} Employees
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
                                        <i class="fas fa-check-circle me-1"> </i>All Clear
                                    </span>
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
                                {{-- <button id="btn-submission" class="btn btn-primary btn-sm" data-toggle="modal"
                                    data-target="#createSubmissionModal">
                                    <i class="fas fa-plus me-1"></i>
                                    Create
                                </button> --}}
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
                                                <img class="rounded-circle me-3" width="48" height="48"
                                                    src="{{ asset('img/avatar/avatar-' . rand(1, 4) . '.png') }}"
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
@endpush
