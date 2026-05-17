<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\FingerprintRecap;
use App\Models\ManualRecapLog;
use App\Models\Roster;
use App\Models\Shifts;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; 

class ManualRecapController extends Controller
{
    private const MANUAL_SN          = '616231023292867';
    private const SYNC_STATUS_MANUAL = 'Manual';
    private const MAX_BATCH_SIZE     = 20000;
    private const LOCK_DURATION      = 300;
    private const CHUNK_SIZE         = 1000;

    /**
     * PKWT → Work, Leave, Cuti Melahirkan, Public Holiday
     * OJT  → Work, Public Holiday
     * DW   → Work saja
     */
    private function allowedDayTypes(?string $statusEmployee): array
    {
        $status = strtoupper($statusEmployee ?? '');

        if ($status === 'DW') {
            return ['Work'];
        }

        if ($status === 'On Job Training') {
            return ['Work', 'Public Holiday'];
        }

        return ['Work', 'Leave', 'Cuti Melahirkan', 'Public Holiday'];
    }

    private function calculateShiftDuration(?string $timeIn, ?string $timeOut): ?int
    {
        if (!$timeIn || !$timeOut) {
            return null;
        }

        $dtIn  = Carbon::parse($timeIn);
        $dtOut = Carbon::parse($timeOut);

        if ($dtOut->lt($dtIn)) {
            $dtOut->addDay();
        }

        return (int) $dtIn->diffInMinutes($dtOut);
    }

    public function shiftList()
    {
        $shifts = Shifts::select('id', 'shift_name', 'start_time', 'end_time')
            ->orderBy('shift_name')
            ->get()
            ->map(fn($s) => [
                'id'   => $s->id,
                'name' => $s->shift_name,
                'time' => $s->start_time . ' - ' . $s->end_time,
            ]);

        return response()->json(['data' => $shifts]);
    }

    public function store(Request $request)
    {
        set_time_limit(180);
        ini_set('memory_limit', '512M');

        $request->validate([
            'employee_ids'     => 'required|array|min:1',
            'employee_ids.*'   => 'exists:employees_tables,id',
            'scan_date'        => 'required|date',
            'end_date'         => 'required|date|after_or_equal:scan_date',
            'shift_id'         => 'nullable|exists:shifts_tables,id',
            'reason'           => 'required|string|min:10|max:1000',
            'evidence_files'   => 'required|array|min:1',
            'evidence_files.*' => 'file|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx|max:5120',
        ], [
            'employee_ids.required'   => 'Pilih minimal 1 karyawan.',
            'employee_ids.array'      => 'Format daftar karyawan tidak valid.',
            'employee_ids.min'        => 'Pilih minimal 1 karyawan.',
            'employee_ids.*.exists'   => 'Salah satu karyawan yang dipilih tidak ditemukan.',
            'scan_date.required'      => 'Scan Date wajib diisi.',
            'scan_date.date'          => 'Format Scan Date tidak valid.',
            'end_date.required'       => 'End Date wajib diisi.',
            'end_date.date'           => 'Format End Date tidak valid.',
            'end_date.after_or_equal' => 'End Date tidak boleh sebelum Scan Date.',
            'reason.required'         => 'Alasan wajib diisi.',
            'reason.string'           => 'Alasan harus berupa teks.',
            'reason.min'              => 'Alasan minimal 10 karakter.',
            'reason.max'              => 'Alasan maksimal 1000 karakter.',
            'evidence_files.required' => 'Upload minimal 1 file bukti.',
            'evidence_files.array'    => 'Format file bukti tidak valid.',
            'evidence_files.min'      => 'Upload minimal 1 file bukti.',
            'evidence_files.*.file'   => 'File bukti tidak valid.',
            'evidence_files.*.mimes'  => 'Tipe file tidak diizinkan.',
            'evidence_files.*.max'    => 'Ukuran file maksimal 5 MB per file.',
        ]);

        $scanDate = Carbon::parse($request->scan_date);
        $endDate  = Carbon::parse($request->end_date);
        $dates    = [];
        for ($d = $scanDate->copy(); $d->lte($endDate); $d->addDay()) {
            $dates[] = $d->toDateString();
        }

        $totalIterations = count($request->employee_ids) * count($dates);
        if ($totalIterations > self::MAX_BATCH_SIZE) {
            return response()->json([
                'success' => false,
                'message' => 'Batch terlalu besar (' . number_format($totalIterations) . ' entri). '
                           . 'Maksimal ' . number_format(self::MAX_BATCH_SIZE) . ' entri per submit.',
            ], 422);
        }

        $userId  = Auth::id() ?? 'guest';
        $lockKey = "manual_recap_lock_{$userId}";

        if (Cache::has($lockKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Submit sebelumnya masih diproses. Silakan tunggu sebentar.',
            ], 429);
        }

