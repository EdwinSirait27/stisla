@extends('layouts.app')
@section('title', 'Fingerprints')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        /* ─── Page header ────────────────────────────────────── */
        .section-header h1 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        /* ─── Stat cards ─────────────────────────────────────── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
            margin-bottom: 1.25rem;
        }

        .stat-card {
            background: #fff;
            border: 1px solid #f1f5f9;
            border-radius: .625rem;
            padding: 13px 15px;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
        }

        .stat-card-label {
            font-size: .67rem;
            font-weight: 700;
            /* text-transform: uppercase; */
            letter-spacing: .7px;
            color: #94a3b8;
            margin-bottom: 5px;
        }

        .stat-card-value {
            font-size: 1.4rem;
            font-weight: 600;
            line-height: 1;
            color: #1e293b;
        }

        .stat-card-value.green  { color: #166534; }
        .stat-card-value.amber  { color: #92400e; }
        .stat-card-value.purple { color: #5b21b6; }
        .stat-card-value.red    { color: #991b1b; }

        .stat-card-sub {
            font-size: .68rem;
            color: #94a3b8;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stat-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        /* ─── Card shell ─────────────────────────────────────── */
        .fp-card {
            border: none;
            border-radius: .625rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.07);
            background: #fff;
            overflow: hidden;
        }

        .fp-card-header {
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            padding: .875rem 1.25rem;
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .fp-card-header-icon {
            width: 28px;
            height: 28px;
            background: #eff6ff;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            color: #1d4ed8;
            flex-shrink: 0;
        }

        .fp-card-header-title {
            font-size: .9rem;
            font-weight: 600;
            color: #334155;
            flex: 1;
        }

        /* ─── Filter bar ─────────────────────────────────────── */
        .filter-bar {
            padding: .875rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            background: #fafafa;
            display: flex;
            align-items: flex-end;
            gap: .625rem;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: .25rem;
        }

        .filter-group .filter-label {
            font-size: .67rem;
            font-weight: 700;
            /* text-transform: uppercase; */
            letter-spacing: .5px;
            color: #94a3b8;
            margin: 0;
        }

        .filter-group .form-control,
        .filter-group .select2-container .select2-selection--single {
            height: 34px !important;
            font-size: .8rem;
            border: 1px solid #e2e8f0;
            border-radius: .4375rem;
            min-width: 140px;
        }

        .filter-group .select2-container--default .select2-selection--single {
            border-color: #e2e8f0;
            border-radius: .4375rem;
            height: 34px;
        }

        .filter-group .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 34px;
            font-size: .8rem;
            color: #1e293b;
        }

        .filter-group .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px;
        }

        .filter-bar .btn {
            height: 34px;
            font-size: .8rem;
            padding: 0 .875rem;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border-radius: .4375rem;
        }

        .filter-bar-export {
            margin-left: auto;
            display: flex;
            gap: 6px;
            align-items: flex-end;
        }

        /* ─── Table ──────────────────────────────────────────── */
        #fingerprint-table {
            width: 100% !important;
            font-size: .775rem;
        }

        #fingerprint-table thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: .67rem;
            font-weight: 700;
            /* text-transform: uppercase; */
            letter-spacing: .5px;
            padding: .7rem .85rem;
            border: none;
            border-bottom: 1px solid #f1f5f9;
            white-space: nowrap;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 5;
        }

        #fingerprint-table thead th.col-employee {
            text-align: left;
        }

        #fingerprint-table tbody td {
            padding: .65rem .85rem;
            vertical-align: middle;
            border: none;
            border-bottom: 1px solid #f8fafc;
            text-align: center;
            white-space: nowrap;
        }

        #fingerprint-table tbody td.col-employee {
            text-align: left;
        }

        #fingerprint-table tbody tr:last-child td { border-bottom: none; }
        #fingerprint-table tbody tr:hover td       { background: #f8fafc; }

        /* highlight edited rows */
        #fingerprint-table tbody tr.row-edited td {
            background: #eff6ff;
        }
        /* ── employee cell ── */
        .emp-cell {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .emp-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            font-size: .62rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .emp-name-text {
            font-size: .8rem;
            font-weight: 600;
            line-height: 1.2;
        }

        .emp-nip-text {
            font-size: .68rem;
            color: #94a3b8;
        }

        /* ── roster badge ── */
        .roster-badge {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            border-radius: 5px;
            padding: 2px 7px;
            min-width: 64px;
            border: 1px solid transparent;
            gap: 1px;
        }

        .r-name { font-weight: 700; font-size: .67rem; white-space: nowrap; }
        .r-time  { font-size: .63rem; white-space: nowrap; opacity: .85; }

        .r-work    { background: #dbeafe; border-color: #93c5fd; }
        .r-work    .r-name { color: #1d4ed8; }
        .r-work    .r-time { color: #3b82f6; }
        .r-off     { background: #f1f5f9; border-color: #cbd5e1; }
        .r-off     .r-name { color: #64748b; }
        .r-holiday { background: #fef9c3; border-color: #fde047; }
        .r-holiday .r-name { color: #854d0e; }
        .r-leave   { background: #f3e8ff; border-color: #d8b4fe; }
        .r-leave   .r-name { color: #7e22ce; }

        /* ── time cells ── */
        .time-in    { color: #166534; font-weight: 600; }
        .time-out   { color: #991b1b; font-weight: 600; }
        .time-break { color: #92400e; }
        .time-ovt   { color: #1e40af; }
        .time-null  { color: #cbd5e1; }

        /* ── status badges ── */
        .fp-badge {
            display: inline-flex;
            padding: .18rem .55rem;
            border-radius: 20px;
            font-size: .67rem;
            font-weight: 700;
        }

        .fp-badge-updated  { background: #f0fdf4; color: #166534; }
        .fp-badge-original { background: #f8fafc; color: #475569; }
        .fp-badge-permanent{ background: #eff6ff; color: #1e40af; }
        .fp-badge-contract { background: #fffbeb; color: #92400e; }

        /* ── action buttons ── */
        .action-wrap {
            display: flex;
            gap: 4px;
            justify-content: center;
        }

        .act-btn {
            width: 26px;
            height: 26px;
            border-radius: 5px;
            border: 1px solid #e2e8f0;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #64748b;
            font-size: .72rem;
            text-decoration: none;
            transition: all .15s;
        }

        .act-btn:hover { background: #f8fafc; }

        .act-btn-danger {
            border-color: #fecaca;
            background: #fef2f2;
            color: #dc2626;
        }

        .act-btn-danger:hover { background: #fee2e2; }

        /* ─── DataTables overrides ───────────────────────────── */
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

        /* ─── auto-refresh badge ─────────────────────────────── */
        .refresh-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .72rem;
            color: #64748b;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: .2rem .8rem;
        }

        .refresh-pulse {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #1D9E75;
            animation: pulse-anim 2s infinite;
        }

        @keyframes pulse-anim {
            0%, 100% { opacity: 1; }
            50%       { opacity: .3; }
        }

        /* ─── Responsive ─────────────────────────────────────── */
        @media (max-width: 992px) {
            .stats-row { grid-template-columns: repeat(3, 1fr); }
        }

        @media (max-width: 576px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
@endpush

@section('main')
<div class="main-content">
    <section class="section">

        {{-- ── Page Header ── --}}
        <div class="section-header d-flex align-items-start justify-content-between flex-wrap gap-2 mb-4">
            <div>
                <div style="font-size:.72rem;color:#94a3b8;margin-bottom:3px">
                    Dashboard / <span style="color:#64748b">Fingerprints</span>
                </div>
                <h1>Fingerprint list</h1>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="refresh-badge">
                    <div class="refresh-pulse"></div>
                    Auto-refresh active
                </div>
            </div>
        </div>

        <div class="section-body">

            {{-- ── Stat Cards ── --}}
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-card-label">Total records</div>
                    <div class="stat-card-value" id="stat-total">–</div>
                    <div class="stat-card-sub">
                        <span class="stat-dot" style="background:#1d4ed8"></span> This period
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">On time</div>
                    <div class="stat-card-value green" id="stat-ontime">–</div>
                    <div class="stat-card-sub">
                        <span class="stat-dot" style="background:#16a34a"></span> Arrived on schedule
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Late</div>
                    <div class="stat-card-value amber" id="stat-late">–</div>
                    <div class="stat-card-sub">
                        <span class="stat-dot" style="background:#d97706"></span> Past check-in time
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Updated</div>
                    <div class="stat-card-value purple" id="stat-updated">–</div>
                    <div class="stat-card-sub">
                        <span class="stat-dot" style="background:#7c3aed"></span> Manual edit
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Missing scan</div>
                    <div class="stat-card-value red" id="stat-missing">–</div>
                    <div class="stat-card-sub">
                        <span class="stat-dot" style="background:#dc2626"></span> No clock-out
                    </div>
                </div>
            </div>

            {{-- ── Main Card ── --}}
            <div class="fp-card">

                <div class="fp-card-header">
                    <div class="fp-card-header-icon">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <span class="fp-card-header-title">List fingerprints</span>
                    <button id="recapBtn" class="btn btn-success btn-sm ms-auto"
                        style="height:32px;font-size:.775rem">
                        <i class="fas fa-rotate-right"></i> Attendance Recap
                    </button>
                </div>

                {{-- Filter bar --}}
                <div class="filter-bar">
                    <div class="filter-group">
                        <label class="filter-label">Location</label>
                        <select id="store_name" name="store_name" class="form-control select2"
                            style="min-width:160px">
                            <option value="">All stores</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store }}">{{ $store }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Start date</label>
                        <input type="date" id="startDate" class="form-control"
                            style="min-width:140px">
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">End date</label>
                        <input type="date" id="endDate" class="form-control"
                            style="min-width:140px">
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button id="filterBtn" class="btn btn-primary" style="height:34px;font-size:.8rem">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <button id="resetBtn" class="btn btn-secondary" style="height:34px;font-size:.8rem">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>

                    {{-- Export buttons (injected from DataTables) --}}
                    <div class="filter-bar-export">
                        <div id="custom-buttons"></div>
                    </div>
                </div>

                {{-- DataTables length + search injected here --}}
                <div class="d-flex align-items-center gap-3 px-3 py-2"
                    style="background:#fafafa;border-bottom:1px solid #f1f5f9">
                    <div id="custom-length"></div>
                    <div id="custom-search" class="ms-auto"></div>
                </div>

                {{-- Table --}}
                <div class="table-responsive" style="max-height:560px;overflow-y:auto;padding:0">
                    <table class="table" id="fingerprint-table">
                        <thead>
                            <tr>
                                <th class="col-employee" style="min-width:170px">Employee</th>
                                <th>Location</th>
                                <th>PIN</th>
                                <th>Roster</th>
                                <th>Position</th>
                                <th>Emp. status</th>
                                <th>Scan date</th>
                                <th>In</th>
                                <th>Out</th>
                                <th>Break in</th>
                                <th>Break out</th>
                                <th>Ovt in</th>
                                <th>Ovt out</th>
                                <th>Duration</th>
                                <th>Record status</th>
                                <th class="no-export">Action</th>
                            </tr>
                        </thead>
                        {{-- tbody filled by DataTables --}}
                    </table>
                </div>

                {{-- DataTables pagination injected here --}}
                <div class="d-flex align-items-center justify-content-between px-3 py-2"
                    style="background:#fafafa;border-top:1px solid #f1f5f9">
                    <div id="custom-info" style="font-size:.75rem;color:#64748b"></div>
                    <div id="custom-paging"></div>
                </div>

            </div>{{-- /.fp-card --}}

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
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    /* ── Avatar helpers ── */
    const AVATAR_COLORS = [
        { bg: '#eff6ff', color: '#1e40af' },
        { bg: '#f5f3ff', color: '#5b21b6' },
        { bg: '#f0fdf4', color: '#166534' },
        { bg: '#fffbeb', color: '#92400e' },
        { bg: '#fdf2f8', color: '#9d174d' },
    ];
    function getAvatarStyle(name, index) {
        const c = AVATAR_COLORS[index % AVATAR_COLORS.length];
        return `background:${c.bg};color:${c.color}`;
    }
    function getInitials(name) {
        if (!name) return '?';
        return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
    }
    /* ── Roster badge renderer ── */
    function rosterBadge(data, row) {
        if (!data || data === '-') {
            return '<span style="color:#cbd5e1;font-size:.7rem">–</span>';
        }
        const clsMap = { Off: 'r-off', Holiday: 'r-holiday', Leave: 'r-leave' };
        const cls = clsMap[data] || 'r-work';
        const time = row.roster_time
            ? `<span class="r-time">${row.roster_time}</span>`
            : '';
        return `<span class="roster-badge ${cls}">
                    <span class="r-name">${data}</span>${time}
                </span>`;
    }

    /* ── Time cell renderer ── */
    function timeCell(val, cls) {
        if (!val || val === '-') {
            return '<span class="time-null">–</span>';
        }
        return `<span class="${cls}">${val}</span>`;
    }

    /* ── Default date range: 26 prev month → 25 this month ── */
    (function setDefaultDates() {
        const today = new Date();
        const y = today.getFullYear();
        const m = today.getMonth();
        const fmt = d => {
            const yy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${yy}-${mm}-${dd}`;
        };
        document.getElementById('startDate').value = fmt(new Date(y, m - 1, 26));
        document.getElementById('endDate').value   = fmt(new Date(y, m, 25));
    })();

    $(function () {
        /* ── Select2 ── */
        $('.select2').select2({ width: '100%' });

        /* ── DataTable ── */
        var table = $('#fingerprint-table').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            responsive: false,
            dom: "<'d-none'lf>" +
                 "<'row'<'col-12'tr>>" +
                 "<'row mt-2'<'col-12'B>>",
            buttons: [
                {
                    extend: 'csv',
                    className: 'btn btn-outline-success btn-sm',
                    text: '<i class="fas fa-file-csv me-1"></i> CSV',
                    exportOptions: { columns: ':not(.no-export)' }
                },
                {
                    extend: 'excel',
                    className: 'btn btn-outline-info btn-sm',
                    text: '<i class="fas fa-file-excel me-1"></i> Excel',
                    exportOptions: { columns: ':not(.no-export)' }
                }
            ],
            ajax: {
                url: '{{ route('fingerprints.fingerprints') }}',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                data: function (d) {
                    d.start_date = $('#startDate').val();
                    d.end_date   = $('#endDate').val();
                    d.store_name = $('#store_name').val();
                }
            },
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            pageLength: 25,
          
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
                    data: 'employee_name',
                    className: 'col-employee',
                    render: function (data, type, row, meta) {
                        if (type !== 'display') return data || '';
                        const ini = getInitials(data);
                        const sty = getAvatarStyle(data, meta.row);
                        return `<div class="emp-cell">
                            <div class="emp-avatar" style="${sty}">${ini}</div>
                            <div>
                                <div class="emp-name-text">${data || '-'}</div>
                                <div class="emp-nip-text">${row.employee_pengenal || '-'}</div>
                            </div>
                        </div>`;
                    }
                },
                /* 1 — Location */
                { data: 'name',     className: 'text-center', render: d => d || '-' },
                /* 2 — PIN */
                {
                    data: 'pin',
                    className: 'text-center',
                    render: d => d ? `<span style="font-size:.75rem;color:#64748b;font-family:monospace">${d}</span>` : '-'
                },
                /* 3 — Roster */
                {
                    data: 'roster_name',
                    className: 'text-center',
                    defaultContent: '-',
                    render: function (data, type, row) {
                        return type === 'display' ? rosterBadge(data, row) : (data || '');
                    }
                },
                /* 4 — Position */
                { data: 'position_name',   className: 'text-center', render: d => d || '-' },
                /* 5 — Emp. status */
                {
                    data: 'status_employee',
                    className: 'text-center',
                    render: function (d) {
                        if (!d) return '-';
                        const cls = d === 'Permanent' ? 'fp-badge-permanent' : 'fp-badge-contract';
                        return `<span class="fp-badge ${cls}">${d}</span>`;
                    }
                },
                /* 6 — Scan date */
                {
                    data: 'scan_date',
                    className: 'text-center',
                    render: d => d ? `<span style="font-size:.75rem;color:#64748b">${d}</span>` : '-'
                },
                /* 7–12 — combine_1 … combine_6 (In / Out / Break In / Break Out / Ovt In / Ovt Out) */
                @php $combineClasses = ['time-in','time-out','time-break','time-break','time-ovt','time-ovt']; @endphp
                @for ($i = 1; $i <= 6; $i++)
               
                {
    data: 'combine_{{ $i }}',
    name: 'combine_{{ $i }}',
    className: 'text-center',
    render: function (d, type, row) {
        if (type !== 'display') return d || '';

        let html = timeCell(d, '{{ $combineClasses[$i - 1] }}');

        // 🔴 khusus IN pertama (combine_1)
        @if ($i === 1)
            if (row.is_late) {
                html = `<span class="text-danger fw-bold">${d ?? ''}</span>`;
            } else {
                html = `<span class="text-success">${d ?? ''}</span>`;
            }
        @endif

        return html;
    }
},
                @endfor
                /* 13 — Duration */
                {
                    data: 'duration',
                    className: 'text-center',
                    render: d => d
                        ? `<span style="font-weight:600;font-size:.775rem">${d}</span>`
                        : '<span class="time-null">–</span>'
                },
                /* 14 — Record status */
                {
                    data: 'updated',
                    className: 'text-center',
                    render: function (data, type, row) {
                        if (row.is_updated) {
                            return '<span class="fp-badge fp-badge-updated"><i class="fas fa-check me-1"></i>Updated</span>';
                        }
                        return '<span class="fp-badge fp-badge-original">Original</span>';
                    }
                },
                /* 15 — Action */
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center no-export',
                    render: function (data) {
                        return `<div class="action-wrap">${data}</div>`;
                    }
                }
            ],
            rowCallback: function (row, data) {
                if (data.is_edited == 1) {
                    $(row).addClass('row-edited');
                }
            },
           
//             drawCallback: function () {
//     let api = this.api();
//     let data = api.rows({ search: 'applied' }).data();

//     let total = data.length;
//     let ontime = 0;
//     let late = 0;
//     let updated = 0;
//     let missing = 0;

//     data.each(function (row) {
//         if (row.is_late) {
//             late++;
//         } else {
//             ontime++;
//         }

//         if (row.is_updated) {
//             updated++;
//         }

//         // contoh missing (kalau tidak ada in_1)
//         if (!row.in_1) {
//             missing++;
//         }
//     });

//     $('#stat-total').text(total.toLocaleString('id-ID'));
//     $('#stat-ontime').text(ontime.toLocaleString('id-ID'));
//     $('#stat-late').text(late.toLocaleString('id-ID'));
//     $('#stat-updated').text(updated.toLocaleString('id-ID'));
//     $('#stat-missing').text(missing.toLocaleString('id-ID'));
// },
drawCallback: function (settings) {
    const json = settings.json;
    if (!json || !json.stats) return;

    const s = json.stats;

    $('#stat-total').text(s.total.toLocaleString('id-ID'));
    $('#stat-ontime').text(s.ontime.toLocaleString('id-ID'));
    $('#stat-late').text(s.late.toLocaleString('id-ID'));
    $('#stat-updated').text(s.updated.toLocaleString('id-ID'));
    $('#stat-missing').text(s.missing.toLocaleString('id-ID'));
},
            initComplete: function () {
                /* move length and search controls into custom slots */
                const $length = $('.dataTables_length').addClass('d-flex align-items-center gap-2');
                $length.find('label').css({ fontSize: '.775rem', color: '#64748b', whiteSpace: 'nowrap' });
                $length.find('select').addClass('form-select form-select-sm').css({ height: '30px', fontSize: '.775rem', width: '70px' });

                const $search = $('.dataTables_filter');
                $search.find('input').addClass('form-control form-control-sm')
                    .css({ height: '30px', fontSize: '.775rem', minWidth: '180px' })
                    .attr('placeholder', 'Search employee, PIN...');
                $search.find('label').css('display', 'none');

                $('#custom-length').html($length);
                $('#custom-search').html($search);
                table.buttons().container().appendTo('#custom-buttons');
            }
        });

        /* ── Filter ── */
        $('#filterBtn').on('click', function () {
            table.ajax.reload();
        });

        /* ── Reset ── */
        $('#resetBtn').on('click', function () {
            const today = new Date();
            const y = today.getFullYear(), m = today.getMonth();
            const fmt = d => {
                return d.getFullYear() + '-' +
                    String(d.getMonth() + 1).padStart(2, '0') + '-' +
                    String(d.getDate()).padStart(2, '0');
            };
            $('#startDate').val(fmt(new Date(y, m - 1, 26)));
            $('#endDate').val(fmt(new Date(y, m, 25)));
            $('#store_name').val('').trigger('change');
            table.ajax.reload();
        });

        /* ── Recap absensi ── */
        $('#recapBtn').on('click', function () {
            const startDate = $('#startDate').val();
            const endDate   = $('#endDate').val();
            const storeName = $('#store_name').val();

            if (!startDate || !endDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Caution',
                    text: 'Select Start Date and End Date first.'
                });
                return;
            }

            Swal.fire({
                title: 'Attendance Recap',
                html: `The system will summarize attendance from <strong>${startDate}</strong> to <strong>${endDate}</strong>.<br>
                       <small style="color:#64748b">This process may take a few moments.</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1D9E75',
                confirmButtonText: 'Yes!',
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (!result.isConfirmed) return;

                $('#recapBtn').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin me-1"></i> Proccess...');

                $.ajax({
                    url: '{{ route('fingerprints.recap') }}',
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { start_date: startDate, end_date: endDate, store_name: storeName },
                    success: function (response) {
                        $('#recapBtn').prop('disabled', false)
                            .html('<i class="fas fa-rotate-right me-1"></i> Recap absensi');
                        Swal.fire({ icon: 'success', title: 'Success!', text: response.message })
                            .then(() => table.ajax.reload());
                    },
                    error: function (xhr) {
                        $('#recapBtn').prop('disabled', false)
                            .html('<i class="fas fa-rotate-right me-1"></i> Recap absensi');
                        const msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan saat memproses recap.';
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: msg });
                    }
                });
            });
        });

        /* ── Auto-refresh (skip when user is searching) ── */
        setInterval(function () {
            const isSearching = $('.dataTables_filter input').val().trim().length > 0;
            if (!isSearching) {
                table.ajax.reload(null, false);
            }
        }, 100000);

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