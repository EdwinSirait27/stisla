<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Toilbalances;
use App\Models\ToilLeaveRequests;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Saldo TOIL & Cash milik karyawan yang login, untuk aplikasi mobile. Hanya miliknya sendiri.
 * Hitungannya sama dengan ToilController di web:
 * - saldo TOIL "active" yang tanggalnya lewat dihitung hangus (Toilbalances::effectiveStatus);
 * - saldo Cash tidak hangus, dibayar lewat payroll lalu jadi "paid";
 * - 1 hari libur TOIL = 8 jam; DW tidak bisa libur TOIL (lemburnya selalu Cash).
 */
class ToilBalanceController extends Controller
{
    private const HOURS_PER_DAY = 8;

    /** Saldo TOIL aktif yang hangus dalam sekian hari ditandai "segera hangus". */
    private const EXPIRING_SOON_DAYS = 14;

    public function index(Request $request)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan'], 404);
        }

        $balances = Toilbalances::with('overtimeSubmission')
            ->where('employee_id', $employee->id)
            ->where('status', '!=', 'cancelled')
            ->get();

        $rows = $balances->map(function (Toilbalances $b) {
            $sub    = $b->overtimeSubmission;
            $type   = $sub?->compensation_type;
            $status = $b->effectiveStatus($type);
            $date   = $sub ? Carbon::parse($sub->date) : null;
            $daysLeft = $status === 'active' && $type === 'Toil' && $b->expires_at ? $b->days_until_expired : null;

            return [
                'id'                => $b->id,
                'date'              => $date?->toDateString(),
                'date_label'        => $date?->locale('id')->translatedFormat('l, d F Y'),
                'time_range'        => $sub && $sub->start_time ? substr($sub->start_time, 0, 5) . '–' . substr((string) $sub->end_time, 0, 5) : null,
                'compensation_type' => $type,
                'earned_hours'      => (float) $b->earned_hours,
                'used_hours'        => (float) $b->used_hours,
                'remaining_hours'   => (float) $b->remaining_hours,
                'status'            => $status,
                'expires_at'        => $b->expires_at?->toDateString(),
                'days_left'         => $daysLeft,
                'expiring_soon'     => $daysLeft !== null && $daysLeft <= self::EXPIRING_SOON_DAYS,
                'paid_at'           => $b->paid_at?->toDateString(),
                'paid_period'       => $b->paid_period,
                'reason'            => $sub?->reason,
            ];
        });

        $toilActive = $rows->where('compensation_type', 'Toil')->where('status', 'active');
        $next       = $toilActive->sortBy('expires_at')->first();
        $toilLeft   = (float) $toilActive->sum('remaining_hours');

        // Urutan tampil: yang masih bisa dipakai dulu (paling cepat hangus di atas), lalu riwayat terbaru
        $ordered = $rows->where('status', 'active')->sortBy(fn($r) => $r['expires_at'] ?? '9999-12-31')
            ->concat($rows->where('status', '!=', 'active')->sortByDesc('date'))
            ->values();

        $leaves = ToilLeaveRequests::where('employee_id', $employee->id)
            ->orderByDesc('leave_date')
            ->limit(30)
            ->get()
            ->map(fn($r) => [
                'id'              => $r->id,
                'leave_date'      => $r->leave_date?->toDateString(),
                'date_label'      => $r->leave_date?->locale('id')->translatedFormat('l, d F Y'),
                'hours_used'      => (float) $r->hours_used,
                'status'          => $r->status,
                'reason'          => $r->reason,
                'rejected_reason' => $r->rejected_reason,
            ])->values();

        return response()->json([
            'success' => true,
            'data' => [
                'hours_per_day' => self::HOURS_PER_DAY,
                'is_dw'         => strtoupper($employee->status_employee ?? '') === 'DW',
                'summary'       => [
                    'toil_remaining_hours' => $toilLeft,
                    'toil_remaining_days'  => round($toilLeft / self::HOURS_PER_DAY, 2),
                    'toil_used_hours'      => (float) $rows->where('compensation_type', 'Toil')->sum('used_hours'),
                    'toil_expired_hours'   => (float) $rows->where('compensation_type', 'Toil')->where('status', 'expired')->sum('remaining_hours'),
                    'cash_unpaid_hours'    => (float) $rows->where('compensation_type', 'Cash')->where('status', 'active')->sum('remaining_hours'),
                    'cash_paid_hours'      => (float) $rows->where('compensation_type', 'Cash')->where('status', 'paid')->sum('earned_hours'),
                    'expiring_soon_hours'  => (float) $toilActive->where('expiring_soon', true)->sum('remaining_hours'),
                    'next_expiry'          => $next ? [
                        'date'      => $next['expires_at'],
                        'days_left' => $next['days_left'],
                        'hours'     => $next['remaining_hours'],
                    ] : null,
                ],
                'balances'       => $ordered,
                'leave_requests' => $leaves,
            ],
        ]);
    }
}