        Cache::put($lockKey, true, self::LOCK_DURATION);

        $manualShiftTimeIn   = null;
        $manualShiftTimeOut  = null;
        $manualShiftDuration = null;

        if ($request->shift_id) {
            $shift = Shifts::find($request->shift_id);
            if ($shift) {
                $manualShiftTimeIn   = $shift->start_time;
                $manualShiftTimeOut  = $shift->end_time;
                $manualShiftDuration = $this->calculateShiftDuration($manualShiftTimeIn, $manualShiftTimeOut);
            }
        }

        // ── Upload file bukti dulu (di luar transaction) ──
        $uploadedFiles = [];
        try {
            foreach ($request->file('evidence_files') as $file) {
                $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $path     = $file->storeAs('manual-recap-evidences', $filename, 'public');

                $uploadedFiles[] = [
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ];
            }
        } catch (\Exception $e) {
            $this->cleanupFiles($uploadedFiles);
            Cache::forget($lockKey);
            Log::error('ManualRecap: upload file gagal', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload file bukti: ' . $e->getMessage(),
            ], 500);
        }

        // ─────────────────────────────────────────────────────────────
        //  CROSS-CONNECTION TRANSACTION TRACKING
        //  Track flag mana yang sudah started/committed untuk rollback aman
        // ─────────────────────────────────────────────────────────────
        $primaryStarted   = false;
        $secondStarted    = false;
        $primaryCommitted = false;
        $secondCommitted  = false;

        try {
            // ── Eager load semua data dulu ──
            $employees = Employee::with('store:id,name')
                ->select('id', 'pin', 'store_id', 'employee_name', 'status_employee')
                ->whereIn('id', $request->employee_ids)
                ->get()
                ->keyBy('id');

            if ($employees->isEmpty()) {
                $this->cleanupFiles($uploadedFiles);
                Cache::forget($lockKey);
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada karyawan valid yang ditemukan.',
                ], 422);
            }

            $employeeIds = $employees->pluck('id')->toArray();

            $rosters = Roster::with('shift:id,shift_name,start_time,end_time')
                ->whereIn('employee_id', $employeeIds)
                ->whereIn('date', $dates)
                ->get()
                ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

            // ── Build data di memory ──
            $manualAddedRows  = [];
            $fingerprintRows  = [];
            $manualLogRows    = [];
            $deleteManualKeys = [];

            $successCount = 0;
            $skippedCount = 0;
            $now          = now();

            foreach ($request->employee_ids as $employeeId) {
                $employee = $employees->get($employeeId);
                if (!$employee) {
                    Log::warning("ManualRecap: employee {$employeeId} tidak ditemukan, skip");
                    continue;
                }

                $allowedDayTypes = $this->allowedDayTypes($employee->status_employee);

                foreach ($dates as $date) {
                    $rosterKey = $employee->id . '_' . $date;
                    $roster    = $rosters->get($rosterKey);

                    if (!$roster) {
                        $skippedCount++;
                        continue;
                    }

                    if (!in_array($roster->day_type, $allowedDayTypes)) {
                        $skippedCount++;
                        continue;
                    }

                    $resolvedTimeIn   = null;
                    $resolvedTimeOut  = null;
                    $resolvedDuration = null;

                    if ($request->shift_id) {
                        $resolvedTimeIn   = $manualShiftTimeIn;
                        $resolvedTimeOut  = $manualShiftTimeOut;
                        $resolvedDuration = $manualShiftDuration;
                    } else {
                        if (in_array($roster->day_type, ['Leave', 'Cuti Melahirkan', 'Public Holiday'])) {
                            $resolvedTimeIn   = null;
                            $resolvedTimeOut  = null;
                            $resolvedDuration = null;
                        } elseif (!$roster->shift) {
                            $skippedCount++;
                            continue;
                        } else {
                            $resolvedTimeIn   = $roster->shift->start_time;
                            $resolvedTimeOut  = $roster->shift->end_time;
                            $resolvedDuration = $this->calculateShiftDuration($resolvedTimeIn, $resolvedTimeOut);
                        }
                    }

                    $deleteManualKeys[] = [
                        'pin'  => $employee->pin,
                        'date' => $date,
                    ];

                    if ($resolvedTimeIn) {
                        $manualAddedRows[] = [
                            'sn'         => self::MANUAL_SN,
                            'scan_date'  => $date . ' ' . $resolvedTimeIn,
                            'pin'        => $employee->pin,
                            'verifymode' => 0,
                            'inoutmode'  => 1,
                            'reserved'   => 0,
                            'work_code'  => 0,
                        ];
                    }

                    if ($resolvedTimeOut) {
                        $manualAddedRows[] = [
                            'sn'         => self::MANUAL_SN,
                            'scan_date'  => $date . ' ' . $resolvedTimeOut,
                            'pin'        => $employee->pin,
                            'verifymode' => 0,
                            'inoutmode'  => 2,
                            'reserved'   => 0,
                            'work_code'  => 0,
                        ];
                    }

                    $fingerprintRows[] = [
                        'id'               => (string) Str::uuid(),
                        'employee_id'      => $employee->id,
                        'date'             => $date,
                        'pin'              => $employee->pin,
                        'time_in'          => $resolvedTimeIn,
                        'time_out'         => $resolvedTimeOut,
                        'duration_minutes' => $resolvedDuration,
                        'is_counted'       => 1,
                        'device_sn'        => self::MANUAL_SN,
                        'sync_status'      => self::SYNC_STATUS_MANUAL,
                        'synced_at'        => $now,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];

                    foreach ($uploadedFiles as $fileData) {
                        $manualLogRows[] = [
                            'id'          => (string) Str::uuid(),
                            'employee_id' => $employee->id,
                            'reason'      => $request->reason,
                            'file_name'   => $fileData['file_name'],
                            'file_path'   => $fileData['file_path'],
                            'mime_type'   => $fileData['mime_type'],
                            'file_size'   => $fileData['file_size'],
                            'created_at'  => $now,
                            'updated_at'  => $now,
                        ];
                    }

                    $successCount++;
                }
            }

            if ($successCount === 0) {
                $this->cleanupFiles($uploadedFiles);
                Cache::forget($lockKey);
                return response()->json([
                    'success' => false,
                    'message' => "Tidak ada entri valid untuk diproses. {$skippedCount} tanggal dilewati.",
                    'skipped' => $skippedCount,
                ], 422);
            }

            // ═══════════════════════════════════════════════════════════════
            //  BEGIN TRANSACTION DI KEDUA CONNECTION
            // ═══════════════════════════════════════════════════════════════

            DB::beginTransaction();
            $primaryStarted = true;

            DB::connection('mysql_second')->beginTransaction();
            $secondStarted = true;

            // ── 1. Bulk DELETE manual_added existing (mysql_second) ──
            $deletePinsByDate = [];
            foreach ($deleteManualKeys as $key) {
                $deletePinsByDate[$key['date']][] = $key['pin'];
            }

            foreach ($deletePinsByDate as $date => $pins) {
                DB::connection('mysql_second')
                    ->table('manual_added')
                    ->whereIn('pin', array_unique($pins))
                    ->whereDate('scan_date', $date)
                    ->delete();
            }

            // ── 2. Bulk INSERT manual_added (mysql_second) ──
            foreach (array_chunk($manualAddedRows, self::CHUNK_SIZE) as $chunk) {
                DB::connection('mysql_second')->table('manual_added')->insert($chunk);
            }

            // ── 3. Bulk UPSERT fingerprints_recap (mysql utama) ──
            foreach (array_chunk($fingerprintRows, self::CHUNK_SIZE) as $chunk) {
                FingerprintRecap::upsert(
                    $chunk,
                    ['employee_id', 'date'],
                    [
                        'pin', 'time_in', 'time_out', 'duration_minutes',
                        'is_counted', 'device_sn', 'sync_status', 'synced_at', 'updated_at',
                    ]
                );
            }

            // ── 4. Bulk INSERT manual_recap_logs (mysql utama) ──
            foreach (array_chunk($manualLogRows, self::CHUNK_SIZE) as $chunk) {
                DB::table('manual_recap_logs')->insert($chunk);
            }

            // ═══════════════════════════════════════════════════════════════
            //  COMMIT BERURUTAN
            //  Order: mysql utama dulu (yang dipakai untuk perhitungan),
            //         baru mysql_second (yang hanya untuk display).
            //
            //  Kalau commit utama gagal → mysql_second masih bisa rollback
            //  Kalau commit mysql_second gagal → log critical untuk recovery
            // ═══════════════════════════════════════════════════════════════

            DB::commit();
            $primaryCommitted = true;

            DB::connection('mysql_second')->commit();
            $secondCommitted = true;

            Cache::forget($lockKey);

            $logCount = count($manualLogRows);

            Log::info('ManualRecap: berhasil', [
                'success_count' => $successCount,
                'skipped_count' => $skippedCount,
                'log_count'     => $logCount,
                'user_id'       => $userId,
            ]);

            return response()->json([
                'success'   => true,
                'message'   => "Berhasil menambah manual recap untuk {$successCount} entri "
                             . "({$skippedCount} tanggal dilewati karena Off/Libur/tidak ada roster/status tidak diizinkan) "
                             . "dengan {$logCount} log file bukti.",
                'count'     => $successCount,
                'skipped'   => $skippedCount,
                'log_count' => $logCount,
            ]);

        } catch (\Exception $e) {
            // ═══════════════════════════════════════════════════════════════
            //  SAFE ROLLBACK
            //  Hanya rollback yang sudah started & belum committed
            // ═══════════════════════════════════════════════════════════════

            if ($primaryStarted && !$primaryCommitted) {
                try {
                    DB::rollBack();
                } catch (\Exception $rollbackErr) {
                    Log::error('ManualRecap: gagal rollback mysql utama', [
                        'error' => $rollbackErr->getMessage(),
                    ]);
                }
            }

            if ($secondStarted && !$secondCommitted) {
                try {
                    DB::connection('mysql_second')->rollBack();
                } catch (\Exception $rollbackErr) {
                    Log::error('ManualRecap: gagal rollback mysql_second', [
                        'error' => $rollbackErr->getMessage(),
                    ]);
                }
            }

            // ⚠️ EDGE CASE: mysql utama sudah committed, tapi mysql_second gagal
            // Data fingerprints_recap sudah ada (yang penting untuk perhitungan),
            // tapi manual_added belum sinkron. Log critical untuk admin.
            if ($primaryCommitted && !$secondCommitted) {
                Log::critical('ManualRecap: INKONSISTENSI DATA — mysql utama committed, mysql_second gagal', [
                    'user_id'         => $userId,
                    'employee_ids'    => $request->employee_ids,
                    'scan_date'       => $request->scan_date,
                    'end_date'        => $request->end_date,
                    'error'           => $e->getMessage(),
                    'action_required' => 'Cek manual_added di mysql_second & re-sync jika perlu',
                ]);
            }

            $this->cleanupFiles($uploadedFiles);
            Cache::forget($lockKey);

            Log::error('ManualRecap: store ERROR', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function cleanupFiles(array $uploadedFiles): void
    {
        foreach ($uploadedFiles as $uf) {
            try {
                Storage::disk('public')->delete($uf['file_path']);
            } catch (\Exception $e) {
                Log::warning('ManualRecap: gagal hapus file ' . $uf['file_path'], [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}