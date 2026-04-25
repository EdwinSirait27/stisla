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
.r-work    { background: #dbeafe; border-color: #93c5fd; }
.r-work    .r-name { color: #1d4ed8; }
.r-work    .r-time { color: #3b82f6; }
.r-off     { background: #f1f5f9; border-color: #cbd5e1; }
.r-off     .r-name { color: #64748b; }
.r-holiday { background: #fef9c3; border-color: #fde047; }
.r-holiday .r-name { color: #854d0e; }
.r-leave   { background: #f3e8ff; border-color: #d8b4fe; }
.r-leave   .r-name { color: #7e22ce; }
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
.btn-secondary-r { background: #e2e8f0; color: #334155; border: none; border-radius: 6px; padding: 8px 14px; font-size: 13px; cursor: pointer; }
.btn-danger-r    { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; border-radius: 6px; padding: 5px 10px; font-size: 12px; cursor: pointer; }

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
.empty-state { text-align:center; padding: 60px 20px; color: #94a3b8; }
.empty-state i { font-size: 48px; margin-bottom: 16px; display: block; }
.empty-state h5 { font-size: 16px; font-weight: 600; color: #64748b; margin-bottom: 8px; }
.empty-state p { font-size: 13px; color: #94a3b8; }
</style>
@endpush

@section('main')
<div class="main-content">
<section class="section">

    <div class="section-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h1>Roster & Schedule</h1>
        @if($storeId)
        <div class="d-flex" style="gap:16px">
            <button class="btn-primary-r" onclick="openModal('modalBulk')" style="padding:8px 24px">
                <i class="fas fa-calendar-plus"></i> Bulk Assign
            </button>
            {{-- ↓ PERUBAHAN: onclick ke confirmCopyRoster(), bukan openModal --}}
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
                {{-- <div class="filter-item-date">
                    <label class="f-label">Start Date</label>
                    <input type="date" name="start_date" class="f-control" value="{{ $startDate }}">
                </div> --}}
                <div class="filter-item-date">
    <label class="f-label">Start Date</label>
    <input type="text" id="start_date" name="start_date"
        class="f-control"
        value="{{ $startDate }}">
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

        {{-- ── Legend Checkbox Filter (radio behavior, tanpa kotak warna) ── --}}
        <div class="legend">

            <label class="legend-item">
                <input type="checkbox" class="legend-filter" data-type="work" checked>
                Shift Kerja
            </label>
            <label class="legend-item">
                <input type="checkbox" class="legend-filter" data-type="off">
                Off
            </label>
            <label class="legend-item">
                <input type="checkbox" class="legend-filter" data-type="holiday">
                Public Holiday
            </label>
            <label class="legend-item">
                <input type="checkbox" class="legend-filter" data-type="leave">
                Leave
            </label>
            <label class="legend-item">
                <input type="checkbox" class="legend-filter" data-type="melahirkan">
                Cuti Melahirkan
            </label>
            <label class="legend-item">
                <input type="checkbox" class="legend-filter" data-type="weekend">
                Weekend
            </label>
            <label class="legend-item">
                <input type="checkbox" class="legend-filter" data-type="today">
                Hari Ini
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
                                @endphp
                                <tr>
                                    <td class="col-emp">
                                        <div class="emp-name">Employee Name : {{ $employee->employee_name }}</div>
                                        <div class="emp-meta">Department : {{ $employee->department->department_name ?? '-' }}</div>
                                        <div class="emp-meta">Position : {{ $employee->position->name ?? '-' }}</div>
                                        <div class="emp-meta">Location {{ $employee->store->name ?? '-' }}</div>
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
                                                    $badgeClass = 'r-badge r-off'; $badgeName = 'Off';
                                                    $cellType = 'off';
                                                } elseif ($roster->day_type === 'Public Holiday') {
                                                    $badgeClass = 'r-badge r-holiday'; $badgeName = 'Public Holiday';
                                                    $cellType = 'holiday';
                                                } elseif ($roster->day_type === 'Cuti Melahirkan') {
                                                    $badgeClass = 'r-badge r-leave'; $badgeName = 'Cuti Melahirkan';
                                                    $cellType = 'melahirkan';
                                                } elseif ($roster->day_type === 'Leave') {
                                                    $badgeClass = 'r-badge r-leave'; $badgeName = 'Leave';
                                                    $cellType = 'leave';
                                                } elseif ($roster->shift) {
                                                    $badgeClass = 'r-badge r-work';
                                                    $badgeName  = $roster->shift->shift_name;
                                                    $badgeTime  = substr($roster->shift->start_time,0,5).'-'.substr($roster->shift->end_time,0,5);
                                                    $cellType = 'work';
                                                } else {
                                                    $badgeClass = 'r-badge r-work'; $badgeName = 'Work';
                                                    $cellType = 'work';
                                                }
                                            } elseif ($isWeekend) {
                                                $badgeClass = 'r-badge r-off'; $badgeName = 'Off';
                                                $cellType = 'weekend';
                                            }
                                        @endphp
                                        <td class="day-cell {{ $isWeekend ? 'weekend' : '' }} {{ $isToday ? 'today' : '' }}"
                                            data-emp-id="{{ $employee->id }}"
                                            data-emp-name="{{ $employee->employee_name }}"
                                            data-date="{{ $dateStr }}"
                                            data-shift-id="{{ $roster?->shift_id ?? '' }}"
                                            data-day-type="{{ $roster?->day_type ?? 'Work' }}"
                                            data-has-roster="{{ $roster ? '1' : '0' }}"
                                            data-cell-type="{{ $cellType }}"
                                            data-is-today="{{ $isToday ? '1' : '0' }}"
                                            onclick="openCellModal(this)"
                                            title="{{ $employee->employee_name }} – {{ $dateStr }}">
                                            @if($badgeClass === '')
                                                <span class="r-empty">+</span>
                                            @else
                                                <span class="{{ $badgeClass }}">
                                                    <span class="r-name">{{ $badgeName }}</span>
                                                    @if($badgeTime)<span class="r-time">{{ $badgeTime }}</span>@endif
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

{{-- ── Modal: Set Jadwal ── --}}
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
                <option value="Public Holiday">Public Holiday</option>
                <option value="Leave">Leave (Cuti)</option>
                <option value="Cuti Melahirkan">Cuti Melahirkan</option>
            </select>

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
            <button class="btn-primary-r" onclick="saveRoster()">
                <i class="fas fa-save"></i> Save
            </button>
        </div>
    </div>
</div>

{{-- ── Modal: Bulk Assign ── --}}
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
                    <option value="{{ $emp->id }}">{{ $emp->employee_name }} – {{ $emp->store->name ?? '' }}</option>
                @endforeach
            </select>
            <small class="text-muted d-block mb-3">Hold the button <kbd>Ctrl</kbd> to choose more than 1 employee</small>

            <div class="d-flex gap-2 mb-3">
                <div style="flex:1"><label class="f-label">Start Date</label><input type="date" id="bulkStart" class="f-control" value="{{ $startDate }}"></div>
                <div style="flex:1"><label class="f-label">End Date</label><input type="date" id="bulkEnd" class="f-control" value="{{ $endDate }}"></div>
            </div>

            <label class="f-label">Day Type</label>
            <select id="bulkDayType" class="f-control mb-3" onchange="toggleBulkShift()">
                <option value="Work">Work</option>
                <option value="Off">Off</option>
                <option value="Public Holiday">Public Holiday</option>
                <option value="Leave">Leave</option>
                <option value="Cuti Melahirkan">Cuti Melahirkan / Maternity leave</option>
            </select>

            <div id="bulkShiftWrap">
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

{{-- ── Modal: Copy Roster ── --}}
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

{{-- ── Modal: Bulk Delete ── --}}
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
            <button class="btn-danger-r" onclick="saveBulkDelete()">
                <i class="fas fa-trash"></i> Hapus
            </button>
        </div>
    </div>
</div>

<div id="rosterToast"></div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
{{-- <script>
    flatpickr("#start_date", {
        dateFormat: "Y-m-d",   // format ke backend Laravel
        altInput: true,
        altFormat: "d F Y",    // tampilan: 23 April 2026
        allowInput: true
    });
</script> --}}
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
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function toast(msg, ok = true) {
    const el = document.getElementById('rosterToast');
    el.textContent = msg;
    el.style.background = ok ? '#0f172a' : '#991b1b';
    el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 3500);
}

function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.m-overlay').forEach(el => {
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
});

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

// ── Legend Checkbox Filter (radio behavior) ──
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
        }

        cell.style.visibility    = show ? 'visible' : 'hidden';
        cell.style.pointerEvents = show ? 'auto' : 'none';
    });
}

// ── Klik cell ──
function openCellModal(cell) {
    document.getElementById('mEmpId').value          = cell.dataset.empId;
    document.getElementById('mEmpName').textContent  = cell.dataset.empName;
    document.getElementById('mDate').textContent     = cell.dataset.date;
    document.getElementById('mDayType').value        = cell.dataset.dayType || 'Work';
    document.getElementById('mShiftId').value        = cell.dataset.shiftId || '';
    document.getElementById('mNotes').value          = '';
    document.getElementById('mDeleteBtn').style.display = cell.dataset.hasRoster === '1' ? 'block' : 'none';
    toggleShift();
    openModal('modalCell');
}

// ── Simpan roster ──
function saveRoster() {
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
            toast(' Schedule saved!');
            closeModal('modalCell');
            setTimeout(() => location.reload(), 700);
        } else {
            toast('❌ Failed to save data.', false);
        }
    });
}

// ── Hapus roster ──
function deleteRoster() {
    if (!confirm('Delete this schedule?')) return;
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
            toast('Schedule deleted!');
            closeModal('modalCell');
            setTimeout(() => location.reload(), 700);
        }
    });
}

