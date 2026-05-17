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

/**
 * Controller untuk fitur "Auto Generate Roster – Store Lain".
 *
 * FUNGSI:
 * ───────
 * Generate roster otomatis PER MINGGU untuk store selain:
 *   - Head Office
 *   - Holding
 *   - Distribution Center
 *
 * PERIODE:
 * ────────
 * User memilih satu minggu (week_start = Senin) dan satu store.
 * Range yang di-generate: week_start (Mon) → week_start + 6 (Sun).
 *
 * POLA JADWAL (dikustomisasi per request):
 * ─────────────────────────────────────────
 * User mengirimkan "day_pattern": array 7 elemen (index 0 = Senin … 6 = Minggu),
 * masing-masing berisi:
 *   {
 *     "day_type": "Work" | "Off" | "Public Holiday",
 *     "shift_id": <int|null>   ← wajib jika day_type = "Work"
 *   }
 *
 * Contoh payload POST:
 * {
 *   "store_id"    : 5,
 *   "week_start"  : "2026-05-04",        ← harus hari Senin
 *   "apply_to"    : "all" | "selected",  ← "all" = semua karyawan store, "selected" = list employee_ids
 *   "employee_ids": [1, 2, 3],           ← wajib jika apply_to = "selected"
 *   "day_pattern" : [
 *     { "day_type": "Work", "shift_id": 7 },   // Senin
 *     { "day_type": "Work", "shift_id": 7 },   // Selasa
 *     { "day_type": "Work", "shift_id": 7 },   // Rabu
 *     { "day_type": "Work", "shift_id": 7 },   // Kamis
 *     { "day_type": "Work", "shift_id": 7 },   // Jumat
 *     { "day_type": "Work", "shift_id": 8 },   // Sabtu
 *     { "day_type": "Off",  "shift_id": null } // Minggu
 *   ],
 *   "override_existing": false  ← jika true, timpa roster yang sudah ada
 * }
 *
 * FILTER PUBLIC HOLIDAY PER AGAMA & STATUS KARYAWAN:
 * ────────────────────────────────────────────────────
 * Sama seperti AutoRosterController (Hindu vs Non Hindu).
 * Jika sebuah hari di-pattern sebagai "Work" tapi ternyata PH
 * untuk agama karyawan tsb, maka otomatis berubah menjadi "Public Holiday".
 *
 * Status karyawan menentukan eligibilitas PH:
 *   - PKWT → dapat PH
 *   - OJT  → dapat PH
 *   - DW   → TIDAK dapat PH (tetap mengikuti pattern Work/Off)
 *
 * BEHAVIOR JIKA ROSTER SUDAH ADA:
 * ───────────────────────────────
 * Default : SKIP (override_existing = false)
 * Jika override_existing = true : UPDATE roster yang ada
 *
 * Endpoint:
 *   POST /roster/auto-generate/other         → generate()
 *   GET  /roster/auto-generate/other/preview → preview()
 *   GET  /roster/auto-generate/other/stores  → listStores()
 */
class AutoRosterOtherStoreController extends Controller
{
    /**
     * Store yang di-handle oleh AutoRosterController (static schedule).
     * Store ini TIDAK boleh di-generate lewat controller ini.
     */
    private const EXCLUDED_STORES = [
        'Head Office',
        'Holding',
        'Distribution Center',
    ];

    private const DAYS_IN_WEEK = 7;

    // ─────────────────────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────────────────────

    /**
     * Resolve type PH yang relevan berdasarkan agama karyawan.
     */
    private function resolveRelevantPhTypes(?string $religion): array
    {
        return ($religion === 'Hindu')
            ? ['Hindu', 'All']
            : ['Non Hindu', 'All'];
    }

