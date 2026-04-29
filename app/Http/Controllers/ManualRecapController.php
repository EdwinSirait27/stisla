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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ManualRecapController extends Controller
{
    private const MANUAL_SN = '616231023292867';
    private const SYNC_STATUS_MANUAL = 'Manual';

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
            'employee_ids.required' => 'Pilih minimal 1 karyawan.',
            'employee_ids.array'    => 'Format daftar karyawan tidak valid.',
            'employee_ids.min'      => 'Pilih minimal 1 karyawan.',
            'employee_ids.*.exists' => 'Salah satu karyawan yang dipilih tidak ditemukan.',
            'scan_date.required'      => 'Scan Date wajib diisi.',
            'scan_date.date'          => 'Format Scan Date tidak valid.',
            'end_date.required'       => 'End Date wajib diisi.',
            'end_date.date'           => 'Format End Date tidak valid.',
            'end_date.after_or_equal' => 'End Date tidak boleh sebelum Scan Date.',
            'reason.required' => 'Alasan wajib diisi.',
            'reason.string'   => 'Alasan harus berupa teks.',
            'reason.min'      => 'Alasan minimal 10 karakter.',
            'reason.max'      => 'Alasan maksimal 1000 karakter.',
            'evidence_files.required' => 'Upload minimal 1 file bukti.',
            'evidence_files.array'    => 'Format file bukti tidak valid.',
            'evidence_files.min'      => 'Upload minimal 1 file bukti.',
            'evidence_files.*.file'   => 'File bukti tidak valid.',
            'evidence_files.*.mimes'  => 'Tipe file tidak diizinkan. Hanya JPG, PNG, GIF, WEBP, PDF, DOC, DOCX, XLS, XLSX.',
            'evidence_files.*.max'    => 'Ukuran file maksimal 5 MB per file.',
        ]);

        // ── Resolve shift jika dipilih manual ──
        $shiftTimeIn   = null;
        $shiftTimeOut  = null;
        $shiftDuration = null;

        if ($request->shift_id) {
            $shift = Shifts::find($request->shift_id);
            if ($shift) {
                $shiftTimeIn  = $shift->start_time;
                $shiftTimeOut = $shift->end_time;
                if ($shiftTimeIn && $shiftTimeOut) {
                    $dtIn  = Carbon::parse($shiftTimeIn);
                    $dtOut = Carbon::parse($shiftTimeOut);
                    if ($dtOut->lt($dtIn)) $dtOut->addDay();
                    $shiftDuration = (int) $dtIn->diffInMinutes($dtOut);
                }
            }
        }

        // ── Generate range tanggal ──
        $scanDate = Carbon::parse($request->scan_date);
        $endDate  = Carbon::parse($request->end_date);
        $dates    = [];
        for ($d = $scanDate->copy(); $d->lte($endDate); $d->addDay()) {
            $dates[] = $d->toDateString();
        }

        // ── Upload file bukti dulu ──
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
            foreach ($uploadedFiles as $uf) {
                Storage::disk('public')->delete($uf['file_path']);
            }
            Log::error('ManualRecap: upload file gagal', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload file bukti: ' . $e->getMessage(),
            ], 500);
        }

        // ── Day type yang dianggap "masuk" (berbayar) ──
        $allowedDayTypes = ['Work', 'Leave', 'Cuti Melahirkan', 'Public Holiday'];

        $successCount = 0;
        $skippedCount = 0;
        $logCount     = 0;
        $errors       = [];

        try {
            DB::beginTransaction();

            foreach ($request->employee_ids as $employeeId) {
                $employee = Employee::with('store:id,name')->find($employeeId);

                if (!$employee) {
                    $errors[] = "Karyawan ID {$employeeId} tidak ditemukan";
                    continue;
                }

                foreach ($dates as $date) {
                    $resolvedTimeIn   = $shiftTimeIn;
                    $resolvedTimeOut  = $shiftTimeOut;
                    $resolvedDuration = $shiftDuration;

                    // ── ROSTER sebagai GATEKEEPER ──
                    $roster = Roster::with('shift')
                        ->where('employee_id', $employee->id)
                        ->where('date', $date)
                        ->first();

                    // Skip: tidak ada roster
                    if (!$roster) {
                        $skippedCount++;
                        Log::info("ManualRecap: skip {$employee->id} on {$date} — no roster");
                        continue;
                    }

                    // Skip: bukan hari yang dianggap masuk (Off, dll)
                    if (!in_array($roster->day_type, $allowedDayTypes)) {
                        $skippedCount++;
                        Log::info("ManualRecap: skip {$employee->id} on {$date} — day_type={$roster->day_type}");
                        continue;
                    }

                    // ── Tentukan waktu shift ──
                    if ($request->shift_id) {
                        // HR memilih shift manual → pakai waktu dari shift pilihan HR
                        Log::info("ManualRecap: {$employee->id} on {$date} — using manual shift_id={$request->shift_id}");
                    } else {
                        // Cuti & Public Holiday tidak butuh shift — time_in/out null, is_counted tetap 1
                        if (in_array($roster->day_type, ['Leave', 'Cuti Melahirkan', 'Public Holiday'])) {
                            $resolvedTimeIn   = null;
                            $resolvedTimeOut  = null;
                            $resolvedDuration = null;
                            Log::info("ManualRecap: {$employee->id} on {$date} — day_type={$roster->day_type}, no shift needed");
                        } elseif (!$roster->shift) {
                            // Work tapi shift belum diset di roster
                            $skippedCount++;
                            Log::info("ManualRecap: skip {$employee->id} on {$date} — roster Work but shift is null");
                            continue;
                        } else {
                            // Work → ambil shift dari roster
                            $resolvedTimeIn  = $roster->shift->start_time;
                            $resolvedTimeOut = $roster->shift->end_time;

                            if ($resolvedTimeIn && $resolvedTimeOut) {
                                $dtIn  = Carbon::parse($resolvedTimeIn);
                                $dtOut = Carbon::parse($resolvedTimeOut);
                                if ($dtOut->lt($dtIn)) $dtOut->addDay();
                                $resolvedDuration = (int) $dtIn->diffInMinutes($dtOut);
                            }

                            Log::info("ManualRecap: {$employee->id} on {$date} — using roster shift={$roster->shift->shift_name}");
                        }
                    }

                    // ── 1. Insert ke manual_added (mysql_second) ──
                    DB::connection('mysql_second')
                        ->table('manual_added')
                        ->where('pin', $employee->pin)
                        ->whereDate('scan_date', $date)
                        ->delete();

                    if ($resolvedTimeIn) {
                        DB::connection('mysql_second')->table('manual_added')->insert([
                            'sn'         => self::MANUAL_SN,
                            'scan_date'  => $date . ' ' . $resolvedTimeIn,
                            'pin'        => $employee->pin,
                            'verifymode' => 0,
                            'inoutmode'  => 1,
                            'reserved'   => 0,
                            'work_code'  => 0,
                        ]);
                    }

                    if ($resolvedTimeOut) {
                        DB::connection('mysql_second')->table('manual_added')->insert([
                            'sn'         => self::MANUAL_SN,
                            'scan_date'  => $date . ' ' . $resolvedTimeOut,
                            'pin'        => $employee->pin,
                            'verifymode' => 0,
                            'inoutmode'  => 2,
                            'reserved'   => 0,
                            'work_code'  => 0,
                        ]);
                    }

                    // ── 2. Update/Insert fingerprints_recap ──
                    FingerprintRecap::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'date'        => $date,
                        ],
                        [
                            'pin'              => $employee->pin,
                            'time_in'          => $resolvedTimeIn,
                            'time_out'         => $resolvedTimeOut,
                            'duration_minutes' => $resolvedDuration,
                            'is_counted'       => 1,
                            'device_sn'        => self::MANUAL_SN,
                            'sync_status'      => self::SYNC_STATUS_MANUAL,
                            'synced_at'        => now(),
                        ]
                    );

                    $successCount++;

                    // ── 3. Insert ke manual_recap_logs ──
                    foreach ($uploadedFiles as $fileData) {
                        ManualRecapLog::create([
                            'employee_id' => $employee->id,
                            'reason'      => $request->reason,
                            'file_name'   => $fileData['file_name'],
                            'file_path'   => $fileData['file_path'],
                            'mime_type'   => $fileData['mime_type'],
                            'file_size'   => $fileData['file_size'],
                        ]);
                        $logCount++;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success'   => true,
                'message'   => "Berhasil menambah manual recap untuk {$successCount} entri "
                             . "({$skippedCount} tanggal dilewati karena Off/Libur/tidak ada roster) "
                             . "dengan {$logCount} log file bukti.",
                'count'     => $successCount,
                'skipped'   => $skippedCount,
                'log_count' => $logCount,
                'errors'    => $errors,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            foreach ($uploadedFiles as $uf) {
                Storage::disk('public')->delete($uf['file_path']);
            }

            Log::error('ManualRecap: store ERROR', [
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