// ── Bulk assign ──
function saveBulk() {
    const selected = [...document.getElementById('bulkEmps').selectedOptions].map(o => o.value);
    if (!selected.length) { toast(' Choose at least 1 employee.', false); return; }

    const saturdayShiftChecked = document.getElementById('bulkSaturdayShift').checked;
    const saturdayShiftId      = document.getElementById('bulkSaturdayShiftId').value;

    if (saturdayShiftChecked && !saturdayShiftId) {
        toast('⚠️ Pilih shift untuk hari Sabtu.', false);
        return;
    }

    const btn = document.querySelector('#modalBulk .btn-primary-r');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

    fetch('{{ route('roster.bulkAssign') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            employee_ids:       selected,
            shift_id:           document.getElementById('bulkShift').value || null,
            start_date:         document.getElementById('bulkStart').value,
            end_date:           document.getElementById('bulkEnd').value,
            day_type:           document.getElementById('bulkDayType').value,
            skip_weekend:       document.getElementById('bulkSkipWeekend').checked,
            saturday_shift:     saturdayShiftChecked,
            saturday_shift_id:  saturdayShiftId || null,
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
            toast('❌ Gagal memproses.', false);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-calendar-check"></i> Assign';
        }
    })
    .catch(() => {
        toast('❌ Terjadi kesalahan.', false);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-calendar-check"></i> Assign';
    });
}