    /**
     * ✅ TAMBAHAN BARU
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

    /**
     * Validasi bahwa store bukan termasuk store static.
     *
     * @throws \InvalidArgumentException
     */
    private function validateStoreNotExcluded(Stores $store): void
    {
        if (in_array($store->name, self::EXCLUDED_STORES, true)) {
            throw new \InvalidArgumentException(
                "Store \"{$store->name}\" menggunakan jadwal static. "
                . 'Gunakan tombol Auto Generate Roster (utama) untuk store ini.'
            );
        }
    }

    /**
     * Validasi & parse week_start.
     * Harus format YYYY-MM-DD dan merupakan hari Senin.
     *
     * @throws \InvalidArgumentException
     * @return Carbon
     */
    private function parseWeekStart(string $weekStartRaw): Carbon
    {
        try {
            $weekStart = Carbon::parse($weekStartRaw)->startOfDay();
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(
                'Format week_start tidak valid. Gunakan format YYYY-MM-DD.'
            );
        }

        if ($weekStart->dayOfWeek !== Carbon::MONDAY) {
            throw new \InvalidArgumentException(
                'week_start harus merupakan hari Senin (awal minggu).'
            );
        }

        // Tidak boleh lebih dari 3 bulan ke depan
        if ($weekStart->gt(Carbon::now()->addMonths(3)->endOfDay())) {
            throw new \InvalidArgumentException(
                'week_start tidak boleh lebih dari 3 bulan ke depan.'
            );
        }

        return $weekStart;
    }

    /**
     * Validasi day_pattern dari request.
     * Harus array 7 elemen dengan struktur yang benar.
     *
     * @throws \InvalidArgumentException
     * @return array  Array 7 elemen yang sudah divalidasi
     */
    private function validateDayPattern(array $pattern): array
    {
        if (count($pattern) !== self::DAYS_IN_WEEK) {
            throw new \InvalidArgumentException(
                'day_pattern harus berisi tepat 7 elemen (Senin hingga Minggu).'
            );
        }

        $validDayTypes = ['Work', 'Off', 'Public Holiday'];

        foreach ($pattern as $index => $day) {
            $dayName = Carbon::now()->startOfWeek()->addDays($index)->format('l');

            if (!isset($day['day_type'])) {
                throw new \InvalidArgumentException(
                    "day_pattern[{$index}] ({$dayName}): 'day_type' wajib diisi."
                );
            }

            if (!in_array($day['day_type'], $validDayTypes, true)) {
                throw new \InvalidArgumentException(
                    "day_pattern[{$index}] ({$dayName}): day_type '{$day['day_type']}' tidak valid. "
                    . 'Gunakan: ' . implode(', ', $validDayTypes) . '.'
                );
            }

            if ($day['day_type'] === 'Work' && empty($day['shift_id'])) {
                throw new \InvalidArgumentException(
                    "day_pattern[{$index}] ({$dayName}): shift_id wajib diisi jika day_type = 'Work'."
                );
            }
        }

        return $pattern;
    }

    // ─────────────────────────────────────────────────────────────
    //  LIST STORES (untuk dropdown UI)
    // ─────────────────────────────────────────────────────────────

