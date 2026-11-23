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

    /**
     * Tim manager/SPV: karyawan aktif yang berada di store DAN departemen yang sama dengan $employee
     * (semua penempatannya, bukan hanya yang utama), ditambah bawahan langsung (employee_atasans,
     * walau beda store/departemen). $employee sendiri tidak ikut.
     * Sama dengan cakupan ManageEmployeeSPVManager di EmployeeController / AttendanceMobileController.
     */
    public function teamEmployees(?Employee $employee): Collection
    {
        if (!$employee) {
            return collect();
        }

        $storeIds      = $employee->store()->pluck('stores_tables.id')->toArray();
        $departmentIds = $employee->department()->pluck('departments_tables.id')->toArray();

        // Bawahan langsung dari pivot employee_atasans
        $bawahanIds = $employee->bawahanList()->pluck('employees_tables.id')->toArray();

        if ((empty($storeIds) || empty($departmentIds)) && empty($bawahanIds)) {
            return collect();
        }

        return Employee::whereIn('status', self::ACTIVE_STATUSES)
            ->whereNull('deleted_at')
            ->whereKeyNot($employee->id)
            ->where(function ($q) use ($storeIds, $departmentIds, $bawahanIds) {
                if (!empty($storeIds) && !empty($departmentIds)) {
                    // Kondisi 1: karyawan di store + department yang sama
                    $q->where(fn($q1) => $q1
                        ->whereHas('store', fn($s) => $s->whereIn('stores_tables.id', $storeIds))
                        ->whereHas('department', fn($d) => $d->whereIn('departments_tables.id', $departmentIds)));
                }
                // Kondisi 2: bawahan langsung (beda company/store/department sekalipun)
                if (!empty($bawahanIds)) {
                    $q->orWhereIn('id', $bawahanIds);
                }
            })
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
                'shift'    => $r && $r->shift ? $r->shift->time_label : null,
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
