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
                                                            @if ($status === 'present') badge-success
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
                <h1>Store Manager Dashboard</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
                    <div class="breadcrumb-item">Store Manager</div>
                </div>
            </div>

            <div class="section-body">
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

                <div class="row">
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
@endsection --}}
@extends('layouts.app')
@section('title', 'Manager Dashboard')

@push('styles')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #25316D 0%, #3E497A 100%);
            --success-gradient: linear-gradient(135deg, #0A8A6A 0%, #096C57 100%);
            --warning-gradient: linear-gradient(135deg, #C7A845 0%, #A8862A 100%);
            --info-gradient: linear-gradient(135deg, #4A7BA7 0%, #3F8DAE 100%);
            --card-shadow: 0 2px 12px rgba(0, 0, 0, 0.12);
            --card-hover-shadow: 0 8px 24px rgba(0, 0, 0, 0.20);
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

        .quick-stats { margin-bottom: 32px; }

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

        .stat-icon.primary { background: var(--primary-gradient); }
        .stat-icon.success { background: var(--success-gradient); }
        .stat-icon.warning { background: var(--warning-gradient); }
        .stat-icon.info    { background: var(--info-gradient); }

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

        .stat-trend.up   { background: rgba(56, 239, 125, 0.15); color: #11998e; }
        .stat-trend.down { background: rgba(245, 87, 108, 0.15); color: #f5576c; }

        .team-overview-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .team-overview-card .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 24px;
            border: none;
        }

        .team-overview-card .card-header h4 {
            margin: 0;
            font-weight: 600;
            color: white;
        }

        .team-member-item {
            padding: 16px 24px;
            border-bottom: 1px solid #f1f3f5;
            transition: background-color 0.2s;
        }

        .team-member-item:hover { background-color: #f8f9fa; }
        .team-member-item:last-child { border-bottom: none; }

        .member-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e9ecef;
        }

        .member-info h6 {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: #344767;
            font-size: 0.95rem;
        }

        .member-info small { color: #64748b; font-size: 0.8rem; }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.present  { background: rgba(56, 239, 125, 0.15); color: #11998e; }
        .status-badge.absent   { background: rgba(245, 87, 108, 0.15); color: #f5576c; }
        .status-badge.leave    { background: rgba(255, 171, 0, 0.15); color: #f59e0b; }

        /* ── Overtime Assignment Card ── */
        .overtime-assignment-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .overtime-assignment-card .card-header {
            background: white;
            border-bottom: 2px solid #f1f3f5;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .overtime-assignment-card .card-header h4 {
            margin: 0;
            font-weight: 600;
            color: #344767;
        }

        .overtime-item {
            padding: 18px 24px;
            border-bottom: 1px solid #f1f3f5;
            transition: all 0.2s;
        }

        .overtime-item:hover { background-color: #f8f9fa; }
        .overtime-item:last-child { border-bottom: none; }

        .overtime-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 4px;
        }

        /* ── Pending Approvals (existing, kept intact) ── */
        .pending-approvals-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .pending-approvals-card .card-header {
            background: white;
            border-bottom: 2px solid #f1f3f5;
            padding: 20px 24px;
        }

        .pending-approvals-card .card-header h4 {
            margin: 0;
            font-weight: 600;
            color: #344767;
        }

        .approval-item {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f3f5;
            transition: all 0.2s;
        }

        .approval-item:hover { background-color: #f8f9fa; }
        .approval-item:last-child { border-bottom: none; }

        .approval-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .approval-type {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .approval-type.leave    { background: rgba(102, 126, 234, 0.1); color: #667eea; }
        .approval-type.overtime { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }

        .approval-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 12px;
        }

        .approval-meta i { width: 16px; }

        .approval-actions { display: flex; gap: 8px; }

        .btn-approve {
            background: var(--success-gradient);
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .btn-approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(17, 153, 142, 0.3);
            color: white;
        }

        .btn-reject {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            color: #64748b;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .btn-reject:hover {
            background: #fff5f5;
            border-color: #f5576c;
            color: #f5576c;
        }

        .performance-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
        }

        .performance-card .card-header {
            background: white;
            border-bottom: 2px solid #f1f3f5;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .performance-card .card-header h4 {
            margin: 0;
            font-weight: 600;
            color: #344767;
        }

        .chart-filters { display: flex; gap: 8px; }

        .filter-btn {
            padding: 6px 16px;
            border: 1px solid #e9ecef;
            background: white;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #64748b;
            transition: all 0.2s;
            cursor: pointer;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
        }

        .quick-actions-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            padding: 24px;
        }

        .quick-actions-card h5 {
            margin: 0 0 20px 0;
            font-weight: 600;
            color: #344767;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .action-btn {
            padding: 16px;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            background: white;
            transition: all 0.2s;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
            color: #344767;
        }

        .action-btn:hover {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: var(--card-shadow);
        }

        .action-btn i {
            font-size: 1.5rem;
            margin-bottom: 8px;
            display: block;
        }

        .action-btn span { font-size: 0.875rem; font-weight: 500; }

        .activities-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
        }

        .activities-card .card-header {
            background: white;
            border-bottom: 2px solid #f1f3f5;
            padding: 20px 24px;
        }

        .activities-card .card-header h4 {
            margin: 0;
            font-weight: 600;
            color: #344767;
        }

        .activity-timeline { padding: 24px; }

        .activity-item {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
            position: relative;
        }

        .activity-item:last-child { margin-bottom: 0; }

        .activity-item::before {
            content: '';
            position: absolute;
            left: 19px;
            top: 40px;
            bottom: -24px;
            width: 2px;
            background: #e9ecef;
        }

        .activity-item:last-child::before { display: none; }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
            background: white;
            border: 2px solid #e9ecef;
        }

        .activity-icon.success { background: rgba(56, 239, 125, 0.15); border-color: #11998e; color: #11998e; }
        .activity-icon.warning { background: rgba(255, 171, 0, 0.15); border-color: #f59e0b; color: #f59e0b; }
        .activity-icon.info    { background: rgba(79, 172, 254, 0.15); border-color: #4facfe; color: #4facfe; }

        .activity-content h6 { margin: 0 0 4px 0; font-weight: 600; color: #344767; font-size: 0.9rem; }
        .activity-content p  { margin: 0; color: #64748b; font-size: 0.85rem; }
        .activity-time       { font-size: 0.75rem; color: #94a3b8; margin-top: 4px; }

        .empty-state { text-align: center; padding: 48px 24px; }
        .empty-state i   { font-size: 4rem; color: #cbd5e1; margin-bottom: 16px; }
        .empty-state h6  { color: #64748b; font-weight: 600; margin-bottom: 8px; }
        .empty-state p   { color: #94a3b8; font-size: 0.875rem; margin: 0; }

        .modal-content  { border-radius: 16px; border: none; }

        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 20px 24px;
        }

        .modal-header .modal-title { font-weight: 600; color: white; }
        .modal-header .close       { color: white; opacity: 0.9; }
        .modal-body   { padding: 24px; }
        .modal-footer { padding: 16px 24px; border-top: 1px solid rgba(0, 0, 0, 0.05); }

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

        @media (max-width: 768px) {
            .welcome-banner { padding: 24px; }
            .welcome-banner h2 { font-size: 1.5rem; }
            .stat-card { margin-bottom: 16px; }
            .action-grid { grid-template-columns: 1fr; }
            .chart-filters { flex-direction: column; width: 100%; }
            .filter-btn { width: 100%; }
            .approval-actions { flex-direction: column; }
            .btn-approve, .btn-reject { width: 100%; }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in-up { animation: fadeInUp 0.5s ease-out; }

        .text-gradient {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .badge-primary-soft {
            background: rgba(102, 126, 234, 0.1);
            color: #ffffff;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
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

        .announcements-header h5 {
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

        .announcement-item:hover { background-color: #f8f9fa; }
        .announcement-item:last-child { border-bottom: none; }

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

        .announcement-date { font-size: 0.75rem; color: #94a3b8; }

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

        .profile-content { position: relative; z-index: 1; }

        .profile-avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.3);
            object-fit: cover;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .profile-info h2 { font-size: 2rem; font-weight: 700; margin-bottom: 8px; }

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

        .profile-meta-item i { font-size: 1.1rem; }

        /* ── Select2 in modal fix ── */
        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            position: relative;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            color: #344767;
            font-size: 0.875rem;
            padding-left: 0.75rem;
            padding-right: 2.5rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
            top: 0;
            right: 0;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear {
            position: absolute;
            right: 25px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1rem;
            cursor: pointer;
            pointer-events: all;
            z-index: 1;
        }

        .select2-container--default .select2-selection--multiple {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            min-height: 38px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #5e72e4;
            color: white;
            border: none;
            padding: 0.15rem 0.5rem;
            font-size: 0.85rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
            margin-right: 0.3rem;
        }

        .select2-dropdown {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            font-size: 0.875rem;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #5e72e4;
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">

            {{-- ── Profile Header ── --}}
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
                                    <h2>{{ Auth::user()->employee->employee_name ?? 'Manager' }}</h2>
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
                    </div>
                </div>
            </div>

            {{-- ── Quick Stats ── --}}
            <div class="quick-stats">
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-12 mb-4">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div class="stat-icon primary">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="stat-content" title="Employee Team's total">
                                <h3>{{ $totalEmployees ?? 0 }}</h3>
                                <p>Team Members</p>
                                <span class="stat-trend up" title="Employees who were pending, please contact HR Department">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    {{ $totalEmployeespending }} Pending
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
                            <div class="stat-content" title="Employee who are present today">
                                <h3>{{ $presentToday ?? 0 }}</h3>
                                <p>Present Today</p>
                                <span class="stat-trend down" title="Employee who are absent today">
                                    <i class="fas fa-arrow-down me-1"></i>
                                    {{ $absentToday ?? 0 }} Absent
                                </span>
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
                                        <i class="fas fa-check-circle me-1"></i>
                                        All Clear
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
                                <span class="badge-primary-soft mt-2">This Week</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Company Announcements ── --}}
            <div class="col-lg-12 col-12 mb-4 px-0">
                <div class="announcements-card">
                    <div class="announcements-header">
                        <h5>
                            <i class="fas fa-bullhorn me-2"></i>
                            Company Announcements
                        </h5>
                    </div>
                    <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                        @forelse($announcements as $a)
                            <div class="announcement-item" data-toggle="modal" data-target="#previewModal"
                                data-title="{{ $a->title }}"
                                data-content="{{ str_replace('&nbsp;', ' ', $a->content) }}"
                                data-publish="{{ \Carbon\Carbon::parse($a->publish_date)->format('d M Y') }}"
                                data-end="{{ \Carbon\Carbon::parse($a->end_date)->format('d M Y') }}"
                                data-employee="{{ $a->user->Employee->department->department_name ?? 'Unknown' }}">
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
                            <div class="p-3 text-center text-muted">No announcements found.</div>
                        @endforelse
                    </div>
                    <div class="card-footer bg-light text-center"></div>
                </div>
            </div>

            {{-- ── Main Content Grid ── --}}
            <div class="row">

                {{-- ════════════════════════════════════════
                     Overtime Assignment (replaced Pending Approvals)
                     ════════════════════════════════════════ --}}
                <div class="col-lg-8 col-12 mb-4">
                    <div class="overtime-assignment-card">
                        <div class="card-header">
                            <h4 class="mb-0">
                                <i class="fas fa-business-time me-2"></i>
                                Overtime Assignment
                            </h4>
                            <button class="btn btn-primary btn-sm"
                                data-toggle="modal" data-target="#modalAddOvertime">
                                <i class="fas fa-plus me-1"></i> Add Overtime
                            </button>
                        </div>
                        <div class="card-body p-0">
                            @forelse($submissions ?? [] as $submission)
                                <div class="overtime-item">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-3">
                                            <img class="rounded-circle" width="42" height="42"
                                                src="{{ asset('img/avatar/avatar-' . rand(1, 4) . '.png') }}"
                                                alt="{{ $submission->employee->employee_name ?? 'Employee' }}">
                                            <div>
                                                <h6 class="mb-0 font-weight-bold">
                                                    {{ $submission->employees->employee_name ?? 'Unknown' }}
                                                </h6>
                                                <div class="overtime-meta">
                                                    <span>
                                                        <i class="fas fa-calendar me-1"></i>
                                                        {{ \Carbon\Carbon::parse($submission->leave_date_from ?? now())->format('d M Y') }}
                                                    </span>
                                                    <span>
                                                        <i class="fas fa-clock me-1"></i>
                                                        {{ $submission->created_at->diffForHumans() }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="approval-type overtime">
                                            {{ $submission->type ?? 'Overtime' }}
                                        </span>
                                    </div>
                                    @if ($submission->notes)
                                        <p class="text-muted mt-2 mb-0" style="font-size: 0.85rem;">
                                            <i class="fas fa-comment-alt me-1"></i>
                                            {{ $submission->notes }}
                                        </p>
                                    @endif
                                </div>
                            @empty
                                <div class="empty-state">
                                    <i class="fas fa-business-time"></i>
                                    <h6>No Overtime Records</h6>
                                    <p>Click "Add Overtime" to assign overtime for your team.</p>
                                </div>
                            @endforelse
                        </div>
                        <div class="card-footer bg-light text-center">
                            <a href="{{ route('toil.assignment.index') }}" class="text-decoration-none">
                                View All Overtime Assignments
                                <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- ── Sidebar ── --}}
                <div class="col-lg-4 col-12">

                    {{-- Quick Actions --}}
                    <div class="quick-actions-card mb-4">
                        <h5>
                            <i class="fas fa-bolt me-2"></i>
                            Quick Actions
                        </h5>
                        <div class="action-grid">
                            <a href="{{ route('pages.Team') }}" class="action-btn">
                                <i class="fas fa-users"></i>
                                <span>My Team</span>
                            </a>
                            <a href="#" class="action-btn" id="requestLeaveBtn">
                                <i class="fas fa-umbrella-beach"></i>
                                <span>Request Leave</span>
                            </a>
                            <a href="#" class="action-btn">
                                <i class="fas fa-chart-line"></i>
                                <span>Coming Soon</span>
                            </a>
                            <a href="#" class="action-btn">
                                <i class="fas fa-calendar"></i>
                                <span>Coming Soon</span>
                            </a>
                        </div>
                    </div>

                    {{-- Recent Activities --}}
                    <div class="activities-card">
                        <div class="card-header">
                            <h4>
                                <i class="fas fa-history me-2"></i>
                                Recent Activities
                            </h4>
                        </div>
                        <div class="activity-timeline">
                            @forelse($recentActivities ?? [] as $activity)
                                <div class="activity-item">
                                    <div class="activity-icon {{ $activity->type ?? 'info' }}">
                                        <i class="fas fa-{{ $activity->icon ?? 'bell' }}"></i>
                                    </div>
                                    <div class="activity-content">
                                        <h6>{{ $activity->title ?? 'Activity' }}</h6>
                                        <p>{{ $activity->description ?? 'No description' }}</p>
                                        <div class="activity-time">
                                            {{ $activity->created_at->diffForHumans() ?? 'Recently' }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state py-4">
                                    <i class="fas fa-history"></i>
                                    <h6>No Recent Activities</h6>
                                    <p>Activities will appear here</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Team Overview & Performance ── --}}
            <div class="row mt-4">
                <div class="col-lg-8 col-12 mb-4">
                    <div class="performance-card">
                        <div class="card-header">
                            <h4>
                                <i class="fas fa-chart-bar me-2"></i>
                                Team Performance
                            </h4>
                            <div class="chart-filters">
                                <button class="filter-btn active" data-period="week">Week</button>
                                <button class="filter-btn" data-period="month">Month</button>
                                <button class="filter-btn" data-period="year">Year</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="performanceChart" height="280"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-12 mb-4">
                    <div class="team-overview-card">
                        <div class="card-header">
                            <h4>
                                <i class="fas fa-users-cog me-2"></i>
                                Team Status
                            </h4>
                        </div>
                        <div class="card-body p-0">
                            @forelse($teamMembers ?? [] as $member)
                                <div class="team-member-item">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-3">
                                            <img class="member-avatar"
                                                src="{{ asset('img/avatar/avatar-' . rand(1, 4) . '.png') }}"
                                                alt="{{ $member->employee_name ?? 'Employee' }}">
                                            <div class="member-info">
                                                <h6>{{ $member->employee_name ?? 'Unknown' }}</h6>
                                                <small>{{ $member->position ?? 'Employee' }}</small>
                                            </div>
                                        </div>
                                        <span class="status-badge {{ strtolower($member->status ?? 'present') }}">
                                            {{ $member->status ?? 'Present' }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <i class="fas fa-users"></i>
                                    <h6>No Team Members</h6>
                                    <p>Your team members will appear here</p>
                                </div>
                            @endforelse
                        </div>
                        @if (count($teamMembers ?? []) > 0)
                            <div class="card-footer bg-light text-center">
                                <a href="{{ route('pages.Employee') }}" class="text-decoration-none">
                                    View All Team Members
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </section>
    </div>

    {{-- ════════════════════════════════════════
         Modal: Add Overtime (baru)
         ════════════════════════════════════════ --}}
    <div class="modal fade" id="modalAddOvertime" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="formOvertimeDashboard">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-business-time me-2"></i> Assign Overtime
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        {{-- Pilih Karyawan --}}
                        <div class="form-group">
                            <label class="form-label">
                                Select Employee(s) <span class="text-danger">*</span>
                            </label>
                           <select name="employee_ids[]" id="ot_employee_ids" multiple required>
                        {{-- diisi via AJAX saat modal dibuka --}}
                        </select>
                            <small class="text-muted" id="ot-employee-loading">Loading employees...</small>
                            <small class="text-muted">Can select more than 1 employee.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">
                                        Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control" name="date" id="ot_date" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Start Time</label>
                                    <input type="time" class="form-control" name="start_time" id="ot_start_time">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">End Time</label>
                                    <input type="time" class="form-control" name="end_time" id="ot_end_time">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        Total Hours <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" name="total_hours" id="ot_total_hours"
                                        step="0.5" min="0.5" max="24" required>
                                    <small class="text-muted">0.5 - 24 hours</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        Compensation Type <span class="text-danger">*</span>
                                    </label>
                                    <select name="compensation_type" id="ot_compensation_type" required>
                                        <option value="">-- Choose Type --</option>
                                        <option value="Cash">Cash (Auto to Payroll)</option>
                                        <option value="Toil">Toil Leave (Holiday Balance)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Reason / Notes <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" name="reason" id="ot_reason" rows="2"
                                required minlength="10" maxlength="1000"
                                placeholder="Min 10 characters..."></textarea>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Submit Assignment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         Modal: Create Submission (existing, kept intact)
         ════════════════════════════════════════ --}}
    <div class="modal fade" id="createSubmissionModal" tabindex="-1" aria-labelledby="createSubmissionLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('Submissions.store') }}" method="POST" id="submissionForm">
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
                        <div class="mb-4">
                            <label class="form-label" for="type">
                                <i class="fas fa-clipboard-list me-1"></i> Type
                            </label>
                            <select name="type" id="type"
                                class="form-control select2 @error('type') is-invalid @enderror" required>
                                <option value="">Choose submission type</option>
                                @foreach ($types ?? [] as $value)
                                    <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4" id="overtimeTypeDiv" style="display: none;">
                            <label class="form-label" for="overtime_type">
                                <i class="fas fa-clock me-1"></i> Overtime Type
                            </label>
                            <select name="overtime_type" id="overtime_type" class="form-control select2">
                                <option value="">Choose overtime type</option>
                                <option value="Weekday">Weekday</option>
                                <option value="Weekend">Weekend</option>
                                <option value="Holiday">Holiday</option>
                            </select>
                        </div>

                        <div class="mb-4" id="annualLeaveInfo" style="display: none;">
                            <label class="form-label">
                                <i class="fas fa-info-circle me-1"></i> Annual Leave Balance
                            </label>
                            <div class="alert alert-info d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Total:</strong>
                                    <span id="totalLeave">{{ $leaveBalance->total ?? 0 }}</span> days
                                </div>
                                <div>
                                    <strong>Used:</strong>
                                    <span id="usedLeave">{{ $leaveBalance->used ?? 0 }}</span> days
                                </div>
                                <div>
                                    <strong>Remaining:</strong>
                                    <span id="remainingLeave">{{ $leaveBalance->remaining ?? 0 }}</span> days
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="leave_date_from">
                                    <i class="fas fa-calendar-alt me-1"></i> Start Date
                                </label>
                                <input type="date" name="leave_date_from" id="leave_date_from"
                                    class="form-control @error('leave_date_from') is-invalid @enderror" required>
                                @error('leave_date_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="leave_date_to">
                                    <i class="fas fa-calendar-check me-1"></i> End Date
                                </label>
                                <input type="date" name="leave_date_to" id="leave_date_to"
                                    class="form-control @error('leave_date_to') is-invalid @enderror" required>
                                @error('leave_date_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="notes">
                                <i class="fas fa-sticky-note me-1"></i> Notes / Reason
                            </label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror"
                                rows="4" placeholder="Please provide details about your request..." required></textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-light border" id="durationDisplay" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-calendar-day me-2"></i><strong>Duration:</strong></span>
                                <span id="durationText" class="text-primary font-weight-bold">0 days</span>
                            </div>
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

    {{-- ════════════════════════════════════════
         Modal: Approval Action (existing, kept intact)
         ════════════════════════════════════════ --}}
    <div class="modal fade" id="approvalActionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="approvalForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="approvalActionTitle">
                            <i class="fas fa-check-circle me-2"></i>
                            Confirm Action
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="approvalActionMessage">Are you sure you want to proceed with this action?</p>
                        <div id="rejectionReasonDiv" style="display: none;">
                            <label class="form-label" for="rejection_reason">
                                <i class="fas fa-comment me-1"></i> Reason for Rejection
                            </label>
                            <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3"
                                placeholder="Please provide a reason..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn" id="confirmActionBtn">
                            <i class="fas fa-check me-1"></i> Confirm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         Modal: Announcement Preview (existing, kept intact)
         ════════════════════════════════════════ --}}
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
                    <div id="previewContent" style="max-height: 400px; overflow-y: auto; line-height: 1.8;"></div>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Announcement preview
            $('.announcement-item').on('click', function () {
                $('#previewTitle').text($(this).data('title'));
                $('#previewContent').html($(this).data('content'));
                $('#previewDate').text($(this).data('publish'));
                $('#previewEndDate').text($(this).data('end'));
                $('#previewEmployee').text($(this).data('employee'));
            });
        });
    </script>

    <script>
        $(document).ready(function () {

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            // ── Select2: existing submission form ──
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            // ── Select2: overtime modal — multi employee ──
            $('#ot_employee_ids').select2({
                placeholder: 'Select one or more employees...',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modalAddOvertime')
            });

            // ── Select2: overtime modal — compensation type ──
            $('#ot_compensation_type').select2({
                placeholder: '-- Choose Type --',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modalAddOvertime')
            });

            // ── Load subordinates via AJAX saat modal dibuka ──
            $('#modalAddOvertime').on('show.bs.modal', function () {
            $('#ot-employee-loading').text('Loading employees...').show();

            $.ajax({
                url:    '{{ route('toil.assignment.subordinates') }}',
                method: 'GET',
                success: function (res) {
                var $select = $('#ot_employee_ids');
                $select.empty();

            if (!res.data || res.data.length === 0) {
                $('#ot-employee-loading').text('No subordinates found in your structure.');
                return;
            }

            res.data.forEach(function (emp) {
                $select.append(new Option(
                    emp.employee_name + ' (' + emp.pin + ')',
                    emp.id
                ));
            });

            $('#ot-employee-loading').hide();
        },
        error: function () {
            $('#ot-employee-loading').text('Failed to load employees.');
        }
    });
});

            // ── Set min date = today for overtime form ──
            $('#ot_date').attr('min', new Date().toISOString().split('T')[0]);

            // ── Auto hitung Total Hours dari Start & End Time ──
            $('#ot_start_time, #ot_end_time').on('change', function () {
                var start = $('#ot_start_time').val();
                var end   = $('#ot_end_time').val();

            if (start && end) {
                var startParts = start.split(':');
                var endParts   = end.split(':');

                var startMinutes = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
                var endMinutes   = parseInt(endParts[0])   * 60 + parseInt(endParts[1]);

            // Handle overnight (misal 22:00 - 02:00)
                if (endMinutes < startMinutes) {
                endMinutes += 24 * 60;
            }

        var diffMinutes = endMinutes - startMinutes;
        var diffHours   = diffMinutes / 60;

        // Bulatkan ke kelipatan 0.5
        diffHours = Math.round(diffHours * 2) / 2;

        if (diffHours > 0 && diffHours <= 24) {
            $('#ot_total_hours').val(diffHours);
        } else {
            $('#ot_total_hours').val('');
        }
    }
});

            // ── Submit overtime from dashboard modal ──
            $('#formOvertimeDashboard').on('submit', function (e) {
            e.preventDefault();

            // ── Loading state ──
            var $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

            var formData = {
            employee_ids:      $('#ot_employee_ids').val(),
            date:              $('#ot_date').val(),
            start_time:        $('#ot_start_time').val() || null,
            end_time:          $('#ot_end_time').val() || null,
            total_hours:       $('#ot_total_hours').val(),
            compensation_type: $('#ot_compensation_type').val(),
            reason:            $('#ot_reason').val()
        };

        $.ajax({
        url:    '{{ route('toil.assignment.store') }}',
        method: 'POST',
        data:   formData,
        success: function (res) {
            Swal.fire({ icon: 'success', title: 'Success', text: res.message });
            $('#modalAddOvertime').modal('hide');
            $('#formOvertimeDashboard')[0].reset();
            $('#ot_employee_ids').val(null).trigger('change');
            $('#ot_compensation_type').val('').trigger('change');
        },
        error: function (xhr) {
            var msg = 'An error occurred';
            if (xhr.responseJSON) {
                msg = xhr.responseJSON.message
                    || Object.values(xhr.responseJSON.errors ?? {}).flat().join('<br>');
            }
            Swal.fire({ icon: 'error', title: 'Failed', html: msg });
        },
            complete: function () {
                // ── Restore button ──
                $btn.prop('disabled', false)
                    .html('<i class="fas fa-paper-plane me-1"></i> Submit Assignment');
            }
        });
    });

            // ── Reset Select2 when overtime modal closes ──
            $('#modalAddOvertime').on('hidden.bs.modal', function () {
                $('#formOvertimeDashboard')[0].reset();
                $('#ot_employee_ids').val(null).trigger('change');
                $('#ot_compensation_type').val('').trigger('change');
            });

        });
    </script>

    <script>
        @if (session('success'))
            Swal.fire({ icon: 'success', title: 'Success', text: '{{ session('success') }}' });
        @endif
        @if (session('error'))
            Swal.fire({ title: 'Failed!', text: "{{ session('error') }}", icon: 'error', confirmButtonText: 'OK' });
        @endif
    </script>
@endpush