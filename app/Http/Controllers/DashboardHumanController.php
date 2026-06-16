<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Leavebalance;
use App\Models\Roster;
use App\Models\Leaverequest;
use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardHumanController extends Controller
{
    public function index(Request $request)
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
            ->whereHas('leaves', fn ($q) => $q->where('name', 'Annual Leave'))
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
            ->whereHas('leavebalance', fn ($q) =>
                $q->where('employee_id', Auth::user()->employee_id))
            ->latest()
            ->get();

        $pendingDays = $submissionsRaw->where('status', 'Pending')->sum('total_days');

        $displayBalance = $annualLeave
            ? max(0, (float) $annualLeave->balance_days - (float) $pendingDays)
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
                ? $start->format('M d, Y')
                : $start->format('M d, Y') . ' - ' . $end->format('M d, Y');

            $isRejected = $statusClass === 'rejected';

            return [
                'leave_name'  => $sub->leavebalance->leaves->name ?? 'Leave',
                'status'      => $sub->status,
                'statusClass' => $statusClass,
                'statusIcon'  => $statusIcon,
                'dateLabel'   => $dateLabel,
                'totalDays'   => $sub->total_days,
                'ago'         => Carbon::parse($sub->created_at)->diffForHumans(),
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
            ->keyBy(fn ($r) => Carbon::parse($r->date)->day);

        $today     = Carbon::today();
        $daysInMon = $cursor->daysInMonth;
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
            ];
        }

        $prev = $cursor->copy()->subMonth();
        $next = $cursor->copy()->addMonth();

        $calendarLabel = $cursor->translatedFormat('F Y');
        $prevMonth     = ['month' => $prev->month, 'year' => $prev->year];
        $nextMonth     = ['month' => $next->month, 'year' => $next->year];

        $viewData = compact(
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

        if ($request->ajax()) {
            return view('pages.Dashboard.calendar', $viewData);
        }

        return view('pages.dashboardHuman.dashboardHuman', $viewData);
    }
}