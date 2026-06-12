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
        #filter-emp-status+.select2-container .select2-selection--multiple {
            min-height: 32px !important;
            height: auto !important;
            /* expand otomatis */
            font-size: .775rem !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: .4rem !important;
            min-width: 125px;
            max-width: 250px;
            /* biar tidak terlalu lebar */
            padding: 2px 4px;
        }

        #filter-emp-status+.select2-container .select2-selection__choice {
            font-size: .7rem !important;
            padding: 1px 6px !important;
            margin: 2px !important;
            border-radius: .3rem !important;
            background-color: #4f46e5 !important;
            /* sesuaikan warna badge */
            border: none !important;
            color: #fff !important;
        }

        #filter-emp-status+.select2-container .select2-selection__choice__remove {
            color: #fff !important;
            margin-right: 4px;
        }

        #filter-emp-status+.select2-container .select2-selection__rendered {
            display: flex !important;
            flex-wrap: wrap !important;
            padding: 2px !important;
            gap: 2px;
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
    @foreach ($departments as $dept)
        <option value="{{ $dept->department_name }}">{{ $dept->department_name }}</option>
    @endforeach
</select>

                        {{-- <select id="filter-department" class="select2 form-select form-select-sm"
                            style="height:32px;font-size:.775rem;border:1px solid #e2e8f0;border-radius:.4rem;min-width:125px;">
                            <option value="">All Departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department }}">{{ $department }}</option>
                            @endforeach
                        </select> --}}
                        {{-- <select id="filter-store" class="select2 form-select form-select-sm"
                            style="height:32px;font-size:.775rem;border:1px solid #e2e8f0;border-radius:.4rem;min-width:125px;">
                            <option value="">All Locations</option>
                            @foreach ($stores as $location)
                                <option value="{{ $location }}">{{ $location }}</option>
                            @endforeach
                        </select> --}}
                        <select id="filter-store" class="select2 form-select form-select-sm"
    style="height:32px;font-size:.775rem;border:1px solid #e2e8f0;border-radius:.4rem;min-width:125px;">
    <option value="">All Locations</option>
    @foreach ($stores as $store)
        <option value="{{ $store->name }}">{{ $store->name }}</option>
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


                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Bagan Organisasi</h4>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex gap-3 flex-wrap mb-4 align-items-end">
                                        <div>
                                            <label class="form-label text-muted" style="font-size:13px">Store</label>
                                            <select id="sel-store" class="form-control select2" style="min-width:180px">
                                                <option value="all">-- Semua Store --</option>
                                                @foreach ($stores as $store)
                                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label text-muted" style="font-size:13px">Department</label>
                                            <select id="sel-dept" class="form-control select2" style="min-width:180px">
                                                <option value="all">-- Semua Department --</option>
                                                @foreach ($departments as $dept)
                                                    <option value="{{ $dept->id }}">{{ $dept->department_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button id="btn-load" class="btn btn-primary">
                                            <i class="fas fa-sitemap"></i> Tampilkan Bagan
                                        </button>
                                      
                                    </div>

                                    {{-- Legend --}}
                                    <div class="d-flex gap-3 flex-wrap mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:12px;height:12px;border-radius:50%;background:#534AB7"></div>
                                            <small class="text-muted">Director / GM</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:12px;height:12px;border-radius:50%;background:#1D9E75"></div>
                                            <small class="text-muted">Manager</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:12px;height:12px;border-radius:50%;background:#D85A30"></div>
                                            <small class="text-muted">Asst. Manager / Supervisor</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:12px;height:12px;border-radius:50%;background:#888780"></div>
                                            <small class="text-muted">Staff</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:12px;height:12px;border-radius:50%;background:#a69833"></div>
                                            <small class="text-muted">DW</small>
                                        </div>
                                    </div>

                                    {{-- Chart Area --}}
                                  
                                    {{-- Zoom Controls --}}
                                    <div class="d-flex gap-2 mb-2">
                                        <button id="btn-zoom-in" class="btn btn-sm btn-outline-secondary"
                                            title="Zoom In">
                                            <i class="fas fa-search-plus"></i>
                                        </button>
                                        <button id="btn-zoom-out" class="btn btn-sm btn-outline-secondary"
                                            title="Zoom Out">
                                            <i class="fas fa-search-minus"></i>
                                        </button>
                                        <button id="btn-fit" class="btn btn-sm btn-outline-secondary"
                                            title="Fit to Screen">
                                            <i class="fas fa-compress-arrows-alt"></i> Fit
                                        </button>
                                        <button id="btn-reset-zoom" class="btn btn-sm btn-outline-secondary"
                                            title="Reset">
                                            <i class="fas fa-undo"></i> Reset
                                        </button>
                                        <span id="zoom-label" class="align-self-center text-muted"
                                            style="font-size:12px">100%</span>
                                    </div>

                                    {{-- Chart Area --}}
                                    <div id="chart-area"
                                        style="overflow:hidden;min-height:500px;height:600px;border:1px solid #e9ecef;border-radius:8px;position:relative;cursor:grab;">
                                        <div id="chart-placeholder"
                                            class="d-flex align-items-center justify-content-center" style="height:100%">
                                            <div class="text-center text-muted">
                                                <i class="fas fa-sitemap fa-3x mb-3 d-block"></i>
                                                <p>Pilih store dan department untuk menampilkan bagan</p>
                                            </div>
                                        </div>
                                        <svg id="svg-chart"
                                            style="display:none;position:absolute;top:0;left:0;transform-origin:0 0;"></svg>
                                    </div>

                                </div>
                            </div>
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
        $(function() {
            $('#filter-emp-status').select2({
                placeholder: 'All Emp. Status',
                allowClear: true,
                width: 'resolve'
            });
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
                        data: 'store_name',
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
    {{-- untuk abgan --}}
    <script>
        $('#sel-store, #sel-dept').select2({
            width: 'resolve'
        });

        const BAGAN_URL = '{{ route('employee.bagan') }}';

        // Zoom & Pan state
        let scale = 1,
            panX = 0,
            panY = 0;
        let isPanning = false,
            startX = 0,
            startY = 0,
            startPanX = 0,
            startPanY = 0;
        let totalW = 0,
            totalH = 0;

        function applyTransform() {
            const svg = document.getElementById('svg-chart');
            svg.style.transform = `translate(${panX}px, ${panY}px) scale(${scale})`;
            document.getElementById('zoom-label').textContent = Math.round(scale * 100) + '%';
        }

        function fitToScreen() {
            const area = document.getElementById('chart-area');
            const areaW = area.clientWidth - 32;
            const areaH = area.clientHeight - 32;
            if (!totalW || !totalH) return;
            scale = Math.min(areaW / totalW, areaH / totalH, 1);
            panX = (areaW - totalW * scale) / 2 + 16;
            panY = 16;
            applyTransform();
        }

        // Zoom buttons
        document.getElementById('btn-zoom-in').addEventListener('click', () => {
            scale = Math.min(scale + 0.1, 3);
            applyTransform();
        });
        document.getElementById('btn-zoom-out').addEventListener('click', () => {
            scale = Math.max(scale - 0.1, 0.1);
            applyTransform();
        });
        document.getElementById('btn-fit').addEventListener('click', fitToScreen);
        document.getElementById('btn-reset-zoom').addEventListener('click', () => {
            scale = 1;
            panX = 0;
            panY = 0;
            applyTransform();
        });

        // Mouse wheel zoom
        document.getElementById('chart-area').addEventListener('wheel', function(e) {
            e.preventDefault();
            const delta = e.deltaY > 0 ? -0.08 : 0.08;
            const newScale = Math.min(Math.max(scale + delta, 0.1), 3);

            // Zoom toward mouse position
            const rect = this.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;
            panX = mouseX - (mouseX - panX) * (newScale / scale);
            panY = mouseY - (mouseY - panY) * (newScale / scale);
            scale = newScale;
            applyTransform();
        }, {
            passive: false
        });

        // Pan / drag
        const chartArea = document.getElementById('chart-area');
        chartArea.addEventListener('mousedown', function(e) {
            if (e.target.closest('svg')) {
                isPanning = true;
                startX = e.clientX;
                startY = e.clientY;
                startPanX = panX;
                startPanY = panY;
                this.style.cursor = 'grabbing';
            }
        });
        document.addEventListener('mousemove', function(e) {
            if (!isPanning) return;
            panX = startPanX + (e.clientX - startX);
            panY = startPanY + (e.clientY - startY);
            applyTransform();
        });
        document.addEventListener('mouseup', function() {
            isPanning = false;
            chartArea.style.cursor = 'grab';
        });

        // Touch pan
        chartArea.addEventListener('touchstart', function(e) {
            if (e.touches.length === 1) {
                isPanning = true;
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                startPanX = panX;
                startPanY = panY;
            }
        }, {
            passive: true
        });
        chartArea.addEventListener('touchmove', function(e) {
            if (!isPanning || e.touches.length !== 1) return;
            panX = startPanX + (e.touches[0].clientX - startX);
            panY = startPanY + (e.touches[0].clientY - startY);
            applyTransform();
        }, {
            passive: true
        });
        chartArea.addEventListener('touchend', () => {
            isPanning = false;
        });

        function gradingColor(level) {
            if (level <= 1) return {
                fill: '#EEEDFE',
                stroke: '#534AB7',
                text: '#3C3489'
            };
            if (level <= 2) return {
                fill: '#E1F5EE',
                stroke: '#1D9E75',
                text: '#085041'
            };
            if (level <= 3) return {
                fill: '#FAECE7',
                stroke: '#D85A30',
                text: '#712B13'
            };
            return {
                fill: '#F1EFE8',
                stroke: '#888780',
                text: '#444441'
            };
        }

        function initials(name) {
            return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
        }

        function truncate(str, max) {
            return str.length > max ? str.substring(0, max - 1) + '…' : str;
        }

        function drawEmpty(message) {
            const svg = document.getElementById('svg-chart');
            const placeholder = document.getElementById('chart-placeholder');
            svg.style.display = 'none';

            // ← Balik lagi ke d-flex
            placeholder.classList.remove('d-none');
            placeholder.classList.add('d-flex');

            placeholder.innerHTML = `
        <div class="text-center text-muted">
            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
            <p>${message}</p>
        </div>`;
        }

        function drawChart(nodes) {
            const placeholder = document.getElementById('chart-placeholder');
            const svg = document.getElementById('svg-chart');

            // ← Tambah d-none, hapus d-flex
            placeholder.classList.remove('d-flex');
            placeholder.classList.add('d-none');

            svg.style.display = 'block';
            svg.innerHTML = '';

            const nodeW = 180,
                nodeH = 76,
                hGap = 28,
                vGap = 64;

            const byLevel = {};
            nodes.forEach(n => {
                if (!byLevel[n.grading_level]) byLevel[n.grading_level] = [];
                byLevel[n.grading_level].push(n);
            });
            const levels = Object.keys(byLevel).map(Number).sort((a, b) => a - b);
            const maxInRow = Math.max(...levels.map(l => byLevel[l].length));
            totalW = Math.max(700, maxInRow * (nodeW + hGap) + hGap * 2);
            totalH = levels.length * (nodeH + vGap) + vGap + 20;

            svg.setAttribute('width', totalW);
            svg.setAttribute('height', totalH);

            const posMap = {};
            levels.forEach((level, li) => {
                const row = byLevel[level];
                const rowW = row.length * nodeW + (row.length - 1) * hGap;
                const startX = (totalW - rowW) / 2;
                const y = vGap + li * (nodeH + vGap);
                row.forEach((node, ni) => {
                    const x = startX + ni * (nodeW + hGap);
                    posMap[node.id] = {
                        x,
                        y,
                        cx: x + nodeW / 2,
                        cy: y + nodeH / 2
                    };
                });
            });

            const ns = 'http://www.w3.org/2000/svg';
            const defs = document.createElementNS(ns, 'defs');
            const marker = document.createElementNS(ns, 'marker');
            marker.setAttribute('id', 'arrow');
            marker.setAttribute('markerWidth', '6');
            marker.setAttribute('markerHeight', '6');
            marker.setAttribute('refX', '5');
            marker.setAttribute('refY', '3');
            marker.setAttribute('orient', 'auto');
            const mpath = document.createElementNS(ns, 'path');
            mpath.setAttribute('d', 'M0,0 L0,6 L6,3 z');
            mpath.setAttribute('fill', '#B4B2A9');
            marker.appendChild(mpath);
            defs.appendChild(marker);
            svg.appendChild(defs);
            

            nodes.forEach(node => {
                if (!node.atasan_id || !posMap[node.atasan_id]) return;
                const from = posMap[node.atasan_id];
                const to = posMap[node.id];
                const midY = from.y + nodeH + (to.y - from.y - nodeH) / 2;
                const path = document.createElementNS(ns, 'path');
                path.setAttribute('d',
                    `M${from.cx},${from.y + nodeH} L${from.cx},${midY} L${to.cx},${midY} L${to.cx},${to.y}`);
                path.setAttribute('stroke', '#B4B2A9');
                path.setAttribute('stroke-width', '1.5');
                path.setAttribute('fill', 'none');
                path.setAttribute('marker-end', 'url(#arrow)');
                svg.appendChild(path);
            });

            nodes.forEach(node => {
                const pos = posMap[node.id];
                const c = gradingColor(node.grading_level);
                const g = document.createElementNS(ns, 'g');

                const rect = document.createElementNS(ns, 'rect');
                rect.setAttribute('x', pos.x);
                rect.setAttribute('y', pos.y);
                rect.setAttribute('width', nodeW);
                rect.setAttribute('height', nodeH);
                rect.setAttribute('rx', 8);
                rect.setAttribute('fill', c.fill);
                rect.setAttribute('stroke', c.stroke);
                rect.setAttribute('stroke-width', '1');
                g.appendChild(rect);
                g.style.cursor = 'pointer';
g.addEventListener('click', function() {
    const info = `
        <b>${node.name}</b><br>
        <small>
            <b>Company:</b> ${node.company_name}<br>
            <b>Grading:</b> ${node.grading}<br>
            <b>Position:</b> ${node.all_positions}<br>
            <b>Location:</b> ${node.all_stores}<br>
            <b>Department:</b> ${node.all_departments}
        </small>`;
    
    Swal.fire({
        title: 'Detail Karyawan',
        html: info,
        icon: 'info',
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#534AB7',
    });
});

                const avatarCx = pos.x + 28;
                const avatarCy = pos.y + nodeH / 2;
                const circle = document.createElementNS(ns, 'circle');
                circle.setAttribute('cx', avatarCx);
                circle.setAttribute('cy', avatarCy);
                circle.setAttribute('r', 20);
                circle.setAttribute('fill', c.stroke);
                g.appendChild(circle);
                g.style.cursor = 'pointer';
g.addEventListener('click', function() {
    const info = `
        <b>${node.name}</b><br>
        <small>
            <b>Company:</b> ${node.company_name}<br>

            <b>Grading:</b> ${node.grading}<br>
            <b>Position:</b> ${node.all_positions}<br>
            <b>Store:</b> ${node.all_stores}<br>
            <b>Department:</b> ${node.all_departments}
        </small>`;
    
    Swal.fire({
        title: 'Detail Karyawan',
        html: info,
        icon: 'info',
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#534AB7',
    });
});

                if (node.photo) {
                    const clipId = 'clip-' + node.id;
                    const clipPath = document.createElementNS(ns, 'clipPath');
                    clipPath.setAttribute('id', clipId);
                    const clipCircle = document.createElementNS(ns, 'circle');
                    clipCircle.setAttribute('cx', avatarCx);
                    clipCircle.setAttribute('cy', avatarCy);
                    clipCircle.setAttribute('r', 20);
                    clipPath.appendChild(clipCircle);
                    defs.appendChild(clipPath);
                    const img = document.createElementNS(ns, 'image');
                    img.setAttribute('href', node.photo);
                    img.setAttribute('x', avatarCx - 20);
                    img.setAttribute('y', avatarCy - 20);
                    img.setAttribute('width', 40);
                    img.setAttribute('height', 40);
                    img.setAttribute('clip-path', `url(#${clipId})`);
                    g.appendChild(img);
                    g.style.cursor = 'pointer';
g.addEventListener('click', function() {
    const info = `
        <b>${node.name}</b><br>
        <small>
            <b>Company:</b> ${node.company_name}<br>

            <b>Grading:</b> ${node.grading}<br>
            <b>Position:</b> ${node.all_positions}<br>
            <b>Store:</b> ${node.all_stores}<br>
            <b>Department:</b> ${node.all_departments}
        </small>`;
    
    Swal.fire({
        title: 'Detail Karyawan',
        html: info,
        icon: 'info',
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#534AB7',
    });
});
                } else {
                    const avatarText = document.createElementNS(ns, 'text');
                    avatarText.setAttribute('x', avatarCx);
                    avatarText.setAttribute('y', avatarCy + 5);
                    avatarText.setAttribute('text-anchor', 'middle');
                    avatarText.setAttribute('font-size', '12');
                    avatarText.setAttribute('font-weight', '500');
                    avatarText.setAttribute('fill', '#fff');
                    avatarText.textContent = initials(node.name);
                    g.appendChild(avatarText);
                    g.style.cursor = 'pointer';
g.addEventListener('click', function() {
    const info = `
        <b>${node.name}</b><br>
        <small>
            <b>Company:</b> ${node.company_name}<br>

            <b>Grading:</b> ${node.grading}<br>
            <b>Position:</b> ${node.all_positions}<br>
            <b>Store:</b> ${node.all_stores}<br>
            <b>Department:</b> ${node.all_departments}
        </small>`;
    
    Swal.fire({
        title: 'Detail Karyawan',
        html: info,
        icon: 'info',
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#534AB7',
    });
});
                }

                const nameText = document.createElementNS(ns, 'text');
                nameText.setAttribute('x', pos.x + 56);
                nameText.setAttribute('y', pos.y + 24);
                nameText.setAttribute('font-size', '12');
                nameText.setAttribute('font-weight', '500');
                nameText.setAttribute('fill', c.text);
                nameText.textContent = truncate(node.name, 20);
                g.appendChild(nameText);
                g.style.cursor = 'pointer';
g.addEventListener('click', function() {
    const info = `
        <b>${node.name}</b><br>
        <small>
            <b>Company:</b> ${node.company_name}<br>

            <b>Grading:</b> ${node.grading}<br>
            <b>Position:</b> ${node.all_positions}<br>
            <b>Store:</b> ${node.all_stores}<br>
            <b>Department:</b> ${node.all_departments}
        </small>`;
    
    Swal.fire({
        title: 'Detail Karyawan',
        html: info,
        icon: 'info',
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#534AB7',
    });
});

                const posText = document.createElementNS(ns, 'text');
                posText.setAttribute('x', pos.x + 56);
                posText.setAttribute('y', pos.y + 42);
                posText.setAttribute('font-size', '11');
                posText.setAttribute('fill', c.stroke);
                posText.textContent = truncate(node.position, 22);
                g.appendChild(posText);
                g.style.cursor = 'pointer';
g.addEventListener('click', function() {
    const info = `
        <b>${node.name}</b><br>
        <small>
            <b>Company:</b> ${node.company_name}<br>
            
            <b>Grading:</b> ${node.grading}<br>
            <b>Position:</b> ${node.all_positions}<br>
            <b>Store:</b> ${node.all_stores}<br>
            <b>Department:</b> ${node.all_departments}
        </small>`;
    
    Swal.fire({
        title: 'Detail Karyawan',
        html: info,
        icon: 'info',
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#534AB7',
    });
});
                const gradText = document.createElementNS(ns, 'text');
                gradText.setAttribute('x', pos.x + 56);
                gradText.setAttribute('y', pos.y + 58);
                gradText.setAttribute('font-size', '10');
                gradText.setAttribute('fill', c.text);
                gradText.setAttribute('opacity', '0.7');
                gradText.textContent = node.grading;
                g.appendChild(gradText);
                g.style.cursor = 'pointer';
g.addEventListener('click', function() {
    const info = `
        <b>${node.name}</b><br>
        <small>
            <b>Company:</b> ${node.company_name}<br>

            <b>Grading:</b> ${node.grading}<br>
            <b>Position:</b> ${node.all_positions}<br>
            <b>Store:</b> ${node.all_stores}<br>
            <b>Department:</b> ${node.all_departments}
        </small>`;
    
    Swal.fire({
        title: 'Detail Karyawan',
        html: info,
        icon: 'info',
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#534AB7',
    });
});

                svg.appendChild(g);
            });

            // Auto fit setelah render
            setTimeout(fitToScreen, 50);
        }

        document.getElementById('btn-load').addEventListener('click', function() {
            const storeId = $('#sel-store').val();
            const deptId = $('#sel-dept').val();
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memuat...';

            fetch(`${BAGAN_URL}?store_id=${storeId}&department_id=${deptId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        drawEmpty('Error: ' + data.error);
                        return;
                    }
                    if (!data.nodes || !data.nodes.length) {
                        drawEmpty('Tidak ada karyawan di store dan department ini.');
                    } else {
                        drawChart(data.nodes);
                    }
                })
                .catch(err => {
                    drawEmpty('Gagal memuat data: ' + err.message);
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-sitemap"></i> Tampilkan Bagan';
                });
        });
  
    </script>
@endpush
