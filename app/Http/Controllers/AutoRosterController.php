<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PublicHoliday;
use App\Models\Roster;
use App\Models\Shifts;
use App\Models\Stores;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoRosterController extends Controller
{
    private const TARGET_STORES = [
        'Head Office',
        'Holding',
        'Distribution Center',
    ];

    private const SHIFT_WEEKDAY  = '9 to 5';
    private const SHIFT_SATURDAY = '9 to 3';
    private const MAX_RANGE_DAYS = 62;

    // ─────────────────────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────────────────────

    private function resolveRelevantPhTypes(?string $religion): array
    {
        return ($religion === 'Hindu')
            ? ['Hindu', 'All']
            : ['Non Hindu', 'All'];
    }

    /**
     * Tentukan apakah karyawan berhak mendapat Public Holiday
     * berdasarkan status_employee:
     *   - PKWT → dapat PH
     *   - OJT  → dapat PH
     *   - DW   → TIDAK dapat PH
     */
    private function isEligibleForPH(?string $statusEmployee): bool
    {
        return !in_array(strtoupper($statusEmployee ?? ''), ['DW']);
    }

    private function defaultPeriod(): array
    {
        $today = Carbon::now();

        $startDate = ($today->day >= 26)
            ? $today->copy()->day(26)
            : $today->copy()->subMonth()->day(26);

        $endDate = $startDate->copy()->addMonth()->day(25);

        return [$startDate, $endDate];
    }

    private function parsePeriodFromRequest(Request $request): ?array
    {
        $startRaw = $request->input('start_date');
        $endRaw   = $request->input('end_date');

        if (empty($startRaw) && empty($endRaw)) {
            return null;
        }

        if (empty($startRaw) || empty($endRaw)) {
            throw new \InvalidArgumentException(
                'start_date dan end_date harus diisi keduanya atau dikosongkan keduanya.'
            );
        }

        try {
            $startDate = Carbon::parse($startRaw)->startOfDay();
            $endDate   = Carbon::parse($endRaw)->startOfDay();
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(
                'Format tanggal tidak valid. Gunakan format YYYY-MM-DD.'
            );
        }

        if ($endDate->lt($startDate)) {
            throw new \InvalidArgumentException(
                'end_date tidak boleh sebelum start_date.'
            );
        }

        if ($startDate->diffInDays($endDate) > self::MAX_RANGE_DAYS) {
            throw new \InvalidArgumentException(
                'Rentang tanggal tidak boleh lebih dari ' . self::MAX_RANGE_DAYS . ' hari.'
            );
        }

        if ($startDate->gt(Carbon::now()->addMonths(3)->endOfDay())) {
            throw new \InvalidArgumentException(
                'start_date tidak boleh lebih dari 3 bulan ke depan.'
            );
        }

        return [$startDate, $endDate];
    }

    // ─────────────────────────────────────────────────────────────
    //  GENERATE
    // ─────────────────────────────────────────────────────────────

    public function generate(Request $request)
    {
        try {
            $override = $this->parsePeriodFromRequest($request);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        [$startDate, $endDate] = $override ?? $this->defaultPeriod();

        try {
            Log::info('AutoRoster: generate dipanggil', [
                'start_date' => $startDate->toDateString(),
                'end_date'   => $endDate->toDateString(),
                'is_custom'  => $override !== null,
            ]);

            $stores = Stores::whereIn('name', self::TARGET_STORES)->get();

            if ($stores->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada store target yang ditemukan.',
                ], 422);
            }

            $storeIds = $stores->pluck('id')->toArray();


            // ── Tambah status_employee di select ──
            $employees = Employee::with('store:id,name')
                ->select('id', 'employee_name', 'store_id', 'religion', 'status_employee')
                ->whereIn('store_id', $storeIds)
                ->whereNull('deleted_at')
                ->get();

            // if ($employees->isEmpty()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Tidak ada karyawan di store target.',
            //     ], 422);
            // }
            $employees = Employee::with('store:id,name')
    ->select('id', 'employee_name', 'store_id', 'religion')
    ->whereIn('store_id', $storeIds)
    ->whereIn('status', ['Active', 'Mutation', 'Pending'])
    ->whereNull('deleted_at')
    ->get();

if ($employees->isEmpty()) {
    return response()->json([
        'success' => false,
        'message' => 'Tidak ada karyawan di store target.',
    ], 422);
}

            $shifts = Shifts::whereIn('store_id', $storeIds)
                ->whereIn('shift_name', [self::SHIFT_WEEKDAY, self::SHIFT_SATURDAY])
                ->get()
                ->keyBy(fn($s) => $s->store_id . '_' . $s->shift_name);

            $missingShifts = [];
            foreach ($stores as $store) {
                foreach ([self::SHIFT_WEEKDAY, self::SHIFT_SATURDAY] as $shiftName) {
                    if (!$shifts->has($store->id . '_' . $shiftName)) {
                        $missingShifts[] = "{$store->name}: {$shiftName}";
                    }
                }
            }

            if (!empty($missingShifts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shift belum di-setup untuk: ' . implode(', ', $missingShifts),
                ], 422);
            }

            $allPublicHolidays = PublicHoliday::whereBetween('date', [
                    $startDate->toDateString(),
                    $endDate->toDateString(),
                ])
                ->get()
                ->groupBy(fn($ph) => Carbon::parse($ph->date)->toDateString());

            $employeeIds = $employees->pluck('id')->toArray();

            $existingRosters = Roster::whereIn('employee_id', $employeeIds)
                ->whereBetween('date', [
                    $startDate->toDateString(),
                    $endDate->toDateString(),
                ])
                ->get()
                ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

            $dates = [];
            for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
                $dates[] = $d->copy();
            }

            $created   = 0;
            $skipped   = 0;
            $phApplied = 0;
            $breakdown = [
                'Work'           => 0,
                'Off'            => 0,
                'Public Holiday' => 0,
            ];

            DB::beginTransaction();

            foreach ($employees as $employee) {
                $shift9to5 = $shifts->get($employee->store_id . '_' . self::SHIFT_WEEKDAY);
                $shift9to3 = $shifts->get($employee->store_id . '_' . self::SHIFT_SATURDAY);

                $relevantPhTypes  = $this->resolveRelevantPhTypes($employee->religion);
                // ── Cek eligibilitas PH berdasarkan status_employee ──
                $eligibleForPH    = $this->isEligibleForPH($employee->status_employee);

                foreach ($dates as $date) {
                    $dateStr = $date->toDateString();
                    $key     = $employee->id . '_' . $dateStr;

                    if ($existingRosters->has($key)) {
                        $skipped++;
                        continue;
                    }

                    // Cek PH — hanya untuk karyawan yang eligible (PKWT & OJT)
                    $phForDate = null;
                    if ($eligibleForPH && $allPublicHolidays->has($dateStr)) {
                        $phForDate = $allPublicHolidays->get($dateStr)
                            ->first(fn($ph) => in_array($ph->type, $relevantPhTypes));
                    }

                    $dayType = null;
                    $shiftId = null;
                    $notes   = null;

                    if ($phForDate) {
                        $dayType = 'Public Holiday';
                        $notes   = $phForDate->remark;
                        $phApplied++;
                    } elseif ($date->isSunday()) {
                        $dayType = 'Off';
                    } elseif ($date->isSaturday()) {
                        $dayType = 'Work';
                        $shiftId = $shift9to3->id;
                    } else {
                        $dayType = 'Work';
                        $shiftId = $shift9to5->id;
                    }

                    Roster::create([
                        'employee_id' => $employee->id,
                        'shift_id'    => $shiftId,
                        'date'        => $dateStr,
                        'day_type'    => $dayType,
                        'notes'       => $notes,
                    ]);

                    $created++;
                    $breakdown[$dayType]++;
                }
            }

            DB::commit();

            Log::info('AutoRoster: generate sukses', [
                'created'    => $created,
                'skipped'    => $skipped,
                'ph_applied' => $phApplied,
            ]);

            $message = "Berhasil generate {$created} roster";
            if ($skipped > 0) {
                $message .= " ({$skipped} dilewati karena sudah ada)";
            }
            if ($phApplied > 0) {
                $message .= ", {$phApplied} hari libur nasional diterapkan";
            }
            $message .= '.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'summary' => [
                    'period' => [
                        'start' => $startDate->toDateString(),
                        'end'   => $endDate->toDateString(),
                    ],
                    'is_custom_period'   => $override !== null,
                    'stores'             => $stores->pluck('name')->values(),
                    'total_employees'    => $employees->count(),
                    'total_dates'        => count($dates),
                    'created'            => $created,
                    'skipped'            => $skipped,
                    'public_holidays'    => $phApplied,
                    'breakdown_by_type'  => $breakdown,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('AutoRoster: generate ERROR', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  PREVIEW
    // ─────────────────────────────────────────────────────────────

    public function preview(Request $request)
    {
        try {
            $override = $this->parsePeriodFromRequest($request);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        [$startDate, $endDate] = $override ?? $this->defaultPeriod();

        try {
            $employeeCounts = Employee::query()
                ->whereHas('store', fn($q) => $q->whereIn('name', self::TARGET_STORES))
                ->whereNull('deleted_at')
                ->select('religion', DB::raw('COUNT(*) as count'))
                ->groupBy('religion')
                ->get();

            $totalEmployees = $employeeCounts->sum('count');
            $hinduCount     = $employeeCounts->where('religion', 'Hindu')->sum('count');
            $nonHinduCount  = $totalEmployees - $hinduCount;

            $totalDates = $startDate->diffInDays($endDate) + 1;

            $phByType = PublicHoliday::query()
                ->whereBetween('date', [
                    $startDate->toDateString(),
                    $endDate->toDateString(),
                ])
                ->select('type', DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type');

            $employeeIds = Employee::query()
                ->whereHas('store', fn($q) => $q->whereIn('name', self::TARGET_STORES))
                ->whereNull('deleted_at')
                ->pluck('id')
                ->toArray();

            $existingCount = Roster::whereIn('employee_id', $employeeIds)
                ->whereBetween('date', [
                    $startDate->toDateString(),
                    $endDate->toDateString(),
                ])
                ->count();

            $estimatedRows = $totalEmployees * $totalDates;

            return response()->json([
                'success' => true,
                'preview' => [
                    'start_date'       => $startDate->toDateString(),
                    'end_date'         => $endDate->toDateString(),
                    'is_custom_period' => $override !== null,
                    'total_dates'      => $totalDates,
                    'total_employees'  => $totalEmployees,
                    'employees_by_religion' => [
                        'Hindu'     => $hinduCount,
                        'Non Hindu' => $nonHinduCount,
                    ],
                    'public_holidays_by_type' => $phByType,
                    'estimated_rows'   => $estimatedRows,
                    'existing_rosters' => $existingCount,
                    'will_be_created'  => max(0, $estimatedRows - $existingCount),
                    'will_be_skipped'  => $existingCount,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}