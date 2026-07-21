<?php

namespace App\Http\Controllers;

use App\Models\Leaverequest;
use App\Models\Leavebalance;
use App\Models\Roster;
use App\Models\ToilLeaveRequests;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LeaverequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee->employee_name;
        return view('pages.Leaverequest.Leaverequest', compact('employee'));
    }

    public function create()
    {
        $leaveBalances = Leavebalance::where('employee_id', auth()->user()->employee_id)->get();
        return view('pages.Leaverequest.create', compact('leaveBalances'));
    }

    public function getLeaverequests()
    {
        $employeeId = Auth::user()->employee_id;

        $leaves = Leaverequest::with(['leavebalance', 'approver'])
            ->whereHas('leavebalance', function ($q) use ($employeeId) {
                $q->where('employee_id', $employeeId);
            })
            ->select(['id', 'leave_balance_id', 'start_date', 'end_date', 'status', 'approved_by'])
            ->get()
            ->map(function ($leave) {
                $leave->id_hashed = substr(hash('sha256', $leave->id . env('APP_KEY')), 0, 8);

                $showButton = '
                    <a href="' . route('Leaverequest.show', $leave->id_hashed) . '"
                       class="mx-2"
                       data-bs-toggle="tooltip"
                       data-bs-original-title="View details"
                       title="Show Leave Request: ' . e($leave->leavebalance->employees->employee_name) . '">
                        <i class="fas fa-eye"></i>
                    </a>';

                $editButton = '
                    <a href="' . route('Leaverequest.edit', $leave->id_hashed) . '"
                       class="mx-2"
                       data-bs-toggle="tooltip"
                       data-bs-original-title="Edit request"
                       title="Edit Leave Request: ' . e($leave->leavebalance->employees->employee_name) . '">
                        <i class="fas fa-user-edit"></i>
                    </a>';

                $leave->action = $showButton . $editButton;
                return $leave;
            });

        return DataTables::of($leaves)
            ->addColumn('approver', fn($e) => optional($e->approver)->employee_name ?? 'empty')
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        // 1. Validasi input (dikirim sebagai multipart/FormData karena upload lampiran)
        $validated = $request->validate([
            'leave_balance_id' => ['required', 'string', 'exists:leave_balances_tables,id'],
            'start_date'       => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'end_date'         => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'employee_reason'  => ['required', 'string', 'min:5', 'max:500'],
            'attachment'       => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [
            'leave_balance_id.required' => 'Jenis cuti wajib dipilih.',
            'leave_balance_id.exists'   => 'Jenis cuti tidak valid.',
            'start_date.required'       => 'Tanggal mulai wajib diisi.',
            'start_date.date_format'    => 'Format tanggal mulai tidak valid.',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'end_date.required'         => 'Tanggal selesai wajib diisi.',
            'end_date.date_format'      => 'Format tanggal selesai tidak valid.',
            'end_date.after_or_equal'   => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'employee_reason.required'  => 'Alasan cuti wajib diisi.',
            'employee_reason.min'       => 'Alasan cuti minimal 5 karakter.',
            'employee_reason.max'       => 'Alasan cuti maksimal 500 karakter.',
            'attachment.mimes'          => 'Lampiran harus berformat JPG, PNG, atau PDF.',
            'attachment.max'            => 'Ukuran lampiran maksimal 5MB.',
        ]);

        // 2. Pastikan saldo milik karyawan yang login
        $balance = Leavebalance::findOrFail($validated['leave_balance_id']);

        if ($balance->employee_id !== auth()->user()->employee_id) {
            return response()->json([
                'title' => 'Gagal',
                'text'  => 'Saldo cuti tidak valid.',
                'icon'  => 'error',
            ], 403);
        }

        $employee      = $balance->employees;
        $leaveTypeName = $balance->leaves->name ?? 'Cuti';
        $rules         = $balance->leaves;                        // aturan Level 2
        $isSpecial     = (bool) ($rules->is_special ?? false);    // SAKLAR

        // 2a. GUARD: relasi employees() memfilter status='Active' → null utk non-Active.
        //     Kebijakan: hanya karyawan Active yang boleh mengajukan cuti.
        if (!$employee) {
            return response()->json([
                'title' => 'Gagal',
                'text'  => 'Pengajuan cuti hanya dapat dilakukan oleh karyawan berstatus aktif.',
                'icon'  => 'error',
            ], 422);
        }

        // 2b. GUARD: DW / On Job Training tidak punya hak cuti.
        if ($this->isBlockedStatus($employee->status_employee ?? null)) {
            return response()->json([
                'title' => 'Gagal',
                'text'  => 'Status karyawan Anda (' . ($employee->status_employee ?? '-') . ') tidak memiliki hak cuti.',
                'icon'  => 'error',
            ], 422);
        }

        // 2c. GUARD GENERIK cuti KHUSUS (is_special) — aturan dibaca dari DB.
        //     Cuti biasa (annual, is_special=false) MELEWATI blok ini utuh.
        if ($isSpecial) {
            $genderRule = strtolower(trim($rules->gender_rule ?? 'all'));

            if ($genderRule === 'female' && strtolower(trim($employee->gender ?? '')) !== 'female') {
                return response()->json([
                    'title' => 'Gagal',
                    'text'  => $leaveTypeName . ' hanya dapat diajukan oleh karyawan perempuan.',
                    'icon'  => 'error',
                ], 422);
            }
            if ($genderRule === 'male' && strtolower(trim($employee->gender ?? '')) !== 'male') {
                return response()->json([
                    'title' => 'Gagal',
                    'text'  => $leaveTypeName . ' hanya dapat diajukan oleh karyawan laki-laki.',
                    'icon'  => 'error',
                ], 422);
            }

            if (($rules->require_married ?? false)
                && strtolower(trim($employee->marriage ?? '')) !== 'yes') {
                return response()->json([
                    'title' => 'Gagal',
                    'text'  => $leaveTypeName . ' hanya dapat diajukan oleh karyawan yang sudah menikah.',
                    'icon'  => 'error',
                ], 422);
            }

            $allowedStatus = trim((string) ($rules->allowed_status ?? ''));
            if ($allowedStatus !== '') {
                $allowed = array_map(fn($s) => strtoupper(trim($s)), explode(',', $allowedStatus));
                if (!in_array(strtoupper(trim($employee->status_employee ?? '')), $allowed, true)) {
                    return response()->json([
                        'title' => 'Gagal',
                        'text'  => $leaveTypeName . ' hanya berlaku untuk karyawan: ' . implode(', ', $allowed) . '.',
                        'icon'  => 'error',
                    ], 422);
                }
            }

            if (($rules->require_attachment ?? false) && !$request->hasFile('attachment')) {
                return response()->json([
                    'title' => 'Gagal',
                    'text'  => 'Bukti (lampiran) wajib disertakan untuk ' . $leaveTypeName . '.',
                    'icon'  => 'error',
                ], 422);
            }

            if (!empty($rules->fixed_days)) {
                $fixed       = (int) $rules->fixed_days;
                $expectedEnd = Carbon::parse($validated['start_date'])->addDays($fixed - 1)->toDateString();
                if ($validated['end_date'] !== $expectedEnd) {
                    return response()->json([
                        'title' => 'Gagal',
                        'text'  => 'Durasi ' . $leaveTypeName . ' adalah ' . $fixed . ' hari kalender. Tanggal selesai seharusnya ' . $expectedEnd . '.',
                        'icon'  => 'error',
                    ], 422);
                }
            }
        }

        // 3. Cek apakah karyawan masih punya pengajuan yang belum selesai (bukan Rejected)
        $hasPending = Leaverequest::whereHas('leavebalance', function ($q) {
            $q->where('employee_id', auth()->user()->employee_id);
        })
            ->where('status', '!=', 'Rejected')
            ->where('status', 'Pending')
            ->exists();

        if ($hasPending) {
            return response()->json([
                'title' => 'Tidak Bisa Mengajukan',
                'text'  => 'Anda masih memiliki pengajuan cuti yang menunggu persetujuan. Harap tunggu hingga disetujui atau ditolak.',
                'icon'  => 'warning',
            ], 422);
        }

        // 4. Pastikan saldo masih aktif.
        //    Cuti khusus boleh melintas tahun: cukup TAHUN MULAI cocok tahun saldo.
        //    Cuti biasa tetap pakai tahun berjalan.
        $startYear = (int) Carbon::parse($validated['start_date'])->year;
        $balanceOk = $isSpecial
            ? ((int) $balance->year === $startYear)
            : ((int) $balance->year === (int) date('Y'));

        if (!$balanceOk) {
            return response()->json([
                'title' => 'Gagal',
                'text'  => 'Saldo cuti sudah tidak aktif untuk periode ini.',
                'icon'  => 'error',
            ], 422);
        }

        // 5. Hitung total hari
        $start     = Carbon::parse($validated['start_date']);
        $end       = Carbon::parse($validated['end_date']);
        $totalDays = $start->diffInDays($end) + 1;

        // 6. Cek saldo mencukupi
        if ($balance->balance_days < $totalDays) {
            return response()->json([
                'title' => 'Saldo Tidak Cukup',
                'text'  => 'Sisa saldo: ' . $balance->balance_days . ' hari, durasi pengajuan: ' . $totalDays . ' hari.',
                'icon'  => 'error',
            ], 422);
        }

        // 7. Cek overlap tanggal dengan pengajuan lain yang aktif
        $overlapping = Leaverequest::whereHas('leavebalance', function ($q) {
            $q->where('employee_id', auth()->user()->employee_id);
        })
            ->whereNotIn('status', ['Rejected'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhereBetween('end_date',  [$validated['start_date'], $validated['end_date']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_date', '<=', $validated['start_date'])
                            ->where('end_date',   '>=', $validated['end_date']);
                    });
            })
            ->exists();

        if ($overlapping) {
            return response()->json([
                'title' => 'Tanggal Bertabrakan',
                'text'  => 'Terdapat pengajuan cuti lain pada tanggal tersebut.',
                'icon'  => 'error',
            ], 422);
        }

        // 8. Simpan lampiran ke S3 (jika ada)
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave-attachments', 's3');
        }

        // 9. Simpan pengajuan
        $leaveRequest = Leaverequest::create([
            'leave_balance_id' => $balance->id,
            'start_date'       => $validated['start_date'],
            'end_date'         => $validated['end_date'],
            'total_days'       => $totalDays,
            'status'           => 'Pending',
            'employee_reason'  => $validated['employee_reason'],
            'attachment'       => $attachmentPath,
        ]);

        // 10. Potong saldo saat pengajuan (Pending). Tiap jenis memotong baris
        //     Leavebalance-nya sendiri → saldo annual tak tersentuh cuti khusus.
        $balance->balance_days -= $totalDays;
        $balance->save();

        return response()->json([
            'title' => 'Berhasil',
            'text'  => 'Pengajuan cuti berhasil dibuat.',
            'icon'  => 'success',
            'data'  => $leaveRequest,
        ]);
    }

    // ─────────────────────────────────────────────────────────────

    /**
     * Tentukan day_type di grid roster (Opsi B: baca roster_day_type dari DB).
     * Cuti biasa & jenis tanpa pengaturan → 'Leave'.
     */
    private function resolveDayType($rules): string
    {
        $dayType = trim((string) ($rules->roster_day_type ?? ''));
        return $dayType !== '' ? $dayType : 'Leave';
    }

    /** DW / On Job Training tidak punya hak cuti. */
    private function isBlockedStatus(?string $statusEmployee): bool
    {
        $status = strtoupper($statusEmployee ?? '');
        return $status === 'DW' || $status === 'ON JOB TRAINING';
    }

    public function approve(Request $request, $id)
    {
        $leaveRequest = Leaverequest::with('leavebalance')->findOrFail($id);

        $employeeId = auth()->user()->employee_id;

        if (!$leaveRequest->canBeApprovedBy($employeeId)) {
            return response()->json([
                'title' => 'Ditolak',
                'text'  => 'Anda tidak berhak menyetujui pengajuan ini.',
                'icon'  => 'error',
            ], 403);
        }

        if ($leaveRequest->status !== 'Pending') {
            return response()->json([
                'title' => 'Gagal',
                'text'  => 'Pengajuan ini sudah diproses sebelumnya.',
                'icon'  => 'error',
            ], 422);
        }

        $reason = trim((string) $request->input('approver_reason'));
        if ($reason === '') {
            return response()->json([
                'title' => 'Gagal',
                'text'  => 'Alasan persetujuan wajib diisi.',
                'icon'  => 'error',
            ], 422);
        }

        $balance          = $leaveRequest->leavebalance;
        $rosterEmployeeId = $balance->employee_id;

        $toilClash = ToilLeaveRequests::where('employee_id', $rosterEmployeeId)
            ->where('status', 'Approved')
            ->whereBetween('leave_date', [$leaveRequest->start_date, $leaveRequest->end_date])
            ->exists();

        if ($toilClash) {
            return response()->json([
                'title' => 'Bentrok TOIL',
                'text'  => 'Ada TOIL Leave yang sudah Approved di rentang tanggal ini. Cancel TOIL dulu.',
                'icon'  => 'error',
            ], 422);
        }

        $leaveRequest->update([
            'status'          => 'Approved',
            'approved_by'     => $employeeId,
            'approver_reason' => $reason,
        ]);

        $employee = $balance->employees;
        $rules    = $balance->leaves;

        if ($rosterEmployeeId && !$this->isBlockedStatus($employee?->status_employee)) {
            $dayType = $this->resolveDayType($rules);
            $current = Carbon::parse($leaveRequest->start_date);
            $end     = Carbon::parse($leaveRequest->end_date);

            while ($current->lte($end)) {
                Roster::updateOrCreate(
                    [
                        'employee_id' => $rosterEmployeeId,
                        'date'        => $current->toDateString(),
                    ],
                    [
                        'shift_id' => null,
                        'day_type' => $dayType,
                        'notes'    => $leaveRequest->employee_reason,
                    ]
                );
                $current->addDay();
            }
        }

        return response()->json([
            'title' => 'Berhasil',
            'text'  => 'Pengajuan cuti disetujui.',
            'icon'  => 'success',
        ]);
    }

    public function reject(Request $request, $id)
    {
        $leaveRequest = Leaverequest::with('leavebalance')->findOrFail($id);

        $employeeId = auth()->user()->employee_id;

        if (!$leaveRequest->canBeApprovedBy($employeeId)) {
            return response()->json([
                'title' => 'Ditolak',
                'text'  => 'Anda tidak berhak menolak pengajuan ini.',
                'icon'  => 'error',
            ], 403);
        }

        if ($leaveRequest->status !== 'Pending') {
            return response()->json([
                'title' => 'Gagal',
                'text'  => 'Pengajuan ini sudah diproses sebelumnya.',
                'icon'  => 'error',
            ], 422);
        }

        $reason = trim((string) $request->input('approver_reason'));
        if ($reason === '') {
            return response()->json([
                'title' => 'Gagal',
                'text'  => 'Alasan penolakan wajib diisi.',
                'icon'  => 'error',
            ], 422);
        }

        // Kembalikan saldo (dipotong sejak Pending)
        $balance = $leaveRequest->leavebalance;
        $balance->balance_days += $leaveRequest->total_days;
        $balance->save();

        $leaveRequest->update([
            'status'          => 'Rejected',
            'approved_by'     => $employeeId,
            'approver_reason' => $reason,
        ]);

        // Hapus lampiran dari S3 saat ditolak
        if ($leaveRequest->attachment) {
            Storage::disk('s3')->delete($leaveRequest->attachment);
            $leaveRequest->update(['attachment' => null]);
        }

        return response()->json([
            'title' => 'Berhasil',
            'text'  => 'Pengajuan cuti ditolak.',
            'icon'  => 'success',
        ]);
    }
}