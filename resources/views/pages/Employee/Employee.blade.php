@extends('layouts.app')
@section('title', 'Employees')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

    <style>
        /* ─── Page header ────────────────────────────────────── */
        .section-header h1 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 1.25rem;
        }

        .page-actions {
            display: flex;
            gap: 8px;
        }

        .page-actions .btn {
            height: 36px;
            font-size: .825rem;
            padding: 0 1rem;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border-radius: .5rem;
        }

        /* ─── Stat cards row ─────────────────────────────────── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 1.25rem;
        }

        .stat-card {
            background: #fff;
            border: 1px solid #f1f5f9;
            border-radius: .625rem;
            padding: 14px 16px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .stat-card-label {
            font-size: .68rem;
            font-weight: 700;
            /* text-transform: uppercase; */
            letter-spacing: .7px;
            color: #94a3b8;
            margin-bottom: 6px;
        }

        .stat-card-value {
            font-size: 1.5rem;
            font-weight: 600;
            line-height: 1;
            color: #1e293b;
        }

        .stat-card-value.green {
            color: #166534;
        }

        .stat-card-value.amber {
            color: #92400e;
        }

        .stat-card-value.red {
            color: #991b1b;
        }

        .stat-card-sub {
            font-size: .7rem;
            color: #94a3b8;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stat-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
        }

        /* ─── Card shell ─────────────────────────────────────── */
        .emp-card {
            border: none;
            border-radius: .625rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .07);
            background: #fff;
            overflow: hidden;
            margin-bottom: 1.25rem;
        }

        .emp-card-header {
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            padding: .875rem 1.25rem;
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .emp-card-header-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            flex-shrink: 0;
        }

        .emp-card-header-icon.blue {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .emp-card-header-icon.green {
            background: #f0fdf4;
            color: #16a34a;
        }

        .emp-card-header-title {
            font-size: .9rem;
            font-weight: 600;
            color: #334155;
            flex: 1;
        }

        .emp-card-header-count {
            font-size: .72rem;
            color: #64748b;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: .15rem .7rem;
        }

        /* ─── Toolbar ────────────────────────────────────────── */
        .dt-toolbar {
            padding: .75rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            background: #fafafa;
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .dt-toolbar .btn {
            height: 32px;
            font-size: .775rem;
            padding: 0 .75rem;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border-radius: .4rem;
        }

        /* ─── Table ──────────────────────────────────────────── */
        #employees-table,
        #activityTable {
            width: 100% !important;
            font-size: .8rem;
        }

        #employees-table thead th,
        #activityTable thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: .68rem;
            font-weight: 700;
            /* text-transform: uppercase; */
            letter-spacing: .5px;
            padding: .7rem .9rem;
            border: none;
            border-bottom: 1px solid #f1f5f9;
            white-space: nowrap;
        }

        #employees-table tbody td,
        #activityTable tbody td {
            padding: .75rem .9rem;
            vertical-align: middle;
            border: none;
            border-bottom: 1px solid #f8fafc;
            color: #334155;
        }

        #employees-table tbody tr:last-child td,
        #activityTable tbody tr:last-child td {
            border-bottom: none;
        }

        #employees-table tbody tr:hover td,
        #activityTable tbody tr:hover td {
            background: #f8fafc;
        }

        /* employee name cell */
        .emp-cell {
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .emp-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            font-size: .65rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .emp-avatar-name {
            font-weight: 600;
            font-size: .8rem;
            line-height: 1.2;
        }

        .emp-avatar-nip {
            font-size: .7rem;
            color: #94a3b8;
        }

        /* status badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: .18rem .6rem;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-active {
            background: #f0fdf4;
            color: #166534;
        }

        .badge-leave {
            background: #fffbeb;
            color: #92400e;
        }

        .badge-resign {
            background: #fef2f2;
            color: #991b1b;
        }

        .badge-mutation {
            background: #eff6ff;
            color: #1e40af;
        }

        .badge-pending {
            background: #f8fafc;
            color: #475569;
        }

        .badge-permanent {
            background: #eff6ff;
            color: #1e40af;
        }

        .badge-contract {
            background: #fffbeb;
            color: #92400e;
        }

        .badge-internship {
            background: #fdf4ff;
            color: #6b21a8;
        }

        /* action buttons */
        .action-wrap {
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .act-btn {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #64748b;
            font-size: .75rem;
            text-decoration: none;
            transition: all .15s;
        }

        .act-btn:hover {
            background: #f8fafc;
            color: #1e293b;
        }

        .act-btn.act-danger {
            border-color: #fecaca;
            background: #fef2f2;
            color: #dc2626;
        }

        .act-btn.act-danger:hover {
            background: #fee2e2;
        }

        /* ─── Hint bar ───────────────────────────────────────── */
        .hint-bar {
            padding: .65rem 1.25rem;
            background: #fafafa;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            flex-wrap: wrap;
        }

        .hint-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: .72rem;
            color: #94a3b8;
        }

        /* ─── DataTables overrides ───────────────────────────── */
        div.dataTables_wrapper div.dataTables_filter input,
        div.dataTables_wrapper div.dataTables_length select {
            height: 32px;
            font-size: .775rem;
            border: 1px solid #e2e8f0;
            border-radius: .4rem;
        }

        div.dataTables_wrapper div.dataTables_info {
            font-size: .75rem;
            color: #64748b;
            padding-top: .5rem;
        }

        div.dataTables_wrapper div.dataTables_paginate {
            padding-top: .3rem;
        }

        div.dataTables_wrapper div.dataTables_paginate .paginate_button {
            font-size: .75rem;
            border-radius: .375rem !important;
            padding: .2rem .5rem;
        }

        .dataTables_wrapper {
            padding: .75rem 1.25rem 1rem;
        }

        .dt-buttons .btn {
            height: 32px;
            font-size: .775rem;
            padding: 0 .75rem;
        }

        .dt-filter-bar .select2-container {
            min-width: 140px;
            flex: 1 1 140px;
        }

        .select2-container--default .select2-selection--single {
            height: 32px !important;
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection__rendered {
            font-size: .775rem;
        }

        /* ─── Responsive ─────────────────────────────────────── */
        @media (max-width: 768px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 480px) {
            .stats-row {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">

            {{-- ── Page Header ── --}}
            <div class="section-header">
                <div>
                    <div style="font-size:.72rem;color:#94a3b8;margin-bottom:3px">
                        Dashboard / <span style="color:#64748b">Employees</span>
                    </div>
                    <h1>Employees</h1>
                </div>
                <div class="page-actions">
                    <a href="{{ route('Employee.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create employee
                    </a>
                    <a href="{{ route('pages.Employeeall') }}" class="btn btn-success">
                        <i class="fas fa-users"></i> Employee Details
                    </a>
                </div>
            </div>

            <div class="section-body">

                {{-- ── Stat Cards ── --}}
                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-card-label">Total employees</div>
                        <div class="stat-card-value" id="stat-total">–</div>
                        <div class="stat-card-sub">
                            <span class="stat-dot" style="background:#1d4ed8"></span> All companies
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-label">Active</div>
                        <div class="stat-card-value green" id="stat-active">{{ $countactives }}</div>
                        <div class="stat-card-sub">
                            <span class="stat-dot" style="background:#16a34a"></span> Currently active
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-label">Pending</div>
                        <div class="stat-card-value amber" id="stat-leave">{{ $countpendings }}</div>
                        <div class="stat-card-sub">
                            <span class="stat-dot" style="background:#d97706"></span> Employee Pending
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-label">Resigned</div>
                        <div class="stat-card-value red" id="stat-resign">{{ $countresigns }}</div>
                        <div class="stat-card-sub">
                            <span class="stat-dot" style="background:#dc2626"></span> No longer active
                        </div>
                    </div>
                </div>

                {{-- ── Employee Table Card ── --}}
                <div class="emp-card">
                    <div class="emp-card-header">
                        <div class="emp-card-header-icon blue">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="emp-card-header-title">List employees</span>
                        <span class="emp-card-header-count" id="emp-count">Loading...</span>

                    </div>
                    {{-- ── Filter Bar ── --}}
                    <div class="dt-filter-bar"
                        style="
    padding: .75rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
    background: #fafafa;
    display: flex;
    align-items: center;
    gap: .5rem;
    flex-wrap: wrap;
">
                        <select id="filter-company" class="select2 form-select form-select-sm"
                            style="height:32px;font-size:.775rem;border:1px solid #e2e8f0;border-radius:.4rem;min-width:125px;">
                            <option value="">All Companies</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company }}">{{ $company }}</option>
                            @endforeach
                        </select>

                        <select id="filter-department" class="select2 form-select form-select-sm"
                            style="height:32px;font-size:.775rem;border:1px solid #e2e8f0;border-radius:.4rem;min-width:125px;">
                            <option value="">All Departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department }}">{{ $department }}</option>
                            @endforeach
                        </select>
                        <select id="filter-store" class="select2 form-select form-select-sm"
                            style="height:32px;font-size:.775rem;border:1px solid #e2e8f0;border-radius:.4rem;min-width:125px;">
                            <option value="">All Locations</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location }}">{{ $location }}</option>
                            @endforeach
                        </select>

                        <select id="filter-emp-status" class="select2 form-select form-select-sm"
                            style="height:32px;font-size:.775rem;border:1px solid #e2e8f0;border-radius:.4rem;min-width:125px;">
                            <option value="">All Emp. Status</option>
                            @foreach ($employeestatuses as $employeestatuse)
                                <option value="{{ $employeestatuse }}">{{ $employeestatuse }}</option>
                            @endforeach

                        </select>
                        <select id="filter-grading" class="select2 form-select form-select-sm"
                            style="height:32px;font-size:.775rem;border:1px solid #e2e8f0;border-radius:.4rem;min-width:125px;">
                            <option value="">All Grd</option>
                            @foreach ($gradings as $grading)
                                <option value="{{ $grading }}">{{ $grading }}</option>
                            @endforeach

                        </select>
                        <select id="filter-group" class="select2 form-select form-select-sm"
                            style="height:32px;font-size:.775rem;border:1px solid #e2e8f0;border-radius:.4rem;min-width:125px;">
                            <option value="">All Groups</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group }}">{{ $group }}</option>
                            @endforeach

                        </select>

                        <select id="filter-los" class="select2 form-select form-select-sm"
                            style="height:32px;font-size:.775rem;border:1px solid #e2e8f0;border-radius:.4rem;min-width:125px;">
                            <option value="">All LOS</option>
                            <option value="under3months">Under 3 Months</option>
                            <option value="1year">1+ Year</option>
                            <option value="3years">3+ Years</option>
                            <option value="5years">5+ Years</option>

                        </select>

                        <select id="filter-status" class="select2 form-select form-select-sm"
                            style="height:32px;font-size:.775rem;border:1px solid #e2e8f0;border-radius:.4rem;min-width:125px;">
                            <option value="">All Status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>

                        <button id="btn-reset-filter" class="btn btn-sm btn-light"
                            style="height:32px;font-size:.775rem;padding:0 .75rem;display:inline-flex;align-items:center;gap:.35rem;border-radius:.4rem;border:1px solid #e2e8f0">
                            <i class="fas fa-rotate-left"></i> Reset
                        </button>
                        <!-- Tombol Export, filter ikut terbawa via URL -->
                        <a id="btn-export-excel" href="{{ route('Employee.export') }}" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                        <a id="btn-export-csv" href="{{ route('Employee.export') }}?type=csv" class="btn btn-info">
                            <i class="fas fa-file-csv"></i> Export CSV
                        </a>
                    </div>
                    <div class="table-responsive">

                        <table id="employees-table" class="table">
                            <thead>
                                <tr>
                                    <th class="text-center">Employee</th>
                                    <th class="text-center">NIP</th>
                                    <th class="text-center">Company</th>
                                    <th class="text-center">Department</th>
                                    <th class="text-center">Location</th>
                                    <th class="text-center">Position</th>
                                    <th class="text-center">Grade</th>
                                    <th class="text-center">Group</th>
                                    <th class="text-center">Emp. status</th>
                                    <th class="text-center">LOS</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            {{-- tbody filled by DataTables --}}
                        </table>
                    </div>

                    <div class="hint-bar">
                        <div class="hint-item">
                            <i class="fas fa-user-edit text-secondary"></i> Click edit to modify employee data
                        </div>
                        <div class="hint-item">
                            <i class="fas fa-eye text-secondary"></i>Click edit to show employee data
                        </div>

                    </div>
                </div>

                {{-- ── Activity History Card ── --}}
                <div class="emp-card">
                    <div class="emp-card-header">
                        <div class="emp-card-header-icon green">
                            <i class="fas fa-clock-rotate-left"></i>
                        </div>
                        <span class="emp-card-header-title">Employee activity history</span>
                    </div>

                    <div class="table-responsive">
                        <table id="activityTable" class="table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:50px">No.</th>
                                    <th>Description</th>
                                    <th class="text-center">By</th>
                                    <th class="text-center">Date</th>
                                </tr>
                            </thead>
                            {{-- tbody filled by DataTables --}}
                        </table>
                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $('.select2').select2({
            width: 'resolve' // atau bisa dihapus saja
        });
    </script>
    <script>
        /* ── Helpers ── */
        const AVATAR_COLORS = [{
                bg: '#eff6ff',
                color: '#1e40af'
            },
            {
                bg: '#f5f3ff',
                color: '#5b21b6'
            },
            {
                bg: '#f0fdf4',
                color: '#166534'
            },
            {
                bg: '#fffbeb',
                color: '#92400e'
            },
            {
                bg: '#fdf2f8',
                color: '#9d174d'
            },
        ];

        function avatarStyle(name, idx) {
            const c = AVATAR_COLORS[idx % AVATAR_COLORS.length];
            return `background:${c.bg};color:${c.color}`;
        }

        function initials(name) {
            if (!name) return '?';
            return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
        }

        const STATUS_BADGE = {
            'Active': 'badge-active',
            'On leave': 'badge-leave',
            'Resign': 'badge-resign',
            'Mutation': 'badge-mutation',
            'Pending': 'badge-pending',
        };

        const EMP_STATUS_BADGE = {
            'PKWT': 'badge-permanent',
            'On Job Training': 'badge-contract',
            'DW': 'badge-internship',
        };

        function statusBadge(val, map) {
            if (!val) return '-';
            const cls = map[val] || 'badge-pending';
            return `<span class="status-badge ${cls}">${val}</span>`;
        }

        /* ── Employee DataTable ── */
        $(function() {
            var empTable = $('#employees-table').DataTable({

                processing: true,
                serverSide: true,
                autoWidth: false,
                dom: "<'row align-items-center mb-2'<'col-sm-6'l><'col-sm-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
                ajax: {
                    url: '{{ route('employees.employees') }}',
                    data: function(d) {
                        d.filter_company = $('#filter-company').val();
                        d.filter_department = $('#filter-department').val();
                        d.filter_emp_status = $('#filter-emp-status').val();
                        d.filter_status = $('#filter-status').val();
                        d.filter_store = $('#filter-store').val();
                        d.filter_los = $('#filter-los').val();
                        d.filter_group = $('#filter-group').val();
                        d.filter_grading = $('#filter-grading').val();
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load employee data.'
                        });
                    }
                },
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                pageLength: 10,
                language: {
                    lengthMenu: 'Show _MENU_',
                    search: '',
                    searchPlaceholder: 'Search employee...',
                    info: 'Showing _START_–_END_ of _TOTAL_',
                    infoEmpty: 'No entries found',
                    infoFiltered: '(filtered from _MAX_ total)',
                    paginate: {
                        previous: '‹',
                        next: '›'
                    }
                },
                columns: [
                    /* 0 — Employee (name + NIP merged) */
                    // {
                    //     data: 'employee_name',
                    //     render: function(data, type, row, meta) {
                    //         const ini = initials(data);
                    //         const sty = avatarStyle(data, meta.row);
                    //         return `<div class="emp-cell">
                    //         <div class="emp-avatar" style="${sty}">${ini}</div>
                    //         <div>
                    //             <div class="emp-avatar-name">${data || '-'}</div>
                    //             <div class="emp-avatar-nip">NIP : ${row.employee_pengenal || ''}</div>
                    //         </div>
                    //     </div>`;
                    //     }
                    // },
                    {
                        data: 'employee_name',
                        className: 'text-center',
                        render: d => d || '-'
                    },
                    {
                        data: 'employee_pengenal',
                        className: 'text-center',
                        render: d => d || '-'
                    },
                    {
                        data: 'name_company',
                        className: 'text-center',
                        render: d => d || '-'
                    },
                    {
                        data: 'department_name',
                        className: 'text-center',
                        render: d => d || '-'
                    },
                    {
                        data: 'name',
                        className: 'text-center',
                        render: d => d || '-'
                    },
                    {
                        data: 'position_name',
                        className: 'text-center',
                        render: d => d || '-'
                    },
                    {
                        data: 'grading_name',
                        className: 'text-center',
                        render: d => d ? `<strong>${d}</strong>` : '-'
                    },
                    {
                        data: 'remark',
                        className: 'text-center',
                        render: d => d ? `<strong>${d}</strong>` : '-'
                    },
                    {
                        data: 'status_employee',
                        className: 'text-center',
                        render: d => statusBadge(d, EMP_STATUS_BADGE)
                    },
                    {
                        data: 'length_of_service',
                        className: 'text-center',
                        render: d => d ? `<span style="color:#64748b;font-size:.775rem">${d}</span>` :
                            '-'
                    },
                    {
                        data: 'status',
                        className: 'text-center',
                        render: d => statusBadge(d, STATUS_BADGE)
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        /* action HTML is returned from controller — we just ensure it gets proper classes */
                        render: function(data) {
                            return `<div class="action-wrap">${data}</div>`;
                        }
                    }
                ],
                drawCallback: function(settings) {
                    const info = settings.json;
                    if (info && info.recordsTotal !== undefined) {
                        $('#emp-count').text(info.recordsTotal + ' records');
                        $('#stat-total').text(info.recordsTotal);
                    }
                    if (info && info.stats) {
                        $('#stat-active').text(info.stats.active ?? '–');
                        $('#stat-leave').text(info.stats.on_leave ?? '–');
                        $('#stat-resign').text(info.stats.resign ?? '–');
                    }
                },
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control form-control-sm');
                    $('.dataTables_length select').addClass('form-select form-select-sm');
                }
            });
            $('#filter-company, #filter-department, #filter-emp-status, #filter-status, #filter-los, #filter-store, #filter-grading, #filter-group')
                .on('change', function() {
                    empTable.ajax.reload();
                    updateExportLinks(); // ← tambahkan di sini
                });

            /* ── Reset filter ── */
            $('#btn-reset-filter').on('click', function() {

                $('#filter-company').val('').trigger('change');
                $('#filter-department').val('').trigger('change');
                $('#filter-emp-status').val('').trigger('change');
                $('#filter-status').val('').trigger('change');
                $('#filter-store').val('').trigger('change');
                $('#filter-los').val('').trigger('change');
                $('#filter-group').val('').trigger('change');
                $('#filter-grading').val('').trigger('change');
                empTable.ajax.reload();
            });
            // Setiap kali filter berubah, update href tombol export
            function updateExportLinks() {
                const params = new URLSearchParams({
                    filter_company: $('#filter-company').val(),
                    filter_department: $('#filter-department').val(),
                    filter_group: $('#filter-group').val(),
                    filter_grading: $('#filter-grading').val(),
                    filter_store: $('#filter-store').val(),
                    filter_emp_status: $('#filter-emp-status').val(),
                    filter_status: $('#filter-status').val(),
                    filter_los: $('#filter-los').val(),
                });

                const baseUrl = "{{ route('Employee.export') }}";
                $('#btn-export-excel').attr('href', `${baseUrl}?${params}`);
                $('#btn-export-csv').attr('href', `${baseUrl}?${params}&type=csv`);
            }

            // Panggil setiap filter berubah
            updateExportLinks();

            /* ── Activity DataTable ── */
            $('#activityTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('data.data') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: d => `<span style="color:#94a3b8;font-size:.775rem">${d}</span>`
                    },
                    {
                        data: 'description',
                        name: 'description',
                        render: d => `<span style="font-size:.8rem">${d || '-'}</span>`
                    },
                    {
                        data: 'causer',
                        name: 'causer',
                        className: 'text-center',
                        render: function(d) {
                            if (!d) return '-';
                            const ini = initials(d);
                            const sty = avatarStyle(d, 1);
                            return `<div class="emp-cell" style="justify-content:center;gap:6px">
                            <div class="emp-avatar" style="${sty};width:24px;height:24px;font-size:.6rem">${ini}</div>
                            <span style="font-size:.775rem">${d}</span>
                        </div>`;
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        className: 'text-center',
                        render: d => d ? `<span style="font-size:.75rem;color:#64748b">${d}</span>` :
                            '-'
                    }
                ],
                order: [
                    [3, 'desc']
                ],
                language: {
                    searchPlaceholder: 'Search activity...',
                    sSearch: '',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_–_END_ of _TOTAL_',
                    paginate: {
                        previous: '‹',
                        next: '›'
                    }
                },
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control form-control-sm');
                    $('.dataTables_length select').addClass('form-select form-select-sm');
                }
            });

            /* ── Session flash ── */
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
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
        });
    </script>
@endpush
