@extends('layouts.app')
@section('title', 'Roster & Schedule')
@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* ── Wrapper ── */
        .roster-scroll {
            overflow-x: auto;
            border-radius: 8px;
        }

        .roster-table {
            border-collapse: collapse;
            width: 100%;
            font-size: 13px;
        }

        /* ── Header ── */
        .roster-table thead th {
            background: #1e293b;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            padding: 10px 6px;
            text-align: center;
            white-space: nowrap;
            border: 1px solid #334155;
        }

        .roster-table thead th.col-emp {
            position: sticky;
            left: 0;
            z-index: 4;
            background: #0f172a;
            min-width: 200px;
            text-align: left;
            padding-left: 14px;
        }

        .roster-table thead th.weekend {
            background: #7f1d1d;
        }

        .roster-table thead th.today {
            background: #78350f;
        }

        /* ── Body ── */
        .roster-table tbody td {
            border: 1px solid #e2e8f0;
            padding: 5px 4px;
            vertical-align: middle;
            text-align: center;
            background: #fff;
        }

        .roster-table tbody td.col-emp {
            position: sticky;
            left: 0;
            z-index: 2;
            background: #f8fafc;
            text-align: left;
            padding: 8px 14px;
            border-right: 2px solid #cbd5e1;
            min-width: 200px;
        }

        .roster-table tbody tr:nth-child(even) td.col-emp {
            background: #f1f5f9;
        }

        .roster-table tbody tr:hover td {
            background: #f0f9ff !important;
        }


        /* ── Employee info ── */
        .emp-name {
            font-weight: 600;
            color: #0f172a;
            font-size: 13px;
        }

        .emp-meta {
            font-size: 10px;
            color: #64748b;
            margin-top: 1px;
        }

        /* ── Status Badge ── */
        .emp-status {
            display: inline-block;
            font-size: 9px;
            font-weight: 700;
            padding: 1px 7px;
            border-radius: 10px;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-pkwt {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-ojt {
            background: #fef9c3;
            color: #854d0e;
        }

        .status-dw {
            background: #fce7f3;
            color: #9d174d;
        }

        /* ── Day cell ── */
        .day-cell {
            cursor: pointer;
            min-width: 85px;
        }

        .day-cell:hover {
            background: #eff6ff !important;
        }

        .day-cell.weekend {
            background: #fff1f2 !important;
        }

        .day-cell.today {
            background: #fefce8 !important;
            outline: 2px solid #eab308;
            outline-offset: -2px;
        }

        /* ── Roster badge ── */
        .r-badge {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            border-radius: 5px;
            padding: 3px 8px;
            min-width: 72px;
            border: 1px solid transparent;
        }

        .r-badge .r-name {
            font-weight: 700;
            font-size: 11px;
            white-space: nowrap;
        }

        .r-badge .r-time {
            font-size: 10px;
            white-space: nowrap;
            opacity: .85;
        }

        .r-badge .r-notes {
            font-size: 9px;
            font-style: italic;
            margin-top: 2px;
            max-width: 80px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            opacity: 0.9;
        }

        .r-work {
            background: #dbeafe;
            border-color: #93c5fd;
        }

        .r-work .r-name {
            color: #1d4ed8;
        }

        .r-work .r-time {
            color: #3b82f6;
        }

        .r-work .r-notes {
            color: #1e40af;
        }

        .r-off {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .r-off .r-name {
            color: #64748b;
        }

        .r-off .r-notes {
            color: #475569;
        }

        .r-holiday {
            background: #fef9c3;
            border-color: #fde047;
        }

        .r-holiday .r-name {
            color: #854d0e;
        }

        .r-holiday .r-notes {
            color: #92400e;
            font-weight: 500;
        }

        .r-leave {
            background: #f3e8ff;
            border-color: #d8b4fe;
        }

        .r-leave .r-name {
            color: #7e22ce;
        }

        .r-leave .r-notes {
            color: #6b21a8;
        }

        /* ── Sick (oranye) ── */
        .r-sick {
            background: #ffedd5;
            border-color: #fdba74;
        }

        .r-sick .r-name {
            color: #c2410c;
        }

        .r-sick .r-notes {
            color: #9a3412;
        }

        /* ── TOIL Off (teal — beda dari Off biasa) ── */
        .r-toiloff {
            background: #ccfbf1;
            border-color: #5eead4;
        }

        .r-toiloff .r-name {
            color: #0f766e;
        }

        .r-toiloff .r-notes {
            color: #115e59;
        }

        .r-empty {
            color: #cbd5e1;
            font-size: 20px;
            line-height: 1;
        }

        /* ── Filter card ── */
        .filter-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 8px rgba(0, 0, 0, .08);
            padding: 20px 24px;
            margin-bottom: 16px;
        }

        .f-label {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
            display: block;
            white-space: nowrap;
        }

        .f-control {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 13px;
            color: #0f172a;
        }

        .f-control:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .filter-item {
            min-width: 180px;
        }

        .filter-item-date {
            min-width: 160px;
        }

        .filter-item-btn {
            display: flex;
            gap: 8px;
            align-items: flex-end;
            padding-bottom: 1px;
        }

        /* ── Buttons ── */
        .btn-primary-r {
            background: #1d4ed8;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary-r:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .btn-secondary-r {
            background: #e2e8f0;
            color: #334155;
            border: none;
            border-radius: 6px;
            padding: 8px 14px;
            font-size: 13px;
            cursor: pointer;
        }

        .btn-danger-r {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
            border-radius: 6px;
            padding: 5px 10px;
            font-size: 12px;
            cursor: pointer;
        }

        .btn-danger-r:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .btn-success-r {
            background: #16a34a;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-success-r:hover {
            background: #15803d;
        }

        .btn-success-r:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* ── Modal ── */
        .m-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .m-overlay.open {
            display: flex;
        }

        .m-box {
            background: #fff;
            border-radius: 12px;
            width: 320px;
            max-width: 95vw;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
            overflow: hidden;
        }

        .m-head {
            background: #1e293b;
            color: #fff;
            padding: 14px 18px;
            font-weight: 700;
            font-size: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 10;
        }

        .m-head button {
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 20px;
            cursor: pointer;
            line-height: 1;
        }

        .m-head button:hover {
            color: #fff;
        }

        .m-body {
            padding: 18px;
        }

        .m-foot {
            padding: 12px 18px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        /* ── Toast ── */
        #rosterToast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 99999;
            background: #0f172a;
            color: #fff;
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 13px;
            font-weight: 500;
            display: none;
            max-width: 320px;
        }

        /* ── Legend Checkbox ── */
        .legend {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 12px;
            align-items: center;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #475569;
            cursor: pointer;
            user-select: none;
        }

        .legend-item input[type="checkbox"] {
            width: 15px;
            height: 15px;
            cursor: pointer;
            accent-color: #3b82f6;
        }

        /* ── Empty state ── */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            display: block;
        }

        .empty-state h5 {
            font-size: 16px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
        }

        .empty-state p {
            font-size: 13px;
            color: #94a3b8;
        }

        /* ── Auto Roster Static: Periode card ── */
        .ag-period-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 14px;
        }

        .ag-period-title {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .ag-period-title small {
            font-size: 11px;
            font-weight: 400;
            color: #94a3b8;
        }

        .btn-update-preview {
            background: none;
            border: none;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            padding: 4px 0;
            margin-top: 4px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-update-preview:hover {
            color: #1e40af;
            text-decoration: underline;
        }

        .btn-update-preview:disabled {
            color: #94a3b8;
            cursor: not-allowed;
        }

        .auto-roster-preview {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 12px;
            font-size: 12px;
            color: #1e40af;
            margin-bottom: 14px;
        }

        .auto-roster-preview .preview-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }

        .auto-roster-preview .preview-row strong {
            color: #1e3a8a;
        }

        .auto-roster-preview .preview-row.created strong {
            color: #16a34a;
            font-weight: 700;
        }

        .auto-roster-preview .preview-row.skipped strong {
            color: #f59e0b;
            font-weight: 700;
        }

        /* ══════════════════════════════════════════════════════
                                           AUTO ROSTER OTHER STORE — Step Indicator
                                           ══════════════════════════════════════════════════════ */
        .aro-step-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .aro-step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .aro-step-dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #94a3b8;
            font-weight: 700;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .25s ease;
        }

        .aro-step-item.active .aro-step-dot {
            background: #1d4ed8;
            color: #fff;
        }

        .aro-step-item.done .aro-step-dot {
            background: #16a34a;
            color: #fff;
        }

        .aro-step-label {
            font-size: 10px;
            margin-top: 4px;
            color: #94a3b8;
            white-space: nowrap;
        }

        .aro-step-item.active .aro-step-label {
            color: #1d4ed8;
            font-weight: 600;
        }

        .aro-step-item.done .aro-step-label {
            color: #16a34a;
        }

        .aro-step-line {
            width: 60px;
            height: 2px;
            background: #e2e8f0;
            margin-bottom: 18px;
            transition: all .25s ease;
        }

        .aro-step-line.done {
            background: #16a34a;
        }

        /* ARO modal body scroll */
        .aro-modal-body {
            padding: 18px;
            max-height: 70vh;
            overflow-y: auto;
        }

        /* ARO day pattern table */
        .aro-day-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .aro-day-table th {
            background: #1e293b;
            color: #fff;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }

        .aro-day-table td {
            border-bottom: 1px solid #f1f5f9;
            padding: 6px 8px;
            vertical-align: middle;
        }

        .aro-day-table tr:last-child td {
            border-bottom: none;
        }

        .aro-day-table tr.weekend-row td {
            background: #fff8f8;
        }

        /* ARO info box */
        .aro-info-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 11px;
            color: #92400e;
            margin-bottom: 14px;
            line-height: 1.6;
        }

        .aro-preview-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 12px;
            font-size: 12px;
            margin-bottom: 14px;
        }

        .aro-preview-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            color: #166534;
        }

        .aro-preview-row strong {
            color: #14532d;
        }

        /* ARO result card */
        .aro-result-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 14px;
        }

        .aro-result-card {
            border-radius: 8px;
            padding: 14px;
            text-align: center;
        }

        .aro-result-card .val {
            font-size: 26px;
            font-weight: 700;
        }

        .aro-result-card .lbl {
            font-size: 11px;
            margin-top: 2px;
        }

        .aro-card-created {
            background: #f0fdf4;
        }

        .aro-card-created .val {
            color: #16a34a;
        }

        .aro-card-created .lbl {
            color: #4ade80;
        }

        .aro-card-updated {
            background: #eff6ff;
        }

        .aro-card-updated .val {
            color: #1d4ed8;
        }

        .aro-card-updated .lbl {
            color: #60a5fa;
        }

        .aro-card-skipped {
            background: #fafafa;
        }

        .aro-card-skipped .val {
            color: #64748b;
        }

        .aro-card-skipped .lbl {
            color: #94a3b8;
        }

        .aro-card-ph {
            background: #fefce8;
        }

        .aro-card-ph .val {
            color: #ca8a04;
        }

        .aro-card-ph .lbl {
            color: #fbbf24;
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">


            <div class="section-header">
                <div class="d-flex align-items-center flex-wrap mb-2" style="gap: 12px;">
                    <h1 class="mb-0 mr-auto">Roster & Schedule</h1>

                    @if ($storeId)
                        @php
                            $autoGenerateStores = ['Head Office', 'Holding', 'Distribution Center'];
                            $currentStoreName = optional($stores->firstWhere('id', $storeId))->name ?? '';
                            $showAutoGenerate = in_array($currentStoreName, $autoGenerateStores);
                        @endphp

                        <div class="d-flex flex-wrap ml-auto" style="gap: 8px;">
                            @can('ManageRoster')
                                @if ($showAutoGenerate)
                                    <button class="btn-success-r" onclick="confirmAutoGenerate()"
                                        title="Auto generate roster untuk periode yang dipilih">
                                        <i class="fas fa-magic"></i>
                                        <span class="d-none d-sm-inline"> Auto Generate Roster</span>
                                        <span class="d-inline d-sm-none"> Auto Generate</span>
                                    </button>
                                @else
                                    {{-- <button class="btn-success-r" onclick="openAroModal()"
                                        title="Auto generate roster mingguan dengan pola kustom">
                                        <i class="fas fa-calendar-week"></i>
                                        <span class="d-none d-sm-inline"> Auto Generate Roster</span>
                                        <span class="d-inline d-sm-none"> Auto Generate</span>
                                    </button> --}}
                                    {{-- <button class="btn-secondary-r" onclick="confirmCopyRoster()">
                                        <i class="fas fa-copy"></i>
                                        <span class="d-none d-sm-inline"> Copy Roster</span>
                                    </button> --}}
                                @endif

                                <button class="btn-secondary-r" onclick="downloadTemplate()">
                                    <i class="fas fa-download"></i>
                                    <span class="d-none d-sm-inline"> Download Template</span>
                                </button>

                                <button class="btn-primary-r" onclick="openImportModal()">
                                    <i class="fas fa-file-import"></i>
                                    <span class="d-none d-sm-inline">Import Excel</span>
                                </button>

                                <button class="btn-danger-r" onclick="openModal('modalBulkDelete')">
                                    <i class="fas fa-trash"></i>
                                    <span class="d-none d-sm-inline"> Bulk Delete</span>
                                </button>
                            @endcan
                            @can('ManageRosterSPVManager')
                                @if ($isSupervisorOrManager && $isRosterOpen)
                                    <button class="btn-secondary-r" onclick="downloadTemplate()">
                                        <i class="fas fa-download"></i>
                                        <span class="d-none d-sm-inline"> Download Template</span>
                                    </button>

                                    <button class="btn-primary-r" onclick="openImportModal()">
                                        <i class="fas fa-file-import"></i>
                                        <span class="d-none d-sm-inline"> Import Excel</span>
                                    </button>

                                    <button class="btn-danger-r" onclick="openModal('modalBulkDelete')">
                                        <i class="fas fa-trash"></i>
                                        <span class="d-none d-sm-inline"> Bulk Delete</span>
                                    </button>
                                @else
                                    <span class="d-none d-sm-inline">Closed</span>
                                @endif
                            @endcan

                        </div>

                    @endif
                    {{-- Tombol hanya muncul kalau ada storeId ATAU SPV All Location --}}
{{-- @if ($storeId || ($canManageSPV && !$canManageAll))

    @php
        $autoGenerateStores = ['Head Office', 'Holding', 'Distribution Center'];
        $currentStoreName = $storeId
            ? (optional($stores->firstWhere('id', $storeId))->name ?? '')
            : '';
        $showAutoGenerate = in_array($currentStoreName, $autoGenerateStores);
    @endphp

    <div class="d-flex flex-wrap ml-auto" style="gap: 8px;">
        @can('ManageRoster')
            @if ($showAutoGenerate)
                <button class="btn-success-r" onclick="confirmAutoGenerate()"
                    title="Auto generate roster untuk periode yang dipilih">
                    <i class="fas fa-magic"></i>
                    <span class="d-none d-sm-inline"> Auto Generate Roster</span>
                    <span class="d-inline d-sm-none"> Auto Generate</span>
                </button>
            @else
                <button class="btn-success-r" onclick="openAroModal()"
                    title="Auto generate roster mingguan dengan pola kustom">
                    <i class="fas fa-calendar-week"></i>
                    <span class="d-none d-sm-inline"> Auto Generate Roster</span>
                    <span class="d-inline d-sm-none"> Auto Generate</span>
                </button>
                <button class="btn-secondary-r" onclick="confirmCopyRoster()">
                    <i class="fas fa-copy"></i>
                    <span class="d-none d-sm-inline"> Copy Roster</span>
                </button>
            @endif

            <button class="btn-secondary-r" onclick="downloadTemplate()">
                <i class="fas fa-download"></i>
                <span class="d-none d-sm-inline"> Download Template</span>
            </button>

            <button class="btn-primary-r" onclick="openImportModal()">
                <i class="fas fa-file-import"></i>
                <span class="d-none d-sm-inline"> Import Excel</span>
            </button>

            <button class="btn-danger-r" onclick="openModal('modalBulkDelete')">
                <i class="fas fa-trash"></i>
                <span class="d-none d-sm-inline"> Bulk Delete</span>
            </button>
        @endcan

        @can('ManageRosterSPVManager')
            @if ($isSupervisorOrManager && $isRosterOpen)
                @if($storeId)
                    <button class="btn-secondary-r" onclick="downloadTemplate()">
                        <i class="fas fa-download"></i>
                        <span class="d-none d-sm-inline"> Download Template</span>
                    </button>

                    <button class="btn-primary-r" onclick="openImportModal()">
                        <i class="fas fa-file-import"></i>
                        <span class="d-none d-sm-inline"> Import Excel</span>
                    </button>
                @endif

                <button class="btn-danger-r" onclick="openModal('modalBulkDelete')">
                    <i class="fas fa-trash"></i>
                    <span class="d-none d-sm-inline"> Bulk Delete</span>
                </button>
            @else
                <span class="d-none d-sm-inline">Closed</span>
            @endif
        @endcan
    </div>

@endif --}}

                </div>
            </div>

            <div class="section-body">

                {{-- ── Filter ── --}}
                <div class="filter-card">
                    <form method="GET" action="{{ route('roster.index') }}" class="d-flex flex-wrap align-items-end"
                        style="gap: 20px;">
                        <div class="filter-item">
                            <label class="f-label">
                                Location
                                <span style="color:#ef4444">*</span>
                                <small style="color:#94a3b8;font-weight:400">(Required)</small>
                            </label>
                            {{-- @can('ManageRoster')
                                <select name="store_id" class="f-control select2" style="min-width:180px" required>
                                    <option value="">Choose Location</option>
                                    @foreach ($stores as $store)
                                        <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>
                                            {{ $store->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                @if (isset($myStores) && $myStores->count() > 1)
                                    <select name="store_id" class="f-control select2" style="min-width:180px" required>
                                        @foreach ($myStores as $store)
                                            <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>
                                                {{ $store->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="hidden" name="store_id" value="{{ $myStoreId }}">
                                    <input type="text" class="f-control" value="{{ $myStoreName }}" disabled
                                        style="min-width:180px; background:#f1f5f9;">
                                @endif
                            @endcan --}}
                            @can('ManageRoster')
    <select name="store_id" class="f-control select2" style="min-width:180px" required>
        <option value="">Choose Location</option>
        @foreach ($stores as $store)
            <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>
                {{ $store->name }}
            </option>
        @endforeach
    </select>
{{-- @elsecan('ManageRosterSPVManager')
    @if($myStores->count() > 1)
        <select name="store_id" class="f-control select2" style="min-width:180px" required>
            <option value="">Choose Location</option>
            @foreach ($myStores as $store)
                <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>
                    {{ $store->name }}
                </option>
            @endforeach
        </select>
    @else
        <input type="hidden" name="store_id" value="{{ $myStoreId }}">
        <input type="text" class="f-control" value="{{ $myStoreName }}" disabled
            style="min-width:180px;background:#f1f5f9;">
    @endif --}}
    {{-- @elsecan('ManageRosterSPVManager')
    @if($myStores->count() > 1)
        <select name="store_id" class="f-control select2" style="min-width:180px">
            <option value="">All Location</option> 
            @foreach ($myStores as $store)
                <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>
                    {{ $store->name }}
                </option>
            @endforeach
        </select>
    @else
        <input type="hidden" name="store_id" value="{{ $myStoreId }}">
        <input type="text" class="f-control" value="{{ $myStoreName }}" disabled
            style="min-width:180px;background:#f1f5f9;">
    @endif --}}
    {{-- @elsecan('ManageRosterSPVManager')
    @if($myStores->count() > 1)
        <select name="store_id" class="f-control select2" style="min-width:180px">
            <option value="">All Location</option>
            @foreach ($myStores as $store)
                <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>
                    {{ $store->name }}
                </option>
            @endforeach
        </select>
    @else
        <input type="hidden" name="store_id" value="{{ $myStoreId }}">
        <input type="text" class="f-control" value="{{ $myStoreName }}" disabled
            style="min-width:180px;background:#f1f5f9;">
    @endif
@else
    <input type="hidden" name="store_id" value="{{ $myStoreId }}">
    <input type="text" class="f-control" value="{{ $myStoreName }}" disabled
        style="min-width:180px;background:#f1f5f9;">
@endcan --}}
@elsecan('ManageRosterSPVManager')
    @if($myStores->count() >= 1)  {{-- ← ganti > 1 jadi >= 1 --}}
        <select name="store_id" class="f-control select2" style="min-width:180px">
            <option value="">All Location</option>
            @foreach ($myStores as $store)
                <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>
                    {{ $store->name }}
                </option>
            @endforeach
        </select>
    @else
        {{-- Tidak punya store sama sekali — fixed primary store --}}
        <input type="hidden" name="store_id" value="{{ $myStoreId }}">
        <input type="text" class="f-control" value="{{ $myStoreName }}" disabled
            style="min-width:180px;background:#f1f5f9;">
    @endif
@else
    {{-- ViewRoster — fixed store sendiri --}}
    <input type="hidden" name="store_id" value="{{ $myStoreId }}">
    <input type="text" class="f-control" value="{{ $myStoreName }}" disabled
        style="min-width:180px;background:#f1f5f9;">
@endcan
                        </div>
                        @can('ManageRoster')
                            <div class="filter-item-date">
                                <label class="f-label">Start Date</label>
                                <input type="date" name="start_date" class="f-control" value="{{ $startDate }}">
                            </div>
                            <div class="filter-item-date">
                                <label class="f-label">End Date</label>
                                <input type="date" name="end_date" class="f-control" value="{{ $endDate }}">
                            </div>
                        @endcan

                        @cannot('ManageRoster')
                            @can('ManageRosterSPVManager')
                                <div class="filter-item-date">
                                    <label class="f-label">Start Date</label>
                                    <input type="date" name="start_date" class="f-control" value="{{ $startDate }}"
                                        min="{{ $startDate }}"
                                        max="{{ \Carbon\Carbon::parse($startDate)->addMonth()->toDateString() }}">
                                </div>
                                <div class="filter-item-date">
                                    <label class="f-label">End Date</label>
                                    <input type="date" name="end_date" class="f-control" value="{{ $endDate }}"
                                        min="{{ $endDate }}"
                                        max="{{ \Carbon\Carbon::parse($endDate)->addMonth()->toDateString() }}">
                                </div>
                            @endcan
                        @endcannot

                        <div class="filter-item-btn">
                            <button type="submit" class="btn-primary-r"><i class="fas fa-search"></i> Filter</button>
                            <a href="{{ route('roster.index') }}" class="btn-secondary-r">Reset</a>
                        </div>
                    </form>
                </div>

                {{-- @if (!$storeId)
                    <div class="card" style="border:none;box-shadow:0 1px 8px rgba(0,0,0,.08);">
                        <div class="card-body">
                            <div class="empty-state">
                                <i class="fas fa-store"></i>
                                <h5>Choose Location First</h5>
                                <p>Please select location in the top filter then click <strong>Filter</strong> untuk
                                    menampilkan data roster.</p>
                            </div>
                        </div>
                    </div>
                @else --}}
                @if (!$storeId && !($canManageSPV && !$canManageAll))
    <div class="card" style="border:none;box-shadow:0 1px 8px rgba(0,0,0,.08);">
        <div class="card-body">
            <div class="empty-state">
                <i class="fas fa-store"></i>
                <h5>Choose Location First</h5>
                <p>Please select location in the top filter then click <strong>Filter</strong> untuk
                    menampilkan data roster.</p>
            </div>
        </div>
    </div>
@else
                    {{-- ── Legend Checkbox Filter ── --}}
                    <div class="legend">
                        <label class="legend-item">
                            <input type="checkbox" class="legend-filter" data-type="work" checked> Work
                        </label>
                        <label class="legend-item">
                            <input type="checkbox" class="legend-filter" data-type="off"> Off
                        </label>
                        <label class="legend-item">
                            <input type="checkbox" class="legend-filter" data-type="holiday"> Public Holiday
                        </label>
                        <label class="legend-item">
                            <input type="checkbox" class="legend-filter" data-type="leave"> Leave
                        </label>
                        <label class="legend-item">
                            <input type="checkbox" class="legend-filter" data-type="melahirkan"> Maternity leave
                        </label>
                        <label class="legend-item">
                            <input type="checkbox" class="legend-filter" data-type="weekend"> Weekend
                        </label>
                        <label class="legend-item">
                            <input type="checkbox" class="legend-filter" data-type="today"> Today
                        </label>
                        <label class="legend-item">
                            <input type="checkbox" class="legend-filter" data-type="sick"> Sick
                        </label>
                        <label class="legend-item">
                            <input type="checkbox" class="legend-filter" data-type="toiloff"> TOIL Off
                        </label>
                    </div>

                    {{-- ── Grid Roster ── --}}
                    <div class="card" style="border:none;box-shadow:0 1px 8px rgba(0,0,0,.08);">
                        <div class="card-body p-0">
                            <div class="roster-scroll">
                                <table class="roster-table">
                                    <thead>
                                        <tr>
                                            <th class="col-emp">Employee</th>
                                            @foreach ($dates as $carbon)
                                                @php
                                                    $isWeekend = $carbon->isWeekend();
                                                    $isToday = $carbon->isToday();
                                                @endphp
                                                <th class="{{ $isWeekend ? 'weekend' : '' }} {{ $isToday ? 'today' : '' }}"
                                                    style="min-width:85px">
                                                    <div>{{ $carbon->format('D') }}</div>
                                                    <div style="font-size:10px;opacity:.8">{{ $carbon->format('d/m') }}
                                                    </div>
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($employees as $employee)
                                            <tr>
                                                <td class="col-emp">
                                                    <div class="emp-meta">Employee Name : {{ $employee->employee_name }}
                                                    </div>
                                                    <div class="emp-meta">Department :
                                                        {{ $employee->department->first()?->department_name ?? '-' }}
                                                    </div>
                                                    @if(!$storeId && $canManageSPV)
        <div class="emp-meta">Location : {{ $employee->store->first()?->name ?? '-' }}</div>
    @endif
                                                    <div class="emp-meta">Position :
                                                        {{ $employee->position->first()?->name ?? '-' }}
                                                    </div>
                                                    {{-- <div class="emp-meta">Location {{ $employee->store->first()?->name ?? '-' }}
                    </div> --}}
                                                    @if ($employee->status_employee)
                                                        <div class="emp-meta">Status :
                                                            {{ $employee->status_employee ?? '-' }}
                                                        </div>
                                                    @endif
                                                    @if ($employee->status)
                                                        <div class="emp-meta">Status :
                                                            {{ $employee->status ?? '-' }}
                                                        </div>
                                                    @endif
                                                </td>
                                                @foreach ($employee->cells as $cell)
                                                    <td class="day-cell {{ $cell['is_weekend'] ? 'weekend' : '' }} {{ $cell['is_today'] ? 'today' : '' }}"
                                                        data-emp-id="{{ $employee->id }}"
                                                        data-emp-name="{{ $employee->employee_name }}"
                                                        data-emp-status="{{ $employee->status_employee ?? '' }}"
                                                        data-date="{{ $cell['date_str'] }}"
                                                        data-shift-id="{{ $cell['shift_id'] }}"
                                                        data-day-type="{{ $cell['day_type'] }}"
                                                        data-has-roster="{{ $cell['has_roster'] ? '1' : '0' }}"
                                                        data-sick-attachment="{{ $cell['sick_attachment'] }}"
                                                        data-notes="{{ $cell['notes'] }}"
                                                        data-cell-type="{{ $cell['cell_type'] }}"
                                                        data-is-today="{{ $cell['is_today'] ? '1' : '0' }}"
                                                        onclick="openCellModal(this)"
                                                        title="{{ $employee->employee_name }} – {{ $cell['date_str'] }}{{ $cell['notes'] ? ' | ' . $cell['notes'] : '' }}">
                                                        @if ($cell['badge_class'] === '')
                                                            <span class="r-empty">+</span>
                                                        @else
                                                            <span class="{{ $cell['badge_class'] }}">
                                                                <span class="r-name">{{ $cell['badge_name'] }}</span>
                                                                @if ($cell['badge_time'])
                                                                    <span class="r-time">{{ $cell['badge_time'] }}</span>
                                                                @endif
                                                                @if ($cell['notes'])
                                                                    <span class="r-notes">{{ $cell['notes'] }}</span>
                                                                @endif
                                                            </span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ count($dates) + 1 }}"
                                                    class="text-center py-5 text-muted">
                                                    No employees were found in this location.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>




                    {{-- ── Tabel Histori Roster ── --}}
                    <div class="card mt-4" style="border:none;box-shadow:0 1px 8px rgba(0,0,0,.08);">
                        <div class="card-body">
                            <h6 style="font-weight:700;color:#0f172a;margin-bottom:16px;">
                                <i class="fas fa-history"></i> Histori Roster
                            </h6>

                            {{-- Filter Histori --}}
                            <div class="d-flex flex-wrap align-items-end gap-3 mb-3">
                                @can('ManageRoster')
                                    <div>
                                        <label class="f-label">Location</label>
                                        <select id="historyStore" class="f-control select2" style="min-width:100px;">
                                            <option value="">-- All Locations --</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}"
                                                    {{ $storeId == $store->id ? 'selected' : '' }}>
                                                    {{ $store->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    {{-- SPV: hidden, otomatis pakai store sendiri --}}
                                    {{-- <input type="hidden" id="historyStore" value="{{ $myStoreId }}">
                                @endcan --}}
                                  @if($myStores->count() > 1)
        <select id="historyStore" class="f-control select2" style="min-width:100px;">
            <option value="">-- All Locations --</option>
            @foreach ($myStores as $store)
                <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>
                    {{ $store->name }}
                </option>
            @endforeach
        </select>
    @else
        <input type="hidden" id="historyStore" value="{{ $myStoreId }}">
    @endif
@endcan

                                <div>
                                    <label class="f-label">Start Date</label>
                                    <input type="date" id="historyStart" value="{{ $startDate }}"
                                        class="f-control">
                                </div>
                                <div>
                                    <label class="f-label">End Date</label>
                                    <input type="date" id="historyEnd" value="{{ $endDate }}"
                                        class="f-control">
                                </div>

                                <div>
                                    <label class="f-label">Cari Nama</label>
                                    <input type="text" id="historySearch" class="f-control"
                                        placeholder="Nama karyawan...">
                                </div>
                                <div>
                                    <button class="btn-primary-r" onclick="loadHistory()">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                    <button class="btn-secondary-r" onclick="resetHistory()">
                                        Reset
                                    </button>
                                    {{-- @canany(['ManageRoster', 'ManageRosterSPVManager']) --}}

                                    <button class="btn-success-r" onclick="exportHistory()">
                                        <i class="fas fa-file-excel"></i> Export Excel
                                    </button>
                                    {{-- @endcanany --}}
                                </div>
                            </div>

                            {{-- Hasil --}}
                            <div id="historyResult">
                                <div class="text-muted text-center py-4" style="font-size:13px;">
                                    <i class="fas fa-search"></i> Masukkan rentang tanggal lalu klik Cari.
                                </div>
                            </div>
                        </div>
                    </div>

                @endif
                @canany(['ManageRoster', 'ManageRosterSPVManager'])
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-history me-2"></i> Roster Activity Log</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="activityTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="50">#</th>
                                            <th width="100">Event</th>
                                            <th>Description</th>
                                            <th width="150">By</th>
                                            <th>Changes</th>
                                            <th width="160">Date & Time</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endcanany

            </div>
        </section>
    </div>


    <div class="m-overlay" id="modalCell">
        <div class="m-box">
            <div class="m-head">
                <span>Set Schedule</span>
                <button onclick="closeModal('modalCell')">×</button>
            </div>

            @if ($canViewOnly)
                {{-- ViewRoster: read only --}}
                <div class="m-body">
                    <div style="font-weight:700;color:#0f172a;margin-bottom:2px" id="mEmpName"></div>
                    <div style="font-size:12px;color:#64748b;margin-bottom:14px" id="mDate"></div>
                    <div
                        style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:20px;text-align:center;">
                        <i class="fas fa-lock" style="color:#ef4444;font-size:24px;margin-bottom:10px;display:block;"></i>
                        <div style="font-weight:600;color:#991b1b;margin-bottom:4px;">Akses Terbatas</div>
                        <div style="font-size:12px;color:#b91c1c;">Anda tidak memiliki izin untuk mengubah jadwal.</div>
                    </div>
                </div>
                <div class="m-foot">
                    <button class="btn-secondary-r" onclick="closeModal('modalCell')">Tutup</button>
                </div>
            @else
                {{-- ManageRoster / ManageRosterSPVManager: form normal --}}
                <div class="m-body">
                    <div style="font-weight:700;color:#0f172a;margin-bottom:2px" id="mEmpName"></div>
                    <div style="font-size:12px;color:#64748b;margin-bottom:14px" id="mDate"></div>
                    <input type="hidden" id="mEmpId">

                    <label class="f-label">Day Type</label>
                    <select id="mDayType" class="f-control mb-3" onchange="toggleShift()">
                        <option value="Work">Work (Kerja)</option>
                        <option value="Off">Off (Libur)</option>
                        <option value="Public Holiday" class="opt-ph">Public Holiday</option>
                        <option value="Leave" class="opt-cuti">Leave (Cuti)</option>
                        <option value="Cuti Melahirkan" class="opt-cuti">Cuti Melahirkan</option>
                        <option value="Sick" class="opt-sick">Sick</option>
                        <option value="TOIL Off" class="opt-toiloff">TOIL Off</option>
                    </select>
                    <small id="mDayTypeNote"
                        style="display:none;color:#92400e;font-size:11px;margin-top:-8px;margin-bottom:10px;display:block"></small>

                    <div id="shiftWrap">
                        <label class="f-label">Shift</label>
                        <select id="mShiftId" class="f-control mb-3">
                            <option value="">-- Choose Shift --</option>
                            @foreach ($shifts as $shift)
                                <option value="{{ $shift->id }}">
                                    {{ $shift->shift_name }}
                                    ({{ substr($shift->start_time, 0, 5) }} - {{ substr($shift->end_time, 0, 5) }})
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <div id="sickWrap" style="display:none">
                        <label class="f-label">Bukti Sakit <span style="color:#ef4444">*</span>
                            <small style="color:#94a3b8;font-weight:400">(JPG/PNG/PDF, maks 5MB)</small>
                        </label>
                        <input type="file" id="mSickFile" class="f-control mb-3" accept=".jpg,.jpeg,.png,.pdf">
                    </div>

                    <div id="mSickExisting"
                        style="font-size:11px;color:#16a34a;margin-top:-8px;margin-bottom:10px;display:none">
                        <i class="fas fa-check-circle"></i> Sudah ada bukti tersimpan.
                        <button type="button" id="mSickLihatBtn"
                            style="margin-left:6px;background:none;border:none;color:#0369a1;font-weight:600;cursor:pointer;font-size:11px;padding:0">
                            <i class="fas fa-eye"></i> Lihat
                        </button>
                    </div>
                    {{-- <div id="mSickExisting"
        style="font-size:11px;color:#16a34a;margin-top:-8px;margin-bottom:10px;display:none">
        <i class="fas fa-check-circle"></i> Sudah ada bukti tersimpan.
        <a id="mSickPreviewLink" href="#" target="_blank"
            style="margin-left:6px;color:#0369a1;font-weight:600">
            <i class="fas fa-eye"></i> Lihat
        </a>
    </div> --}}

                    <div id="phCarryoverWrap" style="display:none">
                        <label class="f-label">PH Tukar (Simpanan)
                            <small style="color:#94a3b8;font-weight:400">(opsional — pilih kalau pakai PH simpanan)</small>
                        </label>
                        <select id="mPhCarryover" class="f-control mb-3">
                            <option value="">-- Tidak pakai PH simpanan --</option>
                        </select>
                        <small id="mPhCarryoverNote"
                            style="display:none;color:#0f766e;font-size:11px;margin-top:-8px;margin-bottom:10px;"></small>
                    </div>

                    <label class="f-label">Notes</label>
                    <input type="text" id="mNotes" class="f-control" placeholder="optional note...">
                </div>

                <div class="m-foot">
                    <button class="btn-danger-r" id="mDeleteBtn" style="display:none" onclick="deleteRoster()">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    <button class="btn-secondary-r" onclick="closeModal('modalCell')">Cancel</button>
                    <button class="btn-primary-r" id="mSaveBtn" onclick="saveRoster()">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            @endif

        </div>
    </div>



    {{-- ════════════════════════════════════════════════════
     MODAL: Bulk Assign
     ════════════════════════════════════════════════════ --}}
    <div class="m-overlay" id="modalBulk">
        <div class="m-box" style="width:400px">
            <div class="m-head">
                <span>Bulk Assign Shift</span>
                <button onclick="closeModal('modalBulk')">×</button>
            </div>
            <div class="m-body">
                <label class="f-label">Choose Employee</label>
                <select id="bulkEmps" class="f-control mb-1" multiple style="height:100px">
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}" data-status="{{ $emp->status_employee ?? '' }}">
                            {{ $emp->employee_name }} – {{ $emp->store->first()?->name ?? '' }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted d-block mb-3">Hold the button <kbd>Ctrl</kbd> to choose more than 1
                    employee</small>

                <div class="d-flex gap-2 mb-3">
                    <div style="flex:1"><label class="f-label">Start Date</label><input type="date" id="bulkStart"
                            class="f-control" value="{{ $startDate }}"></div>
                    <div style="flex:1"><label class="f-label">End Date</label><input type="date" id="bulkEnd"
                            class="f-control" value="{{ $endDate }}"></div>
                </div>

                <div class="m-body">
                    <label class="f-label">Choose Employee</label>
                    <select id="bulkEmps" class="f-control mb-1" multiple style="height:100px">
                        {{-- @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}" data-status="{{ $emp->status_employee ?? '' }}">
                            {{ $emp->employee_name }} – {{ $emp->store->first()?->name ?? '' }}
                        </option>
                    @endforeach --}}
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}" data-status="{{ $emp->status_employee ?? '' }}">
                                {{ $emp->employee_name }} – {{ $emp->store->first()?->name ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex align-items-center gap-2 mb-2">
                    <input type="checkbox" id="bulkSkipWeekend" checked>
                    <label for="bulkSkipWeekend" class="f-label mb-0">Skip Sunday</label>
                </div>

                <div class="d-flex align-items-center gap-2 mb-2" style="margin-top:8px">
                    <input type="checkbox" id="bulkSaturdayShift" onchange="toggleSaturdayShift()">
                    <label for="bulkSaturdayShift" class="f-label mb-0">Add Saturday Shift</label>
                </div>

                <div id="saturdayShiftWrap" style="display:none;margin-top:8px">
                    <label class="f-label">Pilih Shift Sabtu</label>
                    <select id="bulkSaturdayShiftId" class="f-control mb-3">
                        <option value="">-- Pilih Shift Sabtu --</option>
                        @foreach ($shifts as $shift)
                            <option value="{{ $shift->id }}">
                                {{ $shift->shift_name }}
                                ({{ substr($shift->start_time, 0, 5) }}-{{ substr($shift->end_time, 0, 5) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="m-foot">
                <button class="btn-secondary-r" onclick="closeModal('modalBulk')">Cancel</button>
                <button class="btn-primary-r" onclick="saveBulk()"><i class="fas fa-calendar-check"></i> Assign</button>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
     MODAL: Copy Roster
     ════════════════════════════════════════════════════ --}}
    <div class="m-overlay" id="modalCopy">
        <div class="m-box">
            <div class="m-head">
                <span>📋 Copy Roster</span>
                <button onclick="closeModal('modalCopy')">×</button>
            </div>
            <div class="m-body">
                <p style="font-size:12px;color:#64748b;margin-bottom:14px">Copy jadwal dari periode sumber ke periode
                    target.</p>
                <div
                    style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:10px;font-size:12px;color:#1e40af;margin-bottom:14px">
                    <i class="fas fa-info-circle"></i>
                    <strong>Sumber</strong> = periode yang ingin dicopy.
                    <strong>Target</strong> = periode tujuan yang akan diisi jadwal baru.
                </div>
                <label class="f-label">Sumber: Start Date <span style="color:#ef4444">*</span></label>
                <input type="date" id="copySourceStart" class="f-control mb-2" value="{{ $startDate }}">
                <label class="f-label">Sumber: End Date <span style="color:#ef4444">*</span></label>
                <input type="date" id="copySourceEnd" class="f-control mb-3" value="{{ $endDate }}">
                <div style="border-top:1px dashed #e2e8f0;margin:14px 0"></div>
                <label class="f-label">Target: Start Date <span style="color:#ef4444">*</span></label>
                <input type="date" id="copyTargetStart" class="f-control mb-2">
                <label class="f-label">Target: End Date <span style="color:#ef4444">*</span></label>
                <input type="date" id="copyTargetEnd" class="f-control mb-2">
            </div>
            <div class="m-foot">
                <button class="btn-secondary-r" onclick="closeModal('modalCopy')">Cancel</button>
                <button class="btn-primary-r" onclick="saveCopy()"><i class="fas fa-copy"></i> Copy</button>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
     MODAL: Bulk Delete
     ════════════════════════════════════════════════════ --}}
    <div class="m-overlay" id="modalBulkDelete">
        <div class="m-box" style="width:400px">
            <div class="m-head">
                <span>🗑️ Bulk Delete Roster</span>
                <button onclick="closeModal('modalBulkDelete')">×</button>
            </div>

            <div class="m-body">
                <label class="f-label">Pilih Karyawan</label>
                <select id="deleteEmps" class="f-control mb-1" multiple style="height:100px">
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->employee_name }} –
                            {{ $emp->store->first()?->name ?? '' }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted d-block mb-3">Tahan <kbd>Ctrl</kbd> untuk pilih lebih dari satu</small>
                <div class="d-flex gap-2 mb-3">
                    <div style="flex:1">
                        <label class="f-label">Start Date</label>
                        <input type="date" id="deleteStart" class="f-control" value="{{ $startDate }}">
                    </div>
                    <div style="flex:1">
                        <label class="f-label">End Date</label>
                        <input type="date" id="deleteEnd" class="f-control" value="{{ $endDate }}">
                    </div>
                </div>
                <div
                    style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px;font-size:12px;color:#991b1b;">
                    Semua jadwal karyawan yang dipilih dalam rentang tanggal ini akan dihapus permanen!
                </div>
            </div>
            <div class="m-foot">
                <button class="btn-secondary-r" onclick="closeModal('modalBulkDelete')">Batal</button>
                <button class="btn-danger-r" onclick="saveBulkDelete()"><i class="fas fa-trash"></i> Hapus</button>
            </div>

        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
     MODAL: Auto Generate Roster — 3 Store Static
     ════════════════════════════════════════════════════ --}}
    <div class="m-overlay" id="modalAutoGenerate">
        <div class="m-box" style="width:440px; max-height:90vh; display:flex; flex-direction:column;">
            <div class="m-head">
                <span>Auto Generate Roster</span>
                <button onclick="closeModal('modalAutoGenerate')">×</button>
            </div>
            <div class="m-body" style="overflow-y:auto; flex:1;">
                <p style="font-size:12px;color:#64748b;margin-bottom:14px;line-height:1.6">
                    Sistem akan generate roster otomatis untuk karyawan di:
                    <strong>Head Office, Holding, Distribution Center</strong>.
                </p>

                {{-- Pilih Shift --}}
                <div class="ag-period-card">
                    <div class="ag-period-title">
                        Pilih Shift <small>(wajib dipilih)</small>
                    </div>
                    <div class="d-flex gap-2">
                        <div style="flex:1">
                            <label class="f-label">
                                Shift Senin - Jumat <span style="color:#ef4444">*</span>
                            </label>
                            <select id="ag-shift-weekday" class="f-control ag-shift-select2">
                                <option value="">-- Pilih Shift --</option>
                                @foreach ($shifts as $shift)
                                    <option value="{{ $shift->id }}">
                                        {{ $shift->shift_name }}
                                        ({{ substr($shift->start_time, 0, 5) }} - {{ substr($shift->end_time, 0, 5) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div style="flex:1">
                            <label class="f-label">
                                Shift Sabtu <span style="color:#ef4444">*</span>
                            </label>
                            <select id="ag-shift-saturday" class="f-control ag-shift-select2">
                                <option value="">-- Pilih Shift --</option>
                                @foreach ($shifts as $shift)
                                    <option value="{{ $shift->id }}">
                                        {{ $shift->shift_name }}
                                        ({{ substr($shift->start_time, 0, 5) }} - {{ substr($shift->end_time, 0, 5) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>


                <div class="m-body">
                    <div class="d-flex gap-2 mb-3">
                        <div style="flex:1">
                            <label class="f-label">Start Date</label>
                            <input type="date" id="ag-start-date" class="f-control"
                                onchange="loadAutoGeneratePreview()">
                        </div>
                        <div style="flex:1">
                            <label class="f-label">End Date</label>
                            <input type="date" id="ag-end-date" class="f-control"
                                onchange="loadAutoGeneratePreview()">
                        </div>
                    </div>
                    <button type="button" class="btn-update-preview" onclick="loadAutoGeneratePreview()">
                        <i class="fas fa-sync-alt"></i> Update Preview
                    </button>
                </div>

                <div class="auto-roster-preview" id="autoRosterPreview">
                    <div style="font-weight:600;margin-bottom:8px;color:#1e3a8a">Detail Periode</div>
                    <div class="preview-row"><span>Periode:</span><strong id="ag-period">-</strong></div>
                    <div class="preview-row"><span>Total karyawan:</span><strong id="ag-employees">-</strong></div>
                    <div class="preview-row"><span>&nbsp;&nbsp;• Hindu:</span><strong id="ag-hindu">-</strong></div>
                    <div class="preview-row"><span>&nbsp;&nbsp;• Non Hindu:</span><strong id="ag-non-hindu">-</strong>
                    </div>
                    <div class="preview-row"><span>Public Holiday dalam periode:</span><strong id="ag-ph">-</strong>
                    </div>
                    <div style="border-top:1px dashed #bfdbfe;margin:6px 0"></div>
                    <div class="preview-row created"><span>Estimasi akan dibuat:</span><strong
                            id="ag-estimated-created">-</strong></div>
                    <div class="preview-row skipped"><span>Estimasi akan dilewati (sudah ada):</span><strong
                            id="ag-estimated-skipped">-</strong></div>
                </div>

                <div
                    style="background:#fef9c3;border:1px solid #fde047;border-radius:6px;padding:10px;font-size:12px;color:#854d0e;">
                    <i class="fas fa-info-circle"></i>
                    Pola jadwal: <strong>Senin-Jumat</strong> (sesuai shift dipilih), <strong>Sabtu</strong> (sesuai shift
                    dipilih), <strong>Minggu</strong> (Off).<br><br>
                    Public Holiday di-filter sesuai agama karyawan.<br><br>
                    Roster yang sudah ada <strong>tidak akan ditimpa</strong>.
                </div>
            </div>
            <div class="m-foot">
                <button class="btn-secondary-r" onclick="closeModal('modalAutoGenerate')">Cancel</button>
                <button class="btn-success-r" onclick="executeAutoGenerate()" id="btnExecuteAutoGenerate">
                    <i class="fas fa-magic"></i> Generate
                </button>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
     MODAL: Auto Generate Roster — Store Lain (Per Minggu)
     ════════════════════════════════════════════════════ --}}
    {{-- <div class="m-overlay" id="modalAroOther">
        <div class="m-box" style="width:560px; max-width:97vw;">
            <div class="m-head" style="background:linear-gradient(135deg,#0f172a,#1e3a8a);">
                <span>📅 Auto Generate Roster — Store Lain</span>
                <button onclick="closeModal('modalAroOther')">×</button>
            </div>

            <div style="background:#f8fafc;padding:14px 18px 0;border-bottom:1px solid #e2e8f0;">
                <div class="aro-step-wrap">
                    <div class="aro-step-item active" id="aroStepDot1">
                        <div class="aro-step-dot">1</div>
                        <div class="aro-step-label">Pilih Minggu</div>
                    </div>

                    <div class="aro-step-line" id="aroStepLine1"></div>
                    <div class="aro-step-item" id="aroStepDot2">
                        <div class="aro-step-dot">2</div>
                        <div class="aro-step-label">Atur Pola Shift</div>
                    </div>
                    <div class="aro-step-line" id="aroStepLine2"></div>
                    <div class="aro-step-item" id="aroStepDot3">
                        <div class="aro-step-dot">3</div>
                        <div class="aro-step-label">Preview & Generate</div>
                    </div>
                </div>
            </div>

            <div id="aroPanel1" class="aro-modal-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="f-label">Pilih Minggu (Senin) <span style="color:#ef4444">*</span></label>
                        <input type="date" id="aroWeekStart" class="f-control" onchange="aroOnWeekChange()">
                        <div id="aroWeekRangeInfo" style="font-size:11px;color:#64748b;margin-top:4px;"></div>
                        <div id="aroWeekDayWarn" style="font-size:11px;color:#dc2626;margin-top:3px;display:none;">
                            <i class="fas fa-exclamation-triangle"></i> Bukan hari Senin, otomatis disesuaikan.
                        </div>
                    </div>
                    <div>
                        <label class="f-label">Store Aktif</label>
                        <div style="background:#f1f5f9;border-radius:6px;padding:10px 12px;font-size:12px;color:#334155;">
                            <strong id="aroStoreName">{{ $currentStoreName ?? '-' }}</strong>
                            <div id="aroStoreEmpCount" style="color:#64748b;margin-top:2px;">— karyawan</div>
                        </div>
                    </div>
                </div>

                <label class="f-label">Terapkan ke Karyawan</label>
                <div style="display:flex;gap:20px;margin-bottom:10px;">
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                        <input type="radio" name="aroApplyTo" value="all" checked onchange="aroToggleEmpSelect()">
                        <span><strong>Semua Karyawan</strong></span>
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                        <input type="radio" name="aroApplyTo" value="selected" onchange="aroToggleEmpSelect()">
                        <span><strong>Karyawan Tertentu</strong></span>
                    </label>
                </div>
                <div id="aroEmpSelectWrap" style="display:none;margin-bottom:12px;">
                    <select id="aroEmpMultiSelect" class="f-control" multiple style="height:90px;">
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->employee_name }}</option>
                        @endforeach
                    </select>
                    <small style="color:#64748b;font-size:11px;">Tahan Ctrl/Cmd untuk memilih lebih dari satu.</small>
                </div>

                <div
                    style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px 12px;margin-bottom:4px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
                        <input type="checkbox" id="aroOverride" style="width:15px;height:15px;">
                        <span>
                            <strong>Timpa roster yang sudah ada</strong>
                            <span style="display:block;font-size:11px;color:#64748b;font-weight:400;">
                                Jika aktif, roster di minggu ini yang sudah ada akan diperbarui.
                            </span>
                        </span>
                    </label>
                </div>

                <div class="m-foot" style="padding:12px 0 0;border-top:none;justify-content:flex-end;">
                    <button class="btn-primary-r" id="aroStep1Next" onclick="aroGoStep2()" disabled>
                        Atur Pola Shift <i class="fas fa-arrow-right" style="margin-left:6px;"></i>
                    </button>
                </div>
            </div>

            <div id="aroPanel2" class="aro-modal-body" style="display:none;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                    <div style="font-size:13px;font-weight:600;color:#0f172a;">
                        <i class="fas fa-clock" style="color:#1d4ed8;margin-right:6px;"></i>
                        Pola Shift Senin – Minggu
                    </div>
                    <div style="display:flex;gap:6px;">
                        <button class="btn-secondary-r" style="padding:5px 10px;font-size:11px;"
                            onclick="aroQuickPattern('default')">
                            <i class="fas fa-magic"></i> Default
                        </button>
                        <button class="btn-secondary-r" style="padding:5px 10px;font-size:11px;"
                            onclick="aroQuickPattern('allOff')">
                            <i class="fas fa-moon"></i> Semua Off
                        </button>
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table class="aro-day-table">
                        <thead>
                            <tr>
                                <th style="width:90px;">Hari</th>
                                <th style="width:150px;">Tipe Hari</th>
                                <th>Shift</th>
                                <th style="width:130px;">Jam</th>
                            </tr>
                        </thead>
                        <tbody id="aroDayPatternBody"></tbody>
                    </table>
                </div>

                <div class="aro-info-box" style="margin-top:12px;">
                    <i class="fas fa-info-circle"></i>
                    Jika hari yang di-set <strong>Work</strong> ternyata merupakan hari libur nasional yang relevan
                    untuk agama karyawan, sistem otomatis mengubahnya menjadi <strong>Public Holiday</strong>.
                </div>

                <div class="m-foot" style="padding:12px 0 0;border-top:none;justify-content:space-between;">
                    <button class="btn-secondary-r" onclick="aroGoStep1()">
                        <i class="fas fa-arrow-left" style="margin-right:6px;"></i> Kembali
                    </button>
                    <button class="btn-primary-r" onclick="aroGoStep3()">
                        Preview & Konfirmasi <i class="fas fa-arrow-right" style="margin-left:6px;"></i>
                    </button>
                </div>
            </div>

            <div id="aroPanel3" class="aro-modal-body" style="display:none;">
                <div id="aroPreviewLoading" style="text-align:center;padding:30px 0;">
                    <i class="fas fa-spinner fa-spin fa-2x" style="color:#1d4ed8;"></i>
                    <div style="margin-top:10px;font-size:13px;color:#64748b;">Memuat preview...</div>
                </div>
                <div id="aroPreviewContent" style="display:none;">
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px;margin-bottom:12px;"
                        id="aroPreviewCards"></div>
                    <div id="aroPhSection" style="display:none;margin-bottom:12px;">
                        <div
                            style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px 12px;font-size:12px;color:#991b1b;">
                            <strong><i class="fas fa-calendar-times"></i> Hari Libur Nasional di Minggu Ini:</strong>
                            <ul id="aroPhList" style="margin:6px 0 0 16px;padding:0;"></ul>
                        </div>
                    </div>
                    <div style="font-size:12px;font-weight:600;color:#334155;margin-bottom:6px;">Ringkasan Pola Shift:
                    </div>
                    <div style="overflow-x:auto;margin-bottom:12px;">
                        <table class="aro-day-table">
                            <thead>
                                <tr>
                                    <th>Hari</th>
                                    <th>Tipe</th>
                                    <th>Shift</th>
                                </tr>
                            </thead>
                            <tbody id="aroPreviewPatternBody"></tbody>
                        </table>
                    </div>
                    <div id="aroOverrideWarn" style="display:none;margin-bottom:12px;">
                        <div
                            style="background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:10px 12px;font-size:12px;color:#92400e;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Mode Timpa aktif.</strong>
                            <span id="aroOverrideWarnCount"></span> roster yang sudah ada akan <strong>diperbarui</strong>.
                        </div>
                    </div>
                </div>
                <div class="m-foot" style="padding:12px 0 0;border-top:none;justify-content:space-between;">
                    <button class="btn-secondary-r" onclick="aroGoStep2()">
                        <i class="fas fa-arrow-left" style="margin-right:6px;"></i> Kembali
                    </button>
                    <button class="btn-success-r" id="aroGenerateBtn" onclick="aroDoGenerate()" disabled>
                        <i class="fas fa-lightning-bolt"></i> Generate Sekarang
                    </button>
                </div>
            </div>

            <div id="aroPanel4" class="aro-modal-body" style="display:none;">
                <div id="aroResultContent"></div>
                <div class="m-foot" style="padding:16px 0 0;border-top:none;justify-content:center;gap:10px;">
                    <button class="btn-secondary-r" onclick="aroReset()">
                        <i class="fas fa-redo" style="margin-right:6px;"></i> Generate Lagi
                    </button>
                    <button class="btn-primary-r" onclick="closeModal('modalAroOther');location.reload();">
                        <i class="fas fa-check" style="margin-right:6px;"></i> Selesai & Refresh
                    </button>
                </div>
            </div>

        </div>
    </div> --}}

    {{-- ════════════════════════════════════════════════════
     MODAL: Import Excel Roster
     ════════════════════════════════════════════════════ --}}
    <div class="m-overlay" id="modalImport">
        <div class="m-box" style="width:460px">
            <div class="m-head">
                <span>📥 Import Roster (Excel)</span>
                <button onclick="closeModal('modalImport')">×</button>
            </div>
            <div class="m-body">
                <div
                    style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:10px 12px;font-size:12px;color:#1e40af;margin-bottom:14px;line-height:1.6">
                    <i class="fas fa-info-circle"></i>
                    Format: kolom A = <strong>Employee Pengenal</strong>, B = Nama, C dst = shift per tanggal.
                    Kolom shift pertama = <strong>tanggal mulai</strong> yang dipilih di bawah.
                </div>

                <label class="f-label">Tanggal Mulai <span style="color:#ef4444">*</span>
                    <small style="color:#94a3b8;font-weight:400">(kolom shift pertama di Excel)</small>
                </label>
                @can('ManageRoster')
                    <input type="date" id="importStartDate" class="f-control mb-3" value="{{ $startDate }}">
                @endcan
                @can('ManageRosterSPVManager')
                    <input type="date" id="importStartDate" class="f-control mb-3" value="{{ $startDate }}"
                        min="{{ $startDate }}" max="{{ \Carbon\Carbon::parse($startDate)->addMonth()->toDateString() }}">
                @endcan
                <label class="f-label">File Excel <span style="color:#ef4444">*</span>
                    <small style="color:#94a3b8;font-weight:400">(.xlsx/.xls/.csv, maks 5MB)</small>
                </label>
                <input type="file" id="importFile" class="f-control mb-3" accept=".xlsx,.xls,.csv">

                {{-- Hasil import --}}
                <div id="importResult" style="display:none;font-size:12px;margin-top:8px;"></div>
            </div>
            <div class="m-foot">
                <button class="btn-secondary-r" onclick="closeModal('modalImport')">Cancel</button>
                <button class="btn-primary-r" id="importBtn" onclick="doImport()">
                    <i class="fas fa-file-import"></i> Import
                </button>
            </div>
        </div>
    </div>

    <div id="rosterToast"></div>

@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#start_date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d F Y",
            locale: "id",
            defaultDate: (function() {
                let now = new Date();
                return new Date(now.getFullYear(), now.getMonth(), 26);
            })()
        });
    </script>
    <script>
        // ════════════════════════════════════════════════════════
        //  GLOBALS
        // ════════════════════════════════════════════════════════
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const CURRENT_STORE_ID = '{{ $storeId ?? '' }}';
        const CURRENT_STORE_NAME = '{{ $currentStoreName ?? '' }}';

        // ── Toast ──
        function toast(msg, ok = true) {
            const el = document.getElementById('rosterToast');
            el.textContent = msg;
            el.style.background = ok ? '#0f172a' : '#991b1b';
            el.style.display = 'block';
            setTimeout(() => el.style.display = 'none', 3500);
        }

        // ── Modal open/close ──
        function openModal(id) {
            document.getElementById(id).classList.add('open');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('open');
        }

        document.querySelectorAll('.m-overlay').forEach(el => {
            el.addEventListener('click', e => {
                if (e.target === el) el.classList.remove('open');
            });
        });

        // ── Toggles ──
        // function toggleShift() {
        // const dt = document.getElementById('mDayType').value;
        // document.getElementById('shiftWrap').style.display = dt === 'Work' ? 'block' : 'none';
        // document.getElementById('sickWrap').style.display = dt === 'Sick' ? 'block' : 'none';
        // document.getElementById('phCarryoverWrap').style.display = dt === 'Public Holiday' ? 'block' : 'none';
        // }
        function toggleShift() {
            const dt = document.getElementById('mDayType').value;
            document.getElementById('shiftWrap').style.display = dt === 'Work' ? 'block' : 'none';
            document.getElementById('sickWrap').style.display = dt === 'Sick' ? 'block' : 'none';
            document.getElementById('phCarryoverWrap').style.display = dt === 'Public Holiday' ? 'block' : 'none';
        }


        function toggleBulkShift() {
            document.getElementById('bulkShiftWrap').style.display =
                document.getElementById('bulkDayType').value === 'Work' ? 'block' : 'none';
        }

        function toggleSaturdayShift() {
            document.getElementById('saturdayShiftWrap').style.display =
                document.getElementById('bulkSaturdayShift').checked ? 'block' : 'none';
        }


        // ── Format tanggal DD/MM/YYYY ──
        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            return String(d.getDate()).padStart(2, '0') + '/' +
                String(d.getMonth() + 1).padStart(2, '0') + '/' +
                d.getFullYear();
        }

        // ── Legend filter ──
        document.querySelectorAll('.legend-filter').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    document.querySelectorAll('.legend-filter').forEach(function(cb) {
                        if (cb !== this) cb.checked = false;
                    }.bind(this));
                }
                applyLegendFilter();
            });
        });

        function applyLegendFilter() {
            const checkedTypes = [...document.querySelectorAll('.legend-filter:checked')].map(cb => cb.dataset.type);
            const showAll = checkedTypes.length === 0;

            document.querySelectorAll('.day-cell').forEach(function(cell) {
                const type = cell.dataset.cellType;
                const isToday = cell.dataset.isToday === '1';
                let show = false;

                if (showAll || type === 'empty') {
                    show = true;
                } else if (checkedTypes.includes('work') && type === 'work') {
                    show = true;
                } else if (checkedTypes.includes('off') && type === 'off') {
                    show = true;
                } else if (checkedTypes.includes('holiday') && type === 'holiday') {
                    show = true;
                } else if (checkedTypes.includes('leave') && type === 'leave') {
                    show = true;
                } else if (checkedTypes.includes('melahirkan') && type === 'melahirkan') {
                    show = true;
                } else if (checkedTypes.includes('weekend') && type === 'weekend') {
                    show = true;
                } else if (checkedTypes.includes('today') && isToday) {
                    show = true;
                } else if (checkedTypes.includes('sick') && type === 'sick') {
                    show = true;
                } else if (checkedTypes.includes('toiloff') && type === 'toiloff') {
                    show = true;
                }

                cell.style.visibility = show ? 'visible' : 'hidden';
                cell.style.pointerEvents = show ? 'auto' : 'none';
            });
        }

        // ════════════════════════════════════════════════════════
        //  HELPER: Filter opsi day_type berdasarkan status_employee
        //  PKWT            → semua opsi
        //  On Job Training → Work, Off, Public Holiday (sembunyikan Cuti)
        //  DW              → Work, Off saja (sembunyikan PH + Cuti)
        // ════════════════════════════════════════════════════════
        function applyDayTypeFilter(status, selectEl, noteEl) {
            const s = (status || '').toUpperCase().trim();
            // ✅ FIX 2: gunakan includes('JOB TRAINING') agar cocok dengan "On Job Training" dari DB
            const isDW = s === 'DW';
            const isOJT = s.includes('JOB TRAINING');

            selectEl.querySelectorAll('option').forEach(opt => {
                let hidden = false;
                if (opt.classList.contains('opt-ph') && isDW) hidden = true;
                if (opt.classList.contains('bulk-opt-ph') && isDW) hidden = true;
                if (opt.classList.contains('opt-cuti') && (isDW || isOJT)) hidden = true;
                if (opt.classList.contains('bulk-opt-cuti') && (isDW || isOJT)) hidden = true;
                if (opt.classList.contains('opt-sick') && isDW) hidden = true;
                opt.style.display = hidden ? 'none' : '';
            });

            // Reset ke Work jika nilai aktif tersembunyi
            const currentOpt = selectEl.options[selectEl.selectedIndex];
            if (currentOpt && currentOpt.style.display === 'none') {
                selectEl.value = 'Work';
            }

            // Tampilkan note
            if (noteEl) {
                if (isDW) {
                    noteEl.textContent = 'Karyawan DW — opsi Public Holiday dan Cuti tidak tersedia.';
                    noteEl.style.display = 'block';
                } else if (isOJT) {
                    noteEl.textContent = 'Karyawan On Job Training — opsi Cuti tidak tersedia.';
                    noteEl.style.display = 'block';
                } else {
                    noteEl.style.display = 'none';
                }
            }
        }

        // ════════════════════════════════════════════════════════
        //  CELL MODAL
        // ════════════════════════════════════════════════════════

        let _currentSickUrl = null;
        let _currentSickPath = null;

        function openCellModal(cell) {
            document.getElementById('mEmpId').value = cell.dataset.empId;
            document.getElementById('mEmpName').textContent = cell.dataset.empName;
            document.getElementById('mDate').textContent = cell.dataset.date;
            document.getElementById('mNotes').value = cell.dataset.notes || '';
            document.getElementById('mDeleteBtn').style.display = cell.dataset.hasRoster === '1' ? 'block' : 'none';

            const select = document.getElementById('mDayType');
            const noteEl = document.getElementById('mDayTypeNote');
            const status = cell.dataset.empStatus || '';

            applyDayTypeFilter(status, select, noteEl);

            const currentDayType = cell.dataset.dayType || 'Work';
            const targetOpt = Array.from(select.options).find(o => o.value === currentDayType && o.style.display !==
                'none');
            select.value = targetOpt ? currentDayType : 'Work';

            document.getElementById('mShiftId').value = cell.dataset.shiftId || '';

            // ── Sick attachment ──
            document.getElementById('mSickFile').value = '';
            _currentSickUrl = null;
            _currentSickPath = null;

            const sickPath = cell.dataset.sickAttachment || '';
            const sickExisting = document.getElementById('mSickExisting');
            const lihatBtn = document.getElementById('mSickLihatBtn');

            console.log('[SICK] dayType:', cell.dataset.dayType, '| hasRoster:', cell.dataset.hasRoster, '| sickPath:',
                sickPath);

            if (cell.dataset.dayType === 'Sick' && cell.dataset.hasRoster === '1' && sickPath) {
                _currentSickPath = sickPath;
                sickExisting.style.display = 'block';

                // Set onclick via JS langsung agar tidak tertimpa event lain
                lihatBtn.onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    previewSickAttachment();
                };
            } else {
                sickExisting.style.display = 'none';
                lihatBtn.onclick = null;
            }

            const saveBtn = document.getElementById('mSaveBtn');
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Save';

            const delBtn = document.getElementById('mDeleteBtn');
            delBtn.disabled = false;
            delBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';

            loadPhCarryovers(cell.dataset.empId, cell.dataset.date);
            toggleShift();
            openModal('modalCell');
        }

        function previewSickAttachment() {
            if (!_currentSickPath) {
                toast('Tidak ada bukti sakit tersimpan.', false);
                return;
            }

            // Simpan isi body asli
            const mBody = document.querySelector('#modalCell .m-body');
            const mFoot = document.querySelector('#modalCell .m-foot');
            _savedModalBody = mBody.innerHTML;
            _savedModalFoot = mFoot.innerHTML;

            // Tampilkan loading
            mBody.innerHTML = `
        <div style="text-align:center;padding:10px">
            <i class="fas fa-spinner fa-spin" style="font-size:24px;color:#64748b"></i>
            <p style="color:#64748b;margin-top:8px">Memuat bukti sakit...</p>
        </div>`;

            mFoot.innerHTML = `
        <button class="btn-secondary-r" onclick="closeSickPreview()">
            <i class="fas fa-arrow-left"></i> Kembali
        </button>
        <a id="sickPreviewDownload" href="#" target="_blank" class="btn-primary-r">
            <i class="fas fa-download"></i> Download
        </a>`;

            fetch(`{{ route('roster.sick-attachment-url') }}?path=${encodeURIComponent(_currentSickPath)}`, {
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.url) {
                        mBody.innerHTML = '<p style="color:#ef4444;text-align:center">Gagal memuat bukti sakit.</p>';
                        return;
                    }
                    document.getElementById('sickPreviewDownload').href = data.url;
                    const isPdf = _currentSickPath.toLowerCase().endsWith('.pdf');
                    if (isPdf) {
                        mBody.innerHTML =
                            `<iframe src="${data.url}" style="width:100%;height:450px;border:none;border-radius:6px"></iframe>`;
                    } else {
                        mBody.innerHTML =
                            `<img src="${data.url}" style="max-width:100%;max-height:450px;border-radius:6px;object-fit:contain">`;
                    }
                })
                .catch(() => {
                    mBody.innerHTML = '<p style="color:#ef4444;text-align:center">Terjadi kesalahan.</p>';
                });
        }

        let _savedModalBody = null;
        let _savedModalFoot = null;

        function closeSickPreview() {
            if (_savedModalBody) {
                document.querySelector('#modalCell .m-body').innerHTML = _savedModalBody;
                document.querySelector('#modalCell .m-foot').innerHTML = _savedModalFoot;
                _savedModalBody = null;
                _savedModalFoot = null;

                // Re-attach event listeners yang hilang setelah innerHTML di-replace
                document.getElementById('mSickLihatBtn').onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    previewSickAttachment();
                };
            }
        }

        function saveRoster() {
            const btn = document.getElementById('mSaveBtn');
            const dayType = document.getElementById('mDayType').value;
            const empId = document.getElementById('mEmpId').value;
            const date = document.getElementById('mDate').textContent;
            const phCarryId = document.getElementById('mPhCarryover').value;

            // Jika Public Holiday + ada carryover dipilih → validasi ulang ketersediaan
            if (dayType === 'Public Holiday' && phCarryId) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memvalidasi...';

                // Re-fetch carryover untuk pastikan masih available
                fetch(`{{ route('roster.phCarryovers') }}?employee_id=${empId}&date=${date}`, {
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        const stillAvailable = data.success && data.data.some(it => it.id === phCarryId);
                        if (!stillAvailable) {
                            toast('Saldo PH tukar sudah tidak tersedia. Silakan pilih ulang.', false);
                            // Refresh dropdown
                            loadPhCarryovers(empId, date);
                            document.getElementById('mPhCarryover').value = '';
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-save"></i> Save';
                            return;
                        }
                        // Lanjut submit
                        submitRoster(btn, dayType, phCarryId);
                    })
                    .catch(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-save"></i> Save';
                        toast('Gagal validasi saldo PH.', false);
                    });
                return;
            }

            submitRoster(btn, dayType, phCarryId);
        }

        function submitRoster(btn, dayType, phCarryId) {
            const sickFile = document.getElementById('mSickFile').files[0];
            const hasExisting = document.getElementById('mSickExisting').style.display === 'block';
            const shiftId = document.getElementById('mShiftId').value;

            // ── Validasi shift wajib saat Work ──
            if (dayType === 'Work' && !shiftId) {
                toast('Shift wajib dipilih untuk tipe Work.', false);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Save';
                return;
            }

            // ── Validasi sick attachment ──
            if (dayType === 'Sick' && !sickFile && !hasExisting) {
                toast('Bukti sakit wajib di-upload.', false);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Save';
                return;
            }

            // ── Validasi PH tanpa carryover ──
            const phCarrySelect = document.getElementById('mPhCarryover');
            const hasCarryOptions = phCarrySelect && phCarrySelect.options.length > 1;

            if (dayType === 'Public Holiday' && !phCarryId) {
                if (!hasCarryOptions) {
                    toast('Tanggal ini bukan Public Holiday dan tidak ada saldo PH Tukar tersedia.', false);
                } else {
                    toast('Tanggal ini bukan Public Holiday. Pilih saldo PH Tukar (Simpanan) terlebih dahulu.', false);
                }
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Save';
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            const fd = new FormData();
            fd.append('employee_id', document.getElementById('mEmpId').value);
            fd.append('shift_id', shiftId || '');
            fd.append('date', document.getElementById('mDate').textContent);
            fd.append('day_type', dayType);
            fd.append('notes', document.getElementById('mNotes').value);
            if (sickFile) fd.append('sick_attachment', sickFile);

            // PH Tukar: kirim id saldo kalau dipilih
            if (dayType === 'Public Holiday' && phCarryId) {
                fd.append('ph_carryover_id', phCarryId);
            }

            fetch('{{ route('roster.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: fd,
                })
                .then(r => r.json().then(data => ({
                    ok: r.ok,
                    data
                })))
                .then(({
                    ok,
                    data
                }) => {
                    if (ok && data.success) {
                        btn.innerHTML = '<i class="fas fa-check"></i> Berhasil!';
                        toast('Schedule saved!');
                        closeModal('modalCell');
                        setTimeout(() => location.reload(), 700);
                    } else {
                        toast((data.message || 'Failed to save data.'), false);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-save"></i> Save';
                    }
                })
                .catch(() => {
                    toast('Terjadi kesalahan.', false);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save"></i> Save';
                });
        }



        function deleteRoster() {
            if (!confirm('Delete this schedule?')) return;
            const btn = document.getElementById('mDeleteBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            fetch('{{ route('roster.destroy') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({
                        employee_id: document.getElementById('mEmpId').value,
                        date: document.getElementById('mDate').textContent,
                    }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        btn.innerHTML = '<i class="fas fa-check"></i> Berhasil!';
                        toast('Schedule deleted!');
                        closeModal('modalCell');
                        setTimeout(() => location.reload(), 700);
                    } else {
                        toast('Gagal menghapus.', false);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-trash"></i> Delete';
                    }
                })
                .catch(() => {
                    toast('Terjadi kesalahan.', false);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-trash"></i> Delete';
                });
        }

        // ════════════════════════════════════════════════════════
        //  BULK ASSIGN — filter day_type berdasarkan status karyawan
        // ════════════════════════════════════════════════════════
        function filterBulkDayType() {
            const selected = [...document.getElementById('bulkEmps').selectedOptions];
            const statuses = selected.map(o => (o.dataset.status || '').toUpperCase().trim());

            const hasDW = statuses.includes('DW');
            // ✅ FIX 3: gunakan .some() + includes('JOB TRAINING') agar cocok dengan "On Job Training" dari DB
            const hasOJT = statuses.some(s => s.includes('JOB TRAINING'));

            const select = document.getElementById('bulkDayType');
            const noteEl = document.getElementById('bulkDayTypeNote');

            select.querySelectorAll('option').forEach(opt => {
                let hidden = false;
                if (opt.classList.contains('bulk-opt-ph') && hasDW) hidden = true;
                if (opt.classList.contains('bulk-opt-cuti') && (hasDW || hasOJT)) hidden = true;
                opt.style.display = hidden ? 'none' : '';
            });

            // Reset ke Work jika nilai aktif tersembunyi
            const currentOpt = select.options[select.selectedIndex];
            if (currentOpt && currentOpt.style.display === 'none') {
                select.value = 'Work';
                toggleBulkShift();
            }

            // Tampilkan note
            if (hasDW) {
                noteEl.textContent = 'Terdapat karyawan DW — opsi Public Holiday dan Cuti tidak tersedia.';
                noteEl.style.display = 'block';
            } else if (hasOJT) {
                noteEl.textContent = 'Terdapat karyawan On Job Training — opsi Cuti tidak tersedia.';
                noteEl.style.display = 'block';
            } else {
                noteEl.style.display = 'none';
            }
        }

        document.getElementById('bulkEmps').addEventListener('change', filterBulkDayType);

        function saveBulk() {
            const selected = [...document.getElementById('bulkEmps').selectedOptions].map(o => o.value);
            if (!selected.length) {
                toast('Choose at least 1 employee.', false);
                return;
            }
            const shiftId = document.getElementById('bulkShift').value;
            if (!shiftId) {
                toast('Pilih shift terlebih dahulu.', false);
                return;
            }


            const saturdayShiftChecked = document.getElementById('bulkSaturdayShift').checked;
            const saturdayShiftId = document.getElementById('bulkSaturdayShiftId').value;
            if (saturdayShiftChecked && !saturdayShiftId) {
                toast('Pilih shift untuk hari Sabtu.', false);
                return;
            }

            const btn = document.querySelector('#modalBulk .btn-primary-r');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            fetch('{{ route('roster.bulkAssign') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({
                        employee_ids: selected,
                        shift_id: document.getElementById('bulkShift').value || null,
                        start_date: document.getElementById('bulkStart').value,
                        end_date: document.getElementById('bulkEnd').value,
                        day_type: document.getElementById('bulkDayType').value,
                        skip_weekend: document.getElementById('bulkSkipWeekend').checked,
                        saturday_shift: saturdayShiftChecked,
                        saturday_shift_id: saturdayShiftId || null,
                    }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        btn.innerHTML = '<i class="fas fa-check"></i> Berhasil!';
                        toast('success' + data.message);
                        closeModal('modalBulk');
                        setTimeout(() => location.reload(), 700);
                    } else {
                        toast('failed' + (data.message || 'Gagal memproses.'), false);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-calendar-check"></i> Assign';
                    }
                })
                .catch(() => {
                    toast('Terjadi kesalahan.', false);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-calendar-check"></i> Assign';
                });
        }

        function openImportModal() {
            document.getElementById('importFile').value = '';
            document.getElementById('importResult').style.display = 'none';
            document.getElementById('importResult').innerHTML = '';
            const btn = document.getElementById('importBtn');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-file-import"></i> Import';
            openModal('modalImport');
        }

        function downloadTemplate() {
            if (!CURRENT_STORE_ID) {
                toast('Pilih store di filter atas dulu.', false);
                return;
            }
            const start = document.querySelector('input[name="start_date"]')?.value || '{{ $startDate }}';
            const end = document.querySelector('input[name="end_date"]')?.value || '{{ $endDate }}';

            const url = `{{ route('roster.template') }}?store_id=${CURRENT_STORE_ID}&start_date=${start}&end_date=${end}`;
            window.location.href = url;
        }

        function doImport() {
            const startDate = document.getElementById('importStartDate').value;
            const file = document.getElementById('importFile').files[0];

            if (!startDate) {
                toast('Tanggal mulai wajib diisi.', false);
                return;
            }
            if (!file) {
                toast('Pilih file Excel dulu.', false);
                return;
            }
            if (!CURRENT_STORE_ID) {
                toast('Pilih store di filter atas dulu.', false);
                return;
            }

            const btn = document.getElementById('importBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            const fd = new FormData();
            fd.append('store_id', CURRENT_STORE_ID);
            fd.append('start_date', startDate);
            fd.append('file', file);

            fetch('{{ route('roster.import') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: fd,
                })
                .then(r => r.json())
                .then(data => {
                    const resultEl = document.getElementById('importResult');
                    resultEl.style.display = 'block';

                    if (data.success) {
                        let html = `<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:10px;color:#166534;">
                    <strong><i class="fas fa-check-circle"></i> ${data.message}</strong>`;
                        if (data.errors && data.errors.length) {
                            html +=
                                `<div style="margin-top:8px;color:#92400e;"><strong>Baris dilewati:</strong><ul style="margin:4px 0 0 16px;padding:0;">`;
                            data.errors.forEach(e => html += `<li>${e}</li>`);
                            html += `</ul></div>`;
                        }
                        html += `</div>`;
                        resultEl.innerHTML = html;

                        toast('Import selesai!');
                        btn.innerHTML = '<i class="fas fa-check"></i> Selesai';
                        setTimeout(() => location.reload(), 7000);
                    } else {
                        resultEl.innerHTML = `<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px;color:#991b1b;">
                    <i class="fas fa-exclamation-circle"></i> ${data.message || 'Gagal import.'}
                </div>`;
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-file-import"></i> Import';
                    }
                })
                .catch(() => {
                    toast('Terjadi kesalahan.', false);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-file-import"></i> Import';
                });
        }


        // ════════════════════════════════════════════════════════
        //  COPY ROSTER
        // ════════════════════════════════════════════════════════
        function confirmCopyRoster() {
            Swal.fire({
                icon: 'warning',
                iconColor: '#f59e0b',
                title: 'Perhatian!',
                html: `<div style="text-align:left;padding:8px 0;line-height:1.7;font-size:14px;color:#374151">
                Mohon Dicek Kembali Setelah Mengcopy Roster Karena Mengikuti Roster Pada Bulan Ini, Agar Karyawan Yang Mengambil
                <strong>Cuti</strong>, <strong>Libur</strong>, Dan lain-lain Tidak Terinput Kembali Di Roster Yang Akan Di Gunakan Pada Bulan Berikutnya.
               </div>`,
                showCancelButton: true,
                confirmButtonColor: '#1d4ed8',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-copy"></i> Mengerti, Lanjutkan',
                cancelButtonText: 'Batal',
                focusCancel: true
            }).then(result => {
                if (result.isConfirmed) openModal('modalCopy');
            });
        }

        function saveCopy() {
            const sourceStart = document.getElementById('copySourceStart').value;
            const sourceEnd = document.getElementById('copySourceEnd').value;
            const targetStart = document.getElementById('copyTargetStart').value;
            const targetEnd = document.getElementById('copyTargetEnd').value;

            if (!sourceStart || !sourceEnd || !targetStart || !targetEnd) {
                toast('Semua tanggal wajib diisi.', false);
                return;
            }
            if (sourceEnd < sourceStart) {
                toast('Sumber End Date tidak boleh sebelum Sumber Start Date.', false);
                return;
            }
            if (targetEnd < targetStart) {
                toast('Target End Date tidak boleh sebelum Target Start Date.', false);
                return;
            }

            const btn = document.querySelector('#modalCopy .btn-primary-r');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            fetch('{{ route('roster.copyRoster') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({
                        source_start: sourceStart,
                        source_end: sourceEnd,
                        target_start: targetStart,
                        target_end: targetEnd,
                        store_id: CURRENT_STORE_ID
                    }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        btn.innerHTML = '<i class="fas fa-check"></i> Berhasil!';
                        toast('success' + data.message);
                        closeModal('modalCopy');
                        setTimeout(() => location.reload(), 700);
                    } else {
                        toast('Gagal memproses.', false);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-copy"></i> Copy';
                    }
                })
                .catch(() => {
                    toast('Terjadi kesalahan.', false);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-copy"></i> Copy';
                });
        }


        // ════════════════════════════════════════════════════════
        //  BULK DELETE
        // ════════════════════════════════════════════════════════
        function saveBulkDelete() {
            const selected = [...document.getElementById('deleteEmps').selectedOptions].map(o => o.value);
            if (!selected.length) {
                toast('Pilih minimal 1 karyawan.', false);
                return;
            }
            if (!confirm('Yakin ingin menghapus semua jadwal yang dipilih?')) return;

            const btn = document.querySelector('#modalBulkDelete .btn-danger-r');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            fetch('{{ route('roster.bulkDelete') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({
                        employee_ids: selected,
                        start_date: document.getElementById('deleteStart').value,
                        end_date: document.getElementById('deleteEnd').value,
                    }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        btn.innerHTML = '<i class="fas fa-check"></i> Berhasil!';
                        toast('delete' + data.message);
                        closeModal('modalBulkDelete');
                        setTimeout(() => location.reload(), 700);
                    } else {
                        toast('Gagal menghapus.', false);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-trash"></i> Hapus';
                    }
                })
                .catch(() => {
                    toast('Terjadi kesalahan.', false);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-trash"></i> Hapus';
                });
        }

        // ════════════════════════════════════════════════════════
        //  AUTO GENERATE ROSTER — 3 STORE STATIC
        // ════════════════════════════════════════════════════════
        function confirmAutoGenerate() {
            Swal.fire({
                icon: 'info',
                iconColor: '#16a34a',
                title: 'Auto Generate Roster',
                html: `<div style="text-align:left;padding:8px 0;line-height:1.7;font-size:14px;color:#374151">
                Sistem akan men-generate roster otomatis untuk karyawan di
                <strong>Head Office, Holding, dan Distribution Center</strong> dengan pola jadwal:
                <ul style="margin:10px 0 10px 18px;padding:0">
                    <li>Senin - Jumat: <strong>sesuai shift yang dipilih</strong></li>
                    <li>Sabtu: <strong>sesuai shift yang dipilih</strong></li>
                    <li>Minggu: <strong>Off</strong></li>
                    <li>Public Holiday: sesuai agama karyawan (PKWT & On Job Training)</li>
                    <li>Karyawan DW: tidak mendapat Public Holiday</li>
                </ul>
                Roster yang sudah ada <strong>tidak akan ditimpa</strong>.
               </div>`,

                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-eye"></i> Lihat Preview',
                cancelButtonText: 'Batal',
                focusCancel: true
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('ag-start-date').value = '';
                    document.getElementById('ag-end-date').value = '';
                    $('#ag-shift-weekday').val('').trigger('change');
                    $('#ag-shift-saturday').val('').trigger('change');
                    ['ag-period', 'ag-employees', 'ag-hindu', 'ag-non-hindu', 'ag-ph', 'ag-estimated-created',
                        'ag-estimated-skipped'
                    ]
                    .forEach(id => document.getElementById(id).textContent = '-');
                    const genBtn = document.getElementById('btnExecuteAutoGenerate');
                    genBtn.disabled = false;
                    genBtn.innerHTML = '<i class="fas fa-magic"></i> Generate';
                    openModal('modalAutoGenerate');
                    $('.ag-shift-select2').select2({
                        placeholder: '-- Pilih Shift --',
                        allowClear: false,
                        width: '100%',
                        dropdownParent: $('#modalAutoGenerate .m-body')
                    });

                    $('#modalAutoGenerate .m-body').on('scroll', function() {
                        $('.ag-shift-select2').select2('close');
                    });

                    loadAutoGeneratePreview();
                }
            });
        }

        function loadAutoGeneratePreview() {
            const startDate = document.getElementById('ag-start-date').value;
            const endDate = document.getElementById('ag-end-date').value;
            let url = '{{ route('roster.auto-generate.preview') }}';

            if (startDate && endDate) {
                url += `?start_date=${startDate}&end_date=${endDate}`;
            } else if (startDate || endDate) {
                toast('Isi Start Date dan End Date keduanya, atau kosongkan keduanya.', false);
                return;
            }

            ['ag-period', 'ag-employees', 'ag-hindu', 'ag-non-hindu', 'ag-ph', 'ag-estimated-created',
                'ag-estimated-skipped'
            ]
            .forEach(id => document.getElementById(id).textContent = '...');

            fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const p = data.preview;
                        document.getElementById('ag-period').textContent = formatDate(p.start_date) + ' s/d ' +
                            formatDate(p.end_date);
                        document.getElementById('ag-employees').textContent = p.total_employees + ' karyawan';
                        document.getElementById('ag-hindu').textContent = (p.employees_by_religion?.Hindu ?? 0) +
                            ' orang';
                        document.getElementById('ag-non-hindu').textContent = (p.employees_by_religion?.['Non Hindu'] ??
                            0) + ' orang';
                        const phByType = p.public_holidays_by_type || {};
                        const phTotal = Object.values(phByType).reduce((s, n) => s + n, 0);
                        const phDetail = [];
                        if (phByType.Hindu) phDetail.push('Hindu: ' + phByType.Hindu);
                        if (phByType['Non Hindu']) phDetail.push('Non Hindu: ' + phByType['Non Hindu']);
                        if (phByType.All) phDetail.push('All: ' + phByType.All);
                        document.getElementById('ag-ph').textContent = phTotal + (phDetail.length ? ' (' + phDetail
                            .join(', ') + ')' : '');
                        document.getElementById('ag-estimated-created').textContent = (p.estimated_created ?? p
                            .estimated_rows ?? 0) + ' roster';
                        document.getElementById('ag-estimated-skipped').textContent = (p.estimated_skipped ?? 0) +
                            ' roster';
                        const genBtn = document.getElementById('btnExecuteAutoGenerate');
                        genBtn.disabled = false;
                        genBtn.innerHTML = '<i class="fas fa-magic"></i> Generate';
                    } else {
                        toast('error' + (data.message || 'Gagal load preview'), false);
                        ['ag-period', 'ag-employees', 'ag-hindu', 'ag-non-hindu', 'ag-ph', 'ag-estimated-created',
                            'ag-estimated-skipped'
                        ]
                        .forEach(id => document.getElementById(id).textContent = '-');
                    }
                })
                .catch(err => {
                    toast('Terjadi kesalahan saat load preview.', false);
                    console.error(err);
                });
        }


        function executeAutoGenerate() {
            const startDate = document.getElementById('ag-start-date').value;
            const endDate = document.getElementById('ag-end-date').value;
            const shiftWeekday = document.getElementById('ag-shift-weekday').value;
            const shiftSaturday = document.getElementById('ag-shift-saturday').value;

            // Validasi DULU sebelum disable button
            if (!shiftWeekday) {
                toast('Pilih shift untuk Senin - Jumat terlebih dahulu.', false);
                return;
            }
            if (!shiftSaturday) {
                toast('Pilih shift untuk Sabtu terlebih dahulu.', false);
                return;
            }

            const btn = document.getElementById('btnExecuteAutoGenerate');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            const payload = {};
            if (startDate && endDate) {
                payload.start_date = startDate;
                payload.end_date = endDate;
            }

            // Tambahkan shift ke payload
            payload.shift_weekday_id = shiftWeekday;
            payload.shift_saturday_id = shiftSaturday;

            fetch('{{ route('roster.auto-generate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload),
                })
                .then(r => r.json())
                .then(data => {
                    closeModal('modalAutoGenerate');
                    if (data.success) {
                        const s = data.summary;
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            html: `<div style="text-align:left;padding:8px 0;font-size:13px;color:#374151;line-height:1.8">
                        <div style="margin-bottom:10px">
                            <strong style="color:#16a34a">${s.created}</strong> roster baru di-generate
                            ${s.skipped>0?', <strong style="color:#f59e0b">'+s.skipped+'</strong> dilewati (sudah ada)':''}
                        </div>
                        <div style="background:#f0fdf4;border-radius:6px;padding:10px;font-size:12px">
                            <div style="font-weight:600;margin-bottom:6px">Breakdown:</div>
                            <div>• Work: ${s.breakdown_by_type.Work}</div>
                            <div>• Off: ${s.breakdown_by_type.Off}</div>
                            <div>• Public Holiday: ${s.breakdown_by_type['Public Holiday']}</div>
                        </div>
                        <div style="margin-top:10px;font-size:12px;color:#64748b">
                            Periode: ${formatDate(s.period.start)} s/d ${formatDate(s.period.end)}
                        </div>
                      </div>`,
                            confirmButtonColor: '#16a34a',
                            confirmButtonText: 'OK'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message || 'Terjadi kesalahan',
                            confirmButtonColor: '#dc2626'
                        });
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-magic"></i> Generate';
                    }
                })
                .catch(err => {
                    closeModal('modalAutoGenerate');
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: 'Tidak dapat menghubungi server',
                        confirmButtonColor: '#dc2626'
                    });
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-magic"></i> Generate';
                    console.error(err);
                });
        }

        // ════════════════════════════════════════════════════════
        //  AUTO GENERATE ROSTER — STORE LAIN (PER MINGGU)
        // ════════════════════════════════════════════════════════
        const ARO_DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        const ARO_SHIFTS_DATA = @json($shifts ?? []);

        let aroState = {
            weekStart: null,
            dayPattern: ARO_DAYS.map(() => ({
                day_type: 'Off',
                shift_id: null
            })),
            previewData: null,
            availableShifts: ARO_SHIFTS_DATA,
        };


        function openAroModal() {
            aroReset();
            document.getElementById('aroStoreName').textContent = CURRENT_STORE_NAME || '-';
            document.getElementById('aroStoreEmpCount').textContent = document.querySelectorAll('.day-cell').length > 0 ?
                '{{ $employees->count() ?? 0 }} karyawan aktif' :
                '—';
            openModal('modalAroOther');
        }

        function aroSetStep(n) {
            [1, 2, 3, 4].forEach(i => {
                const panel = document.getElementById('aroPanel' + i);
                if (panel) panel.style.display = (i === n) ? 'block' : 'none';
            });
            [1, 2, 3].forEach(i => {
                const dot = document.getElementById('aroStepDot' + i);
                const line = document.getElementById('aroStepLine' + i);
                if (dot) {
                    dot.classList.toggle('active', i === n);
                    dot.classList.toggle('done', i < n);
                }
                if (line) {
                    line.classList.toggle('done', i < n);
                }
            });
        }

        function aroOnWeekChange() {
            const val = document.getElementById('aroWeekStart').value;
            const warn = document.getElementById('aroWeekDayWarn');
            if (!val) {
                aroState.weekStart = null;
                aroCheckStep1();
                return;
            }

            const date = new Date(val + 'T00:00:00');
            const dow = date.getDay();

            if (dow !== 1) {
                warn.style.display = 'block';
                const diff = (dow === 0) ? -6 : -(dow - 1);
                date.setDate(date.getDate() + diff);
                document.getElementById('aroWeekStart').value = date.toISOString().split('T')[0];
            } else {
                warn.style.display = 'none';
            }

            aroState.weekStart = date.toISOString().split('T')[0];

            const end = new Date(date);
            end.setDate(date.getDate() + 6);
            const fmt = d => d.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
            document.getElementById('aroWeekRangeInfo').textContent = fmt(date) + ' – ' + fmt(end);

            aroCheckStep1();
        }

        function aroCheckStep1() {
            document.getElementById('aroStep1Next').disabled = !aroState.weekStart;
        }

        function aroToggleEmpSelect() {
            const val = document.querySelector('input[name="aroApplyTo"]:checked').value;
            document.getElementById('aroEmpSelectWrap').style.display = val === 'selected' ? 'block' : 'none';
        }

        function aroGoStep1() {
            aroSetStep(1);
        }

        function aroGoStep2() {
            if (!aroState.weekStart) return;
            aroRenderDayPattern();
            aroSetStep(2);
        }

        function aroGoStep3() {
            aroLoadPreview();
            aroSetStep(3);
        }

        function aroRenderDayPattern() {
            const tbody = document.getElementById('aroDayPatternBody');
            tbody.innerHTML = '';

            const shiftOptions = aroState.availableShifts.map(s =>
                `<option value="${s.id}">${s.shift_name} (${s.start_time.substring(0,5)}–${s.end_time.substring(0,5)})</option>`
            ).join('');

            ARO_DAYS.forEach((day, i) => {
                const isWeekend = i >= 5;
                const pat = aroState.dayPattern[i];

                tbody.innerHTML += `
        <tr class="${isWeekend ? 'weekend-row' : ''}">
            <td><span style="font-weight:600;color:${isWeekend?'#b91c1c':'#0f172a'};">${day}</span></td>
            <td>
                <select class="f-control" style="padding:5px 8px;font-size:12px;" id="aroDt${i}" onchange="aroOnDtChange(${i})">
                    <option value="Work"           ${pat.day_type==='Work'?'selected':''}>Work</option>
                    <option value="Off"            ${pat.day_type==='Off'?'selected':''}>Off</option>
                    <option value="Public Holiday" ${pat.day_type==='Public Holiday'?'selected':''}>Public Holiday</option>
                </select>
            </td>
            <td>
                <select class="f-control" style="padding:5px 8px;font-size:12px;" id="aroSh${i}" onchange="aroOnShChange(${i})" ${pat.day_type!=='Work'?'disabled':''}>
                    <option value="">— Pilih Shift —</option>
                    ${shiftOptions}
                </select>
            </td>
            <td style="font-size:11px;color:#64748b;" id="aroShInfo${i}">—</td>
        </tr>`;
            });

            aroState.dayPattern.forEach((pat, i) => {
                if (pat.shift_id) {
                    const sel = document.getElementById(`aroSh${i}`);
                    if (sel) {
                        sel.value = pat.shift_id;
                        aroUpdateShiftInfo(i);
                    }
                }
            });
        }

        function aroOnDtChange(i) {
            const dt = document.getElementById(`aroDt${i}`).value;
            const sel = document.getElementById(`aroSh${i}`);
            aroState.dayPattern[i].day_type = dt;
            if (dt !== 'Work') {
                sel.disabled = true;
                sel.value = '';
                aroState.dayPattern[i].shift_id = null;
                document.getElementById(`aroShInfo${i}`).textContent = '—';
            } else {
                sel.disabled = false;
            }
        }

        function aroOnShChange(i) {
            const val = document.getElementById(`aroSh${i}`).value;
            aroState.dayPattern[i].shift_id = val ? parseInt(val) : null;
            aroUpdateShiftInfo(i);
        }

        function aroUpdateShiftInfo(i) {
            const shiftId = aroState.dayPattern[i].shift_id;
            const shift = aroState.availableShifts.find(s => s.id == shiftId);
            const el = document.getElementById(`aroShInfo${i}`);
            if (el) el.textContent = shift ? shift.start_time.substring(0, 5) + ' – ' + shift.end_time.substring(0, 5) :
                '—';
        }

        function aroQuickPattern(type) {
            const firstShift = aroState.availableShifts[0];
            if (type === 'default') {
                aroState.dayPattern = ARO_DAYS.map((_, i) => ({
                    day_type: (i < 6) ? 'Work' : 'Off',
                    shift_id: (i < 6) ? (firstShift?.id || null) : null,
                }));
            } else {
                aroState.dayPattern = ARO_DAYS.map(() => ({
                    day_type: 'Off',
                    shift_id: null
                }));
            }
            aroRenderDayPattern();
        }

        async function aroLoadPreview() {
            document.getElementById('aroPreviewLoading').style.display = 'block';
            document.getElementById('aroPreviewContent').style.display = 'none';
            document.getElementById('aroGenerateBtn').disabled = true;

            try {
                const url =
                    `/roster/auto-generate/other/preview?store_id=${CURRENT_STORE_ID}&week_start=${aroState.weekStart}`;
                const resp = await fetch(url);
                const data = await resp.json();

                if (!data.success) {
                    document.getElementById('aroPreviewLoading').innerHTML =
                        `<div style="color:#dc2626;font-size:13px;"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
                    return;
                }

                aroState.previewData = data.preview;
                aroRenderPreview(data.preview);
            } catch (err) {
                document.getElementById('aroPreviewLoading').innerHTML =
                    `<div style="color:#dc2626;font-size:13px;"><i class="fas fa-exclamation-circle"></i> Gagal memuat preview: ${err.message}</div>`;
            }
        }

        function aroRenderPreview(p) {
            const override = document.getElementById('aroOverride').checked;

            document.getElementById('aroPreviewCards').innerHTML = `
        <div class="aro-result-card aro-card-created">
            <div class="val">${p.will_be_created}</div>
            <div class="lbl">Akan Dibuat</div>
        </div>
        <div class="aro-result-card aro-card-updated" style="background:${override?'#eff6ff':'#f8fafc'}">
            <div class="val" style="color:${override?'#1d4ed8':'#94a3b8'}">${p.existing_rosters}</div>
            <div class="lbl" style="color:${override?'#60a5fa':'#cbd5e1'}">${override?'Akan Diperbarui':'Dilewati'}</div>
        </div>
        <div class="aro-result-card aro-card-ph">
            <div class="val">${p.public_holidays ? p.public_holidays.length : 0}</div>
            <div class="lbl">Hari Libur</div>
        </div>
        <div class="aro-result-card" style="background:#f0f9ff;">
            <div class="val" style="color:#0369a1;">${p.total_employees}</div>
            <div class="lbl" style="color:#7dd3fc;">Karyawan</div>
        </div>`;

            const phSection = document.getElementById('aroPhSection');
            const phList = document.getElementById('aroPhList');
            if (p.public_holidays && p.public_holidays.length > 0) {
                phList.innerHTML = p.public_holidays.map(ph =>
                    `<li>${ph.date}: ${ph.remark} <span style="background:#fde047;color:#854d0e;border-radius:3px;padding:1px 5px;font-size:10px;">${ph.type}</span></li>`
                ).join('');
                phSection.style.display = 'block';
            } else {
                phSection.style.display = 'none';
            }

            const tbody = document.getElementById('aroPreviewPatternBody');
            tbody.innerHTML = '';
            aroState.dayPattern.forEach((pat, i) => {
                const shift = aroState.availableShifts.find(s => s.id == pat.shift_id);
                const color = pat.day_type === 'Work' ? '#1d4ed8' : pat.day_type === 'Off' ? '#64748b' : '#854d0e';
                tbody.innerHTML += `
            <tr>
                <td style="font-weight:600;">${ARO_DAYS[i]}</td>
                <td><span style="color:${color};font-weight:600;">${pat.day_type}</span></td>
                <td style="color:#64748b;font-size:11px;">${shift ? shift.shift_name+' ('+shift.start_time.substring(0,5)+'–'+shift.end_time.substring(0,5)+')' : '—'}</td>
            </tr>`;
            });

            const overrideWarn = document.getElementById('aroOverrideWarn');
            if (override && p.existing_rosters > 0) {
                document.getElementById('aroOverrideWarnCount').textContent = p.existing_rosters;
                overrideWarn.style.display = 'block';
            } else {
                overrideWarn.style.display = 'none';
            }

            document.getElementById('aroPreviewLoading').style.display = 'none';
            document.getElementById('aroPreviewContent').style.display = 'block';
            document.getElementById('aroGenerateBtn').disabled = false;
        }

        async function aroDoGenerate() {
            const btn = document.getElementById('aroGenerateBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';

            const applyTo = document.querySelector('input[name="aroApplyTo"]:checked').value;
            const empIds = applyTo === 'selected' ?
                Array.from(document.getElementById('aroEmpMultiSelect').selectedOptions).map(o => parseInt(o.value)) :
                [];

            const payload = {
                store_id: CURRENT_STORE_ID,
                week_start: aroState.weekStart,
                apply_to: applyTo,
                employee_ids: empIds,
                override_existing: document.getElementById('aroOverride').checked,
                day_pattern: aroState.dayPattern,
            };

            try {
                const resp = await fetch('/roster/auto-generate/other', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify(payload),
                });
                const data = await resp.json();
                aroShowResult(data);
                aroSetStep(4);
            } catch (err) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-lightning-bolt"></i> Generate Sekarang';
                toast('Terjadi kesalahan: ' + err.message, false);
            }
        }

        function aroShowResult(data) {
            const el = document.getElementById('aroResultContent');
            if (data.success) {
                const s = data.summary;
                el.innerHTML = `
            <div style="text-align:center;margin-bottom:16px;">
                <div style="font-size:48px;">Success</div>
                <h5 style="font-weight:700;margin-top:8px;color:#16a34a;">Generate Berhasil!</h5>
                <p style="color:#64748b;font-size:13px;">${data.message}</p>
            </div>
            <div class="aro-result-grid">
                <div class="aro-result-card aro-card-created"><div class="val">${s.created}</div><div class="lbl">Dibuat</div></div>
                <div class="aro-result-card aro-card-updated"><div class="val">${s.updated}</div><div class="lbl">Diperbarui</div></div>
                <div class="aro-result-card aro-card-skipped"><div class="val">${s.skipped}</div><div class="lbl">Dilewati</div></div>
                <div class="aro-result-card aro-card-ph"><div class="val">${s.public_holidays}</div><div class="lbl">Hari Libur</div></div>
            </div>
            <div style="background:#f8fafc;border-radius:6px;padding:10px 12px;font-size:12px;color:#475569;margin-top:12px;">
                <strong>Breakdown:</strong>
                Work: ${s.breakdown_by_type.Work} &nbsp;|&nbsp;
                Off: ${s.breakdown_by_type.Off} &nbsp;|&nbsp;
                Public Holiday: ${s.breakdown_by_type['Public Holiday']}
            </div>`;
            } else {
                el.innerHTML = `
            <div style="text-align:center;">
                <div style="font-size:48px;">Failed</div>
                <h5 style="font-weight:700;margin-top:8px;color:#dc2626;">Generate Gagal</h5>
                <p style="color:#64748b;font-size:13px;">${data.message}</p>
            </div>`;
            }
        }

        function aroReset() {
            aroState.weekStart = null;
            aroState.dayPattern = ARO_DAYS.map(() => ({
                day_type: 'Off',
                shift_id: null
            }));
            aroState.previewData = null;

            document.getElementById('aroWeekStart').value = '';
            document.getElementById('aroWeekRangeInfo').textContent = '';
            document.getElementById('aroWeekDayWarn').style.display = 'none';
            document.getElementById('aroOverride').checked = false;
            document.getElementById('aroStep1Next').disabled = true;
            document.getElementById('aroEmpSelectWrap').style.display = 'none';
            document.querySelector('input[name="aroApplyTo"][value="all"]').checked = true;

            document.getElementById('aroPreviewLoading').style.display = 'block';
            document.getElementById('aroPreviewLoading').innerHTML = `
        <i class="fas fa-spinner fa-spin fa-2x" style="color:#1d4ed8;"></i>
        <div style="margin-top:10px;font-size:13px;color:#64748b;">Memuat preview...</div>`;
            document.getElementById('aroPreviewContent').style.display = 'none';

            aroSetStep(1);
        }

        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
    {{-- untuk tracking history --}}

    <script>
        function loadHistory() {
            const start = document.getElementById('historyStart').value;
            const end = document.getElementById('historyEnd').value;
            const search = document.getElementById('historySearch').value;
            const storeId = document.getElementById('historyStore').value; // ← ambil dari select/hidden

            const resultEl = document.getElementById('historyResult');

            if (!start || !end) {
                toast('Start Date dan End Date wajib diisi.', false);
                return;
            }

            resultEl.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>';

            let url = `{{ route('roster.history') }}?start_date=${start}&end_date=${end}&search=${search}`;
            // Kirim store_id hanya kalau ada (Admin bisa kosong = semua store)
            if (storeId) url += `&store_id=${storeId}`;
            fetch(url)
                .then(r => r.json())
                .then(data => {
                    if (!data.success || !data.data.length) {
                        resultEl.innerHTML = '<div class="text-muted text-center py-4">Tidak ada data ditemukan.</div>';
                        return;
                    }

                    let html = `<div style="overflow-x:auto;">
                <table class="roster-table" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th class="col-emp">Employee</th>
                            <th>Tanggal</th>
                            <th>Day Type</th>
                            <th>Shift</th>
                            <th>Jam</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>`;

                    data.data.forEach(emp => {
                        emp.rosters.forEach((r, i) => {
                            const badgeColor = {
                                'Work': '#dbeafe',
                                'Off': '#f1f5f9',
                                'Public Holiday': '#fef9c3',
                                'Leave': '#fce7f3',
                                'Cuti Melahirkan': '#fce7f3',
                            } [r.day_type] || '#f1f5f9';

                            html += `<tr>`;

                            // Rowspan untuk nama employee
                            if (i === 0) {
                                html += `<td class="col-emp" rowspan="${emp.rosters.length}" style="vertical-align:top;">
                            <div class="emp-name">${emp.employee_name}</div>
                            <div class="emp-meta">Dept: ${emp.department}</div>
                            <div class="emp-meta">Pos: ${emp.position}</div>
                            <span class="emp-status status-pkwt">${emp.status_employee}</span>
                        </td>`;
                            }

                            html += `
                        <td>${r.date}</td>
                        <td><span style="background:${badgeColor};padding:2px 8px;border-radius:4px;font-size:11px;">${r.day_type}</span></td>
                        <td>${r.shift_name}</td>
                        <td>${r.start_time && r.end_time ? r.start_time + ' - ' + r.end_time : '-'}</td>
                        <td style="color:#64748b;">${r.notes || '-'}</td>
                    </tr>`;
                        });
                    });
                    html += `</tbody></table></div>`;
                    resultEl.innerHTML = html;
                })
                .catch(() => {
                    resultEl.innerHTML = '<div class="text-danger text-center py-4">Terjadi kesalahan.</div>';
                });
        }

        function resetHistory() {
            document.getElementById('historyStart').value = '';
            document.getElementById('historyEnd').value = '';
            document.getElementById('historySearch').value = '';
            document.getElementById('historyResult').innerHTML =
                '<div class="text-muted text-center py-4"><i class="fas fa-search"></i> Masukkan rentang tanggal lalu klik Cari.</div>';
        }

        function exportHistory() {
            const start = document.getElementById('historyStart').value;
            const end = document.getElementById('historyEnd').value;
            const search = document.getElementById('historySearch').value;
            const storeId = document.getElementById('historyStore').value;

            if (!start || !end) {
                toast('Start Date dan End Date wajib diisi.', false);
                return;
            }

            let url = `{{ route('roster.history.export') }}?start_date=${start}&end_date=${end}&search=${search}`;
            if (storeId) url += `&store_id=${storeId}`;

            // Langsung download via window.location
            window.location.href = url;
        }

        // Ambil daftar PH simpanan untuk karyawan + tanggal aktif
        function loadPhCarryovers(empId, date) {
            const sel = document.getElementById('mPhCarryover');
            const note = document.getElementById('mPhCarryoverNote');
            sel.innerHTML = '<option value="">-- Tidak pakai PH simpanan --</option>';
            note.style.display = 'none';

            const url = `{{ route('roster.phCarryovers') }}?employee_id=${empId}&date=${date}`;
            fetch(url, {
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.data.length) {
                        data.data.forEach(it => {
                            const opt = document.createElement('option');
                            opt.value = it.id;
                            opt.textContent = `${it.ph_name} (asal ${it.ph_date}, exp ${it.expired_at})`;
                            sel.appendChild(opt);
                        });
                    } else {
                        note.textContent = 'Tidak ada PH simpanan tersedia untuk karyawan ini.';
                        note.style.display = 'block';
                    }
                })
                .catch(() => {
                    note.textContent = 'Gagal memuat PH simpanan.';
                    note.style.display = 'block';
                });
        }
    </script>

    <script>
        $(document).ready(function() {
            $('#activityTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('roster.activities') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '50px'
                    },
                    {
                        data: 'event_badge',
                        name: 'event',
                        width: '100px'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'causer_name',
                        name: 'causer_name',
                        width: '150px'
                    },
                    {
                        data: 'properties',
                        name: 'properties',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_at_formatted',
                        name: 'created_at',
                        width: '160px'
                    },
                ],
                order: [
                    [5, 'desc']
                ],
                pageLength: 10,
                language: {
                    processing: '<i class="fas fa-spinner fa-spin"></i> Loading...',
                    emptyTable: 'No activity log found.',
                    zeroRecords: 'No matching records found.',
                }
            });
        });
    </script>
    @if ($isSupervisorOrManager && !$isRosterOpen)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Roster Ditutup',
                    text: 'Periode pengisian roster sedang ditutup.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6',
                });
            });

            // 1. Cek element ada
            console.log(document.getElementById('mSickLihatBtn'))

            // 2. Cek onclick ter-set
            console.log(document.getElementById('mSickLihatBtn').onclick)

            // 3. Cek _currentSickPath
            console.log(_currentSickPath)

            // 4. Trigger manual
            document.getElementById('mSickLihatBtn').dispatchEvent(new MouseEvent('click', {
                bubbles: true
            }))
        </script>
    @endif
@endpush
