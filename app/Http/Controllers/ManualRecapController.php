<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Fingerprintrecap;
use App\Models\Payroll;
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
     * Manual recap hanya untuk hari kerja (roster Work), untuk semua status karyawan.
     * - Leave / Cuti Melahirkan / Sick / PH / TOIL Off sudah dibayar otomatis oleh
     *   FingerprintRecapCalculator dari roster, jadi tidak perlu kehadiran manual.
     * - Kehadiran manual di hari PH justru berbahaya: dianggap "sudah masuk di hari PH"
     *   sehingga saldo PH pengganti bisa sah tanpa benar-benar bekerja.
     */
    private const ALLOWED_DAY_TYPES = ['Work'];

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
        $shifts = Shifts::with('store')->select('id', 'store_id','shift_name', 'start_time', 'end_time', 'is_split', 'break_start', 'break_end')
            ->orderBy('shift_name')
            ->get()
            ->map(fn($s) => [
                'id'   => $s->id,
                'name' => $s->shift_name,
                'store_name' => $s->store->name,
                'time' => $s->isSplit() ? str_replace('-', ' - ', $s->time_label) : $s->start_time . ' - ' . $s->end_time,
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
            // Kehadiran manual hanya untuk hari yang sudah terjadi
            'end_date'         => 'required|date|after_or_equal:scan_date|before_or_equal:today',
            'shift_id'         => 'nullable|exists:shifts_tables,id',
            'reason'           => 'nullable|string|min:1|max:1000',
            'evidence_files'   => 'required|array|min:1',
            'evidence_files.*' => 'file|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx|max:512',
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
            'end_date.before_or_equal' => 'Manual recap tidak bisa untuk tanggal yang belum terjadi (maksimal hari ini).',
            'reason.required'         => 'Alasan wajib diisi.',
            'reason.string'           => 'Alasan harus berupa teks.',
            'evidence_files.required' => 'Upload minimal 1 file bukti.',
            'evidence_files.array'    => 'Format file bukti tidak valid.',
            'evidence_files.min'      => 'Upload minimal 1 file bukti.',
            'evidence_files.*.file'   => 'File bukti tidak valid.',
            'evidence_files.*.mimes'  => 'Tipe file tidak diizinkan.',
            'evidence_files.*.max'    => 'Ukuran file maksimal 512 kb per file.',
        ]);


// 1. Generate dates
$scanDate = Carbon::parse($request->scan_date);
$endDate  = Carbon::parse($request->end_date);
$dates    = [];
for ($d = $scanDate->copy(); $d->lte($endDate); $d->addDay()) {
    $dates[] = $d->toDateString();
}

// 2. Cek batch size
$totalIterations = count($request->employee_ids) * count($dates);
if ($totalIterations > self::MAX_BATCH_SIZE) {
    return response()->json([
        'success' => false,
        'message' => 'Warning (' . number_format($totalIterations) . ' entri). '
                   . 'Maksimal ' . number_format(self::MAX_BATCH_SIZE) . ' entri per submit.',
    ], 422);
}

// 2b. Tolak kalau payroll periode ini sudah diproses (bukan draft) atau periodenya
//     sudah closed/locked: kehadiran sudah dibayar dan tidak boleh berubah diam-diam.
$lockedPayrolls = Payroll::with('employee:id,employee_name')
    ->whereIn('employee_id', $request->employee_ids)
    ->whereDate('period_start', '<=', $endDate->toDateString())
    ->whereDate('period_end', '>=', $scanDate->toDateString())
    ->where(function ($q) {
        $q->where('status', '!=', 'draft')
            ->orWhereHas('period', fn($p) => $p->where('status', '!=', 'open'));
    })
    ->get();

if ($lockedPayrolls->isNotEmpty()) {
    $names = $lockedPayrolls
        ->map(fn($p) => ($p->employee?->employee_name ?? $p->employee_id)
            . ' (' . Carbon::parse($p->period_start)->format('d M') . ' - ' . Carbon::parse($p->period_end)->format('d M Y')
            . ', ' . $p->status . ')')
        ->implode(', ');

    return response()->json([
        'success' => false,
        'message' => 'Payroll periode ini sudah diproses atau periodenya sudah ditutup, manual recap tidak bisa ditambahkan: ' . $names . '.',
    ], 422);
}

// 3. Lock
$userId  = Auth::id() ?? 'guest';
$lockKey = "manual_recap_lock_{$userId}";

if (Cache::has($lockKey)) {
    return response()->json([
        'success' => false,
        'message' => 'Submit sebelumnya masih diproses. Silakan tunggu sebentar.',
    ], 429);
}

Cache::put($lockKey, true, self::LOCK_DURATION);

// 4. Load employees
$employees = Employee::with([
    'store' => fn($q) => $q->wherePivot('is_primary', true),
])
    ->select('id', 'pin', 'employee_name', 'status_employee')
    ->whereIn('id', $request->employee_ids)
    ->get()
    ->keyBy('id');

