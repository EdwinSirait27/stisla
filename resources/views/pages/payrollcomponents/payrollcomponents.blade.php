@extends('layouts.app')
@section('title', 'Payroll Components')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    {{-- <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"> --}}
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
                        Dashboard / <span style="color:#64748b">Payroll Components</span>
                    </div>
                    <h1>Payroll Components</h1>
                </div>
                <div class="page-actions">
                    <a href="{{ route('payrollcomponents.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Payroll Components
                    </a>
                 
                </div>
            </div>

            <div class="section-body">
                {{-- ── Employee Table Card ── --}}
                <div class="emp-card">
                    <div class="emp-card-header">
                        <div class="emp-card-header-icon blue">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="emp-card-header-title">List Payroll Components</span>
                        <span class="emp-card-header-count" id="emp-count">Payroll Components</span>
                    </div>

                    <div class="table-responsive">
                        <table id="employees-table" class="table">
                            <thead>
                                <tr>
                                    <th class="text-center">Component Name</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">Fixed?</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            {{-- tbody filled by DataTables --}}
                        </table>
                    </div>

                    <div class="hint-bar">
                        <div class="hint-item">
                             <i class="fas fa-user-edit text-secondary"></i>Click icon edit to modify payroll components data
                        </div>

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
        /* ── Employee DataTable ── */
        $(function() {
            var empTable = $('#employees-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: {
                    url: '{{ route('payrollcomponents.payrollcomponents') }}',
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
                    searchPlaceholder: 'Search',
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
                    {
                        data: 'component_name',
                          className: 'text-center',
                        render: d => d || '-'
                       
                    },
                    {
                        data: 'type',
                        className: 'text-center',
                        render: d => d || '-'
                    },
                    {
                        data: 'is_fixed',
                        className: 'text-center',
                        render: d => d || '-'
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
