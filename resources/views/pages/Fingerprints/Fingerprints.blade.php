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
        .time-late  { color: #dc2626; font-weight: 700; background: #fef2f2; padding: 2px 6px; border-radius: 4px; }
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

        /* ═════════════════════════════════════════════════════
           ADD RECAP FEATURE — styles
           ═════════════════════════════════════════════════════ */
        .evidence-dropzone {
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            padding: 30px 20px;
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: all .2s;
        }
        .evidence-dropzone:hover,
        .evidence-dropzone.dragover {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        .evidence-dropzone i.upload-icon {
            font-size: 32px;
            color: #64748b;
            margin-bottom: 8px;
        }
        .evidence-dropzone.dragover i.upload-icon { color: #3b82f6; }
        .evidence-dropzone p { margin: 0; color: #475569; font-size: 13px; }
        .evidence-dropzone small { color: #94a3b8; }

        .evidence-file-list {
            margin-top: 12px;
            max-height: 200px;
            overflow-y: auto;
        }
        .evidence-file-item {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 12px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 6px;
        }
        .evidence-file-icon {
            width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 5px;
            flex-shrink: 0;
        }
        .evidence-file-icon.img { background: #dbeafe; color: #1d4ed8; }
        .evidence-file-icon.pdf { background: #fee2e2; color: #b91c1c; }
        .evidence-file-icon.doc { background: #dcfce7; color: #166534; }
        .evidence-file-icon.xls { background: #fef9c3; color: #854d0e; }
        .evidence-file-icon.other { background: #e2e8f0; color: #475569; }

        .evidence-file-info { flex: 1; min-width: 0; }
        .evidence-file-name {
            font-size: 13px; font-weight: 600; color: #0f172a;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .evidence-file-size { font-size: 11px; color: #64748b; }
        .evidence-file-remove {
            background: transparent; border: none; color: #dc2626; cursor: pointer;
            width: 28px; height: 28px; border-radius: 5px;
        }
        .evidence-file-remove:hover { background: #fee2e2; }

        .evidence-badge {
            display: inline-block; background:#fef3c7; color:#78350f;
            padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;
            margin-left: 6px;
        }

        /* Add Recap button */
        #addRecapBtn {
            background-color: #f59e0b;
            border: none;
            color: #fff;
            font-weight: 600;
        }
        #addRecapBtn:hover {
            background-color: #d97706;
            color: #fff;
        }

        /* Disabled state untuk tombol header */
        #addRecapBtn:disabled,
        #recapBtn:disabled {
            opacity: .5;
            cursor: not-allowed;
            pointer-events: none;
        }
        #addRecapBtn[disabled],
        #recapBtn[disabled] {
            opacity: .5;
            cursor: not-allowed;
        }

        /* Wrapper untuk tooltip pada disabled button */
        .btn-tooltip-wrap {
            display: inline-block;
            position: relative;
        }
        .btn-tooltip-wrap[data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 6px;
            background: #1e293b;
            color: #fff;
            padding: 6px 10px;
            border-radius: 5px;
            font-size: .72rem;
            white-space: nowrap;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,.15);
        }
        .btn-tooltip-wrap[data-tooltip]:hover::before {
            content: '';
            position: absolute;
            top: 100%;
            right: 18px;
            margin-top: 1px;
            border: 5px solid transparent;
            border-bottom-color: #1e293b;
            z-index: 1000;
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


                    <span id="addRecapWrap" class="btn-tooltip-wrap ms-auto"
                          data-tooltip="Klik Filter dulu untuk menampilkan data">
                        <button id="addRecapBtn" class="btn btn-sm" disabled
                            style="height:32px;font-size:.775rem">
                            <i class="fas fa-plus-circle"></i> Add Recap
                        </button>
                    </span>

                    <span id="recapWrap" class="btn-tooltip-wrap"
                          data-tooltip="Klik Filter dulu untuk menampilkan data">
                        <button id="recapBtn" class="btn btn-success btn-sm" disabled
                            style="height:32px;font-size:.775rem">
                            <i class="fas fa-rotate-right"></i> Recap absensi
                        </button>
                    </span>
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

{{-- ═══════════════════════════════════════════════════════════
     ADD RECAP — Modal Form
═══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="addRecapFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;border:none;overflow:hidden">
            <div class="modal-header" style="background:#1e293b;color:#fff">
                <h5 class="modal-title" style="font-weight:700">
                    <i class="fas fa-plus-circle"></i> Tambah Manual Recap
                </h5>
                <button type="button" class="close" style="color:#fff;opacity:.8" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding:25px;max-height:75vh;overflow-y:auto">

                {{-- Pilih Karyawan --}}
                <div class="form-group">
                    <label style="font-weight:600;color:#374151">
                        Pilih Karyawan <span style="color:#ef4444">*</span>
                    </label>
                    <select id="manualEmpIds" class="form-control select2-manual" multiple required style="width:100%">
                    </select>
                    <small class="text-muted">Bisa pilih lebih dari satu karyawan</small>
                </div>

                {{-- Scan Date & End Date --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label style="font-weight:600;color:#374151">
                                Start Date <span style="color:#ef4444">*</span>
                            </label>
                            <input type="date" id="manualScanDate" class="form-control" required>
                            <small class="text-muted">Tanggal mulai</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label style="font-weight:600;color:#374151">
                                End Date <span style="color:#ef4444">*</span>
                            </label>
                            <input type="date" id="manualEndDate" class="form-control" required>
                            <small class="text-muted">Tanggal selesai (isi sama jika hanya 1 hari)</small>
                        </div>
                    </div>
                </div>

                {{-- Shift --}}
                <div class="form-group">
                    <label style="font-weight:600;color:#374151">
                        <i class="fas fa-clock"></i> Shift
                    </label>
                    <select id="manualShiftId" class="form-control select2-manual-shift" style="width:100%">
                        <option value="">-- Gunakan shift dari roster karyawan --</option>
                    </select>
                </div>

                {{-- Bukti Pendukung --}}
                <div class="form-group">
                    <label style="font-weight:600;color:#374151">
                        <i class="fas fa-paperclip"></i>
                        Bukti Pendukung <span style="color:#ef4444">*</span>
                        <span class="evidence-badge">WAJIB</span>
                    </label>
                    <div class="evidence-dropzone" id="evidenceDropzone">
                        <i class="fas fa-cloud-upload-alt upload-icon"></i>
                        <p><strong>Click</strong> atau <strong>Drag &amp; Drop</strong> file ke sini</p>
                        <small>JPG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX · max 5 MB per file · bisa multiple</small>
                        <input type="file" id="evidenceFiles" multiple
                            accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx"
                            style="display:none">
                    </div>
                    <div id="evidenceFileList" class="evidence-file-list"></div>
                </div>

                {{-- Alasan --}}
                <div class="form-group">
                    <label style="font-weight:600;color:#374151">
                        Alasan <span style="color:#ef4444">*</span>
                    </label>
                    <textarea id="manualReason" class="form-control" rows="4" required
                        placeholder="Contoh: Karyawan telah klarifikasi bahwa masuk kerja namun mesin fingerprint rusak..."
                        minlength="10" maxlength="1000"></textarea>
                    <small class="text-muted">Minimal 10 karakter.</small>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitManualRecap()"
                    style="background:#1d4ed8;border:none;font-weight:600" id="submitManualBtn">
                    <i class="fas fa-paper-plane"></i> Submit &amp; Kirim Notifikasi
                </button>
            </div>
        </div>
    </div>
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

    /* ── Toggle Header Buttons ── */
    function toggleHeaderButtons(enable) {
        const addBtn    = document.getElementById('addRecapBtn');
        const recapBtn  = document.getElementById('recapBtn');
        const addWrap   = document.getElementById('addRecapWrap');
        const recapWrap = document.getElementById('recapWrap');

        if (enable) {
            addBtn.disabled   = false;
            recapBtn.disabled = false;
            addWrap.removeAttribute('data-tooltip');
            recapWrap.removeAttribute('data-tooltip');
        } else {
            addBtn.disabled   = true;
            recapBtn.disabled = true;
            addWrap.setAttribute('data-tooltip', 'Klik Filter dulu untuk menampilkan data');
            recapWrap.setAttribute('data-tooltip', 'Klik Filter dulu untuk menampilkan data');
        }
    }

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
                /* 0 — Employee */
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
                { data: 'name', className: 'text-center', render: d => d || '-' },
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
                { data: 'position_name', className: 'text-center', render: d => d || '-' },
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

                /* 7 — In (combine_1) */
                {
                    data: 'combine_1',
                    name: 'combine_1',
                    className: 'text-center',
                    render: function (d, type, row) {
                        if (type !== 'display') return d || '';
                        const cls = row.is_late_in ? 'time-late' : 'time-in';
                        return timeCell(d, cls);
                    }
                },
                /* 8–12 — combine_2 … combine_6 */
                @php $combineClasses = ['time-out','time-break','time-break','time-ovt','time-ovt']; @endphp
                @for ($i = 2; $i <= 6; $i++)
                {
                    data: 'combine_{{ $i }}',
                    name: 'combine_{{ $i }}',
                    className: 'text-center',
                    render: function (d, type) {
                        if (type !== 'display') return d || '';
                        return timeCell(d, '{{ $combineClasses[$i - 2] }}');
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

            drawCallback: function (settings) {
                const json = settings.json;
                if (!json) return;
                if (json.recordsTotal !== undefined) {
                    $('#stat-total').text(Number(json.recordsTotal).toLocaleString('id-ID'));
                }
                if (json.stats) {
                    const s = json.stats;
                    $('#stat-ontime').text(s.on_time  !== undefined ? Number(s.on_time).toLocaleString('id-ID')  : '–');
                    $('#stat-late').text(s.late       !== undefined ? Number(s.late).toLocaleString('id-ID')     : '–');
                    $('#stat-updated').text(s.updated !== undefined ? Number(s.updated).toLocaleString('id-ID')  : '–');
                    $('#stat-missing').text(s.missing !== undefined ? Number(s.missing).toLocaleString('id-ID')  : '–');
                }
                const hasData = (json.recordsDisplay ?? json.recordsTotal ?? 0) > 0;
                toggleHeaderButtons(hasData);
            },
            initComplete: function () {
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
            toggleHeaderButtons(false);
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
            toggleHeaderButtons(false);
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

        /* ── Auto-refresh ── */
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

        /* ═══════════════════════════════════════════════════════════
           ADD RECAP FEATURE — handlers
           ═══════════════════════════════════════════════════════════ */

        let evidenceFiles = [];

        // Load semua karyawan
        function loadEmployeeList() {
            fetch('{{ route("fingerprints.employee-list") }}')
                .then(r => r.json())
                .then(data => {
                    const select = $('#manualEmpIds');
                    select.empty();
                    (data.data || []).forEach(emp => {
                        const text = `${emp.name} — ${emp.store} (PIN: ${emp.pin})`;
                        select.append(new Option(text, emp.id));
                    });
                })
                .catch(err => console.error('Gagal load employees:', err));
        }

        // Load shift list
        function loadShiftList() {
            fetch('{{ route("manual-recap.shift-list") }}')
                .then(r => r.json())
                .then(data => {
                    const select = $('#manualShiftId');
                    select.empty();
                    select.append(new Option('-- Gunakan shift dari roster karyawan --', ''));
                    (data.data || []).forEach(shift => {
                        const text = `${shift.name} (${shift.time})`;
                        select.append(new Option(text, shift.id));
                    });
                })
                .catch(err => console.error('Gagal load shifts:', err));
        }

        // Init dropzone
        function initEvidenceDropzone() {
            const dropzone  = document.getElementById('evidenceDropzone');
            const fileInput = document.getElementById('evidenceFiles');

            dropzone.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', e => handleFiles(e.target.files));

            ['dragenter', 'dragover'].forEach(ev =>
                dropzone.addEventListener(ev, e => {
                    e.preventDefault();
                    dropzone.classList.add('dragover');
                })
            );
            ['dragleave', 'drop'].forEach(ev =>
                dropzone.addEventListener(ev, e => {
                    e.preventDefault();
                    dropzone.classList.remove('dragover');
                })
            );
            dropzone.addEventListener('drop', e => handleFiles(e.dataTransfer.files));
        }

        function handleFiles(fileList) {
            const allowedTypes = [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ];
            const maxSize = 5 * 1024 * 1024;

            Array.from(fileList).forEach(file => {
                if (!allowedTypes.includes(file.type)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Tipe file tidak diizinkan',
                        html: `<strong>${file.name}</strong><br><small>Hanya JPG, PNG, GIF, WEBP, PDF, DOC, DOCX, XLS, XLSX.</small>`,
                        confirmButtonColor: '#dc2626'
                    });
                    return;
                }
                if (file.size > maxSize) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File terlalu besar',
                        html: `<strong>${file.name}</strong><br><small>Ukuran file maksimal 5 MB per file.</small>`,
                        confirmButtonColor: '#dc2626'
                    });
                    return;
                }
                if (evidenceFiles.some(f => f.name === file.name && f.size === file.size)) return;
                evidenceFiles.push(file);
            });

            renderFileList();
            document.getElementById('evidenceFiles').value = '';
        }

        function renderFileList() {
            const list = document.getElementById('evidenceFileList');
            list.innerHTML = '';

            evidenceFiles.forEach((file, idx) => {
                const iconClass = getFileIconClass(file.type);
                const icon      = getFileIcon(file.type);
                const sizeStr   = formatFileSize(file.size);

                const item = document.createElement('div');
                item.className = 'evidence-file-item';
                item.innerHTML = `
                    <div class="evidence-file-icon ${iconClass}"><i class="${icon}"></i></div>
                    <div class="evidence-file-info">
                        <div class="evidence-file-name">${file.name}</div>
                        <div class="evidence-file-size">${sizeStr}</div>
                    </div>
                    <button type="button" class="evidence-file-remove" data-idx="${idx}" title="Hapus">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                list.appendChild(item);
            });
        }

        $(document).on('click', '.evidence-file-remove', function () {
            const idx = parseInt($(this).data('idx'));
            evidenceFiles.splice(idx, 1);
            renderFileList();
        });

        function getFileIconClass(mime) {
            if (mime.startsWith('image/')) return 'img';
            if (mime === 'application/pdf') return 'pdf';
            if (mime.includes('word')) return 'doc';
            if (mime.includes('sheet') || mime.includes('excel')) return 'xls';
            return 'other';
        }

        function getFileIcon(mime) {
            if (mime.startsWith('image/')) return 'fas fa-image';
            if (mime === 'application/pdf') return 'fas fa-file-pdf';
            if (mime.includes('word')) return 'fas fa-file-word';
            if (mime.includes('sheet') || mime.includes('excel')) return 'fas fa-file-excel';
            return 'fas fa-file';
        }

        function formatFileSize(bytes) {
            const units = ['B', 'KB', 'MB', 'GB'];
            let i = 0;
            while (bytes >= 1024 && i < units.length - 1) { bytes /= 1024; i++; }
            return bytes.toFixed(2) + ' ' + units[i];
        }

        // Init Select2 di modal
        let selectManualInitialized = false;
        function initManualSelect2() {
            if (selectManualInitialized) return;
            $('#manualEmpIds').select2({
                dropdownParent: $('#addRecapFormModal'),
                placeholder: 'Pilih karyawan...',
                allowClear: true
            });
            $('#manualShiftId').select2({
                dropdownParent: $('#addRecapFormModal'),
                placeholder: 'Gunakan shift dari roster...',
                allowClear: true
            });
            selectManualInitialized = true;
        }

        // Step 1: Klik "+Add Recap"
        $('#addRecapBtn').on('click', function () {
            Swal.fire({
                icon: 'warning',
                iconColor: '#f59e0b',
                title: '⚠️ Peringatan Pertanggungjawaban',
                html: `
                    <div style="text-align:left;padding:10px 0">
                        <p style="color:#1f2937;font-size:15px;line-height:1.6;margin-bottom:10px">
                            <strong>Data Ini Akan Dikirim Ke Email dan Whatsapp Head HR dan IT Sebagai Pertanggung Jawaban.</strong>
                        </p>
                        <p style="color:#6b7280;font-size:13px;margin:0">
                            Pastikan data &amp; bukti yang Anda input sudah benar dan dapat dipertanggungjawabkan.
                        </p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-check"></i> Saya Mengerti, Lanjutkan',
                cancelButtonText: 'Batal',
                focusCancel: true
            }).then(result => {
                if (result.isConfirmed) openAddRecapForm();
            });
        });

        // Step 2: Buka form
        function openAddRecapForm() {
            loadEmployeeList();
            loadShiftList();
            initManualSelect2();
            initEvidenceDropzone();

            // Reset form
            $('#manualEmpIds').val(null).trigger('change');
            $('#manualShiftId').val('').trigger('change');
            document.getElementById('manualScanDate').value = '';
            document.getElementById('manualEndDate').value  = '';
            document.getElementById('manualReason').value   = '';
            evidenceFiles = [];
            renderFileList();

            $('#addRecapFormModal').modal('show');
        }

        // Step 3: Submit
        window.submitManualRecap = function () {
            const empIds   = $('#manualEmpIds').val() || [];
            const scanDate = document.getElementById('manualScanDate').value;
            const endDate  = document.getElementById('manualEndDate').value;
            const shiftId  = $('#manualShiftId').val();
            const reason   = document.getElementById('manualReason').value.trim();

            // Validasi
            if (empIds.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Pilih minimal 1 karyawan.' });
                return;
            }
            if (!scanDate) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Scan Date wajib diisi.' });
                return;
            }
            if (!endDate) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'End Date wajib diisi.' });
                return;
            }
            if (endDate < scanDate) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'End Date tidak boleh sebelum Scan Date.' });
                return;
            }
            if (evidenceFiles.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Upload minimal 1 file bukti.' });
                return;
            }
            if (reason.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Alasan wajib diisi.' });
                return;
            }
            if (reason.length < 10) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Alasan minimal 10 karakter.' });
                return;
            }
            if (reason.length > 1000) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Alasan maksimal 1000 karakter.' });
                return;
            }

            const diffDays = Math.round((new Date(endDate) - new Date(scanDate)) / (1000*60*60*24)) + 1;

            Swal.fire({
                title: 'Konfirmasi',
                html: `Akan menambah manual recap untuk <strong>${empIds.length} karyawan</strong><br>
                       Periode: <strong>${scanDate}</strong> s/d <strong>${endDate}</strong> (<strong>${diffDays} hari</strong>)<br>
                       Dengan <strong>${evidenceFiles.length} file bukti</strong>.<br>
                       <small style="color:#64748b">Lanjutkan?</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1d4ed8',
                confirmButtonText: 'Ya, submit',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                empIds.forEach(id => formData.append('employee_ids[]', id));
                formData.append('scan_date', scanDate);
                formData.append('end_date',  endDate);
                if (shiftId) formData.append('shift_id', shiftId);
                formData.append('reason', reason);
                evidenceFiles.forEach(file => formData.append('evidence_files[]', file));

                const btn = document.getElementById('submitManualBtn');
                btn.disabled  = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

                fetch('{{ route("manual-recap.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData,
                })
                .then(async (response) => {
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        const error = new Error(data.message || 'Terjadi kesalahan');
                        error.status = response.status;
                        error.errors = data.errors || null;
                        error.data   = data;
                        throw error;
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            confirmButtonColor: '#1d4ed8'
                        }).then(() => {
                            $('#addRecapFormModal').modal('hide');
                            evidenceFiles = [];
                            renderFileList();
                            table.ajax.reload();
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal menambah recap.' });
                    }
                })
                .catch(err => {
                    if (err.status === 422 && err.errors) {
                        const allMessages = Object.values(err.errors)
                            .flat()
                            .map(msg => `• ${msg}`)
                            .join('<br>');
                        Swal.fire({
                            icon: 'warning',
                            title: 'Validasi Gagal',
                            html: `<div style="text-align:left;font-size:14px;line-height:1.6">${allMessages}</div>`,
                            confirmButtonColor: '#f59e0b'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: err.message || 'Terjadi kesalahan, coba lagi.',
                            confirmButtonColor: '#dc2626'
                        });
                    }
                })
                .finally(() => {
                    btn.disabled  = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit & Kirim Notifikasi';
                });
            });
        };
    });
    </script>
@endpush