if ($employees->isEmpty()) {
    Cache::forget($lockKey);
    return response()->json([
        'success' => false,
        'message' => 'Tidak ada karyawan valid yang ditemukan.',
    ], 422);
}

$employeeIds = $employees->pluck('id')->toArray();

// 5. Cek att_log SEBELUM upload
$pinsByDate = [];
foreach ($request->employee_ids as $employeeId) {
    $employee = $employees->get($employeeId);
    if (!$employee || !$employee->pin) continue;
    foreach ($dates as $date) {
        $pinsByDate[$date][] = $employee->pin;
    }
}

$existingAttLogs = collect();
foreach ($pinsByDate as $date => $pins) {
    $found = DB::connection('mysql_second')
        ->table('att_log')
        ->whereIn('pin', array_unique($pins))
        ->whereDate('scan_date', $date)
        ->pluck('pin')
        ->toArray();
    foreach ($found as $pin) {
        $existingAttLogs->push(['pin' => $pin, 'date' => $date]);
    }
}

if ($existingAttLogs->isNotEmpty()) {
    $pinToName = $employees->mapWithKeys(fn($emp) => [$emp->pin => $emp->employee_name]);
    $details   = $existingAttLogs->map(fn($row) =>
        ($pinToName[$row['pin']] ?? $row['pin']) . ' — ' . $row['date']
    )->implode(', ');

    Cache::forget($lockKey);
    return response()->json([
        'success' => false,
        'message' => 'Ada karyawan sudah memiliki data absensi di tanggal tersebut: ' . $details . '.',
    ], 422);
}

// 6. Cek fingerprintrecap SEBELUM upload
$existingRecaps = Fingerprintrecap::whereIn('employee_id', $employeeIds)
    ->whereIn('date', $dates)
    ->get(['employee_id', 'date']);

if ($existingRecaps->isNotEmpty()) {
    $details = $existingRecaps->map(fn($row) =>
        ($employees->get($row->employee_id)?->employee_name ?? $row->employee_id)
        . ' — ' . $row->date
    )->implode(', ');

    Cache::forget($lockKey);
    return response()->json([
        'success' => false,
        'message' => 'Data fingerprint sudah ada untuk: ' . $details,
    ], 422);
}

// 7. Resolve shift
$manualShiftTimeIn   = null;
$manualShiftTimeOut  = null;
$manualShiftDuration = null;
$manualShift         = null;

if ($request->shift_id) {
    $shift = Shifts::find($request->shift_id);
    $manualShift = $shift;
    if ($shift) {
        $manualShiftTimeIn   = $shift->start_time;
        $manualShiftTimeOut  = $shift->end_time;
        $manualShiftDuration = $this->calculateShiftDuration($manualShiftTimeIn, $manualShiftTimeOut);
    }
}

