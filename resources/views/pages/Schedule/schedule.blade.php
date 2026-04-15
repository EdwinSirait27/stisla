@extends('layouts.app')
@section('title', 'Schedule')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<style>
.schedule-scroll { overflow-x: auto; border-radius: 8px; }
.schedule-table  { border-collapse: collapse; width: 100%; font-size: 13px; }

.schedule-table thead th {
    background: #1e293b; color: #fff;
    font-size: 11px; font-weight: 600; text-transform: uppercase;
    padding: 10px 6px; text-align: center; white-space: nowrap;
    border: 1px solid #334155;
}
.schedule-table thead th.col-emp {
    position: sticky; left: 0; z-index: 4; background: #0f172a;
    min-width: 200px; text-align: left; padding-left: 14px;
}
.schedule-table thead th.weekend { background: #7f1d1d; }
.schedule-table thead th.today   { background: #78350f; }

.schedule-table tbody td {
    border: 1px solid #e2e8f0; padding: 5px 4px;
    vertical-align: middle; text-align: center; background: #fff;
}
.schedule-table tbody td.col-emp {
    position: sticky; left: 0; z-index: 2; background: #f8fafc;
    text-align: left; padding: 8px 14px; border-right: 2px solid #cbd5e1;
    min-width: 200px;
}
.schedule-table tbody tr:nth-child(even) td.col-emp { background: #f1f5f9; }
.schedule-table tbody tr:hover td { background: #f0f9ff !important; }

.emp-name { font-weight: 600; color: #0f172a; font-size: 13px; }
.emp-meta { font-size: 10px; color: #64748b; margin-top: 1px; }

.day-cell { cursor: pointer; min-width: 90px; }
.day-cell:hover { background: #eff6ff !important; }
.day-cell.weekend { background: #fff1f2 !important; }
.day-cell.today   { background: #fefce8 !important; outline: 2px solid #eab308; outline-offset: -2px; }

.s-badge {
    display: inline-flex; flex-direction: column; align-items: center;
    border-radius: 5px; padding: 3px 8px; min-width: 72px;
    border: 1px solid transparent;
}
.s-badge .s-name { font-weight: 700; font-size: 11px; white-space: nowrap; }
.s-badge .s-time { font-size: 10px; white-space: nowrap; opacity: .85; }
.s-badge .s-fp   { font-size: 10px; color: #059669; margin-top: 1px; }

.s-work     { background: #dbeafe; border-color: #93c5fd; }
.s-work     .s-name { color: #1d4ed8; }
.s-off      { background: #f1f5f9; border-color: #cbd5e1; }
.s-off      .s-name { color: #64748b; }
.s-holiday  { background: #fef9c3; border-color: #fde047; }
.s-holiday  .s-name { color: #854d0e; }
.s-leave    { background: #f3e8ff; border-color: #d8b4fe; }
.s-leave    .s-name { color: #7e22ce; }
.s-attended { background: #dcfce7; border-color: #86efac; }
.s-attended .s-name { color: #15803d; }
.s-late     { background: #fef9c3; border-color: #fde047; }
.s-late     .s-name { color: #854d0e; }
.s-absent   { background: #fee2e2; border-color: #fca5a5; }
.s-absent   .s-name { color: #b91c1c; }
.s-empty { color: #cbd5e1; font-size: 20px; line-height: 1; }

.filter-card { background: #fff; border-radius: 10px; box-shadow: 0 1px 8px rgba(0,0,0,.08); padding: 20px 24px; margin-bottom: 16px; }
.f-label { font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; display: block; white-space: nowrap; }
.f-control { width: 100%; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; font-size: 13px; color: #0f172a; }
.f-control:focus { outline: none; border-color: #3b82f6; }
.filter-item { min-width: 180px; }
.filter-item-date { min-width: 160px; }
.filter-item-btn { display: flex; gap: 8px; align-items: flex-end; padding-bottom: 1px; }

.btn-primary-s   { background: #1d4ed8; color: #fff; border: none; border-radius: 6px; padding: 8px 18px; font-size: 13px; font-weight: 600; cursor: pointer; }
.btn-secondary-s { background: #e2e8f0; color: #334155; border: none; border-radius: 6px; padding: 8px 14px; font-size: 13px; cursor: pointer; }
.btn-danger-s    { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; border-radius: 6px; padding: 5px 10px; font-size: 12px; cursor: pointer; }

.m-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:9999; align-items:center; justify-content:center; }
.m-overlay.open { display:flex; }
.m-box { background:#fff; border-radius:12px; width:340px; max-width:95vw; box-shadow:0 20px 60px rgba(0,0,0,.2); overflow:hidden; }
.m-box-lg { width: 440px; }
.m-head { background:#1e293b; color:#fff; padding:14px 18px; font-weight:700; font-size:15px; display:flex; justify-content:space-between; align-items:center; }
.m-head button { background:none; border:none; color:#94a3b8; font-size:20px; cursor:pointer; line-height:1; }
.m-head button:hover { color:#fff; }
.m-body { padding:18px; }
.m-foot { padding:12px 18px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:8px; }

#scheduleToast { position:fixed; bottom:24px; right:24px; z-index:99999; background:#0f172a; color:#fff; border-radius:8px; padding:12px 20px; font-size:13px; font-weight:500; display:none; max-width:320px; }

.legend { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px; }
.legend-dot { width:12px; height:12px; border-radius:3px; display:inline-block; margin-right:4px; }

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
        <h1>🗓️ Schedule</h1>
        @if($storeId)
        <div class="d-flex gap-2">
            <button class="btn-primary-s" onclick="openModal('modalBulk')">
                <i class="fas fa-calendar-plus"></i> Bulk Assign
            </button>
            <button class="btn-secondary-s" onclick="openModal('modalCopy')">
                <i class="fas fa-copy"></i> Copy Schedule
            </button>
        </div>
        @endif
    </div>

    <div class="section-body">

        {{-- ── Filter ── --}}
        <div class="filter-card">
            <form method="GET" action="{{ route('schedule.index') }}" class="d-flex flex-wrap align-items-end" style="gap: 20px;">
                <div class="filter-item">
                    <label class="f-label">
                        Store / Lokasi
                        <span style="color:#ef4444">*</span>
                        <small style="color:#94a3b8;font-weight:400">(wajib dipilih)</small>
                    </label>
                    <select name="store_id" class="f-control select2" style="min-width:180px">
                        <option value="">-- Pilih Store --</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-item-date">
                    <label class="f-label">Start Date</label>
                    <input type="date" name="start_date" class="f-control" value="{{ $startDate }}">
                </div>
                <div class="filter-item-date">
                    <label class="f-label">End Date</label>
                    <input type="date" name="end_date" class="f-control" value="{{ $endDate }}">
                </div>
                <div class="filter-item-btn">
                    <button type="submit" class="btn-primary-s"><i class="fas fa-search"></i> Filter</button>
                    <a href="{{ route('schedule.index') }}" class="btn-secondary-s">Reset</a>
                </div>
            </form>
        </div>

        {{-- ── Belum pilih store ── --}}
        @if(!$storeId)
            <div class="card" style="border:none;box-shadow:0 1px 8px rgba(0,0,0,.08);">
                <div class="card-body">
                    <div class="empty-state">
                        <i class="fas fa-store"></i>
                        <h5>Pilih Store terlebih dahulu</h5>
                        <p>Silakan pilih Store/Lokasi di filter atas lalu klik <strong>Filter</strong> untuk menampilkan data schedule.</p>
                    </div>
                </div>
            </div>
        @else

        {{-- ── Legend ── --}}
        <div class="legend">
            <span><span class="legend-dot" style="background:#dbeafe;border:1px solid #93c5fd"></span><small>Scheduled</small></span>
            <span><span class="legend-dot" style="background:#dcfce7;border:1px solid #86efac"></span><small>Attended</small></span>
            <span><span class="legend-dot" style="background:#fef9c3;border:1px solid #fde047"></span><small>Late</small></span>
            <span><span class="legend-dot" style="background:#fee2e2;border:1px solid #fca5a5"></span><small>Absent</small></span>
            <span><span class="legend-dot" style="background:#f1f5f9;border:1px solid #cbd5e1"></span><small>Off</small></span>
            <span><span class="legend-dot" style="background:#f3e8ff;border:1px solid #d8b4fe"></span><small>Leave</small></span>
            <span><span class="legend-dot" style="background:#fff1f2;border:1px solid #fecaca"></span><small>Weekend</small></span>
        </div>

        {{-- ── Grid Schedule ── --}}
        <div class="card" style="border:none;box-shadow:0 1px 8px rgba(0,0,0,.08);">
            <div class="card-body p-0">
                <div class="schedule-scroll">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th class="col-emp">Karyawan</th>
                                @foreach($dates as $carbon)
                                    @php
                                        $isWeekend = $carbon->isWeekend();
                                        $isToday   = $carbon->isToday();
                                    @endphp
                                    <th class="{{ $isWeekend ? 'weekend' : '' }} {{ $isToday ? 'today' : '' }}" style="min-width:90px">
                                        <div>{{ $carbon->format('D') }}</div>
                                        <div style="font-size:10px;opacity:.8">{{ $carbon->format('d/m') }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                                @php
                                    $schedByDate = $employee->schedules->keyBy(
                                        fn($s) => \Carbon\Carbon::parse($s->date)->toDateString()
                                    );
                                @endphp
                                <tr>
                                    <td class="col-emp">
                                        <div class="emp-name">{{ $employee->employee_name }}</div>
                                        <div class="emp-meta">
                                            {{ $employee->position->name ?? '-' }} · PIN: {{ $employee->pin ?? '-' }}
                                        </div>
                                        <div class="emp-meta">🏬 {{ $employee->store->name ?? '-' }}</div>
                                    </td>
                                    @foreach($dates as $carbon)
                                        @php
                                            $dateStr   = $carbon->toDateString();
                                            $schedule  = $schedByDate->get($dateStr);
                                            $isWeekend = $carbon->isWeekend();
                                            $isToday   = $carbon->isToday();
                                            $fp        = $schedule?->fingerprintRecap;

                                            $badgeClass = '';
                                            $badgeName  = '+';
                                            $badgeTime  = '';

                                            if ($schedule) {
                                                $status = $schedule->status;
                                                $type   = $schedule->day_type;

                                                if ($type === 'Off') {
                                                    $badgeClass = 's-badge s-off';
                                                    $badgeName  = 'Off';
                                                } elseif ($type === 'Holiday') {
                                                    $badgeClass = 's-badge s-holiday';
                                                    $badgeName  = 'Holiday';
                                                } elseif ($type === 'Leave') {
                                                    $badgeClass = 's-badge s-leave';
                                                    $badgeName  = 'Leave';
                                                } elseif ($status === 'Attended') {
                                                    $badgeClass = 's-badge s-attended';
                                                    $badgeName  = 'Attended';
                                                } elseif ($status === 'Late') {
                                                    $badgeClass = 's-badge s-late';
                                                    $badgeName  = 'Late';
                                                } elseif ($status === 'Absent') {
                                                    $badgeClass = 's-badge s-absent';
                                                    $badgeName  = 'Absent';
                                                } else {
                                                    $badgeClass = 's-badge s-work';
                                                    $badgeName  = $schedule->roster?->shift?->shift_name ?? 'Scheduled';
                                                }

                                                if ($schedule->roster?->shift) {
                                                    $badgeTime = substr($schedule->roster->shift->start_time, 0, 5)
                                                        . '-' . substr($schedule->roster->shift->end_time, 0, 5);
                                                }
                                            } elseif ($isWeekend) {
                                                $badgeClass = 's-badge s-off';
                                                $badgeName  = 'Off';
                                            }
                                        @endphp
                                        <td class="day-cell {{ $isWeekend ? 'weekend' : '' }} {{ $isToday ? 'today' : '' }}"
                                            data-emp-id="{{ $employee->id }}"
                                            data-emp-name="{{ $employee->employee_name }}"
                                            data-date="{{ $dateStr }}"
                                            data-roster-id="{{ $schedule?->roster_id ?? '' }}"
                                            data-day-type="{{ $schedule?->day_type ?? 'Work' }}"
                                            data-has-schedule="{{ $schedule ? '1' : '0' }}"
                                            onclick="openCellModal(this)"
                                            title="{{ $employee->employee_name }} – {{ $dateStr }}">
                                            @if($badgeClass === '')
                                                <span class="s-empty">+</span>
                                            @else
                                                <span class="{{ $badgeClass }}">
                                                    <span class="s-name">{{ $badgeName }}</span>
                                                    @if($badgeTime)
                                                        <span class="s-time">{{ $badgeTime }}</span>
                                                    @endif
                                                    @if($fp)
                                                        <span class="s-fp">
                                                            {{ substr($fp->time_in ?? '-', 0, 5) }} →
                                                            {{ substr($fp->time_out ?? '-', 0, 5) }}
                                                        </span>
                                                    @endif
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($dates) + 1 }}" class="text-center py-5 text-muted">
                                        Tidak ada karyawan ditemukan di store ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @endif {{-- end if $storeId --}}

    </div>
</section>
</div>

{{-- ── Modal: Set Jadwal ── --}}
<div class="m-overlay" id="modalCell">
    <div class="m-box">
        <div class="m-head">
            <span>🗓️ Set Jadwal</span>
            <button onclick="closeModal('modalCell')">×</button>
        </div>
        <div class="m-body">
            <div style="font-weight:700;color:#0f172a;margin-bottom:2px" id="mEmpName"></div>
            <div style="font-size:12px;color:#64748b;margin-bottom:14px" id="mDate"></div>
            <input type="hidden" id="mEmpId">

            <label class="f-label">Tipe Hari</label>
            <select id="mDayType" class="f-control mb-3" onchange="toggleShift()">
                <option value="Work">Work (Kerja)</option>
                <option value="Off">Off (Libur)</option>
                <option value="Holiday">Holiday</option>
                <option value="Leave">Leave (Cuti)</option>
            </select>

            <div id="shiftWrap">
                <label class="f-label">Roster / Shift</label>
                <select id="mRosterId" class="f-control mb-3">
                    <option value="">-- Pilih Shift --</option>
                    @foreach($rosters as $roster)
                        @if($roster->shift)
                        <option value="{{ $roster->id }}">
                            {{ $roster->shift->shift_name }}
                            ({{ substr($roster->shift->start_time, 0, 5) }} - {{ substr($roster->shift->end_time, 0, 5) }})
                        </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <label class="f-label">Catatan (opsional)</label>
            <input type="text" id="mNotes" class="f-control" placeholder="Tambah catatan...">
        </div>
        <div class="m-foot">
            <button class="btn-danger-s" id="mDeleteBtn" style="display:none" onclick="deleteSchedule()">
                <i class="fas fa-trash"></i> Hapus
            </button>
            <button class="btn-secondary-s" onclick="closeModal('modalCell')">Batal</button>
            <button class="btn-primary-s" onclick="saveSchedule()">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>
    </div>
</div>

{{-- ── Modal: Bulk Assign ── --}}
<div class="m-overlay" id="modalBulk">
    <div class="m-box m-box-lg">
        <div class="m-head">
            <span>📋 Bulk Assign Jadwal</span>
            <button onclick="closeModal('modalBulk')">×</button>
        </div>
        <div class="m-body">
            <label class="f-label">Pilih Karyawan</label>
            <select id="bulkEmps" class="f-control mb-1" multiple style="height:100px">
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">
                        {{ $emp->employee_name }} – {{ $emp->store->name ?? '' }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted d-block mb-3">Tahan <kbd>Ctrl</kbd> untuk pilih lebih dari satu</small>

            <div class="d-flex gap-2 mb-3">
                <div style="flex:1">
                    <label class="f-label">Start Date</label>
                    <input type="date" id="bulkStart" class="f-control" value="{{ $startDate }}">
                </div>
                <div style="flex:1">
                    <label class="f-label">End Date</label>
                    <input type="date" id="bulkEnd" class="f-control" value="{{ $endDate }}">
                </div>
            </div>

            <label class="f-label">Tipe Hari</label>
            <select id="bulkDayType" class="f-control mb-3" onchange="toggleBulkShift()">
                <option value="Work">Work</option>
                <option value="Off">Off</option>
                <option value="Holiday">Holiday</option>
                <option value="Leave">Leave</option>
            </select>

            <div id="bulkShiftWrap">
                <label class="f-label">Roster / Shift</label>
                <select id="bulkRosterId" class="f-control mb-3">
                    <option value="">-- Pilih Shift --</option>
                    @foreach($rosters as $roster)
                        @if($roster->shift)
                        <option value="{{ $roster->id }}">
                            {{ $roster->shift->shift_name }}
                            ({{ substr($roster->shift->start_time, 0, 5) }}-{{ substr($roster->shift->end_time, 0, 5) }})
                        </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="d-flex align-items-center gap-2">
                <input type="checkbox" id="bulkSkipWeekend" checked>
                <label for="bulkSkipWeekend" class="f-label mb-0">Skip Sabtu & Minggu</label>
            </div>
        </div>
        <div class="m-foot">
            <button class="btn-secondary-s" onclick="closeModal('modalBulk')">Batal</button>
            <button class="btn-primary-s" onclick="saveBulk()">
                <i class="fas fa-calendar-check"></i> Assign
            </button>
        </div>
    </div>
</div>

{{-- ── Modal: Copy Schedule ── --}}
<div class="m-overlay" id="modalCopy">
    <div class="m-box">
        <div class="m-head">
            <span>📋 Copy Schedule</span>
            <button onclick="closeModal('modalCopy')">×</button>
        </div>
        <div class="m-body">
            <p style="font-size:12px;color:#64748b;margin-bottom:14px">
                Copy jadwal dari periode sumber ke periode target.
            </p>
            <label class="f-label">Sumber: Start Date</label>
            <input type="date" id="copySourceStart" class="f-control mb-2" value="{{ $startDate }}">
            <label class="f-label">Sumber: End Date</label>
            <input type="date" id="copySourceEnd" class="f-control mb-3" value="{{ $endDate }}">
            <label class="f-label">Target: Start Date</label>
            <input type="date" id="copyTargetStart" class="f-control">
        </div>
        <div class="m-foot">
            <button class="btn-secondary-s" onclick="closeModal('modalCopy')">Batal</button>
            <button class="btn-primary-s" onclick="saveCopy()">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
</div>

<div id="scheduleToast"></div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function toast(msg, ok = true) {
    const el = document.getElementById('scheduleToast');
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

function openCellModal(cell) {
    document.getElementById('mEmpId').value          = cell.dataset.empId;
    document.getElementById('mEmpName').textContent  = cell.dataset.empName;
    document.getElementById('mDate').textContent     = cell.dataset.date;
    document.getElementById('mDayType').value        = cell.dataset.dayType || 'Work';
    document.getElementById('mRosterId').value       = cell.dataset.rosterId || '';
    document.getElementById('mNotes').value          = '';
    document.getElementById('mDeleteBtn').style.display =
        cell.dataset.hasSchedule === '1' ? 'block' : 'none';
    toggleShift();
    openModal('modalCell');
}

function saveSchedule() {
    fetch('{{ route('schedule.store') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            employee_id: document.getElementById('mEmpId').value,
            roster_id:   document.getElementById('mRosterId').value || null,
            date:        document.getElementById('mDate').textContent,
            day_type:    document.getElementById('mDayType').value,
            notes:       document.getElementById('mNotes').value,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            toast('✅ Jadwal disimpan!');
            closeModal('modalCell');
            setTimeout(() => location.reload(), 700);
        } else {
            toast('❌ Gagal menyimpan.', false);
        }
    });
}

function deleteSchedule() {
    if (!confirm('Hapus jadwal ini?')) return;
    fetch('{{ route('schedule.destroy') }}', {
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
            toast('🗑️ Jadwal dihapus!');
            closeModal('modalCell');
            setTimeout(() => location.reload(), 700);
        }
    });
}

function saveBulk() {
    const selected = [...document.getElementById('bulkEmps').selectedOptions].map(o => o.value);
    if (!selected.length) { toast('⚠️ Pilih minimal 1 karyawan.', false); return; }

    fetch('{{ route('schedule.bulkAssign') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            employee_ids:  selected,
            roster_id:     document.getElementById('bulkRosterId').value || null,
            start_date:    document.getElementById('bulkStart').value,
            end_date:      document.getElementById('bulkEnd').value,
            day_type:      document.getElementById('bulkDayType').value,
            skip_weekend:  document.getElementById('bulkSkipWeekend').checked,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            toast('✅ ' + data.message);
            closeModal('modalBulk');
            setTimeout(() => location.reload(), 700);
        } else {
            toast('❌ Gagal bulk assign.', false);
        }
    });
}

function saveCopy() {
    fetch('{{ route('schedule.copySchedule') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            source_start:  document.getElementById('copySourceStart').value,
            source_end:    document.getElementById('copySourceEnd').value,
            target_start:  document.getElementById('copyTargetStart').value,
            store_id:      '{{ $storeId ?? '' }}',
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            toast('✅ ' + data.message);
            closeModal('modalCopy');
            setTimeout(() => location.reload(), 700);
        } else {
            toast('❌ Gagal copy schedule.', false);
        }
    });
}

$(document).ready(function () { $('.select2').select2(); });
</script>
@endpush