    /**
     * Ambil daftar store yang tersedia untuk auto-generate per minggu.
     * (Exclude store static)
     *
     * GET /roster/auto-generate/other/stores
     */
    public function listStores()
    {
        $stores = Stores::whereNotIn('name', self::EXCLUDED_STORES)
            ->whereNull('deleted_at')
            ->get(['id', 'name'])
            ->map(function ($store) {
                $employeeCount = Employee::where('store_id', $store->id)
                    ->whereNull('deleted_at')
                    ->count();

                return [
                    'id'             => $store->id,
                    'name'           => $store->name,
                    'employee_count' => $employeeCount,
                ];
            });

        return response()->json([
            'success' => true,
            'stores'  => $stores,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  PREVIEW
    // ─────────────────────────────────────────────────────────────

    /**
     * Preview sebelum generate.
     *
     * GET /roster/auto-generate/other/preview
     *
     * Query params:
     *   store_id   : int
     *   week_start : string YYYY-MM-DD (Senin)
     */
    public function preview(Request $request)
    {
        // ── Validasi input dasar ──
        $storeId      = $request->query('store_id');
        $weekStartRaw = $request->query('week_start');

        if (!$storeId || !$weekStartRaw) {
            return response()->json([
                'success' => false,
                'message' => 'store_id dan week_start wajib diisi.',
            ], 422);
        }

        // ── Validasi store ──
        $store = Stores::find($storeId);
        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Store tidak ditemukan.',
            ], 404);
        }

        try {
            $this->validateStoreNotExcluded($store);
            $weekStart = $this->parseWeekStart($weekStartRaw);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $weekEnd = $weekStart->copy()->addDays(6);

        // ── Data karyawan ──
        // ✅ PERUBAHAN: tambah status_employee di select
        $employees = Employee::where('store_id', $store->id)
            ->whereNull('deleted_at')
            ->select('id', 'employee_name', 'religion', 'status_employee')
            ->get();

        $hinduCount    = $employees->where('religion', 'Hindu')->count();
        $nonHinduCount = $employees->count() - $hinduCount;

        // ✅ TAMBAHAN: hitung breakdown per status untuk info preview
        $pkwtCount = $employees->filter(fn($e) => strtoupper($e->status_employee ?? '') === 'PKWT')->count();
        $ojtCount  = $employees->filter(fn($e) => strtoupper($e->status_employee ?? '') === 'OJT')->count();
        $dwCount   = $employees->filter(fn($e) => strtoupper($e->status_employee ?? '') === 'DW')->count();

        // ── Public Holiday dalam minggu ini ──
        $publicHolidays = PublicHoliday::whereBetween('date', [
                $weekStart->toDateString(),
                $weekEnd->toDateString(),
            ])
            ->get(['date', 'remark', 'type']);

        // ── Existing roster ──
        $employeeIds   = $employees->pluck('id')->toArray();
        $existingCount = Roster::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [
                $weekStart->toDateString(),
                $weekEnd->toDateString(),
            ])
            ->count();

        $estimatedRows = $employees->count() * self::DAYS_IN_WEEK;

        // ── Shifts tersedia di store ini ──
        $shifts = Shifts::where('store_id', $store->id)
            ->get(['id', 'shift_name', 'start_time', 'end_time']);

        return response()->json([
            'success' => true,
            'preview' => [
                'store'          => ['id' => $store->id, 'name' => $store->name],
                'week_start'     => $weekStart->toDateString(),
                'week_end'       => $weekEnd->toDateString(),
                'week_label'     => $weekStart->format('d M Y') . ' – ' . $weekEnd->format('d M Y'),
                'total_employees'=> $employees->count(),
                'employees_by_religion' => [
                    'Hindu'     => $hinduCount,
                    'Non Hindu' => $nonHinduCount,
                ],
                // ✅ TAMBAHAN: breakdown status untuk info di preview UI
                'employees_by_status' => [
                    'PKWT' => $pkwtCount,
                    'OJT'  => $ojtCount,
                    'DW'   => $dwCount,
                ],
                'public_holidays'  => $publicHolidays,
                'estimated_rows'   => $estimatedRows,
                'existing_rosters' => $existingCount,
                'will_be_created'  => max(0, $estimatedRows - $existingCount),
                'will_be_skipped'  => $existingCount,
                'available_shifts' => $shifts,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  GENERATE
    // ─────────────────────────────────────────────────────────────

    /**
     * Generate roster mingguan untuk store lain.
     *
     * POST /roster/auto-generate/other
     *
     * Body JSON:
     * {
     *   "store_id"          : int,
     *   "week_start"        : "YYYY-MM-DD",     ← Senin
     *   "apply_to"          : "all" | "selected",
     *   "employee_ids"      : [int, ...],        ← jika apply_to = "selected"
     *   "override_existing" : bool,              ← default false
     *   "day_pattern"       : [                  ← 7 elemen: Senin → Minggu
     *     { "day_type": "Work",  "shift_id": 7 },
     *     { "day_type": "Work",  "shift_id": 7 },
     *     { "day_type": "Work",  "shift_id": 7 },
     *     { "day_type": "Work",  "shift_id": 7 },
     *     { "day_type": "Work",  "shift_id": 7 },
     *     { "day_type": "Work",  "shift_id": 8 },
     *     { "day_type": "Off",   "shift_id": null }
     *   ]
     * }
     */
    public function generate(Request $request)
    {
        // ── 1. Validasi input dasar ──
        $storeId          = $request->input('store_id');
        $weekStartRaw     = $request->input('week_start');
        $applyTo          = $request->input('apply_to', 'all');
        $selectedIds      = $request->input('employee_ids', []);
        $overrideExisting = (bool) $request->input('override_existing', false);
        $dayPatternRaw    = $request->input('day_pattern', []);

        // Validasi wajib
        if (!$storeId) {
            return response()->json(['success' => false, 'message' => 'store_id wajib diisi.'], 422);
        }
        if (!$weekStartRaw) {
            return response()->json(['success' => false, 'message' => 'week_start wajib diisi.'], 422);
        }
        if (empty($dayPatternRaw)) {
            return response()->json(['success' => false, 'message' => 'day_pattern wajib diisi.'], 422);
        }
        if (!in_array($applyTo, ['all', 'selected'], true)) {
            return response()->json(['success' => false, 'message' => "apply_to harus 'all' atau 'selected'."], 422);
        }
        if ($applyTo === 'selected' && empty($selectedIds)) {
            return response()->json(['success' => false, 'message' => 'employee_ids wajib diisi jika apply_to = selected.'], 422);
        }

        // ── 2. Validasi store ──
        $store = Stores::find($storeId);
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Store tidak ditemukan.'], 404);
        }

        try {
            $this->validateStoreNotExcluded($store);
            $weekStart  = $this->parseWeekStart($weekStartRaw);
            $dayPattern = $this->validateDayPattern($dayPatternRaw);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $weekEnd = $weekStart->copy()->addDays(6);

        try {
            Log::info('AutoRosterOther: generate dipanggil', [
                'store_id'          => $storeId,
                'store_name'        => $store->name,
                'week_start'        => $weekStart->toDateString(),
                'week_end'          => $weekEnd->toDateString(),
                'apply_to'          => $applyTo,
                'override_existing' => $overrideExisting,
            ]);

            // ── 3. Ambil karyawan ──
            // ✅ PERUBAHAN: tambah status_employee di select
            $employeeQuery = Employee::where('store_id', $store->id)
                ->whereNull('deleted_at')
                ->select('id', 'employee_name', 'religion', 'status_employee');

            if ($applyTo === 'selected') {
                $employeeQuery->whereIn('id', $selectedIds);
            }

            $employees = $employeeQuery->get();

            if ($employees->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada karyawan yang ditemukan di store ini.',
                ], 422);
            }

            // ── 4. Validasi shift_id dalam day_pattern milik store ini ──
            $shiftIdsInPattern = collect($dayPattern)
                ->pluck('shift_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            if (!empty($shiftIdsInPattern)) {
                $validShifts = Shifts::where('store_id', $store->id)
                    ->whereIn('id', $shiftIdsInPattern)
                    ->pluck('id')
                    ->toArray();

                $invalidShiftIds = array_diff($shiftIdsInPattern, $validShifts);
                if (!empty($invalidShiftIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Shift ID tidak valid atau bukan milik store ini: '
                                   . implode(', ', $invalidShiftIds) . '.',
                    ], 422);
                }
            }

            // ── 5. Pre-load Public Holiday dalam minggu ini ──
            $allPublicHolidays = PublicHoliday::whereBetween('date', [
                    $weekStart->toDateString(),
                    $weekEnd->toDateString(),
                ])
                ->get()
                ->groupBy(fn($ph) => Carbon::parse($ph->date)->toDateString());

            // ── 6. Pre-load existing roster (untuk skip / override) ──
            $employeeIds = $employees->pluck('id')->toArray();

            $existingRosters = Roster::whereIn('employee_id', $employeeIds)
                ->whereBetween('date', [
                    $weekStart->toDateString(),
                    $weekEnd->toDateString(),
                ])
                ->get()
                ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

            // ── 7. Generate tanggal dalam minggu (Senin s/d Minggu) ──
            $dates = [];
            for ($d = $weekStart->copy(); $d->lte($weekEnd); $d->addDay()) {
                $dates[] = $d->copy();
            }

            // ── 8. Loop generate ──
            $created   = 0;
            $skipped   = 0;
            $updated   = 0;
            $phApplied = 0;
            $breakdown = ['Work' => 0, 'Off' => 0, 'Public Holiday' => 0];

            DB::beginTransaction();

            foreach ($employees as $employee) {
                $relevantPhTypes = $this->resolveRelevantPhTypes($employee->religion);

                // ✅ TAMBAHAN: cek eligibilitas PH berdasarkan status_employee
                // PKWT & OJT → dapat PH | DW → tidak dapat PH
                $eligibleForPH = $this->isEligibleForPH($employee->status_employee);

                foreach ($dates as $dayIndex => $date) {
                    $dateStr  = $date->toDateString();
                    $key      = $employee->id . '_' . $dateStr;
                    $pattern  = $dayPattern[$dayIndex];

                    $dayType = $pattern['day_type'];
                    $shiftId = $pattern['shift_id'] ?? null;
                    $notes   = null;

                    // ✅ PERUBAHAN: cek PH hanya untuk karyawan yang eligible (PKWT & OJT)
                    // DW tetap mengikuti pattern Work/Off tanpa override PH
                    if ($eligibleForPH && $allPublicHolidays->has($dateStr)) {
                        $phForDate = $allPublicHolidays->get($dateStr)
                            ->first(fn($ph) => in_array($ph->type, $relevantPhTypes));

                        if ($phForDate) {
                            $dayType = 'Public Holiday';
                            $shiftId = null;
                            $notes   = $phForDate->remark;
                            $phApplied++;
                        }
                    }

                    if ($existingRosters->has($key)) {
                        if (!$overrideExisting) {
                            $skipped++;
                            continue;
                        }

                        // Update
                        $existingRosters->get($key)->update([
                            'shift_id' => $shiftId,
                            'day_type' => $dayType,
                            'notes'    => $notes,
                        ]);
                        $updated++;
                        $breakdown[$dayType]++;
                        continue;
                    }

                    // Create
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

            Log::info('AutoRosterOther: generate sukses', [
                'store'      => $store->name,
                'created'    => $created,
                'updated'    => $updated,
                'skipped'    => $skipped,
                'ph_applied' => $phApplied,
            ]);

            // ── 9. Response ──
            $parts = [];
            if ($created > 0)  $parts[] = "dibuat {$created}";
            if ($updated > 0)  $parts[] = "diperbarui {$updated}";
            if ($skipped > 0)  $parts[] = "dilewati {$skipped}";

            $message = 'Roster berhasil di-generate untuk ' . $store->name . ': '
                     . implode(', ', $parts) . '.';

            if ($phApplied > 0) {
                $message .= " {$phApplied} hari libur nasional diterapkan otomatis (PKWT & OJT).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'summary' => [
                    'store'       => ['id' => $store->id, 'name' => $store->name],
                    'week_start'  => $weekStart->toDateString(),
                    'week_end'    => $weekEnd->toDateString(),
                    'week_label'  => $weekStart->format('d M Y') . ' – ' . $weekEnd->format('d M Y'),
                    'apply_to'    => $applyTo,
                    'override'    => $overrideExisting,
                    'total_employees'   => $employees->count(),
                    'created'           => $created,
                    'updated'           => $updated,
                    'skipped'           => $skipped,
                    'public_holidays'   => $phApplied,
                    'breakdown_by_type' => $breakdown,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('AutoRosterOther: generate ERROR', [
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
}