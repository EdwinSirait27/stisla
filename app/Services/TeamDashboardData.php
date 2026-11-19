<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Fingerprints;
use App\Models\Leaverequest;
use App\Models\Roster;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Data tim untuk dashboard supervisor & manager:
 * - status hari ini sekumpulan karyawan (roster + scan fingerprint)
 * - cuti bawahan yang menunggu approval (approver = atasan, sama dengan LeaverequestController)
 */
class TeamDashboardData
{
    public const ACTIVE_STATUSES = ['Active', 'Pending', 'Mutation', 'On Leave'];

    /** Bawahan langsung (employee_atasans) yang masih aktif. */
    public function subordinates(?Employee $employee): Collection
    {
        if (!$employee) {
            return collect();
        }

        return $employee->bawahanList()
            ->whereIn('employees_tables.status', self::ACTIVE_STATUSES)
            ->with(['position' => fn($q) => $q->wherePivot('is_primary', true)])
            ->orderBy('employee_name')
            ->get(['employees_tables.id', 'employee_name', 'employee_pengenal', 'pin', 'employees_tables.status']);
    }

    /** Karyawan aktif yang store utamanya salah satu dari $storeIds. */
    public function storeEmployees(array $storeIds): Collection
    {
        if (!$storeIds) {
            return collect();
        }

        return Employee::whereIn('status', self::ACTIVE_STATUSES)
            ->whereHas('store', fn($q) => $q->whereIn('stores_tables.id', $storeIds)->where('employee_stores.is_primary', true))
            ->with(['position' => fn($q) => $q->wherePivot('is_primary', true)])
            ->orderBy('employee_name')
            ->get(['id', 'employee_name', 'employee_pengenal', 'pin', 'status']);
    }

    /**
     * Status hari ini per karyawan + ringkasan.
     * state: present (jadwal kerja & sudah scan), not_in (jadwal kerja, belum scan),
     *        not_working (off/cuti/libur), no_roster.
     */
    public function today(Collection $people): array
    {
        $today = Carbon::today();
        $ids = $people->pluck('id')->all();

        $rosterToday = $ids
            ? Roster::with('shift')->whereIn('employee_id', $ids)->whereDate('date', $today)->get()->keyBy('employee_id')
            : collect();
        $pins = $people->pluck('pin')->filter()->all();
        $firstScan = $pins
            ? Fingerprints::whereIn('pin', $pins)->whereDate('scan_date', $today)
                ->selectRaw('pin, MIN(scan_date) as first_scan')->groupBy('pin')->pluck('first_scan', 'pin')
            : collect();

        $rows = $people->map(function ($m) use ($rosterToday, $firstScan) {
            $r = $rosterToday[$m->id] ?? null;
            $scan = $m->pin ? ($firstScan[$m->pin] ?? null) : null;
            $state = match (true) {
                $r === null             => 'no_roster',
                $r->day_type !== 'Work' => 'not_working',
                $scan !== null          => 'present',
                default                 => 'not_in',
            };

            return [
                'name'     => $m->employee_name,
                'nip'      => $m->employee_pengenal,
                'position' => $m->position->first()?->name,
                'day_type' => $r?->day_type,
                'shift'    => $r && $r->shift ? substr($r->shift->start_time, 0, 5) . '–' . substr($r->shift->end_time, 0, 5) : null,
                'scan'     => $scan ? Carbon::parse($scan)->format('H:i') : null,
                'state'    => $state,
            ];
        });

        $stats = [
            'total'     => $rows->count(),
            'scheduled' => $rows->whereIn('state', ['present', 'not_in'])->count(),
            'present'   => $rows->where('state', 'present')->count(),
            'not_in'    => $rows->where('state', 'not_in')->count(),
            'away'      => $rows->where('state', 'not_working')->count(),
            'no_roster' => $rows->where('state', 'no_roster')->count(),
        ];
        $stats['rate'] = $stats['scheduled'] ? round($stats['present'] / $stats['scheduled'] * 100) : null;

        return [
            'rows'  => $rows->sortBy(fn($m) => ['not_in' => 0, 'no_roster' => 1, 'present' => 2, 'not_working' => 3][$m['state']])->values(),
            'stats' => $stats,
        ];
    }

    /** Cuti Pending milik $employeeIds, plus jumlah total. */
    public function pendingLeaves(array $employeeIds, int $limit = 10): array
    {
        if (!$employeeIds) {
            return ['items' => collect(), 'count' => 0];
        }

        $query = Leaverequest::with(['leavebalance.employees:id,employee_name,employee_pengenal', 'leavebalance.leaves:id,name'])
            ->whereHas('leavebalance', fn($q) => $q->whereIn('employee_id', $employeeIds))
            ->where('status', 'Pending');

        return [
            'count' => (clone $query)->count(),
            'items' => $query->orderBy('start_date')->limit($limit)->get(),
        ];
    }
}
