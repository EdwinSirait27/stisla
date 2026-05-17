<?php

namespace App\Http\Controllers;

use App\Models\Leaverequest;
use App\Models\Leavebalance;
use App\Models\Roster;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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
        // 1. Validasi format input (semua validasi di backend)
        $validated = $request->validate([
            'leave_balance_id' => ['required', 'string', 'exists:leave_balances_tables,id'],
            'start_date'       => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'end_date'         => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'employee_reason'  => ['required', 'string', 'min:5', 'max:500'],
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
        ]);

        // 2. Pastikan saldo milik karyawan yang login
        $balance = Leavebalance::findOrFail($validated['leave_balance_id']);

        if ($balance->employee_id !== auth()->user()->employee_id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Saldo cuti tidak valid.',
            ], 403);
        }

        // 3. Pastikan saldo masih aktif (tahun berjalan)
        if ((int) $balance->year !== (int) date('Y')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Saldo cuti sudah tidak aktif untuk tahun ini.',
            ], 422);
        }

        // 4. Hitung total hari cuti
        $start     = Carbon::parse($validated['start_date']);
        $end       = Carbon::parse($validated['end_date']);
        $totalDays = $start->diffInDays($end) + 1;

        // 5. Cek saldo mencukupi
        if ($balance->balance_days < $totalDays) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Saldo cuti tidak cukup. Sisa saldo: ' . $balance->balance_days . ' hari, durasi pengajuan: ' . $totalDays . ' hari.',
            ], 422);
        }

        // 6. Cek overlap tanggal dengan pengajuan lain yang aktif
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
                'status'  => 'error',
                'message' => 'Terdapat pengajuan cuti lain yang bertabrakan pada tanggal tersebut.',
            ], 422);
        }

        // 7. Simpan pengajuan
        $leaveRequest = Leaverequest::create([
            'leave_balance_id' => $balance->id,
            'employee_id'      => $balance->employee_id,
            'start_date'       => $validated['start_date'],
            'end_date'         => $validated['end_date'],
            'total_days'       => $totalDays,
            'status'           => 'Pending',
            'employee_reason'  => $validated['employee_reason'],
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Leave request created successfully',
            'data'    => $leaveRequest,
        ]);
    }
    // ─────────────────────────────────────────────────────────────

    private function resolveDayType(string $leaveTypeName): string
    {
        $lower = strtolower($leaveTypeName);

        if (str_contains($lower, 'melahirkan')) {
            return 'Cuti Melahirkan';
        }

        return 'Leave';
    }

    private function canHaveLeave(?string $statusEmployee): bool
    {
        $status = strtoupper($statusEmployee ?? '');

        if ($status === 'DW' || $status === 'ON JOB TRAINING') {
            return false;
        }

        return true;
    }

    public function approve($id)
    {
        $leaveRequest = Leaverequest::with('leavebalance')->findOrFail($id);

        $employeeId = auth()->user()->employee_id;

        if (!$leaveRequest->canBeApprovedBy($employeeId)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You are not allowed to approve this request',
            ], 403);
        }

        $balance = $leaveRequest->leavebalance;

        if ($balance->balance_days < $leaveRequest->total_days) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Insufficient leave balance',
            ], 400);
        }

        $balance->balance_days -= $leaveRequest->total_days;
        $balance->save();

        $leaveRequest->update([
            'status'      => 'Approved',
            'approved_by' => $employeeId,
        ]);

        $rosterEmployeeId = $balance->employee_id;
        $employee         = $balance->employees;
        $leaveType        = $balance->leaves;
        $leaveTypeName    = $leaveType?->name ?? $leaveType?->leave_type_name ?? 'Leave';

        if ($rosterEmployeeId && $this->canHaveLeave($employee?->status_employee)) {
            $dayType = $this->resolveDayType($leaveTypeName);
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
                        'notes'    => 'Auto: Leave approved #' . $leaveRequest->id,
                    ]
                );
                $current->addDay();
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Leave request approved',
        ]);
    }

    public function reject(Request $request, $id)
    {
        $leaveRequest = Leaverequest::with('leavebalance')->findOrFail($id);

        $employeeId = auth()->user()->employee_id;

        if (!$leaveRequest->canBeApprovedBy($employeeId)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You are not allowed to reject this request',
            ], 403);
        }

        $leaveRequest->update([
            'status'          => 'Rejected',
            'approved_by'     => $employeeId,
            'approver_reason' => $request->approver_reason,
        ]);

        $balance          = $leaveRequest->leavebalance;
        $rosterEmployeeId = $balance?->employee_id;

        if ($rosterEmployeeId) {
            $current = Carbon::parse($leaveRequest->start_date);
            $end     = Carbon::parse($leaveRequest->end_date);

            while ($current->lte($end)) {
                Roster::where('employee_id', $rosterEmployeeId)
                    ->where('date', $current->toDateString())
                    ->whereIn('day_type', ['Leave', 'Cuti Melahirkan'])
                    ->update([
                        'shift_id' => null,
                        'day_type' => 'Off',
                        'notes'    => 'Auto: Leave rejected #' . $leaveRequest->id,
                    ]);
                $current->addDay();
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Leave request rejected',
        ]);
    }
}