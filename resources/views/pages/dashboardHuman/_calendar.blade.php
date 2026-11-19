{{-- Kalender jadwal karyawan (dipakai dashboardHuman, dimuat ulang via AJAX saat ganti bulan) --}}
@php
    $typeClass = fn($t) => match (true) {
        $t === null => '',
        str_contains(strtolower($t), 'work') => 'is-work',
        str_contains(strtolower($t), 'holiday') => 'is-holiday',
        str_contains(strtolower($t), 'sick') => 'is-sick',
        str_contains(strtolower($t), 'leave'), str_contains(strtolower($t), 'melahirkan') => 'is-leave',
        str_contains(strtolower($t), 'off') => 'is-off',
        default => '',
    };
@endphp
<div class="pk-cal-head">
    <button type="button" class="pr-btn pr-btn-sm pr-btn-ghost" onclick="loadCalendar({{ $prevMonth['month'] }}, {{ $prevMonth['year'] }})" aria-label="Bulan sebelumnya">
        <i class="fas fa-chevron-left" aria-hidden="true"></i>
    </button>
    <span class="pk-cal-title">{{ $calendarLabel }}</span>
    <button type="button" class="pr-btn pr-btn-sm pr-btn-ghost" onclick="loadCalendar({{ $nextMonth['month'] }}, {{ $nextMonth['year'] }})" aria-label="Bulan berikutnya">
        <i class="fas fa-chevron-right" aria-hidden="true"></i>
    </button>
</div>

<div class="pk-cal" role="grid" aria-label="Jadwal {{ $calendarLabel }}">
    @foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $dow)
        <div class="pk-cal-dow" role="columnheader">{{ $dow }}</div>
    @endforeach
    @foreach ($calendarDays as $cell)
        @if ($cell['empty'])
            <div class="pk-cal-day is-empty" aria-hidden="true"></div>
        @else
            @php $missing = ($cell['dayType'] ?? null) === 'Work' && $cell['isPast'] && !$cell['scanned']; @endphp
            <div class="pk-cal-day {{ $typeClass($cell['dayType'] ?? null) }} {{ $cell['isToday'] ? 'is-today' : '' }} {{ $missing ? 'is-missing' : '' }}"
                role="gridcell" title="{{ $cell['dateStr'] }}: {{ $cell['tooltip'] }}{{ $cell['scanned'] ? ', ada scan fingerprint' : '' }}">
                <span class="pk-cal-num">{{ $cell['day'] }}</span>
                @if ($cell['label'])
                    <span class="pk-cal-label">{{ $cell['label'] }}</span>
                @endif
                @if ($cell['shift'])
                    <span class="pk-cal-shift">{{ $cell['shift'] }}</span>
                @endif
                @if ($cell['remark'])
                    <span class="pk-cal-shift">{{ $cell['remark'] }}</span>
                @endif
                @if ($cell['scanned'])
                    <span class="pk-cal-scan"><i class="fas fa-check" aria-hidden="true"></i> scan</span>
                @elseif ($missing)
                    <span class="pk-cal-scan is-missing">belum ada scan</span>
                @endif
            </div>
        @endif
    @endforeach
</div>

<ul class="pk-cal-legend">
    <li><span class="pk-swatch is-work"></span>Kerja</li>
    <li><span class="pk-swatch is-off"></span>Off</li>
    <li><span class="pk-swatch is-holiday"></span>Libur nasional</li>
    <li><span class="pk-swatch is-leave"></span>Cuti</li>
    <li><span class="pk-swatch is-sick"></span>Sakit</li>
    <li><span class="pk-swatch is-missing"></span>Jadwal kerja tanpa scan</li>
</ul>