// 8. Baru upload file (semua validasi sudah lolos)
$uploadedFiles = [];
try {
    $scanDateStr     = $scanDate->toDateString();
    $endDateStr      = $endDate->toDateString();
    $firstEmployeeId = $request->employee_ids[0] ?? null;
    $firstEmployee   = $firstEmployeeId ? $employees->get($firstEmployeeId) : null;
    $empSlug         = $firstEmployee ? Str::slug($firstEmployee->employee_name) : 'employee';

    foreach ($request->file('evidence_files') as $index => $file) {
        // Gambar dikompres & dibersihkan, dokumen apa adanya; ekstensi dari isi file
        $folder   = 'manual-recap-evidence';
        $path     = app(\App\Services\UploadStorage::class)->put(
            $file, $folder, $empSlug . '-' . $scanDateStr . '-' . $endDateStr . '-' . ($index + 1),
            ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx'], 's3', false, 'evidence_files'
        );
        $fileName = basename($path);

        Log::info('[ManualRecap] Upload evidence', [
            'original_name' => $file->getClientOriginalName(),
            'file_name'     => $fileName,
            'path'          => $path,
            'size'          => $file->getSize(),
            'mime'          => $file->getMimeType(),
        ]);

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
            $employees = Employee::with([
    'store' => fn($q) => $q->wherePivot('is_primary', true), // ← pivot
])
                ->select('id', 'pin','employee_name', 'status_employee')
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

            $rosters = Roster::with('shift:id,shift_name,start_time,end_time,is_split,break_start,break_end')
                ->whereIn('employee_id', $employeeIds)
                ->whereIn('date', $dates)
                ->get()
                ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());


                // ── Cek existing di att_log (mysql_second) ──
$existingAttLogs = collect();
$deletePinsByDateCheck = [];
foreach ($request->employee_ids as $employeeId) {
    $employee = $employees->get($employeeId);
    if (!$employee || !$employee->pin) continue;
    foreach ($dates as $date) {
        $deletePinsByDateCheck[$date][] = $employee->pin;
    }
}

foreach ($deletePinsByDateCheck as $date => $pins) {
    $found = DB::connection('mysql_second')
        ->table('att_log')
        ->whereIn('pin', array_unique($pins))
        ->whereDate('scan_date', $date)
        ->pluck('pin')
        ->toArray();

    foreach ($found as $pin) {
        $existingAttLogs->push(['pin' => $pin, 'date' => $date]);
    }
}



if ($existingAttLogs->isNotEmpty()) {
    // Cari nama employee berdasarkan pin
    $pinToName = $employees->mapWithKeys(fn($emp) => [$emp->pin => $emp->employee_name]);

    $details = $existingAttLogs->map(fn($row) =>
        ($pinToName[$row['pin']] ?? $row['pin']) . ' — ' . $row['date']
    )->implode(', ');

    $this->cleanupFiles($uploadedFiles);
    Cache::forget($lockKey);

    return response()->json([
        'success' => false,
        'message' => 'Ada karyawan sudah memiliki data absensi di tanggal tersebut: ' . $details . '.',
    ], 422);
}


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

                foreach ($dates as $date) {
                    $rosterKey = $employee->id . '_' . $date;
                    $roster    = $rosters->get($rosterKey);
Log::info('ManualRecap debug', [
        'employee'    => $employee->employee_name,
        'date'        => $date,
        'roster'      => $roster?->toArray(),
        'day_type'    => $roster?->day_type,
        'allowed'     => self::ALLOWED_DAY_TYPES,
        'shift'       => $roster?->shift?->toArray(),
    ]);
                    if (!$roster) {
                        $skippedCount++;
                        continue;
                    }

                    if (!in_array($roster->day_type, self::ALLOWED_DAY_TYPES, true)) {
                        $skippedCount++;
                        continue;
                    }

                    $resolvedTimeIn   = null;
                    $resolvedTimeOut  = null;
                    $resolvedDuration = null;
                    $resolvedShift    = null;

                    if ($request->shift_id) {
                        $resolvedShift    = $manualShift;
                        $resolvedTimeIn   = $manualShiftTimeIn;
                        $resolvedTimeOut  = $manualShiftTimeOut;
                        $resolvedDuration = $manualShiftDuration;
                    } else {
                        if (!$roster->shift) {
                            $skippedCount++;
                            continue;
                        } else {
                            $resolvedShift    = $roster->shift;
                            $resolvedTimeIn   = $roster->shift->start_time;
                            $resolvedTimeOut  = $roster->shift->end_time;
                            $resolvedDuration = $this->calculateShiftDuration($resolvedTimeIn, $resolvedTimeOut);
                        }
                    }

                    // Split shift: empat scan (masuk/pulang tiap blok), durasi tanpa jeda
                    $isSplit          = $resolvedShift?->isSplit() ?? false;
                    $resolvedBreakOut = $isSplit ? $resolvedShift->break_start : null;
                    $resolvedBreakIn  = $isSplit ? $resolvedShift->break_end : null;
                    if ($isSplit) {
                        $resolvedDuration = $resolvedShift->scheduledMinutes();
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

                    if ($isSplit) {
                        foreach ([[$resolvedBreakOut, 2], [$resolvedBreakIn, 1]] as [$t, $mode]) {
                            $manualAddedRows[] = [
                                'sn'         => self::MANUAL_SN,
                                'scan_date'  => $date . ' ' . $t,
                                'pin'        => $employee->pin,
                                'verifymode' => 0,
                                'inoutmode'  => $mode,
                                'reserved'   => 0,
                                'work_code'  => 0,
                            ];
                        }
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
                        'break_out'        => $resolvedBreakOut,
                        'break_in'         => $resolvedBreakIn,
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
                Fingerprintrecap::upsert(
                    $chunk,
                    ['employee_id', 'date'],
                    [
                        'pin', 'time_in', 'break_out', 'break_in', 'time_out', 'duration_minutes',
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
                             . "({$skippedCount} tanggal dilewati karena roster bukan Work, tidak ada roster, atau Work tanpa shift) ",
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
            Storage::disk('s3')->delete($uf['file_path']); // ← ganti public ke s3
        } catch (\Exception $e) {
            Log::warning('ManualRecap: gagal hapus file ' . $uf['file_path'], [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
public function signedUrl(Request $request)
{
    $request->validate(['path' => 'required|string']);

    // Hanya file bukti manual recap yang tercatat di log. Tanpa ini endpoint bisa
    // membuat link untuk file S3 apa saja (slip gaji, bukti sakit, dokumen karyawan).
    $isEvidence = str_starts_with($request->path, 'manual-recap-evidence/')
        && ManualRecapLog::where('file_path', $request->path)->exists();

    if (!$isEvidence) {
        return response()->json(['message' => 'File bukti tidak ditemukan.'], 404);
    }

    $url = Storage::disk('s3')->temporaryUrl(
        $request->path,
        now()->addMinutes(30)
    );

    return response()->json(['url' => $url]);
}

}