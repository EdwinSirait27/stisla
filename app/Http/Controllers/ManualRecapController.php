<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\FingerprintRecap;
use App\Models\ManualRecapLog;
use App\Models\ManualRecapEvidence;
use App\Models\Roster;
use App\Models\Shifts;
use App\Jobs\SendWhatsAppNotificationJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Controller untuk fitur "+Add Recap" (Manual Recap Absensi).
 *
 * Dipakai ketika HR ingin "memaksa" absensi karyawan yang tidak scan
 * (karena lupa, mesin error, sudah klarifikasi, dll) dengan melampirkan
 * bukti pendukung yang bisa dipertanggungjawabkan.
 *
 * Endpoints:
 *   GET  /manual-recap/hr-list    → hrList()    — dropdown HR (legacy)
 *   GET  /manual-recap/shift-list → shiftList() — dropdown Shift
 *   POST /manual-recap            → store()     — submit manual recap
 *
 * Future (kalau mau ditambah):
 *   GET  /manual-recap           → index()   — halaman riwayat
 *   GET  /manual-recap/{id}      → show()    — detail log
 *   DELETE /manual-recap/{id}    → destroy() — hapus log
 */
class ManualRecapController extends Controller
{
    /**
     * Menampilkan daftar HR untuk dropdown (legacy, tidak dipakai di form baru).
     * Filter: karyawan di store Holding / Head Office.
     */
    public function hrList()
    {
        $hrList = Employee::with('position:id,name', 'store:id,name')
            ->select('id', 'employee_name', 'position_id', 'store_id', 'pin')
            ->whereNull('deleted_at')
            ->whereHas('store', fn($q) =>
                $q->whereIn('name', ['Holding', 'Head Office'])
            )
            ->orderBy('employee_name')
            ->get()
            ->map(fn($e) => [
                'id'       => $e->id,
                'name'     => $e->employee_name,
                'position' => $e->position->name ?? '-',
                'store'    => $e->store->name ?? '-',
            ]);

        return response()->json(['data' => $hrList]);
    }

    /**
     * Menampilkan daftar shift untuk dropdown di modal "+Add Recap".
     */
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

    /**
     * Menyimpan manual recap absensi dengan file bukti.
     *
     * Request body (form-data, karena ada file):
     *   employee_ids[]   : array (wajib, min 1) — karyawan yang di-override
     *   scan_date        : date  (wajib) — tanggal mulai
     *   end_date         : date  (wajib) — tanggal selesai
     *   shift_id         : uuid  (opsional) — jika kosong ambil dari roster
     *   reason           : string (wajib, min 10 char)
     *   evidence_files[] : file[] (wajib, min 1) — bukti pendukung
     *
     * Efek:
     *   1. Update/Insert fingerprints_recap dengan is_counted = 1
     *   2. Simpan audit trail di manual_recap_logs
     *   3. Simpan file bukti di storage + manual_recap_evidences
     *   4. (Pending) Dispatch notifikasi WhatsApp & Email
     */
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
            // ── Karyawan ──
            'employee_ids.required' => 'Pilih minimal 1 karyawan.',
            'employee_ids.array'    => 'Format daftar karyawan tidak valid.',
            'employee_ids.min'      => 'Pilih minimal 1 karyawan.',
            'employee_ids.*.exists' => 'Salah satu karyawan yang dipilih tidak ditemukan.',

            // ── Tanggal ──
            'scan_date.required'      => 'Scan Date wajib diisi.',
            'scan_date.date'          => 'Format Scan Date tidak valid.',
            'end_date.required'       => 'End Date wajib diisi.',
            'end_date.date'           => 'Format End Date tidak valid.',
            'end_date.after_or_equal' => 'End Date tidak boleh sebelum Scan Date.',

            // ── Alasan ──
            'reason.required' => 'Alasan wajib diisi.',
            'reason.string'   => 'Alasan harus berupa teks.',
            'reason.min'      => 'Alasan minimal 10 karakter.',
            'reason.max'      => 'Alasan maksimal 1000 karakter.',

            // ── Evidence Files ──
            'evidence_files.required' => 'Upload minimal 1 file bukti.',
            'evidence_files.array'    => 'Format file bukti tidak valid.',
            'evidence_files.min'      => 'Upload minimal 1 file bukti.',
            'evidence_files.*.file'   => 'File bukti tidak valid.',
            'evidence_files.*.mimes'  => 'Tipe file tidak diizinkan. Hanya JPG, PNG, GIF, WEBP, PDF, DOC, DOCX, XLS, XLSX.',
            'evidence_files.*.max'    => 'Ukuran file maksimal 5 MB per file.',
        ]);

        // ── Ambil HR dari user yang sedang login ──
        $hr = Auth::user()->Employee;
        if (!$hr) {
            return response()->json([
                'success' => false,
                'message' => 'Data HR tidak ditemukan. Pastikan akun Anda terhubung ke data karyawan.',
            ], 422);
        }

        // ── Ambil data shift jika dipilih manual ──
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

        // ── Upload file bukti dulu (sebelum masuk transaksi DB) ──
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

        // ── Proses simpan data dalam transaksi ──
        $successCount = 0;
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

                    // Jika shift tidak dipilih manual → ambil dari roster
                    if (!$request->shift_id) {
                        $roster = Roster::with('shift')
                            ->where('employee_id', $employee->id)
                            ->where('date', $date)
                            ->first();

                        if ($roster && $roster->shift) {
                            $resolvedTimeIn  = $roster->shift->start_time;
                            $resolvedTimeOut = $roster->shift->end_time;
                            if ($resolvedTimeIn && $resolvedTimeOut) {
                                $dtIn  = Carbon::parse($resolvedTimeIn);
                                $dtOut = Carbon::parse($resolvedTimeOut);
                                if ($dtOut->lt($dtIn)) $dtOut->addDay();
                                $resolvedDuration = (int) $dtIn->diffInMinutes($dtOut);
                            }
                        }
                    }

                    // 1. Update/Insert fingerprints_recap (paksa hadir)
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
                            'device_sn'        => null,
                            'sync_status'      => 'Manual',
                            'synced_at'        => now(),
                        ]
                    );

                    // 2. Simpan audit trail
                    $log = ManualRecapLog::create([
                        'employee_id'  => $employee->id,
                        'pin'          => $employee->pin,
                        'date'         => $date,
                        'time_in'      => $resolvedTimeIn,
                        'time_out'     => $resolvedTimeOut,
                        'reason'       => $request->reason,
                        'hr_id'        => $hr->id,
                        'hr_name'      => $hr->employee_name,
                        'submitted_at' => now(),
                    ]);

                    // 3. Simpan file bukti (1:many)
                    foreach ($uploadedFiles as $fileData) {
                        ManualRecapEvidence::create([
                            'manual_recap_log_id' => $log->id,
                            'file_name'           => $fileData['file_name'],
                            'file_path'           => $fileData['file_path'],
                            'mime_type'           => $fileData['mime_type'],
                            'file_size'           => $fileData['file_size'],
                        ]);
                    }

                    // 4. Dispatch notification (aktivasi setelah provider siap)
                    // SendWhatsAppNotificationJob::dispatch($log);
                    // SendEmailNotificationJob::dispatch($log);

                    $successCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menambah manual recap untuk {$successCount} entri. "
                           . "Data akan dilaporkan ke Head HR & IT.",
                'count'   => $successCount,
                'errors'  => $errors,
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