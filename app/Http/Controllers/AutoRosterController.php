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
 * Controller untuk fitur "Auto Generate Roster".
 *
 * FUNGSI:
 * ───────
 * Tombol manual untuk generate roster otomatis bagi karyawan di 3 store
 * dengan jadwal static:
 *   - Head Office
 *   - Holding
 *   - Distribution Center
 *
 * PERIODE:
 * ────────
 * Default : tanggal 26 bulan ini → tanggal 25 bulan depan
 * Override: kirim start_date & end_date di request body (POST) atau query string (GET)
 *
 * POLA JADWAL:
 * ────────────
 *   - Senin - Jumat       → Work, shift "9 to 5" (09:00-17:00)
 *   - Sabtu               → Work, shift "9 to 3" (09:00-15:00)
 *   - Minggu              → Off, shift_id = NULL
 *   - Public Holiday      → Public Holiday, shift_id = NULL
 *     (filter type sesuai agama karyawan)
 *
 * FILTER PUBLIC HOLIDAY PER AGAMA:
 * ────────────────────────────────
 * Karyawan dengan religion='Hindu' → libur untuk type ['Hindu', 'All']
 * Karyawan agama lain              → libur untuk type ['Non Hindu', 'All']
 *
 * SHIFT MAPPING:
 * ──────────────
 * Setiap store punya shift sendiri (shifts_tables.store_id), walaupun nama
 * dan jam-nya sama. Saat generate, filter shift berdasarkan store_id karyawan.
 *
 * BEHAVIOR JIKA ROSTER SUDAH ADA:
 * ───────────────────────────────
 * SKIP — data lama tidak ditimpa. Hanya generate yang belum ada.
 *
 * Endpoint:
 *   POST /roster/auto-generate          → generate()
 *   GET  /roster/auto-generate/preview  → preview()
 */
class AutoRosterController extends Controller
{
    /**
     * Daftar nama store yang dapat di-auto-generate roster-nya.
     */
    private const TARGET_STORES = [
        'Head Office',
        'Holding',
        'Distribution Center',
    ];

    /**
     * Nama shift untuk masing-masing pola hari.
     */
    private const SHIFT_WEEKDAY  = '9 to 5';  // Senin – Jumat
    private const SHIFT_SATURDAY = '9 to 3';  // Sabtu

    /**
     * Maksimal rentang hari yang boleh di-generate sekali jalan.
     * Lindungi server dari request yang tidak wajar.
     */
    private const MAX_RANGE_DAYS = 62;

    // ─────────────────────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────────────────────

    /**
     * Resolve type PH yang berlaku untuk seorang karyawan berdasarkan agamanya.
     *
     * @param  string|null  $religion
     * @return array  ['Hindu','All'] atau ['Non Hindu','All']
     */
    private function resolveRelevantPhTypes(?string $religion): array
    {
        return ($religion === 'Hindu')
            ? ['Hindu', 'All']
            : ['Non Hindu', 'All'];
    }

    /**
     * Hitung periode default: 26 bulan ini → 25 bulan depan.
     * Jika hari ini sudah >= 26, pakai bulan ini; jika belum, mundur ke bulan lalu.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function defaultPeriod(): array
    {
        $today = Carbon::now();

        $startDate = ($today->day >= 26)
            ? $today->copy()->day(26)
            : $today->copy()->subMonth()->day(26);

        $endDate = $startDate->copy()->addMonth()->day(25);

        return [$startDate, $endDate];
    }

    /**
     * Parse & validasi periode dari request.
     * Kembalikan [Carbon $start, Carbon $end] atau null jika tidak ada override.
     * Lempar \InvalidArgumentException jika ada isian tapi tidak valid.
     *
     * @throws \InvalidArgumentException
     */
    private function parsePeriodFromRequest(Request $request): ?array
    {
        $startRaw = $request->input('start_date');
        $endRaw   = $request->input('end_date');

        // Jika keduanya kosong → pakai default
        if (empty($startRaw) && empty($endRaw)) {
            return null;
        }

        // Jika salah satu diisi dan yang lain tidak
        if (empty($startRaw) || empty($endRaw)) {
            throw new \InvalidArgumentException(
                'start_date dan end_date harus diisi keduanya atau dikosongkan keduanya.'
            );
        }

        // Parse
        try {
            $startDate = Carbon::parse($startRaw)->startOfDay();
            $endDate   = Carbon::parse($endRaw)->startOfDay();
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(
                'Format tanggal tidak valid. Gunakan format YYYY-MM-DD.'
            );
        }

        // end >= start
        if ($endDate->lt($startDate)) {
            throw new \InvalidArgumentException(
                'end_date tidak boleh sebelum start_date.'
            );
        }

        // Batas maksimal range
        if ($startDate->diffInDays($endDate) > self::MAX_RANGE_DAYS) {
            throw new \InvalidArgumentException(
                'Rentang tanggal tidak boleh lebih dari ' . self::MAX_RANGE_DAYS . ' hari.'
            );
        }

        // Batas maksimal ke depan: 3 bulan dari sekarang
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

    /**
     * Generate roster untuk 3 store target.
     *
     * Request body (opsional):
     *   start_date : string YYYY-MM-DD  – override periode mulai
     *   end_date   : string YYYY-MM-DD  – override periode selesai
     */
    public function generate(Request $request)
    {
        // ── 1. Validasi & resolve periode ──
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

            // ── 2. Validasi store target & ambil store_ids ──
            $stores = Stores::whereIn('name', self::TARGET_STORES)->get();

            if ($stores->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada store target yang ditemukan. '
                               . 'Pastikan store Head Office, Holding, dan Distribution Center ada di database.',
                ], 422);
            }