// ── STEP 1: Klik tombol Copy Roster → tampilkan alert dulu ──
function confirmCopyRoster() {
    Swal.fire({
        icon: 'warning',
        iconColor: '#f59e0b',
        title: '⚠️ Perhatian!',
        html: `
            <div style="text-align:left;padding:8px 0;line-height:1.7;font-size:14px;color:#374151">
                Mohon Dicek Kembali Setelah Mengcopy Roster Karena Mengikuti Roster Pada Bulan Ini, Agar Karyawan Yang Mengambil <strong>Cuti</strong>, <strong>Libur</strong>, Dan lain-lain Tidak Terinput Kembali Di Roster Yang Akan Di Gunakan Pada Bulan Berikutnya.
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#1d4ed8',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-copy"></i> Mengerti, Lanjutkan',
        cancelButtonText: 'Batal',
        focusCancel: true
    }).then(result => {
        if (result.isConfirmed) {
            // STEP 2: Baru buka modal setelah user klik "Mengerti"
            openModal('modalCopy');
        }
    });
}

// ── STEP 3: User klik Copy di dalam modal → proses copy ──
function saveCopy() {
    const sourceStart = document.getElementById('copySourceStart').value;
    const sourceEnd   = document.getElementById('copySourceEnd').value;
    const targetStart = document.getElementById('copyTargetStart').value;
    const targetEnd   = document.getElementById('copyTargetEnd').value;

    if (!sourceStart || !sourceEnd || !targetStart || !targetEnd) {
        toast('⚠️ Semua tanggal wajib diisi.', false);
        return;
    }
    if (sourceEnd < sourceStart) {
        toast('⚠️ Sumber End Date tidak boleh sebelum Sumber Start Date.', false);
        return;
    }
    if (targetEnd < targetStart) {
        toast('⚠️ Target End Date tidak boleh sebelum Target Start Date.', false);
        return;
    }

    const btn = document.querySelector('#modalCopy .btn-primary-r');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

    fetch('{{ route('roster.copyRoster') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            source_start: sourceStart,
            source_end:   sourceEnd,
            target_start: targetStart,
            target_end:   targetEnd,
            store_id:     '{{ $storeId ?? '' }}',
        }),
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
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-copy"></i> Copy';
        }
    })
    .catch(() => {
        toast('❌ Terjadi kesalahan.', false);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-copy"></i> Copy';
    });
}

// ── Bulk delete ──
function saveBulkDelete() {
    const selected = [...document.getElementById('deleteEmps').selectedOptions].map(o => o.value);
    if (!selected.length) { toast('⚠️ Pilih minimal 1 karyawan.', false); return; }
    if (!confirm('Yakin ingin menghapus semua jadwal yang dipilih?')) return;

    const btn = document.querySelector('#modalBulkDelete .btn-danger-r');
    btn.disabled = true;
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
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash"></i> Hapus';
        }
    })
    .catch(() => {
        toast('❌ Terjadi kesalahan.', false);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash"></i> Hapus';
    });
}

$(document).ready(function () { $('.select2').select2(); });
</script>
@endpush