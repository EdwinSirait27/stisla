@extends('layouts.app')
@section('title', 'Employee Overtime Rates')
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

            </div>

            <div class="section-body">

                {{-- ── Stat Cards ── --}}

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

                    </div>


                    {{-- Bulk assign rate --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-end" style="gap:12px">
                                <div>
                                    <label class="small text-muted d-block mb-1">Rate per Jam (Rp)</label>
                                    <input type="number" id="bulk-rate" class="form-control form-control-sm"
                                        placeholder="Contoh: 50000" min="0" style="width:200px">
                                </div>
                                <div>
                                    <button class="btn btn-primary btn-sm" id="btn-bulk-assign">
                                        <i class="fas fa-save"></i> Set Rate ke Terpilih
                                    </button>
                                    <button class="btn btn-secondary btn-sm ms-1" id="btn-select-all">
                                        <i class="fas fa-check-square"></i> Pilih Semua
                                    </button>
                                </div>
                                <small class="text-muted align-self-end">
                                    <span id="selected-count">0</span> karyawan dipilih
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">

                        <table id="employees-table" class="table">
                            <thead>
                                <tr>
                                    <th class="text-center">Check</th>
                                    <th class="text-center">Employee</th>
                                    <th class="text-center">NIP</th>
                                    <th class="text-center">Company</th>
                                    <th class="text-center">Department</th>
                                    <th class="text-center">Location</th>
                                    <th class="text-center">Position</th>
                                    <th class="text-center">Grade</th>
                                    <th class="text-center">Group</th>
                                    <th class="text-center">Emp. status</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Rate</th>
                                    <th class="text-center">Action</th>
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
                    url: '{{ route('overtimerates.overtimerates') }}',
                    data: function(d) {
                        d.filter_company = $('#filter-company').val();
                        d.filter_department = $('#filter-department').val();
                        d.filter_emp_status = $('#filter-emp-status').val();
                        d.filter_status = $('#filter-status').val();
                        d.filter_store = $('#filter-store').val();
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

                columns: [{
                        data: 'employee_id',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data) {
                            return `<input type="checkbox" class="chk-employee" value="${data}">`;
                        }
                    },
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
                        data: 'status',
                        className: 'text-center',
                        render: d => statusBadge(d, STATUS_BADGE)
                    },
                    {
                        data: 'rate_display',
                        className: 'text-center',
                        render: d => d || '-'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
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
            $('#filter-company, #filter-department, #filter-emp-status, #filter-status, #filter-store, #filter-grading, #filter-group')
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
                $('#filter-group').val('').trigger('change');
                $('#filter-grading').val('').trigger('change');
                empTable.ajax.reload();
            });

        

        // ── Checkbox per row ──
        $(document).on('change', '.chk-employee', function() {
            const count = $('.chk-employee:checked').length;
            $('#selected-count').text(count);
        });

        // ── Select all ──
        $('#btn-select-all').on('click', function() {
            const allChecked = $('.chk-employee:checked').length === $('.chk-employee').length;
            $('.chk-employee').prop('checked', !allChecked);
            $('#selected-count').text(!allChecked ? $('.chk-employee').length : 0);
        });

        // ── Bulk assign ──
        $('#btn-bulk-assign').on('click', function() {
            const ids = $('.chk-employee:checked').map(function() {
                return $(this).val();
            }).get();
            const rate = $('#bulk-rate').val();

            if (!ids.length) return Swal.fire('Info', 'Pilih minimal 1 karyawan.', 'info');
            if (!rate) return Swal.fire('Info', 'Masukkan rate per jam.', 'info');

            Swal.fire({
                title: 'Konfirmasi',
                text: `Set rate Rp ${Number(rate).toLocaleString('id-ID')} untuk ${ids.length} karyawan?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, set',
            }).then(result => {
                if (!result.isConfirmed) return;

                $.post('{{ route('overtime-rate.store') }}', {
                        _token: '{{ csrf_token() }}',
                        employee_ids: ids,
                        rate_per_hour: rate,
                    })
                   .done(res => {
    empTable.ajax.reload(); // ← ganti table ke empTable
    Swal.fire('Berhasil!', res.message, 'success');
})
                    .fail(xhr => Swal.fire('Gagal!', xhr.responseJSON?.message ?? 'Error', 'error'));
            });
        });

        // ── Reset checkbox saat reload ──
        empTable.on('draw', function() { // ← ganti table ke empTable
        $('#selected-count').text(0);
    });

    // ── Session flash ──
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
