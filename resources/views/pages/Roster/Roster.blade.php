@extends('layouts.app')
@section('title', 'Roster & Schedule')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
/* ── Wrapper ── */
.roster-scroll { overflow-x: auto; border-radius: 8px; }
.roster-table  { border-collapse: collapse; width: 100%; font-size: 13px; }

/* ── Header ── */
.roster-table thead th {
    background: #1e293b; color: #fff;
    font-size: 11px; font-weight: 600; text-transform: uppercase;
    padding: 10px 6px; text-align: center; white-space: nowrap;
    border: 1px solid #334155;
}
.roster-table thead th.col-emp {
    position: sticky; left: 0; z-index: 4; background: #0f172a;
    min-width: 200px; text-align: left; padding-left: 14px;
}
.roster-table thead th.weekend { background: #7f1d1d; }
.roster-table thead th.today   { background: #78350f; }

/* ── Body ── */
.roster-table tbody td {
    border: 1px solid #e2e8f0; padding: 5px 4px;
    vertical-align: middle; text-align: center; background: #fff;
}
.roster-table tbody td.col-emp {
    position: sticky; left: 0; z-index: 2; background: #f8fafc;
    text-align: left; padding: 8px 14px; border-right: 2px solid #cbd5e1;
    min-width: 200px;
}
.roster-table tbody tr:nth-child(even) td.col-emp { background: #f1f5f9; }
.roster-table tbody tr:hover td { background: #f0f9ff !important; }

/* ── Employee info ── */
.emp-name { font-weight: 600; color: #0f172a; font-size: 13px; }
.emp-meta { font-size: 10px; color: #64748b; margin-top: 1px; }

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
.status-pkwt { background: #dbeafe; color: #1d4ed8; }
.status-ojt  { background: #fef9c3; color: #854d0e; }
.status-dw   { background: #fce7f3; color: #9d174d; }

/* ── Day cell ── */
.day-cell { cursor: pointer; min-width: 85px; }
.day-cell:hover { background: #eff6ff !important; }
.day-cell.weekend { background: #fff1f2 !important; }
.day-cell.today   { background: #fefce8 !important; outline: 2px solid #eab308; outline-offset: -2px; }

/* ── Roster badge ── */
.r-badge {
    display: inline-flex; flex-direction: column; align-items: center;
    border-radius: 5px; padding: 3px 8px; min-width: 72px;
    border: 1px solid transparent;
}
.r-badge .r-name { font-weight: 700; font-size: 11px; white-space: nowrap; }
.r-badge .r-time { font-size: 10px; white-space: nowrap; opacity: .85; }
.r-badge .r-notes {
    font-size: 9px; font-style: italic; margin-top: 2px;
    max-width: 80px; white-space: nowrap; overflow: hidden;
    text-overflow: ellipsis; opacity: 0.9;
}
.r-work    { background: #dbeafe; border-color: #93c5fd; }
.r-work    .r-name  { color: #1d4ed8; }
.r-work    .r-time  { color: #3b82f6; }
.r-work    .r-notes { color: #1e40af; }
.r-off     { background: #f1f5f9; border-color: #cbd5e1; }
.r-off     .r-name  { color: #64748b; }
.r-off     .r-notes { color: #475569; }
.r-holiday { background: #fef9c3; border-color: #fde047; }
.r-holiday .r-name  { color: #854d0e; }
.r-holiday .r-notes { color: #92400e; font-weight: 500; }
.r-leave   { background: #f3e8ff; border-color: #d8b4fe; }
.r-leave   .r-name  { color: #7e22ce; }
.r-leave   .r-notes { color: #6b21a8; }
.r-empty   { color: #cbd5e1; font-size: 20px; line-height: 1; }

/* ── Filter card ── */
.filter-card { background: #fff; border-radius: 10px; box-shadow: 0 1px 8px rgba(0,0,0,.08); padding: 20px 24px; margin-bottom: 16px; }
.f-label { font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; display: block; white-space: nowrap; }
.f-control { width: 100%; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; font-size: 13px; color: #0f172a; }
.f-control:focus { outline: none; border-color: #3b82f6; }
.filter-item { min-width: 180px; }
.filter-item-date { min-width: 160px; }
.filter-item-btn { display: flex; gap: 8px; align-items: flex-end; padding-bottom: 1px; }

/* ── Buttons ── */
.btn-primary-r   { background: #1d4ed8; color: #fff; border: none; border-radius: 6px; padding: 8px 18px; font-size: 13px; font-weight: 600; cursor: pointer; }
.btn-primary-r:disabled { opacity: 0.7; cursor: not-allowed; }
.btn-secondary-r { background: #e2e8f0; color: #334155; border: none; border-radius: 6px; padding: 8px 14px; font-size: 13px; cursor: pointer; }
.btn-danger-r    { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; border-radius: 6px; padding: 5px 10px; font-size: 12px; cursor: pointer; }
.btn-danger-r:disabled { opacity: 0.7; cursor: not-allowed; }
.btn-success-r   { background: #16a34a; color: #fff; border: none; border-radius: 6px; padding: 8px 18px; font-size: 13px; font-weight: 600; cursor: pointer; }
.btn-success-r:hover { background: #15803d; }
.btn-success-r:disabled { opacity: 0.7; cursor: not-allowed; }

/* ── Modal ── */
.m-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:9999; align-items:center; justify-content:center; }
.m-overlay.open { display:flex; }
.m-box { background:#fff; border-radius:12px; width:320px; max-width:95vw; box-shadow:0 20px 60px rgba(0,0,0,.2); overflow:hidden; }
.m-head { background:#1e293b; color:#fff; padding:14px 18px; font-weight:700; font-size:15px; display:flex; justify-content:space-between; align-items:center; }
.m-head button { background:none; border:none; color:#94a3b8; font-size:20px; cursor:pointer; line-height:1; }
.m-head button:hover { color:#fff; }
.m-body { padding:18px; }
.m-foot { padding:12px 18px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:8px; }

/* ── Toast ── */
#rosterToast { position:fixed; bottom:24px; right:24px; z-index:99999; background:#0f172a; color:#fff; border-radius:8px; padding:12px 20px; font-size:13px; font-weight:500; display:none; max-width:320px; }

/* ── Legend Checkbox ── */
.legend { display:flex; flex-wrap:wrap; gap:12px; margin-bottom:12px; align-items:center; }
.legend-item { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: #475569; cursor: pointer; user-select: none; }
.legend-item input[type="checkbox"] { width: 15px; height: 15px; cursor: pointer; accent-color: #3b82f6; }

/* ── Empty state ── */
.empty-state { text-align:center; padding: 60px 20px; color: #94a3b8; }
.empty-state i { font-size: 48px; margin-bottom: 16px; display: block; }
.empty-state h5 { font-size: 16px; font-weight: 600; color: #64748b; margin-bottom: 8px; }
.empty-state p { font-size: 13px; color: #94a3b8; }

/* ── Auto Roster Static: Periode card ── */
.ag-period-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-bottom: 14px; }
.ag-period-title { font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 8px; }
.ag-period-title small { font-size: 11px; font-weight: 400; color: #94a3b8; }
.btn-update-preview { background: none; border: none; color: #1d4ed8; font-size: 12px; font-weight: 500; cursor: pointer; padding: 4px 0; margin-top: 4px; display: inline-flex; align-items: center; gap: 4px; }
.btn-update-preview:hover { color: #1e40af; text-decoration: underline; }
.btn-update-preview:disabled { color: #94a3b8; cursor: not-allowed; }
.auto-roster-preview { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 12px; font-size: 12px; color: #1e40af; margin-bottom: 14px; }
.auto-roster-preview .preview-row { display: flex; justify-content: space-between; padding: 3px 0; }
.auto-roster-preview .preview-row strong { color: #1e3a8a; }
.auto-roster-preview .preview-row.created strong { color: #16a34a; font-weight: 700; }
.auto-roster-preview .preview-row.skipped strong { color: #f59e0b; font-weight: 700; }

/* ══════════════════════════════════════════════════════
   AUTO ROSTER OTHER STORE — Step Indicator
   ══════════════════════════════════════════════════════ */
.aro-step-wrap { display:flex; align-items:center; justify-content:center; margin-bottom:20px; }
.aro-step-item { display:flex; flex-direction:column; align-items:center; }
.aro-step-dot {
    width:32px; height:32px; border-radius:50%;
    background:#e2e8f0; color:#94a3b8; font-weight:700; font-size:13px;
    display:flex; align-items:center; justify-content:center;
    transition: all .25s ease;
}
.aro-step-item.active .aro-step-dot { background:#1d4ed8; color:#fff; }
.aro-step-item.done   .aro-step-dot { background:#16a34a; color:#fff; }
.aro-step-label { font-size:10px; margin-top:4px; color:#94a3b8; white-space:nowrap; }
.aro-step-item.active .aro-step-label { color:#1d4ed8; font-weight:600; }
.aro-step-item.done   .aro-step-label { color:#16a34a; }
.aro-step-line { width:60px; height:2px; background:#e2e8f0; margin-bottom:18px; transition: all .25s ease; }
.aro-step-line.done { background:#16a34a; }

/* ARO modal body scroll */
.aro-modal-body { padding:18px; max-height:70vh; overflow-y:auto; }

/* ARO day pattern table */
.aro-day-table { width:100%; border-collapse:collapse; font-size:12px; }
.aro-day-table th { background:#1e293b; color:#fff; padding:8px 10px; text-align:left; font-size:11px; text-transform:uppercase; }
.aro-day-table td { border-bottom:1px solid #f1f5f9; padding:6px 8px; vertical-align:middle; }
.aro-day-table tr:last-child td { border-bottom:none; }
.aro-day-table tr.weekend-row td { background:#fff8f8; }

/* ARO info box */
.aro-info-box { background:#fffbeb; border:1px solid #fde68a; border-radius:6px; padding:10px 12px; font-size:11px; color:#92400e; margin-bottom:14px; line-height:1.6; }
.aro-preview-box { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:12px; font-size:12px; margin-bottom:14px; }
.aro-preview-row { display:flex; justify-content:space-between; padding:3px 0; color:#166534; }
.aro-preview-row strong { color:#14532d; }

/* ARO result card */
.aro-result-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:14px; }
.aro-result-card { border-radius:8px; padding:14px; text-align:center; }
.aro-result-card .val { font-size:26px; font-weight:700; }
.aro-result-card .lbl { font-size:11px; margin-top:2px; }
.aro-card-created  { background:#f0fdf4; } .aro-card-created  .val { color:#16a34a; } .aro-card-created  .lbl { color:#4ade80; }
.aro-card-updated  { background:#eff6ff; } .aro-card-updated  .val { color:#1d4ed8; } .aro-card-updated  .lbl { color:#60a5fa; }
.aro-card-skipped  { background:#fafafa; } .aro-card-skipped  .val { color:#64748b; } .aro-card-skipped  .lbl { color:#94a3b8; }
.aro-card-ph       { background:#fefce8; } .aro-card-ph       .val { color:#ca8a04; } .aro-card-ph       .lbl { color:#fbbf24; }
</style>
@endpush

@section('main')
<div class="main-content">
<section class="section">

    <div class="section-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h1>Roster & Schedule</h1>
        @if($storeId)
        <div class="d-flex" style="gap:16px">
            @php
                $autoGenerateStores = ['Head Office', 'Holding', 'Distribution Center'];
                $currentStoreName   = optional($stores->firstWhere('id', $storeId))->name ?? '';
                $showAutoGenerate   = in_array($currentStoreName, $autoGenerateStores);
            @endphp

            @if($showAutoGenerate)
                <button class="btn-success-r" onclick="confirmAutoGenerate()" style="padding:8px 24px"
                        title="Auto generate roster untuk periode yang dipilih">
                    <i class="fas fa-magic"></i> Auto Generate Roster
                </button>
            @else
                <button class="btn-success-r" onclick="openAroModal()" style="padding:8px 24px"
                        title="Auto generate roster mingguan dengan pola kustom">
                    <i class="fas fa-calendar-week"></i> Auto Generate Roster
                </button>
            @endif

            <button class="btn-primary-r" onclick="openModal('modalBulk')" style="padding:8px 24px">
                <i class="fas fa-calendar-plus"></i> Bulk Assign
            </button>
            <button class="btn-secondary-r" onclick="confirmCopyRoster()" style="padding:8px 24px">
                <i class="fas fa-copy"></i> Copy Roster
            </button>
            <button class="btn-danger-r" onclick="openModal('modalBulkDelete')" style="padding:8px 24px;font-size:13px">
                <i class="fas fa-trash"></i> Bulk Delete
            </button>
        </div>
        @endif
    </div>

    <div class="section-body">

        {{-- ── Filter ── --}}
        <div class="filter-card">
            <form method="GET" action="{{ route('roster.index') }}" class="d-flex flex-wrap align-items-end" style="gap: 20px;">
                <div class="filter-item">
                    <label class="f-label">
                        Location
                        <span style="color:#ef4444">*</span>
                        <small style="color:#94a3b8;font-weight:400">(Required)</small>
                    </label>
                    <select name="store_id" class="f-control select2" style="min-width:180px" required>
                        <option value="">Choose Location</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-item-date">
                    <label class="f-label">Start Date</label>
                    <input type="text" id="start_date" name="start_date" class="f-control" value="{{ $startDate }}">
                </div>
                <div class="filter-item-date">
                    <label class="f-label">End Date</label>
                    <input type="date" name="end_date" class="f-control" value="{{ $endDate }}">
                </div>
                <div class="filter-item-btn">
                    <button type="submit" class="btn-primary-r"><i class="fas fa-search"></i> Filter</button>
                    <a href="{{ route('roster.index') }}" class="btn-secondary-r">Reset</a>
                </div>
            </form>
        </div>

        @if(!$storeId)
            <div class="card" style="border:none;box-shadow:0 1px 8px rgba(0,0,0,.08);">
                <div class="card-body">
                    <div class="empty-state">
                        <i class="fas fa-store"></i>
                        <h5>Choose Location First</h5>
                        <p>Please select location in the top filter then click <strong>Filter</strong> untuk menampilkan data roster.</p>
                    </div>
                </div>
            </div>
        @else

        {{-- ── Legend Checkbox Filter ── --}}
        <div class="legend">
            <label class="legend-item">
                <input type="checkbox" class="legend-filter" data-type="work" checked> Shift Kerja
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
                <input type="checkbox" class="legend-filter" data-type="melahirkan"> Cuti Melahirkan
            </label>
            <label class="legend-item">
                <input type="checkbox" class="legend-filter" data-type="weekend"> Weekend
            </label>
            <label class="legend-item">
                <input type="checkbox" class="legend-filter" data-type="today"> Hari Ini
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
                                @foreach($dates as $carbon)
                                    @php
                                        $isWeekend = $carbon->isWeekend();
                                        $isToday   = $carbon->isToday();
                                    @endphp
                                    <th class="{{ $isWeekend ? 'weekend' : '' }} {{ $isToday ? 'today' : '' }}" style="min-width:85px">
                                        <div>{{ $carbon->format('D') }}</div>
                                        <div style="font-size:10px;opacity:.8">{{ $carbon->format('d/m') }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                                @php
                                    $rosterByDate = $employee->rosters->keyBy(fn($r) => \Carbon\Carbon::parse($r->date)->toDateString());
                                    $statusRaw    = strtoupper($employee->status_employee ?? '');

                                    //FIX 1: Gunakan str_contains untuk mendeteksi "On Job Training"
                                    $statusClass  = match(true) {
                                        $statusRaw === 'DW'                        => 'status-dw',
                                        str_contains($statusRaw, 'JOB TRAINING')   => 'status-ojt',
                                        $statusRaw === 'PKWT'                      => 'status-pkwt',
                                        default                                    => 'status-pkwt',
                                    };
                                @endphp
                                <tr>
                                    <td class="col-emp">
                                        <div class="emp-name">Employee Name : {{ $employee->employee_name }}</div>
                                        <div class="emp-meta">Department : {{ $employee->department->department_name ?? '-' }}</div>
                                        <div class="emp-meta">Position : {{ $employee->position->name ?? '-' }}</div>
                                        <div class="emp-meta">Location {{ $employee->store->name ?? '-' }}</div>
                                        @if($employee->status_employee)
                                            <span class="emp-status {{ $statusClass }}">
                                                {{ $employee->status_employee }}
                                            </span>
                                        @endif
                                    </td>
                                    @foreach($dates as $carbon)
                                        @php
                                            $dateStr   = $carbon->toDateString();
                                            $roster    = $rosterByDate->get($dateStr);
                                            $isWeekend = $carbon->isWeekend();
                                            $isToday   = $carbon->isToday();

                                            $badgeClass = '';
                                            $badgeName  = '+';
                                            $badgeTime  = '';
                                            $cellType   = 'empty';

                                            if ($roster) {
                                                if ($roster->day_type === 'Off') {
                                                    $badgeClass = 'r-badge r-off'; $badgeName = 'Off'; $cellType = 'off';
                                                } elseif ($roster->day_type === 'Public Holiday') {
                                                    $badgeClass = 'r-badge r-holiday'; $badgeName = 'Public Holiday'; $cellType = 'holiday';
                                                } elseif ($roster->day_type === 'Cuti Melahirkan') {
                                                    $badgeClass = 'r-badge r-leave'; $badgeName = 'Cuti Melahirkan'; $cellType = 'melahirkan';
                                                } elseif ($roster->day_type === 'Leave') {
                                                    $badgeClass = 'r-badge r-leave'; $badgeName = 'Leave'; $cellType = 'leave';
                                                } elseif ($roster->shift) {
                                                    $badgeClass = 'r-badge r-work';
                                                    $badgeName  = $roster->shift->shift_name;
                                                    $badgeTime  = substr($roster->shift->start_time,0,5).'-'.substr($roster->shift->end_time,0,5);
                                                    $cellType = 'work';
                                                } else {
                                                    $badgeClass = 'r-badge r-work'; $badgeName = 'Work'; $cellType = 'work';
                                                }
                                            } elseif ($isWeekend) {
                                                $badgeClass = 'r-badge r-off'; $badgeName = 'Off'; $cellType = 'weekend';
                                            }
                                        @endphp
                                        <td class="day-cell {{ $isWeekend ? 'weekend' : '' }} {{ $isToday ? 'today' : '' }}"
                                            data-emp-id="{{ $employee->id }}"
                                            data-emp-name="{{ $employee->employee_name }}"
                                            data-emp-status="{{ $employee->status_employee ?? '' }}"
                                            data-date="{{ $dateStr }}"
                                            data-shift-id="{{ $roster?->shift_id ?? '' }}"
                                            data-day-type="{{ $roster?->day_type ?? 'Work' }}"
                                            data-has-roster="{{ $roster ? '1' : '0' }}"
                                            data-notes="{{ $roster?->notes ?? '' }}"
                                            data-cell-type="{{ $cellType }}"
                                            data-is-today="{{ $isToday ? '1' : '0' }}"
                                            onclick="openCellModal(this)"
                                            title="{{ $employee->employee_name }} – {{ $dateStr }}{{ $roster?->notes ? ' | ' . $roster->notes : '' }}">
                                            @if($badgeClass === '')
                                                <span class="r-empty">+</span>
                                            @else
                                                <span class="{{ $badgeClass }}">
                                                    <span class="r-name">{{ $badgeName }}</span>
                                                    @if($badgeTime)<span class="r-time">{{ $badgeTime }}</span>@endif
                                                    @if($roster && $roster->notes)
                                                        <span class="r-notes">{{ $roster->notes }}</span>
                                                    @endif
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($dates) + 1 }}" class="text-center py-5 text-muted">
                                        No employees were found in this location.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @endif

    </div>
</section>
</div>

{{-- ════════════════════════════════════════════════════
     MODAL: Set Jadwal (klik cell)
     ════════════════════════════════════════════════════ --}}
<div class="m-overlay" id="modalCell">
    <div class="m-box">
        <div class="m-head">
            <span>📅 Set Schedule</span>
            <button onclick="closeModal('modalCell')">×</button>
        </div>
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
            </select>
            <small id="mDayTypeNote" style="display:none;color:#92400e;font-size:11px;margin-top:-8px;margin-bottom:10px;display:block"></small>

            <div id="shiftWrap">
                <label class="f-label">Shift</label>
                <select id="mShiftId" class="f-control mb-3">
                    <option value="">-- Choose Shift --</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}">
                            {{ $shift->shift_name }}
                            ({{ substr($shift->start_time,0,5) }} - {{ substr($shift->end_time,0,5) }})
                        </option>
                    @endforeach
                </select>
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
    </div>
</div>

{{-- ════════════════════════════════════════════════════
     MODAL: Bulk Assign
     ════════════════════════════════════════════════════ --}}
<div class="m-overlay" id="modalBulk">
    <div class="m-box" style="width:400px">
        <div class="m-head">
            <span>📋 Bulk Assign Shift</span>
            <button onclick="closeModal('modalBulk')">×</button>
        </div>
        <div class="m-body">
            <label class="f-label">Choose Employee</label>
            <select id="bulkEmps" class="f-control mb-1" multiple style="height:100px">
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" data-status="{{ $emp->status_employee ?? '' }}">
                        {{ $emp->employee_name }} – {{ $emp->store->name ?? '' }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted d-block mb-3">Hold the button <kbd>Ctrl</kbd> to choose more than 1 employee</small>

            <div class="d-flex gap-2 mb-3">
                <div style="flex:1"><label class="f-label">Start Date</label><input type="date" id="bulkStart" class="f-control" value="{{ $startDate }}"></div>
                <div style="flex:1"><label class="f-label">End Date</label><input type="date" id="bulkEnd" class="f-control" value="{{ $endDate }}"></div>
            </div>

            <label class="f-label">Day Type</label>
            <select id="bulkDayType" class="f-control mb-1" onchange="toggleBulkShift()">
                <option value="Work">Work</option>
                <option value="Off">Off</option>
                <option value="Public Holiday" class="bulk-opt-ph">Public Holiday</option>
                <option value="Leave" class="bulk-opt-cuti">Leave</option>
                <option value="Cuti Melahirkan" class="bulk-opt-cuti">Cuti Melahirkan / Maternity leave</option>
            </select>
            <small id="bulkDayTypeNote" style="display:none;color:#92400e;font-size:11px;margin-bottom:10px;display:block"></small>

            <div id="bulkShiftWrap" style="margin-top:10px">
                <label class="f-label">Shift</label>
                <select id="bulkShift" class="f-control mb-3">
                    <option value="">-- Choose Shift --</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->shift_name }} ({{ substr($shift->start_time,0,5) }}-{{ substr($shift->end_time,0,5) }})</option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex align-items-center gap-2 mb-2">
                <input type="checkbox" id="bulkSkipWeekend" checked>
                <label for="bulkSkipWeekend" class="f-label mb-0">Skip Sunday</label>
            </div>

            <div class="d-flex align-items-center gap-2 mb-2" style="margin-top:8px">
                <input type="checkbox" id="bulkSaturdayShift" onchange="toggleSaturdayShift()">
                <label for="bulkSaturdayShift" class="f-label mb-0">Shift Sabtu</label>
            </div>

            <div id="saturdayShiftWrap" style="display:none;margin-top:8px">
                <label class="f-label">Pilih Shift Sabtu</label>
                <select id="bulkSaturdayShiftId" class="f-control mb-3">
                    <option value="">-- Pilih Shift Sabtu --</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}">
                            {{ $shift->shift_name }} ({{ substr($shift->start_time,0,5) }}-{{ substr($shift->end_time,0,5) }})
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
            <p style="font-size:12px;color:#64748b;margin-bottom:14px">Copy jadwal dari periode sumber ke periode target.</p>
            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:10px;font-size:12px;color:#1e40af;margin-bottom:14px">
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
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->employee_name }} – {{ $emp->store->name ?? '' }}</option>
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
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px;font-size:12px;color:#991b1b;">
                ⚠️ Semua jadwal karyawan yang dipilih dalam rentang tanggal ini akan dihapus permanen!
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
    <div class="m-box" style="width:440px">
        <div class="m-head">
            <span>✨ Auto Generate Roster</span>
            <button onclick="closeModal('modalAutoGenerate')">×</button>
        </div>
        <div class="m-body">
            <p style="font-size:12px;color:#64748b;margin-bottom:14px;line-height:1.6">
                Sistem akan generate roster otomatis untuk karyawan di:
                <strong>Head Office, Holding, Distribution Center</strong>.
            </p>

            <div class="ag-period-card">
                <div class="ag-period-title">
                    Periode Generate
                    <small>(kosongkan untuk default otomatis)</small>
                </div>
                <div class="d-flex gap-2">
                    <div style="flex:1">
                        <label class="f-label">Start Date</label>
                        <input type="date" id="ag-start-date" class="f-control" onchange="loadAutoGeneratePreview()">
                    </div>
                    <div style="flex:1">
                        <label class="f-label">End Date</label>
                        <input type="date" id="ag-end-date" class="f-control" onchange="loadAutoGeneratePreview()">
                    </div>
                </div>
                <button type="button" class="btn-update-preview" onclick="loadAutoGeneratePreview()">
                    <i class="fas fa-sync-alt"></i> Update Preview
                </button>
            </div>

            <div class="auto-roster-preview" id="autoRosterPreview">
                <div style="font-weight:600;margin-bottom:8px;color:#1e3a8a">📅 Detail Periode</div>
                <div class="preview-row"><span>Periode:</span><strong id="ag-period">-</strong></div>
                <div class="preview-row"><span>Total karyawan:</span><strong id="ag-employees">-</strong></div>
                <div class="preview-row"><span>&nbsp;&nbsp;• Hindu:</span><strong id="ag-hindu">-</strong></div>
                <div class="preview-row"><span>&nbsp;&nbsp;• Non Hindu:</span><strong id="ag-non-hindu">-</strong></div>
                <div class="preview-row"><span>Public Holiday dalam periode:</span><strong id="ag-ph">-</strong></div>
                <div style="border-top:1px dashed #bfdbfe;margin:6px 0"></div>
                <div class="preview-row created"><span>Estimasi akan dibuat:</span><strong id="ag-estimated-created">-</strong></div>
                <div class="preview-row skipped"><span>Estimasi akan dilewati (sudah ada):</span><strong id="ag-estimated-skipped">-</strong></div>
            </div>

            <div style="background:#fef9c3;border:1px solid #fde047;border-radius:6px;padding:10px;font-size:12px;color:#854d0e;">
                <i class="fas fa-info-circle"></i>
                Pola jadwal: <strong>Senin-Jumat</strong> (9 to 5), <strong>Sabtu</strong> (9 to 3), <strong>Minggu</strong> (Off).
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
<div class="m-overlay" id="modalAroOther">
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
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->employee_name }}</option>
                    @endforeach
                </select>
                <small style="color:#64748b;font-size:11px;">Tahan Ctrl/Cmd untuk memilih lebih dari satu.</small>
            </div>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px 12px;margin-bottom:4px;">
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
                    <button class="btn-secondary-r" style="padding:5px 10px;font-size:11px;" onclick="aroQuickPattern('default')">
                        <i class="fas fa-magic"></i> Default
                    </button>
                    <button class="btn-secondary-r" style="padding:5px 10px;font-size:11px;" onclick="aroQuickPattern('allOff')">
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
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px;margin-bottom:12px;" id="aroPreviewCards"></div>
                <div id="aroPhSection" style="display:none;margin-bottom:12px;">
                    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px 12px;font-size:12px;color:#991b1b;">
                        <strong><i class="fas fa-calendar-times"></i> Hari Libur Nasional di Minggu Ini:</strong>
                        <ul id="aroPhList" style="margin:6px 0 0 16px;padding:0;"></ul>
                    </div>
                </div>
                <div style="font-size:12px;font-weight:600;color:#334155;margin-bottom:6px;">Ringkasan Pola Shift:</div>
                <div style="overflow-x:auto;margin-bottom:12px;">
                    <table class="aro-day-table">
                        <thead><tr><th>Hari</th><th>Tipe</th><th>Shift</th></tr></thead>
                        <tbody id="aroPreviewPatternBody"></tbody>
                    </table>
                </div>
                <div id="aroOverrideWarn" style="display:none;margin-bottom:12px;">
                    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:10px 12px;font-size:12px;color:#92400e;">
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
</div>

<div id="rosterToast"></div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
flatpickr("#start_date", {
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "d F Y",
    locale: "id",
    defaultDate: (function () {
        let now = new Date();
        return new Date(now.getFullYear(), now.getMonth(), 26);
    })()
});
</script>
<script>
// ════════════════════════════════════════════════════════
//  GLOBALS
// ════════════════════════════════════════════════════════
const CSRF               = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const CURRENT_STORE_ID   = '{{ $storeId ?? '' }}';
const CURRENT_STORE_NAME = '{{ $currentStoreName ?? '' }}';

// ── Toast ──
function toast(msg, ok = true) {
    const el = document.getElementById('rosterToast');
    el.textContent      = msg;
    el.style.background = ok ? '#0f172a' : '#991b1b';
    el.style.display    = 'block';
    setTimeout(() => el.style.display = 'none', 3500);
}

// ── Modal open/close ──
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.m-overlay').forEach(el => {
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
});

// ── Toggles ──
function toggleShift() {
    document.getElementById('shiftWrap').style.display =
        document.getElementById('mDayType').value === 'Work' ? 'block' : 'none';
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
    return String(d.getDate()).padStart(2,'0') + '/' +
           String(d.getMonth()+1).padStart(2,'0') + '/' +
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
        const type    = cell.dataset.cellType;
        const isToday = cell.dataset.isToday === '1';
        let show = false;

        if (showAll || type === 'empty')                                        { show = true; }
        else if (checkedTypes.includes('work')       && type === 'work')        { show = true; }
        else if (checkedTypes.includes('off')        && type === 'off')         { show = true; }
        else if (checkedTypes.includes('holiday')    && type === 'holiday')     { show = true; }
        else if (checkedTypes.includes('leave')      && type === 'leave')       { show = true; }
        else if (checkedTypes.includes('melahirkan') && type === 'melahirkan')  { show = true; }
        else if (checkedTypes.includes('weekend')    && type === 'weekend')     { show = true; }
        else if (checkedTypes.includes('today')      && isToday)                { show = true; }

        cell.style.visibility    = show ? 'visible' : 'hidden';
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
    const s     = (status || '').toUpperCase().trim();
    // ✅ FIX 2: gunakan includes('JOB TRAINING') agar cocok dengan "On Job Training" dari DB
    const isDW  = s === 'DW';
    const isOJT = s.includes('JOB TRAINING');

    selectEl.querySelectorAll('option').forEach(opt => {
        let hidden = false;
        if (opt.classList.contains('opt-ph')        && isDW)             hidden = true;
        if (opt.classList.contains('bulk-opt-ph')   && isDW)             hidden = true;
        if (opt.classList.contains('opt-cuti')      && (isDW || isOJT))  hidden = true;
        if (opt.classList.contains('bulk-opt-cuti') && (isDW || isOJT))  hidden = true;
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
            noteEl.textContent   = '⚠️ Karyawan DW — opsi Public Holiday dan Cuti tidak tersedia.';
            noteEl.style.display = 'block';
        } else if (isOJT) {
            noteEl.textContent   = '⚠️ Karyawan On Job Training — opsi Cuti tidak tersedia.';
            noteEl.style.display = 'block';
        } else {
            noteEl.style.display = 'none';
        }
    }
}

// ════════════════════════════════════════════════════════
//  CELL MODAL
// ════════════════════════════════════════════════════════
function openCellModal(cell) {
    document.getElementById('mEmpId').value         = cell.dataset.empId;
    document.getElementById('mEmpName').textContent = cell.dataset.empName;
    document.getElementById('mDate').textContent    = cell.dataset.date;
    document.getElementById('mNotes').value         = cell.dataset.notes || '';
    document.getElementById('mDeleteBtn').style.display = cell.dataset.hasRoster === '1' ? 'block' : 'none';

    const select  = document.getElementById('mDayType');
    const noteEl  = document.getElementById('mDayTypeNote');
    const status  = cell.dataset.empStatus || '';

    // ── Filter opsi berdasarkan status karyawan ──
    applyDayTypeFilter(status, select, noteEl);

    // Set nilai setelah filter
    const currentDayType = cell.dataset.dayType || 'Work';
    const targetOpt = Array.from(select.options).find(o => o.value === currentDayType && o.style.display !== 'none');
    select.value = targetOpt ? currentDayType : 'Work';

    document.getElementById('mShiftId').value = cell.dataset.shiftId || '';

    const saveBtn = document.getElementById('mSaveBtn');
    saveBtn.disabled  = false;
    saveBtn.innerHTML = '<i class="fas fa-save"></i> Save';

    const delBtn = document.getElementById('mDeleteBtn');
    delBtn.disabled  = false;
    delBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';

    toggleShift();
    openModal('modalCell');
}

function saveRoster() {
    const btn = document.getElementById('mSaveBtn');
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

    fetch('{{ route('roster.store') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            employee_id: document.getElementById('mEmpId').value,
            shift_id:    document.getElementById('mShiftId').value || null,
            date:        document.getElementById('mDate').textContent,
            day_type:    document.getElementById('mDayType').value,
            notes:       document.getElementById('mNotes').value,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i> Berhasil!';
            toast('✅ Schedule saved!');
            closeModal('modalCell');
            setTimeout(() => location.reload(), 700);
        } else {
            toast('❌ ' + (data.message || 'Failed to save data.'), false);
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Save';
        }
    })
    .catch(() => {
        toast('❌ Terjadi kesalahan.', false);
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Save';
    });
}

function deleteRoster() {
    if (!confirm('Delete this schedule?')) return;
    const btn = document.getElementById('mDeleteBtn');
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

    fetch('{{ route('roster.destroy') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            employee_id: document.getElementById('mEmpId').value,
            date:        document.getElementById('mDate').textContent,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i> Berhasil!';
            toast('🗑️ Schedule deleted!');
            closeModal('modalCell');
            setTimeout(() => location.reload(), 700);
        } else {
            toast('❌ Gagal menghapus.', false);
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-trash"></i> Delete';
        }
    })
    .catch(() => {
        toast('❌ Terjadi kesalahan.', false);
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-trash"></i> Delete';
    });
}

// ════════════════════════════════════════════════════════
//  BULK ASSIGN — filter day_type berdasarkan status karyawan
// ════════════════════════════════════════════════════════
function filterBulkDayType() {
    const selected = [...document.getElementById('bulkEmps').selectedOptions];
    const statuses = selected.map(o => (o.dataset.status || '').toUpperCase().trim());

    const hasDW  = statuses.includes('DW');
    // ✅ FIX 3: gunakan .some() + includes('JOB TRAINING') agar cocok dengan "On Job Training" dari DB
    const hasOJT = statuses.some(s => s.includes('JOB TRAINING'));

    const select = document.getElementById('bulkDayType');
    const noteEl = document.getElementById('bulkDayTypeNote');

    select.querySelectorAll('option').forEach(opt => {
        let hidden = false;
        if (opt.classList.contains('bulk-opt-ph')   && hasDW)             hidden = true;
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
        noteEl.textContent   = '⚠️ Terdapat karyawan DW — opsi Public Holiday dan Cuti tidak tersedia.';
        noteEl.style.display = 'block';
    } else if (hasOJT) {
        noteEl.textContent   = '⚠️ Terdapat karyawan On Job Training — opsi Cuti tidak tersedia.';
        noteEl.style.display = 'block';
    } else {
        noteEl.style.display = 'none';
    }
}

document.getElementById('bulkEmps').addEventListener('change', filterBulkDayType);

function saveBulk() {
    const selected = [...document.getElementById('bulkEmps').selectedOptions].map(o => o.value);
    if (!selected.length) { toast('⚠️ Choose at least 1 employee.', false); return; }

    const saturdayShiftChecked = document.getElementById('bulkSaturdayShift').checked;
    const saturdayShiftId      = document.getElementById('bulkSaturdayShiftId').value;
    if (saturdayShiftChecked && !saturdayShiftId) { toast('⚠️ Pilih shift untuk hari Sabtu.', false); return; }

    const btn = document.querySelector('#modalBulk .btn-primary-r');
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

    fetch('{{ route('roster.bulkAssign') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            employee_ids:      selected,
            shift_id:          document.getElementById('bulkShift').value || null,
            start_date:        document.getElementById('bulkStart').value,
            end_date:          document.getElementById('bulkEnd').value,
            day_type:          document.getElementById('bulkDayType').value,
            skip_weekend:      document.getElementById('bulkSkipWeekend').checked,
            saturday_shift:    saturdayShiftChecked,
            saturday_shift_id: saturdayShiftId || null,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i> Berhasil!';
            toast('✅ ' + data.message);
            closeModal('modalBulk');
            setTimeout(() => location.reload(), 700);
        } else {
            toast('❌ ' + (data.message || 'Gagal memproses.'), false);
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-calendar-check"></i> Assign';
        }
    })
    .catch(() => {
        toast('❌ Terjadi kesalahan.', false);
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-calendar-check"></i> Assign';
    });
}

// ════════════════════════════════════════════════════════
//  COPY ROSTER
// ════════════════════════════════════════════════════════
function confirmCopyRoster() {
    Swal.fire({
        icon: 'warning', iconColor: '#f59e0b',
        title: '⚠️ Perhatian!',
        html: `<div style="text-align:left;padding:8px 0;line-height:1.7;font-size:14px;color:#374151">
                Mohon Dicek Kembali Setelah Mengcopy Roster Karena Mengikuti Roster Pada Bulan Ini, Agar Karyawan Yang Mengambil
                <strong>Cuti</strong>, <strong>Libur</strong>, Dan lain-lain Tidak Terinput Kembali Di Roster Yang Akan Di Gunakan Pada Bulan Berikutnya.
               </div>`,
        showCancelButton: true,
        confirmButtonColor: '#1d4ed8', cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-copy"></i> Mengerti, Lanjutkan',
        cancelButtonText: 'Batal', focusCancel: true
    }).then(result => { if (result.isConfirmed) openModal('modalCopy'); });
}

function saveCopy() {
    const sourceStart = document.getElementById('copySourceStart').value;
    const sourceEnd   = document.getElementById('copySourceEnd').value;
    const targetStart = document.getElementById('copyTargetStart').value;
    const targetEnd   = document.getElementById('copyTargetEnd').value;

    if (!sourceStart || !sourceEnd || !targetStart || !targetEnd) { toast('⚠️ Semua tanggal wajib diisi.', false); return; }
    if (sourceEnd < sourceStart) { toast('⚠️ Sumber End Date tidak boleh sebelum Sumber Start Date.', false); return; }
    if (targetEnd < targetStart) { toast('⚠️ Target End Date tidak boleh sebelum Target Start Date.', false); return; }

    const btn = document.querySelector('#modalCopy .btn-primary-r');
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

    fetch('{{ route('roster.copyRoster') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ source_start: sourceStart, source_end: sourceEnd, target_start: targetStart, target_end: targetEnd, store_id: CURRENT_STORE_ID }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i> Berhasil!';
            toast('✅ ' + data.message);
            closeModal('modalCopy');
            setTimeout(() => location.reload(), 700);
        } else {
            toast('❌ Gagal memproses.', false);
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-copy"></i> Copy';
        }
    })
    .catch(() => {
        toast('❌ Terjadi kesalahan.', false);
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-copy"></i> Copy';
    });
}

// ════════════════════════════════════════════════════════
//  BULK DELETE
// ════════════════════════════════════════════════════════
function saveBulkDelete() {
    const selected = [...document.getElementById('deleteEmps').selectedOptions].map(o => o.value);
    if (!selected.length) { toast('⚠️ Pilih minimal 1 karyawan.', false); return; }
    if (!confirm('Yakin ingin menghapus semua jadwal yang dipilih?')) return;

    const btn = document.querySelector('#modalBulkDelete .btn-danger-r');
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

    fetch('{{ route('roster.bulkDelete') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            employee_ids: selected,
            start_date:   document.getElementById('deleteStart').value,
            end_date:     document.getElementById('deleteEnd').value,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i> Berhasil!';
            toast('🗑️ ' + data.message);
            closeModal('modalBulkDelete');
            setTimeout(() => location.reload(), 700);
        } else {
            toast('❌ Gagal menghapus.', false);
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-trash"></i> Hapus';
        }
    })
    .catch(() => {
        toast('❌ Terjadi kesalahan.', false);
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-trash"></i> Hapus';
    });
}

// ════════════════════════════════════════════════════════
//  AUTO GENERATE ROSTER — 3 STORE STATIC
// ════════════════════════════════════════════════════════
function confirmAutoGenerate() {
    Swal.fire({
        icon: 'info', iconColor: '#16a34a',
        title: '✨ Auto Generate Roster',
        html: `<div style="text-align:left;padding:8px 0;line-height:1.7;font-size:14px;color:#374151">
                Sistem akan men-generate roster otomatis untuk karyawan di
                <strong>Head Office, Holding, dan Distribution Center</strong> dengan pola jadwal:
                <ul style="margin:10px 0 10px 18px;padding:0">
                    <li>Senin - Jumat: <strong>9 to 5</strong></li>
                    <li>Sabtu: <strong>9 to 3</strong></li>
                    <li>Minggu: <strong>Off</strong></li>
                    <li>Public Holiday: sesuai agama karyawan (PKWT & On Job Training)</li>
                    <li>Karyawan DW: tidak mendapat Public Holiday</li>
                </ul>
                Roster yang sudah ada <strong>tidak akan ditimpa</strong>.
               </div>`,
        showCancelButton: true,
        confirmButtonColor: '#16a34a', cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-eye"></i> Lihat Preview',
        cancelButtonText: 'Batal', focusCancel: true
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById('ag-start-date').value = '';
            document.getElementById('ag-end-date').value   = '';
            ['ag-period','ag-employees','ag-hindu','ag-non-hindu','ag-ph','ag-estimated-created','ag-estimated-skipped']
                .forEach(id => document.getElementById(id).textContent = '-');
            const genBtn = document.getElementById('btnExecuteAutoGenerate');
            genBtn.disabled  = false;
            genBtn.innerHTML = '<i class="fas fa-magic"></i> Generate';
            openModal('modalAutoGenerate');
            loadAutoGeneratePreview();
        }
    });
}

function loadAutoGeneratePreview() {
    const startDate = document.getElementById('ag-start-date').value;
    const endDate   = document.getElementById('ag-end-date').value;
    let url = '{{ route('roster.auto-generate.preview') }}';

    if (startDate && endDate) {
        url += `?start_date=${startDate}&end_date=${endDate}`;
    } else if (startDate || endDate) {
        toast('⚠️ Isi Start Date dan End Date keduanya, atau kosongkan keduanya.', false);
        return;
    }

    ['ag-period','ag-employees','ag-hindu','ag-non-hindu','ag-ph','ag-estimated-created','ag-estimated-skipped']
        .forEach(id => document.getElementById(id).textContent = '...');

    fetch(url, { method:'GET', headers:{ 'X-CSRF-TOKEN': CSRF, 'Accept':'application/json' } })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const p = data.preview;
            document.getElementById('ag-period').textContent            = formatDate(p.start_date) + ' s/d ' + formatDate(p.end_date);
            document.getElementById('ag-employees').textContent         = p.total_employees + ' karyawan';
            document.getElementById('ag-hindu').textContent             = (p.employees_by_religion?.Hindu ?? 0) + ' orang';
            document.getElementById('ag-non-hindu').textContent         = (p.employees_by_religion?.['Non Hindu'] ?? 0) + ' orang';
            const phByType = p.public_holidays_by_type || {};
            const phTotal  = Object.values(phByType).reduce((s,n)=>s+n,0);
            const phDetail = [];
            if (phByType.Hindu)        phDetail.push('Hindu: '+phByType.Hindu);
            if (phByType['Non Hindu']) phDetail.push('Non Hindu: '+phByType['Non Hindu']);
            if (phByType.All)          phDetail.push('All: '+phByType.All);
            document.getElementById('ag-ph').textContent                = phTotal + (phDetail.length ? ' ('+phDetail.join(', ')+')' : '');
            document.getElementById('ag-estimated-created').textContent = (p.estimated_created ?? p.estimated_rows ?? 0) + ' roster';
            document.getElementById('ag-estimated-skipped').textContent = (p.estimated_skipped ?? 0) + ' roster';
            const genBtn = document.getElementById('btnExecuteAutoGenerate');
            genBtn.disabled  = false;
            genBtn.innerHTML = '<i class="fas fa-magic"></i> Generate';
        } else {
            toast('❌ ' + (data.message || 'Gagal load preview'), false);
            ['ag-period','ag-employees','ag-hindu','ag-non-hindu','ag-ph','ag-estimated-created','ag-estimated-skipped']
                .forEach(id => document.getElementById(id).textContent = '-');
        }
    })
    .catch(err => { toast('❌ Terjadi kesalahan saat load preview.', false); console.error(err); });
}

function executeAutoGenerate() {
    const startDate = document.getElementById('ag-start-date').value;
    const endDate   = document.getElementById('ag-end-date').value;
    const btn       = document.getElementById('btnExecuteAutoGenerate');
    btn.disabled    = true;
    btn.innerHTML   = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

    const payload = {};
    if (startDate && endDate) { payload.start_date = startDate; payload.end_date = endDate; }

    fetch('{{ route('roster.auto-generate') }}', {
        method:'POST',
        headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        closeModal('modalAutoGenerate');
        if (data.success) {
            const s = data.summary;
            Swal.fire({
                icon:'success', title:'✅ Berhasil!',
                html:`<div style="text-align:left;padding:8px 0;font-size:13px;color:#374151;line-height:1.8">
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
                confirmButtonColor:'#16a34a', confirmButtonText:'OK'
            }).then(()=>location.reload());
        } else {
            Swal.fire({ icon:'error', title:'❌ Gagal', text: data.message||'Terjadi kesalahan', confirmButtonColor:'#dc2626' });
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-magic"></i> Generate';
        }
    })
    .catch(err => {
        closeModal('modalAutoGenerate');
        Swal.fire({ icon:'error', title:'❌ Terjadi Kesalahan', text:'Tidak dapat menghubungi server', confirmButtonColor:'#dc2626' });
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-magic"></i> Generate';
        console.error(err);
    });
}

// ════════════════════════════════════════════════════════
//  AUTO GENERATE ROSTER — STORE LAIN (PER MINGGU)
// ════════════════════════════════════════════════════════
const ARO_DAYS        = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
const ARO_SHIFTS_DATA = @json($shifts ?? []);

let aroState = {
    weekStart:       null,
    dayPattern:      ARO_DAYS.map(() => ({ day_type: 'Off', shift_id: null })),
    previewData:     null,
    availableShifts: ARO_SHIFTS_DATA,
};

function openAroModal() {
    aroReset();
    document.getElementById('aroStoreName').textContent     = CURRENT_STORE_NAME || '-';
    document.getElementById('aroStoreEmpCount').textContent = document.querySelectorAll('.day-cell').length > 0
        ? '{{ $employees->count() ?? 0 }} karyawan aktif'
        : '—';
    openModal('modalAroOther');
}

function aroSetStep(n) {
    [1,2,3,4].forEach(i => {
        const panel = document.getElementById('aroPanel'+i);
        if (panel) panel.style.display = (i === n) ? 'block' : 'none';
    });
    [1,2,3].forEach(i => {
        const dot  = document.getElementById('aroStepDot'+i);
        const line = document.getElementById('aroStepLine'+i);
        if (dot)  { dot.classList.toggle('active', i===n); dot.classList.toggle('done', i<n); }
        if (line) { line.classList.toggle('done', i<n); }
    });
}

function aroOnWeekChange() {
    const val  = document.getElementById('aroWeekStart').value;
    const warn = document.getElementById('aroWeekDayWarn');
    if (!val) { aroState.weekStart = null; aroCheckStep1(); return; }

    const date = new Date(val + 'T00:00:00');
    const dow  = date.getDay();

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
    const fmt = d => d.toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'});
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

function aroGoStep1() { aroSetStep(1); }
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
        const pat       = aroState.dayPattern[i];

        tbody.innerHTML += `
        <tr class="${isWeekend ? 'weekend-row' : ''}">
            <td><span style="font-weight:600;color:${isWeekend?'#b91c1c':'#0f172a'};">${day}</span></td>
            <td>
                <select class="f-control" style="padding:5px 8px;font-size:12px;" id="aroDt${i}" onchange="aroOnDtChange(${i})">
                    <option value="Work"           ${pat.day_type==='Work'?'selected':''}>✅ Work</option>
                    <option value="Off"            ${pat.day_type==='Off'?'selected':''}>🔴 Off</option>
                    <option value="Public Holiday" ${pat.day_type==='Public Holiday'?'selected':''}>🏖️ Public Holiday</option>
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
            if (sel) { sel.value = pat.shift_id; aroUpdateShiftInfo(i); }
        }
    });
}

function aroOnDtChange(i) {
    const dt  = document.getElementById(`aroDt${i}`).value;
    const sel = document.getElementById(`aroSh${i}`);
    aroState.dayPattern[i].day_type = dt;
    if (dt !== 'Work') {
        sel.disabled = true; sel.value = '';
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
    const shift   = aroState.availableShifts.find(s => s.id == shiftId);
    const el      = document.getElementById(`aroShInfo${i}`);
    if (el) el.textContent = shift ? shift.start_time.substring(0,5) + ' – ' + shift.end_time.substring(0,5) : '—';
}

function aroQuickPattern(type) {
    const firstShift = aroState.availableShifts[0];
    if (type === 'default') {
        aroState.dayPattern = ARO_DAYS.map((_, i) => ({
            day_type: (i < 6) ? 'Work' : 'Off',
            shift_id: (i < 6) ? (firstShift?.id || null) : null,
        }));
    } else {
        aroState.dayPattern = ARO_DAYS.map(() => ({ day_type: 'Off', shift_id: null }));
    }
    aroRenderDayPattern();
}

async function aroLoadPreview() {
    document.getElementById('aroPreviewLoading').style.display = 'block';
    document.getElementById('aroPreviewContent').style.display = 'none';
    document.getElementById('aroGenerateBtn').disabled         = true;

    try {
        const url  = `/roster/auto-generate/other/preview?store_id=${CURRENT_STORE_ID}&week_start=${aroState.weekStart}`;
        const resp = await fetch(url);
        const data = await resp.json();

        if (!data.success) {
            document.getElementById('aroPreviewLoading').innerHTML =
                `<div style="color:#dc2626;font-size:13px;"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
            return;
        }

        aroState.previewData = data.preview;
        aroRenderPreview(data.preview);
    } catch(err) {
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
    const phList    = document.getElementById('aroPhList');
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
    document.getElementById('aroGenerateBtn').disabled         = false;
}

async function aroDoGenerate() {
    const btn     = document.getElementById('aroGenerateBtn');
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';

    const applyTo = document.querySelector('input[name="aroApplyTo"]:checked').value;
    const empIds  = applyTo === 'selected'
        ? Array.from(document.getElementById('aroEmpMultiSelect').selectedOptions).map(o => parseInt(o.value))
        : [];

    const payload = {
        store_id:          CURRENT_STORE_ID,
        week_start:        aroState.weekStart,
        apply_to:          applyTo,
        employee_ids:      empIds,
        override_existing: document.getElementById('aroOverride').checked,
        day_pattern:       aroState.dayPattern,
    };

    try {
        const resp = await fetch('/roster/auto-generate/other', {
            method:  'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF },
            body:    JSON.stringify(payload),
        });
        const data = await resp.json();
        aroShowResult(data);
        aroSetStep(4);
    } catch(err) {
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-lightning-bolt"></i> Generate Sekarang';
        toast('❌ Terjadi kesalahan: ' + err.message, false);
    }
}

function aroShowResult(data) {
    const el = document.getElementById('aroResultContent');
    if (data.success) {
        const s = data.summary;
        el.innerHTML = `
            <div style="text-align:center;margin-bottom:16px;">
                <div style="font-size:48px;">✅</div>
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
                <div style="font-size:48px;">❌</div>
                <h5 style="font-weight:700;margin-top:8px;color:#dc2626;">Generate Gagal</h5>
                <p style="color:#64748b;font-size:13px;">${data.message}</p>
            </div>`;
    }
}

function aroReset() {
    aroState.weekStart   = null;
    aroState.dayPattern  = ARO_DAYS.map(() => ({ day_type: 'Off', shift_id: null }));
    aroState.previewData = null;

    document.getElementById('aroWeekStart').value            = '';
    document.getElementById('aroWeekRangeInfo').textContent  = '';
    document.getElementById('aroWeekDayWarn').style.display  = 'none';
    document.getElementById('aroOverride').checked           = false;
    document.getElementById('aroStep1Next').disabled         = true;
    document.getElementById('aroEmpSelectWrap').style.display = 'none';
    document.querySelector('input[name="aroApplyTo"][value="all"]').checked = true;

    document.getElementById('aroPreviewLoading').style.display = 'block';
    document.getElementById('aroPreviewLoading').innerHTML = `
        <i class="fas fa-spinner fa-spin fa-2x" style="color:#1d4ed8;"></i>
        <div style="margin-top:10px;font-size:13px;color:#64748b;">Memuat preview...</div>`;
    document.getElementById('aroPreviewContent').style.display = 'none';

    aroSetStep(1);
}

$(document).ready(function () { $('.select2').select2(); });
</script>
@endpush