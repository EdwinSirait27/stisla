<div class="calendar-month">
    <a href="javascript:void(0)" onclick="loadCalendar({{ $prevMonth['month'] }}, {{ $prevMonth['year'] }})">
        <i class="fas fa-chevron-left" style="cursor: pointer;"></i>
    </a>
    <span class="mx-4">{{ $calendarLabel }}</span>
    <a href="javascript:void(0)" onclick="loadCalendar({{ $nextMonth['month'] }}, {{ $nextMonth['year'] }})">
        <i class="fas fa-chevron-right" style="cursor: pointer;"></i>
    </a>
</div>

<div class="calendar-grid">
    <div class="calendar-day-header">Sun</div>
    <div class="calendar-day-header">Mon</div>
    <div class="calendar-day-header">Tue</div>
    <div class="calendar-day-header">Wed</div>
    <div class="calendar-day-header">Thu</div>
    <div class="calendar-day-header">Fri</div>
    <div class="calendar-day-header">Sat</div>

    @foreach ($calendarDays as $cell)
        @if ($cell['empty'])
            <div class="calendar-day empty"></div>
        @else
            <div class="calendar-day {{ $cell['cssClass'] }} {{ $cell['isToday'] ? 'today' : '' }}">
                <span class="calendar-day-number">{{ $cell['day'] }}</span>
                @if (!empty($cell['label']))
                    <span class="calendar-day-label">{{ $cell['label'] }}</span>
                @endif
                @if (!empty($cell['remark']))
                    <span class="calendar-day-remark">{{ $cell['remark'] }}</span>
                @endif
            </div>
        @endif
    @endforeach
</div>