<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Submissions;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubmissionsController extends Controller
{
    /**
     * Submission untuk Leave types saja:
     * - Annual Leave
     * - Sick Leave
     * - Maternity Leave
     *
     * Overtime DIPISAH ke OvertimeSubmissionController.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'            => 'required|in:Annual Leave,Sick Leave,Maternity Leave',
            'notes'           => 'required|string|max:1000',
            'leave_date_from' => 'required|date',
            'leave_date_to'   => 'required|date|after_or_equal:leave_date_from',
            'employee_ids'    => 'nullable|array',
            'employee_ids.*'  => 'exists:employees_tables,id',
        ], [
            'type.in' => 'Type submission tidak valid. Untuk Overtime, gunakan menu TOIL Assignment.',
        ]);

        $user     = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        // Cari atasan: department manager DULU, kalau tidak ada baru store manager
        $approver = Employee::where(function ($q) use ($employee) {
                $q->where('department_id', $employee->department_id)
                  ->where('is_manager', 1);
            })
            ->orWhere(function ($q) use ($employee) {
                $q->where('store_id', $employee->store_id)
                  ->where('is_manager_store', 1);
            })
            ->first();

        if (!$approver) {
            return redirect()->back()->with('error', 'Tidak ditemukan manager di departemen atau store yang sama.');
        }

        $from     = Carbon::parse($validated['leave_date_from']);
        $to       = Carbon::parse($validated['leave_date_to']);
        $duration = round($from->floatDiffInHours($to), 2);

        $selectedEmployeeIds = $validated['employee_ids'] ?? [$employee->id];

        DB::transaction(function () use ($selectedEmployeeIds, $validated, $approver, $duration, $employee) {
            foreach ($selectedEmployeeIds as $empId) {
                $emp = Employee::find($empId);

                // Auto-approve jika pembuat adalah manager
                $status = ($employee->is_manager == 1 || $employee->is_manager_store == 1)
                    ? 'Approved'
                    : 'Pending';

                Submissions::create([
                    'employee_id'      => $emp->id,
                    'approver_id'      => $approver->id,
                    'type'             => $validated['type'],
                    'leave_date_from'  => $validated['leave_date_from'],
                    'leave_date_to'    => $validated['leave_date_to'],
                    'notes'            => $validated['notes'],
                    'duration'         => $duration,
                    'status'           => $status,
                ]);

                // Update saldo cuti tahunan
                if ($validated['type'] === 'Annual Leave') {
                    if ($status === 'Approved') {
                        $emp->approved += $duration;
                    } else {
                        $emp->pending += $duration;
                    }
                    $emp->remaining = $emp->total - ($emp->approved + $emp->pending);
                    $emp->save();
                }
            }
        });

        return redirect()->back()->with('success', 'Submission Created Successfully!');
    }
}