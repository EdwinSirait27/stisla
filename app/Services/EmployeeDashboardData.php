<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Employee;
use App\Models\Fingerprintrecaparchive;
use App\Models\Fingerprints;
use App\Models\Leavebalance;
use App\Models\Leaverequest;
use App\Models\Roster;
use App\Models\Toilbalances;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Data dashboard pribadi karyawan (jadwal, saldo cuti, TOIL, kehadiran, kalender, pengajuan).
 * Dipakai bersama oleh dashboard karyawan (Human) dan dashboard supervisor supaya angkanya sama.
 */
class EmployeeDashboardData
{
    public function build(Request $request): array
    {

        // ─── Saldo cuti milik user yang login ────────────────────
        $leaveBalances = Leavebalance::with('leaves')
            ->where('employee_id', Auth::user()->employee_id)
            ->where('year', date('Y'))
            ->where('balance_days', '>', 0)
            ->get();

        // ─── Annual Leave untuk card Leave Balance ───────────────
        $annualLeave = Leavebalance::with('leaves')
            ->where('employee_id', Auth::user()->employee_id)
            ->where('year', date('Y'))
            ->whereHas('leaves', fn($q) => $q->where('name', 'Annual Leave'))
            ->first();

        // cek apakah karyawan belum genap 1 tahun
        $employee = Auth::user()->employee;
        $isNewbie = $employee && $employee->join_date
            ? Carbon::parse($employee->join_date)->diffInYears(now()) < 1
            : false;

        $announcements = Announcement::with('user')
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', Carbon::today());
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // ─── Submissions (riwayat pengajuan) milik employee ──────
        $submissionsRaw = Leaverequest::with('leavebalance.leaves')
            ->whereHas('leavebalance', fn($q) =>
            $q->where('employee_id', Auth::user()->employee_id))
            ->latest()
            ->get();


        $displayBalance = $annualLeave
            ? (float) $annualLeave->balance_days
            : 0;

        $hasPending = $submissionsRaw->where('status', 'Pending')->isNotEmpty();

        /// bentuk data siap-tampil (semua logika di controller, view tinggal loop)
        $submissions = $submissionsRaw->map(function ($sub) {
            $statusLower = strtolower($sub->status);
            $statusClass = match (true) {
                str_contains($statusLower, 'pending')  => 'pending',
                str_contains($statusLower, 'approved') => 'approved',
                str_contains($statusLower, 'rejected') => 'rejected',
                default                                 => 'pending',
            };
            $statusIcon = match ($statusClass) {
                'approved' => 'fa-check-circle',
                'rejected' => 'fa-times-circle',
                default    => 'fa-clock',
            };

            $start = Carbon::parse($sub->start_date);
            $end   = Carbon::parse($sub->end_date);

            $dateLabel = $start->isSameDay($end)
                ? $start->locale('id')->translatedFormat('d M Y')
                : $start->locale('id')->translatedFormat('d M Y') . ' sampai ' . $end->locale('id')->translatedFormat('d M Y');

            $isRejected = $statusClass === 'rejected';

            return [
                'leave_name'  => $sub->leavebalance->leaves->name ?? 'Leave',
                'status'      => $sub->status,
                'statusClass' => $statusClass,
                'statusIcon'  => $statusIcon,
                'dateLabel'   => $dateLabel,
                'totalDays'   => $sub->total_days,
                'ago'         => Carbon::parse($sub->created_at)->locale('id')->diffForHumans(),
                'isRejected'  => $isRejected,
                'reason'      => $isRejected ? $sub->approver_reason : $sub->employee_reason,
                'employeeReason' => $sub->employee_reason,
                'approverReason' => $sub->approver_reason,
            ];
        });

        // ─── Roster karyawan login untuk kalender ────────────────
        $employeeId = Auth::user()->employee_id;

        $month  = (int) $request->query('month', now()->month);
        $year   = (int) $request->query('year', now()->year);
        $cursor = Carbon::createFromDate($year, $month, 1);

        $rosters = Roster::with('shift')
            ->where('employee_id', $employeeId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(fn($r) => Carbon::parse($r->date)->day);

        $today     = Carbon::today();
        $daysInMon = $cursor->daysInMonth;

        // Hari yang ada scan fingerprint di bulan kalender (untuk penanda "tercatat hadir")
        $pin = $employee->pin ?? null;
        $scanDays = $pin
            ? Fingerprints::where('pin', $pin)
                ->whereBetween('scan_date', [$cursor->copy()->startOfMonth(), $cursor->copy()->endOfMonth()->endOfDay()])
                ->selectRaw('DAY(scan_date) as d')->distinct()->pluck('d')->map(fn($d) => (int) $d)->all()
            : [];
        $leadBlank = $cursor->copy()->startOfMonth()->dayOfWeek; // 0=Sun..6=Sat

        $calendarDays = [];

        // kotak kosong sebelum tanggal 1
        for ($i = 0; $i < $leadBlank; $i++) {
            $calendarDays[] = ['empty' => true];
        }

        // isi tanggal
        for ($d = 1; $d <= $daysInMon; $d++) {
            $dateObj = $cursor->copy()->day($d);
            $dow     = $dateObj->dayOfWeek;
            $roster  = $rosters[$d] ?? null;

            if ($roster) {
                $type = strtolower($roster->day_type);
                $cssClass = match (true) {
                    str_contains($type, 'work')           => 'present',
                    str_contains($type, 'off')            => 'weekend',
                    str_contains($type, 'holiday')        => 'leave',
                    str_contains($type, 'leave')          => 'absent',
                    str_contains($type, 'melahirkan')     => 'absent',
                    default                               => '',
                };
                $label = $roster->day_type;
                $remark  = (str_contains($type, 'holiday') || str_contains($type, 'toil')) ? ($roster->notes ?? '') : '';
                $tooltip = $roster->day_type
                    . ($roster->shift ? ' • ' . $roster->shift->shift_name : '')
                    . ($roster->notes ? ' • ' . $roster->notes : '');
            } else {
                $cssClass = in_array($dow, [0, 6]) ? 'weekend' : '';
                $label    = '';
                $remark   = '';
                $tooltip  = 'No roster';
            }

            $calendarDays[] = [
                'empty'    => false,
                'day'      => $d,
                'cssClass' => $cssClass,
                'label'    => $label,
                'remark'   => $remark,
                'isToday'  => $dateObj->isSameDay($today),
                'tooltip'  => $tooltip,
                'dateStr'  => $dateObj->toDateString(),
                'dayType'  => $roster->day_type ?? null,
                'shift'    => $roster && $roster->shift ? substr($roster->shift->start_time, 0, 5) . '–' . substr($roster->shift->end_time, 0, 5) : null,
                'scanned'  => in_array($d, $scanDays, true),
                'isPast'   => $dateObj->lt($today),
            ];
        }

        $prev = $cursor->copy()->subMonth();
        $next = $cursor->copy()->addMonth();

        $calendarLabel = $cursor->locale('id')->translatedFormat('F Y');
        $prevMonth     = ['month' => $prev->month, 'year' => $prev->year];
        $nextMonth     = ['month' => $next->month, 'year' => $next->year];

        // ─── Periode payroll berjalan (26 s/d 25) ─────────────────
        $periodStart = $today->day >= 26 ? $today->copy()->day(26) : $today->copy()->subMonthNoOverflow()->day(26);
        $periodEnd   = $periodStart->copy()->addMonthNoOverflow()->day(25);
        $rosterPeriod = Roster::where('employee_id', $employeeId)
            ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->get(['date', 'day_type']);
        $countType = fn(array $types, $until = null) => $rosterPeriod
            ->filter(fn($r) => in_array($r->day_type, $types, true) && (!$until || Carbon::parse($r->date)->lte($until)))
            ->count();

        $periodSummary = [
            'label'          => $periodEnd->locale('id')->translatedFormat('F Y'),
            'start'          => $periodStart,
            'end'            => $periodEnd,
            'has_roster'     => $rosterPeriod->isNotEmpty(),
            'work_total'     => $countType(['Work']),
            'work_so_far'    => $countType(['Work'], $today),
            'present_days'   => $pin
                ? (int) Fingerprints::where('pin', $pin)
                    ->whereBetween('scan_date', [$periodStart->copy()->startOfDay(), $periodEnd->copy()->endOfDay()])
                    ->selectRaw('COUNT(DISTINCT DATE(scan_date)) as c')->value('c')
                : 0,
            'leave'          => $countType(['Leave', 'Cuti Melahirkan']),
            'sick'           => $countType(['Sick']),
            'off'            => $countType(['Off', 'TOIL Off']),
            'holiday'        => $countType(['Public Holiday']),
            'last_scan'      => $pin ? Fingerprints::where('pin', $pin)->max('scan_date') : null,
        ];

        // Rekap resmi terakhir (dipakai payroll)
        $latestRecap = Fingerprintrecaparchive::where('employee_id', $employeeId)
            ->latest('period_end')->first(['period_start', 'period_end', 'total_hari_kerja', 'total_hari_telat']);

        // Jadwal hari ini
        $todayRoster = Roster::with('shift')->where('employee_id', $employeeId)->whereDate('date', $today)->first();
        $scannedToday = $pin ? Fingerprints::where('pin', $pin)->whereDate('scan_date', $today)->orderBy('scan_date')->value('scan_date') : null;

        // Saldo TOIL aktif
        $toil = Toilbalances::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->where('remaining_hours', '>', 0)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', $today))
            ->orderBy('expires_at')
            ->get(['remaining_hours', 'expires_at']);
        $toilSummary = [
            'hours'       => (float) $toil->sum('remaining_hours'),
            'next_expiry' => $toil->first()?->expires_at,
            'next_hours'  => (float) ($toil->first()?->remaining_hours ?? 0),
        ];

        return compact(
            'periodSummary',
            'latestRecap',
            'todayRoster',
            'scannedToday',
            'toilSummary',
            'leaveBalances',
            'annualLeave',
            'isNewbie',
            'announcements',
            'submissions',
            'displayBalance',
            'hasPending',
            'calendarDays',
            'calendarLabel',
            'prevMonth',
            'nextMonth',
        );
    }
}
