{{-- @extends('layouts.app')
@section('title', 'Manager Dashboard')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <style>
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

        .progress-sm {
            height: 8px;
        }

        .attendance-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .location-badge {
            display: inline-block;
            padding: 0.35rem 0.65rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 6px;
        }

        .modal-backdrop {
            z-index: 1040 !important;
        }

        .modal {
            z-index: 1050 !important;
        }

        .team-member-card {
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .team-member-card:hover {
            border-left-color: #6777ef;
            background-color: #f8f9fa;
        }

        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .status-present {
            background-color: #28a745;
        }

        .status-absent {
            background-color: #dc3545;
        }

        .status-leave {
            background-color: #ffc107;
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Manager Dashboard</h1>
                <div class="section-header-button">
                    <span class="location-badge bg-primary text-white">
                        <i class="fas fa-map-marker-alt me-1"></i> {{ $managerLocation ?? 'N/A' }}
                    </span>
                </div>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
                    <div class="breadcrumb-item">Manager</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div onclick="window.location='{{ route('pages.Employee') }}';" style="cursor: pointer;"
                            title="View team members" class="card card-statistic-1 metric-card">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>My Team</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalTeamMembers ?? 0 }} Members
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
                                    {{ $presentToday ?? 0 }}
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
                                    {{ $onLeave ?? 0 }}
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
                                    {{ $absent ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8 col-md-12 col-12 col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>
                                    <i class="fas fa-calendar-check me-2"></i>
                                    Team Attendance Rate - {{ $managerLocation ?? 'Location' }}
                                </h4>
                                <div class="card-header-action d-flex gap-2">
                                    <input type="date" id="startDate" class="form-control"
                                        value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                    <input type="date" id="endDate" class="form-control"
                                        value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                                    <button id="filterButton" class="btn btn-primary">Filter</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="attendanceChart" height="180"></canvas>
                                <div class="alert alert-secondary mt-4" role="alert">
                                    <span class="text-dark">
                                        <strong>Important Note:</strong> <br>
                                        - X-axis represents dates.<br>
                                        - Y-axis shows total team attendance for each date.<br>
                                        - Data filtered by location: <strong>{{ $managerLocation ?? 'N/A' }}</strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-12 col-12 col-sm-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>
                                    <i class="fas fa-clipboard-list me-2"></i>
                                    Team Submissions
                                </h4>
                                <button id="btn-submission" class="btn btn-primary btn-sm" data-toggle="modal"
                                    data-target="#createSubmissionModal">
                                    <i class="fas fa-plus me-1"></i>
                                    Create
                                </button>
                            </div>

                            <div class="card-body">
                                @if ($selectedType === 'Annual Leave' && isset($leaveData))
                                    <div class="mb-3 p-3 rounded bg-light border">
                                        <h6 class="text-primary mb-2">
                                            <i class="fas fa-umbrella-beach me-2"></i>Your Leave Summary
                                        </h6>
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
                                    @forelse($teamPendingSubmissions as $submission)
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
                                                No pending submissions from team members :)
                                            </div>
                                        </li>
                                    @endforelse
                                </ul>

                                <div class="text-center pt-1 pb-1">
                                    <a href="{{ route('Submissions.index') }}" class="btn btn-primary btn-lg btn-round">
                                        View All Submissions
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>
                                    <i class="fas fa-users-cog me-2"></i>
                                    Team Members - {{ $managerLocation ?? 'Location' }}
                                </h4>
                                <div class="card-header-action">
                                    <span class="badge badge-primary">{{ $totalTeamMembers ?? 0 }} Members</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="team-table">
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Department</th>
                                                <th>Position</th>
                                                <th class="text-center">Today Status</th>
                                                <th class="text-center">Location</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($teamMembers as $member)
                                                <tr class="team-member-card">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="{{ asset('img/avatar/avatar-' . rand(1, 4) . '.png') }}"
                                                                alt="avatar" class="rounded-circle mr-2" width="40">
                                                            <div>
                                                                <strong>{{ $member->employee_name }}</strong><br>
                                                                <small class="text-muted">{{ $member->employee_id }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $member->department->name ?? 'N/A' }}</td>
                                                    <td>{{ $member->position->name ?? 'N/A' }}</td>
                                                    <td class="text-center">
                                                        @php
                                                            $status = $member->todayAttendanceStatus ?? 'absent';
                                                        @endphp
                                                        <span class="badge 
                                                            @if($status === 'present') badge-success
                                                            @elseif($status === 'leave') badge-warning
                                                            @else badge-danger
                                                            @endif">
                                                            <span class="status-indicator status-{{ $status }}"></span>
                                                            {{ ucfirst($status) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-info">
                                                            {{ $member->location->name ?? 'N/A' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ route('pages.Employee.show', $member->id) }}" 
                                                           class="btn btn-sm btn-primary" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">
                                                        No team members found
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>
                                    <i class="fas fa-bullhorn me-2"></i>
                                    Announcements
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="announcements-table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
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
                                    <th scope="row" class="text-dark" style="width: 500px; text-align: right;">End Date</th>
                                    <td style="width: 210px; text-align: right;"><span id="previewEndDate" class="fw-semibold"></span></td>
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

    <div class="modal fade" id="createSubmissionModal" tabindex="-1" role="dialog" aria-labelledby="createSubmissionLabel" aria-hidden="true">
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
                            <select name="type" id="type" class="form-control select2 @error('type') is-invalid @enderror" required>
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
                            <select name="status_submissions" id="status_submissions" class="form-control select2">
                                <option value="">Choose Status Submissions</option>
                                @foreach ($statussubmissions as $value)
                                    <option value="{{ $value }}" {{ old('status_submissions') == $value ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3" id="annualLeaveInfo" style="display: none;">
                            <label class="form-label">Annual Leave Info</label>
                            <div class="border rounded p-3 bg-light">
                                <p class="mb-1"><strong>Total Leave:</strong> <span id="total">{{ $employee->total ?? 0 }}</span></p>
                                <p class="mb-1"><strong>Pending Leave:</strong> <span id="pending">{{ $employee->pending ?? 0 }}</span></p>
                                <p class="mb-0"><strong>Remaining Leave:</strong> <span id="remaining">{{ $employee->remaining ?? 0 }}</span></p>
                            </div>
                        </div>

                        @if ($canCreateOvertime)
                            <div class="mb-3" id="employeeList" style="display: none;">
                                <label class="form-label">Select Team Member(s)</label>
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
                                <small class="text-muted">Select team members from your location/department.</small>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Leave Date From</label>
                            <input type="date" name="leave_date_from" id="leave_date_from" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Leave Date To</label>
                            <input type="date" name="leave_date_to" id="leave_date_to" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" required></textarea>
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
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            $('#team-table').DataTable({
                responsive: true,
                order: [[0, 'asc']]
            });

            $('#announcements-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("announcements.data") }}',
                columns: [
                    { data: 'title', name: 'title' },
                    { data: 'publish_date', name: 'publish_date', className: 'text-center' },
                    { data: 'end_date', name: 'end_date', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ]
            });

            $('#type').on('change', function() {
                const selectedType = $(this).val();
                
                $('#statusDiv').hide();
                $('#annualLeaveInfo').hide();
                $('#employeeList').hide();

                if (selectedType === 'Overtime') {
                    $('#statusDiv').show();
                    $('#employeeList').show();
                } else if (selectedType === 'Annual Leave') {
                    $('#annualLeaveInfo').show();
                }
            });

            // Attendance Chart
            const ctx = document.getElementById('attendanceChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($chartLabels ?? []) !!},
                        datasets: [{
                            label: 'Team Attendance',
                            data: {!! json_encode($chartData ?? []) !!},
                            borderColor: '#6777ef',
                            backgroundColor: 'rgba(103, 119, 239, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            $('#filterButton').on('click', function() {
                const startDate = $('#startDate').val();
                const endDate = $('#endDate').val();
                
                window.location.href = `{{ route('manager.dashboard') }}?start_date=${startDate}&end_date=${endDate}`;
            });
        });
    </script>
@endpush --}}
@extends('layouts.app')
@section('title', 'Manager Dashboard')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <style>
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

        .progress-sm {
            height: 8px;
        }

        .attendance-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .location-badge {
            display: inline-block;
            padding: 0.35rem 0.65rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 6px;
        }

        .modal-backdrop {
            z-index: 1040 !important;
        }

        .modal {
            z-index: 1050 !important;
        }

        .team-member-card {
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .team-member-card:hover {
            border-left-color: #6777ef;
            background-color: #f8f9fa;
        }

        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .status-present {
            background-color: #28a745;
        }

        .status-absent {
            background-color: #dc3545;
        }

        .status-leave {
            background-color: #ffc107;
        }
    </style>
@endpush
@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Store Manager Dashboard</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
                    <div class="breadcrumb-item">Store Manager</div>
                </div>
            </div>

            <div class="section-body">
                <!-- Overview Cards -->
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12" title="View list of store employees">
                        <div onclick="window.location='{{ route('pages.Employee') }}';" style="cursor: pointer;"
                            title="Lihat daftar karyawan toko" class="card card-statistic-1 metric-card">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Team Members</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalEmployees ?? 0 }} Employees
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
                                    <h4>Present Today</h4>
                                </div>
                                <div class="card-body">
                                    {{ $presentToday ?? 0 }} Staff
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1 metric-card">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-calendar-times"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>On Leave</h4>
                                </div>
                                <div class="card-body">
                                    {{ $onLeave ?? 0 }} Staff
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1 metric-card">
                            <div class="card-icon bg-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Absent</h4>
                                </div>
                                <div class="card-body">
                                    {{ $absent ?? 0 }} Staff
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Row -->
                <div class="row">
                    <!-- Attendance Chart -->
                    <div class="col-lg-8 col-md-12 col-12 col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>
                                    <i class="fas fa-chart-line me-2"></i>
                                    Team Attendance Trends
                                </h4>
                                <div class="card-header-action d-flex gap-2">
                                    <input type="date" id="startDate" class="form-control"
                                        value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                    <input type="date" id="endDate" class="form-control"
                                        value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                                    <button id="filterButton" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="attendanceChart" height="180"></canvas>
                                <div class="alert alert-info mt-4" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Chart Guide:</strong><br>
                                    • X-axis: Date<br>
                                    • Y-axis: Number of employees present
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Submissions -->
                    <div class="col-lg-4 col-md-12 col-12 col-sm-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>
                                    <i class="fas fa-clipboard-list me-2"></i>
                                    Pending Requests
                                </h4>
                                <button id="btn-submission" class="btn btn-primary btn-sm" data-toggle="modal"
                                    data-target="#createSubmissionModal">
                                    <i class="fas fa-plus me-1"></i>
                                    New Request
                                </button>
                            </div>

                            <div class="card-body">
                                {{-- Annual Leave Summary --}}
                                @if ($selectedType === 'Annual Leave' && isset($leaveData))
                                    <div class="mb-3 p-3 rounded" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                        <h6 class="text-white mb-2">
                                            <i class="fas fa-umbrella-beach me-2"></i>Your Leave Balance
                                        </h6>
                                        <div class="d-flex justify-content-between text-center">
                                            <div class="flex-fill">
                                                <small class="text-white-50">Total</small>
                                                <h5 class="mb-0 text-white font-weight-bold">{{ $leaveData['total'] }}</h5>
                                            </div>
                                            <div class="flex-fill">
                                                <small class="text-white-50">Pending</small>
                                                <h5 class="mb-0 text-warning font-weight-bold">{{ $leaveData['pending'] }}</h5>
                                            </div>
                                            <div class="flex-fill">
                                                <small class="text-white-50">Available</small>
                                                <h5 class="mb-0 text-white font-weight-bold">{{ $leaveData['remaining'] }}</h5>
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
                                                    <small class="text-muted">
                                                        <i class="far fa-clock"></i>
                                                        {{ $submission->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                                <div class="media-title font-weight-bold">
                                                    {{ $submission->employee->employee_name }}
                                                </div>
                                                <span class="text-small">
                                                    <span class="badge badge-primary">{{ ucfirst($submission->type) }}</span>
                                                    <span class="text-muted">{{ $submission->formattedDuration }}</span>
                                                </span>
                                            </div>
                                        </li>
                                    @empty
                                        <li class="media">
                                            <div class="media-body text-center py-4">
                                                <i class="fas fa-check-circle text-success mb-2" style="font-size: 3rem;"></i>
                                                <p class="text-muted mb-0">All caught up! No pending requests.</p>
                                            </div>
                                        </li>
                                    @endforelse
                                </ul>

                                <div class="text-center pt-1 pb-1">
                                    <a href="#" class="btn btn-primary btn-lg btn-round">
                                        <i class="fas fa-list me-2"></i>
                                        View All Submissions
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Announcements Section -->
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-12 col-sm-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>
                                    <i class="fas fa-bullhorn me-2"></i>
                                    Store Announcements
                                </h4>
                                <button id="btn-announcement" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i>
                                    Create Announcement
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped" id="users-table">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center"><i class="fas fa-heading me-1"></i> Title</th>
                                                <th class="text-center"><i class="fas fa-calendar-alt me-1"></i> Publish Date</th>
                                                <th class="text-center"><i class="fas fa-calendar-check me-1"></i> End Date</th>
                                                <th class="text-center"><i class="fas fa-cog me-1"></i> Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- DataTable will populate this -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-3 shadow-lg border-0">
                <div class="modal-header border-bottom" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="modal-title fw-bold text-white mb-0" id="previewTitle">
                        <i class="fas fa-eye me-2"></i>Announcement Preview
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <table class="table table-borderless table-sm align-middle mb-0">
                            <tbody>
                                <tr>
                                    <th scope="row" class="text-muted" style="width: 130px;">
                                        <i class="far fa-calendar me-1"></i>Publish Date
                                    </th>
                                    <td style="width: 180px;"><span id="previewDate" class="fw-semibold"></span></td>
                                    <th scope="row" class="text-muted" style="width: 130px; text-align: right;">
                                        <i class="far fa-calendar-check me-1"></i>End Date
                                    </th>
                                    <td style="width: 180px; text-align: right;"><span id="previewEndDate" class="fw-semibold"></span></td>
                                </tr>
                                <tr>
                                    <th scope="row" class="text-muted" style="width: 130px;">
                                        <i class="far fa-user me-1"></i>Created By
                                    </th>
                                    <td colspan="3"><span id="previewEmployee" class="fw-semibold"></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr>
                    <div>
                        <div id="previewContent" class="fs-6 text-dark"
                            style="max-height: 450px; overflow-y: auto; line-height: 1.8; padding: 15px; background-color: #f8f9fa; border-radius: 8px;"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        Official announcement from Store Management.
                        For inquiries, contact
                        <a href="https://wa.me/6281138310552" target="_blank" rel="noopener noreferrer"
                            style="color:#25D366; text-decoration:none; font-weight:bold;">
                            <i class="fab fa-whatsapp"></i> Store Manager
                        </a>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Submission Modal -->
    <div class="modal fade" id="createSubmissionModal" tabindex="-1" role="dialog"
        aria-labelledby="createSubmissionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('Submissions.store') }}" method="POST">
                    @csrf
                    <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="modal-title text-white" id="createSubmissionLabel">
                            <i class="fas fa-file-alt me-2"></i> Create New Request
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label font-weight-bold">
                                <i class="fas fa-list-alt me-1"></i>Request Type
                            </label>
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
                            <label class="form-label font-weight-bold">
                                <i class="fas fa-clock me-1"></i>Overtime Type
                            </label>
                            <select name="status_submissions" id="status_submissions"
                                class="form-control select2 @error('status_submissions') is-invalid @enderror">
                                <option value="">Choose Overtime Type</option>
                                @foreach ($statussubmissions as $value)
                                    <option value="{{ $value }}"
                                        {{ old('status_submissions') == $value ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3" id="annualLeaveInfo" style="display: none;">
                            <label class="form-label font-weight-bold">
                                <i class="fas fa-info-circle me-1"></i>Leave Balance
                            </label>
                            <div class="border rounded p-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <div class="row text-white">
                                    <div class="col-4 text-center">
                                        <small class="d-block text-white-50">Total</small>
                                        <h5 class="mb-0 font-weight-bold" id="total">{{ $employee->total ?? 0 }}</h5>
                                    </div>
                                    <div class="col-4 text-center">
                                        <small class="d-block text-white-50">Pending</small>
                                        <h5 class="mb-0 font-weight-bold" id="pending">{{ $employee->pending ?? 0 }}</h5>
                                    </div>
                                    <div class="col-4 text-center">
                                        <small class="d-block text-white-50">Available</small>
                                        <h5 class="mb-0 font-weight-bold" id="remaining">{{ $employee->remaining ?? 0 }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($canCreateOvertime)
                            <div class="mb-3" id="employeeList" style="display: none;">
                                <label class="form-label font-weight-bold">
                                    <i class="fas fa-users me-1"></i>Select Team Member(s)
                                </label>
                                <div class="border rounded p-3 bg-light" style="max-height: 180px; overflow-y: auto;">
                                    @foreach ($managedEmployees as $emp)
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}"
                                                class="form-check-input" id="emp_{{ $emp->id }}">
                                            <label for="emp_{{ $emp->id }}" class="form-check-label">
                                                <i class="far fa-user me-1"></i>{{ $emp->employee_name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>You can select yourself or team members from your department
                                </small>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label font-weight-bold">
                                <i class="far fa-calendar me-1"></i>Start Date
                            </label>
                            <input type="date" name="leave_date_from" id="leave_date_from" 
                                class="form-control @error('leave_date_from') is-invalid @enderror" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-weight-bold">
                                <i class="far fa-calendar-check me-1"></i>End Date
                            </label>
                            <input type="date" name="leave_date_to" id="leave_date_to" 
                                class="form-control @error('leave_date_to') is-invalid @enderror" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-weight-bold">
                                <i class="fas fa-sticky-note me-1"></i>Notes/Reason
                            </label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" 
                                rows="3" required placeholder="Please provide details for your request..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i>Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection