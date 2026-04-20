{{-- @extends('layouts.app')
@section('title', 'Employees')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
@endpush
<style>
    .card {
        border: none;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.08);
        border-radius: 0.5rem;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        background-color: #fff;
    }
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.12);
    }
    .card-header {
        background-color: #f8fafc;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        padding: 1.25rem 1.5rem;
    }
    .card-header h6 {
        margin: 0;
        font-weight: 600;
        color: #4a5568;
        display: flex;
        align-items: center;
        font-size: 0.95rem;
    }
    .card-header h6 i {
        margin-right: 0.75rem;
        color: #5e72e4;
        transition: color 0.3s ease;
    }
    .table-responsive {
        padding: 0 1.5rem;
        overflow: hidden;
    }
    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .table thead th {
        background-color: #f8fafc;
        color: #4a5568;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 1rem 0.75rem;
        position: sticky;
        top: 0;
        z-index: 10;
        transition: all 0.3s ease;
    }
    .table tbody tr {
        transition: all 0.25s ease;
        position: relative;
    }
    .table tbody tr:not(:last-child)::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: rgba(0, 0, 0, 0.05);
    }
    .table tbody tr:hover {
        background-color: rgba(94, 114, 228, 0.03);
        transform: scale(1.002);
    }
    .table tbody td {
        padding: 1.1rem 0.75rem;
        vertical-align: middle;
        color: #4a5568;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        border: none;
        background: #fff;
    }
    .table tbody tr:hover td {
        color: #2d3748;
    }
    .text-center {
        text-align: center;
    }
    .action-buttons {
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: flex-end;
    }
    .btn-primary {
        background-color: #5e72e4;
        border-color: #5e72e4;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        background-color: #4a5bd1;
        border-color: #4a5bd1;
        transform: translateY(-1px);
    }
    .section-header h1 {
        font-weight: 600;
        color: #2d3748;
        font-size: 1.5rem;
    }
    .table-responsive {
        -webkit-overflow-scrolling: touch;
    }
    @media (max-width: 768px) {
        .table-responsive {
            padding: 0 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        .card-header {
            padding: 1rem;
        }
        .table thead th {
            font-size: 0.65rem;
            padding: 0.75rem 0.5rem;
        }
        .table tbody td {
            padding: 0.85rem 0.5rem;
            font-size: 0.8rem;
        }
    }
</style>
@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Employees Table</h1>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-user-shield"></i> List Employees</h6>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="users-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Name</th>
                                                <th class="text-center">NIP</th>
                                                <th class="text-center">Company</th>
                                                <th class="text-center">Departments</th>
                                                <th class="text-center">Location</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Grd Name</th>
                                                <th class="text-center">Status Employee</th>
                                                <th class="text-center">LOS</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="action-buttons">
                                    <button type="button" onclick="window.location='{{ route('Employee.create') }}'"
                                        class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus-circle"></i> Create Employee
                                    </button>
                                    <button type="button" onclick="window.location='{{ route('pages.Employeeall') }}'"
                                        class="btn btn-success btn-sm ml-2">
                                        <i class="fas fa-users"></i> All Employees
                                    </button>
                                </div>
                                <div class="alert alert-secondary mt-4" role="alert">
                                    <span class="text-dark">
                                        <strong>Important Note:</strong> <br>
                                        - <i class="fas fa-user"></i> Press button to edit <br>
                                        - <i class="fas fa-plus-circle"></i> Press button to create employee <br>
                                        - <i class="fas fa-users"></i> Press button to see all employee details <br>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5>Employee Activity History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">

                        <table id="activityTable" class="table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center">Description</th>
                                    <th class="text-center">By</th>
                                    <th class="text-center">Date</th>
                                </tr>
                            </thead>

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
  
    <script>
        $(document).ready(function() {
            var table = $('#users-table').DataTable({
                dom: '<"top row mb-2"<"col-sm-12 col-md-6 d-flex align-items-center"lB><"col-sm-12 col-md-6"f>>rt<"bottom"ip>',
                buttons: [{
                        extend: 'csv',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        className: 'btn btn-sm btn-primary ms-2 me-2',
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7, 8]
                        }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-sm btn-success',
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7, 8]
                        }
                    }
                ],
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: {
                    url: '{{ route('employees.employees') }}',
                    data: function(d) {
                        d.activity_type = $('#activity-type-filter').val();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Failed to load data!'
                        });
                        console.error(xhr.responseText);
                    }
                },
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                language: {
                    lengthMenu: "Show _MENU_ entries",
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    },
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)"
                },
                columns: [

                    {
                        data: 'employee_name',
                        className: 'text-center'
                    },
                    {
                        data: 'nip',
                        className: 'text-center'
                    },
                    {
                        data: 'name_company',
                        className: 'text-center'
                    },
                    {
                        data: 'department_name',
                        className: 'text-center'
                    },
                    {
                        data: 'name',
                        className: 'text-center'
                    },
                    {
                        data: 'oldposition_name',
                        className: 'text-center'
                    },
                    {
                        data: 'grading_name',
                        className: 'text-center'
                    },
                    {
                        data: 'status_employee',
                        className: 'text-center'
                    },
                    {
                        data: 'length_of_service',
                        className: 'text-center'
                    },
                    {
                        data: 'status',
                        className: 'text-center',
                        render: function(data) {
                            const badges = {
                                'Active': 'success',
                                'On leave': 'warning',
                                'Mutation': 'info',
                                'Pending': 'secondary',
                                'Resign': 'warning text-dark'
                            };
                            return `<span class="badge bg-${badges[data] || 'light'}">${data}</span>`;
                        }
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control form-control-sm');
                    $('.dataTables_length select').addClass('form-select form-select-sm');
                }
            });

            $('#activity-type-filter').change(function() {
                table.ajax.reload();
            });

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
                    timer: 3000
                });
            @endif
        });
    </script>
    <script>
        $(function() {
            $('#activityTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('data.data') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'causer',
                        name: 'causer',
                        className: 'text-center'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        className: 'text-center'
                    },
                ],
                order: [
                    [3, 'desc']
                ],
                language: {
                    searchPlaceholder: 'Search...',
                    sSearch: '',
                    lengthMenu: '_MENU_ Show entries',
                },
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ]
            });
        });
    </script>
@endpush --}}
@extends('layouts.app')
@section('title', 'Employees')

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
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

        .stat-card-value.green  { color: #166534; }
        .stat-card-value.amber  { color: #92400e; }
        .stat-card-value.red    { color: #991b1b; }

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
            box-shadow: 0 1px 3px rgba(0,0,0,.07);
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

        .emp-card-header-icon.blue  { background: #eff6ff; color: #1d4ed8; }
        .emp-card-header-icon.green { background: #f0fdf4; color: #16a34a; }

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

        .badge-active    { background: #f0fdf4; color: #166534; }
        .badge-leave     { background: #fffbeb; color: #92400e; }
        .badge-resign    { background: #fef2f2; color: #991b1b; }
        .badge-mutation  { background: #eff6ff; color: #1e40af; }
        .badge-pending   { background: #f8fafc; color: #475569; }
        .badge-permanent { background: #eff6ff; color: #1e40af; }
        .badge-contract  { background: #fffbeb; color: #92400e; }
        .badge-internship{ background: #fdf4ff; color: #6b21a8; }

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
                    <i class="fas fa-users"></i> All employees
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
                    <div class="stat-card-value green" id="stat-active">{{$countactives}}</div>
                    <div class="stat-card-sub">
                        <span class="stat-dot" style="background:#16a34a"></span> Currently active
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Pending</div>
                    <div class="stat-card-value amber" id="stat-leave">{{$countpendings}}</div>
                    <div class="stat-card-sub">
                        <span class="stat-dot" style="background:#d97706"></span> Employee Pending
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Resigned</div>
                    <div class="stat-card-value red" id="stat-resign">{{$countresigns}}</div>
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

                <div class="table-responsive">
                    <table id="employees-table" class="table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th class="text-center">Company</th>
                                <th class="text-center">Department</th>
                                <th class="text-center">Location</th>
                                <th class="text-center">Position</th>
                                <th class="text-center">Grade</th>
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
                        <i class="fas fa-pen-to-square"></i> Click edit to modify employee data
                    </div>
                    <div class="hint-item">
                        <i class="fas fa-trash" style="color:#dc2626"></i> Click delete to remove employee
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

    <script>
    /* ── Helpers ── */
    const AVATAR_COLORS = [
        { bg: '#eff6ff', color: '#1e40af' },
        { bg: '#f5f3ff', color: '#5b21b6' },
        { bg: '#f0fdf4', color: '#166534' },
        { bg: '#fffbeb', color: '#92400e' },
        { bg: '#fdf2f8', color: '#9d174d' },
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
        'Active':   'badge-active',
        'On leave': 'badge-leave',
        'Resign':   'badge-resign',
        'Mutation': 'badge-mutation',
        'Pending':  'badge-pending',
    };

    const EMP_STATUS_BADGE = {
        'Permanent':  'badge-permanent',
        'Contract':   'badge-contract',
        'Internship': 'badge-internship',
    };

    function statusBadge(val, map) {
        if (!val) return '-';
        const cls = map[val] || 'badge-pending';
        return `<span class="status-badge ${cls}">${val}</span>`;
    }

    /* ── Employee DataTable ── */
    $(function () {
        var empTable = $('#employees-table').DataTable({
            dom: '<"dataTables_wrapper"<"dt-toolbar d-flex align-items-center gap-2"lBf>rt<"d-flex align-items-center justify-content-between px-3 py-2"ip>>',
            buttons: [
                {
                    extend: 'csv',
                    text: '<i class="fas fa-file-csv me-1"></i> CSV',
                    className: 'btn btn-outline-secondary btn-sm',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                },
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel me-1"></i> Excel',
                    className: 'btn btn-outline-success btn-sm',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                }
            ],
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('employees.employees') }}',
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load employee data.' });
                }
            },
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            pageLength: 10,
            language: {
                lengthMenu: 'Show _MENU_',
                search: '',
                searchPlaceholder: 'Search employee...',
                info: 'Showing _START_–_END_ of _TOTAL_',
                infoEmpty: 'No entries found',
                infoFiltered: '(filtered from _MAX_ total)',
                paginate: { previous: '‹', next: '›' }
            },
            columns: [
                /* 0 — Employee (name + NIP merged) */
                {
                    data: 'employee_name',
                    render: function (data, type, row, meta) {
                        const ini = initials(data);
                        const sty = avatarStyle(data, meta.row);
                        return `<div class="emp-cell">
                            <div class="emp-avatar" style="${sty}">${ini}</div>
                            <div>
                                <div class="emp-avatar-name">${data || '-'}</div>
                                <div class="emp-avatar-nip">${row.nip || '-'}</div>
                            </div>
                        </div>`;
                    }
                },
                { data: 'name_company',      className: 'text-center', render: d => d || '-' },
                { data: 'department_name',    className: 'text-center', render: d => d || '-' },
                { data: 'name',               className: 'text-center', render: d => d || '-' },
                { data: 'oldposition_name',   className: 'text-center', render: d => d || '-' },
                {
                    data: 'grading_name',
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
                    render: d => d ? `<span style="color:#64748b;font-size:.775rem">${d}</span>` : '-'
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
                    render: function (data) {
                        return `<div class="action-wrap">${data}</div>`;
                    }
                }
            ],
            drawCallback: function (settings) {
                /* update stat cards from recordsTotal */
                const info = settings.json;
                if (info && info.recordsTotal !== undefined) {
                    $('#emp-count').text(info.recordsTotal + ' records');
                    $('#stat-total').text(info.recordsTotal);
                }
                if (info && info.stats) {
                    $('#stat-active').text(info.stats.active  ?? '–');
                    $('#stat-leave').text(info.stats.on_leave ?? '–');
                    $('#stat-resign').text(info.stats.resign  ?? '–');
                }
            },
            initComplete: function () {
                $('.dataTables_filter input').addClass('form-control form-control-sm');
                $('.dataTables_length select').addClass('form-select form-select-sm');
            }
        });

        /* ── Activity DataTable ── */
        $('#activityTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('data.data') }}",
            columns: [
                {
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
                    render: function (d) {
                        if (!d) return '-';
                        const ini  = initials(d);
                        const sty  = avatarStyle(d, 1);
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
                    render: d => d ? `<span style="font-size:.75rem;color:#64748b">${d}</span>` : '-'
                }
            ],
            order: [[3, 'desc']],
            language: {
                searchPlaceholder: 'Search activity...',
                sSearch: '',
                lengthMenu: 'Show _MENU_',
                info: 'Showing _START_–_END_ of _TOTAL_',
                paginate: { previous: '‹', next: '›' }
            },
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
            initComplete: function () {
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