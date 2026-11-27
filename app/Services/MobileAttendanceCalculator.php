<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Roster;
use App\Models\ToilLeaveRequests;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Hari kerja berbayar dari absensi mobile (attendance_logs), untuk Payroll Absensi Mobile.
 * Aturannya mengikuti FingerprintRecapCalculator, hanya sumber kehadirannya yang berbeda:
 *
 * - Hadir      : roster Work + ada CHECK-IN dan CHECK-OUT di work_date itu, keduanya berstatus
 *                approved (supervisor) atau approved_hr. Pending / flagged tidak dihitung.
 * - Public Holiday : dibayar untuk selain DW; PH hari Minggu hangus di lokasi statis.
 * - TOIL Off (klaim Approved), Cuti, Cuti Melahirkan, Sakit : dibayar untuk selain DW.
 *
 * Kalkulator ini berdiri sendiri; payroll fingerprint tidak memakainya.
 */
class MobileAttendanceCalculator
{
    public const VALID_STATUSES = ['approved', 'approved_hr'];

    /** Sama dengan FingerprintRecapCalculator: PH Minggu hangus di lokasi ini. */
    private const STATIC_STORE_IDS = [
        '019623ad-de58-7368-8873-e3cbff2b0aff',
        '019963a7-cdb8-7002-b10b-163645c199d0',
        '019a230d-6146-7001-848d-046ccdbdf163',
    ];

    /**
     * @return array{total_hari_kerja:int, hadir:int, ph:int, toil:int, cuti_sakit:int, tidak_masuk:int, remarks:string}
     */
    public function calculate(Employee $employee, string $startDate, string $endDate): array
    {
        $statusEmp = strtoupper((string) $employee->status_employee);
        $isDailyWorker = $statusEmp === 'DW';
        $d = fn($v) => Carbon::parse($v)->toDateString();

        $rosters = Roster::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get(['date', 'day_type', 'notes']);

        $workDates = $rosters->where('day_type', 'Work')->map(fn($r) => $d($r->date))->unique()->values()->all();

        // Log mobile per tanggal kerja
        $logs = AttendanceLog::where('employee_id', $employee->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->get(['work_date', 'type', 'status']);
        $byDate = $logs->groupBy(fn($l) => $d($l->work_date));

        $present = $incomplete = $unverified = [];
        foreach ($workDates as $date) {
            $dayLogs = $byDate->get($date, collect());
            $valid = $dayLogs->whereIn('status', self::VALID_STATUSES);
            $hasIn  = $valid->contains('type', 'checkin');
            $hasOut = $valid->contains('type', 'checkout');

            if ($hasIn && $hasOut) {
                $present[] = $date;
            } elseif ($dayLogs->isNotEmpty() && $dayLogs->whereNotIn('status', self::VALID_STATUSES)->isNotEmpty()) {
                $unverified[] = $date;   // ada log tapi masih pending / flagged
            } elseif ($dayLogs->isNotEmpty()) {
                $incomplete[] = $date;   // check-in tanpa check-out (atau sebaliknya)
            }
        }

        // Public Holiday
        $isStaticStore = in_array($employee->primaryStore()->value('stores_tables.id') ?? $employee->store_id, self::STATIC_STORE_IDS, true);
        $phDates = $isDailyWorker ? [] : $rosters->where('day_type', 'Public Holiday')
            ->map(fn($r) => $d($r->date))
            ->reject(fn($date) => $isStaticStore && Carbon::parse($date)->isSunday())
            ->values()->all();

        // TOIL Off dari klaim Approved (yang benar-benar memotong saldo)
        $toilDates = ToilLeaveRequests::where('employee_id', $employee->id)
            ->where('status', 'Approved')
            ->whereBetween('leave_date', [$startDate, $endDate])
            ->pluck('leave_date')->map($d)->values()->all();

        // Cuti & sakit berbayar
        $paidAbsenceDates = $isDailyWorker ? [] : $rosters->whereIn('day_type', ['Leave', 'Cuti Melahirkan', 'Sick'])
            ->map(fn($r) => $d($r->date))->values()->all();

        $paidToil = $isDailyWorker ? [] : $toilDates;
        $counted = array_unique(array_merge($present, $phDates, $paidToil, $paidAbsenceDates));

        $absent = array_values(array_diff($workDates, $present, $paidAbsenceDates, $toilDates));

        $fmt = fn(array $dates) => collect($dates)->sort()->map(fn($x) => Carbon::parse($x)->format('d/m'))->implode(', ');
        $remarks = collect([
            'Hadir ' . count($present) . ' hari',
            $phDates ? 'PH ' . count($phDates) : null,
            $paidToil ? 'TOIL ' . count($paidToil) : null,
            $paidAbsenceDates ? 'cuti/sakit ' . count($paidAbsenceDates) : null,
            $unverified ? 'belum diverifikasi (tidak dihitung): ' . $fmt($unverified) : null,
            $incomplete ? 'tanpa check-in/out lengkap (tidak dihitung): ' . $fmt(array_diff($incomplete, $unverified)) : null,
            ($missing = array_diff($absent, $unverified, $incomplete)) ? 'tidak absen: ' . $fmt($missing) : null,
        ])->filter()->implode('; ');

        return [
            'total_hari_kerja' => count($counted),
            'hadir'            => count($present),
            'ph'               => count($phDates),
            'toil'             => count($paidToil),
            'cuti_sakit'       => count($paidAbsenceDates),
            'tidak_masuk'      => count($absent),
            'unverified'       => count($unverified),
            'remarks'          => $remarks,
        ];
    }
}