            $storeIds = $stores->pluck('id')->toArray();

            // ── 3. Ambil semua karyawan di store target ──
            $employees = Employee::with('store:id,name')
                ->select('id', 'employee_name', 'store_id', 'religion')
                ->whereIn('store_id', $storeIds)
                ->whereNull('deleted_at')
                ->get();

            if ($employees->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada karyawan di store target.',
                ], 422);
            }

            // ── 4. Pre-load shifts per store ──
            $shifts = Shifts::whereIn('store_id', $storeIds)
                ->whereIn('shift_name', [self::SHIFT_WEEKDAY, self::SHIFT_SATURDAY])
                ->get()
                ->keyBy(fn($s) => $s->store_id . '_' . $s->shift_name);

            // Validasi: tiap store harus punya kedua shift
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
                    'message' => 'Shift belum di-setup untuk: ' . implode(', ', $missingShifts)
                               . '. Setup shift di halaman Shifts terlebih dahulu.',
                ], 422);
            }

            // ── 5. Pre-load Public Holiday dalam range (semua type) ──
            $allPublicHolidays = PublicHoliday::whereBetween('date', [
                    $startDate->toDateString(),
                    $endDate->toDateString(),
                ])
                ->get()
                ->groupBy(fn($ph) => Carbon::parse($ph->date)->toDateString());

            // ── 6. Pre-load existing rosters (untuk SKIP) ──
            $employeeIds = $employees->pluck('id')->toArray();

            $existingRosters = Roster::whereIn('employee_id', $employeeIds)
                ->whereBetween('date', [
                    $startDate->toDateString(),
                    $endDate->toDateString(),
                ])
                ->get()
                ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

            // ── 7. Generate daftar tanggal ──
            $dates = [];
            for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
                $dates[] = $d->copy();
            }

            // ── 8. Loop generate ──
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

                $relevantPhTypes = $this->resolveRelevantPhTypes($employee->religion);

                foreach ($dates as $date) {
                    $dateStr = $date->toDateString();
                    $key     = $employee->id . '_' . $dateStr;

                    // SKIP jika sudah ada
                    if ($existingRosters->has($key)) {
                        $skipped++;
                        continue;
                    }

                    // Cek PH untuk agama karyawan ini
                    $phForDate = null;
                    if ($allPublicHolidays->has($dateStr)) {
                        $phForDate = $allPublicHolidays->get($dateStr)
                            ->first(fn($ph) => in_array($ph->type, $relevantPhTypes));
                    }

                    // Tentukan day_type, shift_id, notes
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

            // ── 9. Response ──
            $message = "Berhasil generate {$created} roster";
            if ($skipped > 0) {
                $message .= " ({$skipped} dilewati karena sudah ada)";
            }
            if ($phApplied > 0) {
                $message .= ", {$phApplied} hari libur nasional diterapkan (sesuai agama masing-masing karyawan)";
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

    /**
     * Preview periode tanpa generate (untuk konfirmasi UI).
     *
     * Query string (opsional):
     *   start_date : string YYYY-MM-DD
     *   end_date   : string YYYY-MM-DD
     */
    public function preview(Request $request)
    {
        // Validasi & resolve periode
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
            // Jumlah karyawan target per agama
            $employeeCounts = Employee::query()
                ->whereHas('store', fn($q) => $q->whereIn('name', self::TARGET_STORES))
                ->whereNull('deleted_at')
                ->select('religion', DB::raw('COUNT(*) as count'))
                ->groupBy('religion')
                ->get();

            $totalEmployees = $employeeCounts->sum('count');
            $hinduCount     = $employeeCounts->where('religion', 'Hindu')->sum('count');
            $nonHinduCount  = $totalEmployees - $hinduCount;

            // Total hari & PH dalam periode
            $totalDates = $startDate->diffInDays($endDate) + 1;

            $phByType = PublicHoliday::query()
                ->whereBetween('date', [
                    $startDate->toDateString(),
                    $endDate->toDateString(),
                ])
                ->select('type', DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type');

            // Hitung existing roster (sudah ada) untuk estimasi yang akan di-skip
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
                    'start_date'      => $startDate->toDateString(),
                    'end_date'        => $endDate->toDateString(),
                    'is_custom_period'=> $override !== null,
                    'total_dates'     => $totalDates,
                    'total_employees' => $totalEmployees,
                    'employees_by_religion' => [
                        'Hindu'     => $hinduCount,
                        'Non Hindu' => $nonHinduCount,
                    ],
                    'public_holidays_by_type' => $phByType,
                    'estimated_rows'  => $estimatedRows,
                    'existing_rosters'=> $existingCount,
                    'will_be_created' => max(0, $estimatedRows - $existingCount),
                    'will_be_skipped' => $existingCount,
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