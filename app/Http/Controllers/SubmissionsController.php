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
    public function store(Request $request)
{
    $validated = $request->validate([
        'type' => 'required|string|max:255',
        'status_submissions' => 'required|string|max:255',
        'notes' => 'required|string|max:255',
        'leave_date_from' => 'required|date',
        'leave_date_to' => 'required|date|after_or_equal:leave_date_from',
        'employee_ids' => 'nullable|array',
        'employee_ids.*' => 'exists:employees_tables,id',
    ]);

    $user = Auth::user();
    $employee = $user->employee;

    if (!$employee) {
        return redirect()->back()->with('error', 'Data karyawan tidak ditemukan.');
    }

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

    $from = Carbon::parse($validated['leave_date_from']);
    $to   = Carbon::parse($validated['leave_date_to']);
    $duration = round($from->floatDiffInHours($to), 2);

    $timeToil = '00:00:00';
    if ($validated['type'] === 'Overtime') {
        if ($validated['status_submissions'] === 'Cash') {
            $hours = $duration > 4 ? $duration - 4 : 0;
        } elseif ($validated['status_submissions'] === 'TOIL') {
            $hours = $duration;
        } else {
            $hours = 0;
        }

        $totalSeconds = (int) round($hours * 3600);
        $timeToil = gmdate('H:i:s', $totalSeconds);
    }

    $selectedEmployeeIds = $validated['employee_ids'] ?? [$employee->id];

    DB::transaction(function () use ($selectedEmployeeIds, $validated, $approver, $duration, $timeToil, $employee) {
        foreach ($selectedEmployeeIds as $empId) {
            $emp = Employee::find($empId);

            // 🔹 Jika pembuat adalah manager atau manager store → status langsung Approved
            $status = ($employee->is_manager == 1 || $employee->is_manager_store == 1) ? 'Approved' : 'Pending';

            Submissions::create([
                'employee_id' => $emp->id,
                'approver_id' => $approver->id,
                'type' => $validated['type'],
                'status_submissions' => $validated['status_submissions'],
                'leave_date_from' => $validated['leave_date_from'],
                'leave_date_to' => $validated['leave_date_to'],
                'notes' => $validated['notes'],
                'duration' => $duration,
                'time_toil' => $timeToil,
                'status' => $status,
            ]);

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

//    public function store(Request $request)
// {
//     $validated = $request->validate([
//         'type' => 'required|string|max:255',
//         'status_submissions' => 'required|string|max:255',
//         'leave_date_from' => 'required|date',
//         'leave_date_to' => 'required|date|after_or_equal:leave_date_from',
//         'employee_ids' => 'nullable|array',
//         'employee_ids.*' => 'exists:employees_tables,id',
//     ]);

//     $user = Auth::user();
//     $employee = $user->employee;

//     if (!$employee) {
//         return redirect()->back()->with('error', 'Data karyawan tidak ditemukan.');
//     }

//     $approver = Employee::where(function ($q) use ($employee) {
//             $q->where('department_id', $employee->department_id)
//               ->where('is_manager', 1);
//         })
//         ->orWhere(function ($q) use ($employee) {
//             $q->where('store_id', $employee->store_id)
//               ->where('is_manager_store', 1);
//         })
//         ->first();

//     if (!$approver) {
//         return redirect()->back()->with('error', 'Tidak ditemukan manager di departemen atau store yang sama.');
//     }

//     $from = Carbon::parse($validated['leave_date_from']);
//     $to   = Carbon::parse($validated['leave_date_to']);
//     $duration = round($from->floatDiffInHours($to), 2);

//     $timeToil = '00:00:00';
//     if ($validated['type'] === 'Overtime') {
//         if ($validated['status_submissions'] === 'Cash') {
//             $hours = $duration > 4 ? $duration - 4 : 0;
//         } elseif ($validated['status_submissions'] === 'TOIL') {
//             $hours = $duration;
//         } else {
//             $hours = 0;
//         }

//         $totalSeconds = (int) round($hours * 3600);
//         $timeToil = gmdate('H:i:s', $totalSeconds);
//     }

//     $selectedEmployeeIds = $validated['employee_ids'] ?? [$employee->id];

//     DB::transaction(function () use ($selectedEmployeeIds, $validated, $approver, $duration, $timeToil) {
//         foreach ($selectedEmployeeIds as $empId) {
//             $emp = Employee::find($empId);

//             Submissions::create([
//                 'employee_id' => $emp->id,
//                 'approver_id' => $approver->id,
//                 'type' => $validated['type'],
//                 'status_submissions' => $validated['status_submissions'],
//                 'leave_date_from' => $validated['leave_date_from'],
//                 'leave_date_to' => $validated['leave_date_to'],
//                 'duration' => $duration,
//                 'time_toil' => $timeToil,
//                 'status' => 'Pending',
//             ]);

//             if ($validated['type'] === 'Annual Leave') {
//                 $emp->pending += $duration;
//                 $emp->remaining = $emp->total - ($emp->approved + $emp->pending);
//                 $emp->save();
//             }
//         }
//     });

//     return redirect()->back()->with('success', 'Submission Created Successfully!');
// }


    
// public function store(Request $request)
// {
//     $validated = $request->validate([
//         'type' => 'required|string|max:255',
//         'status_submissions' => 'required|string|max:255',
//         'leave_date_from' => 'required|date',
//         'leave_date_to' => 'required|date|after_or_equal:leave_date_from',
//     ]);

//     $user = Auth::user();
//     $employee = $user->employee;

//     if (!$employee) {
//         return redirect()->back()->with('error', 'Data karyawan tidak ditemukan.');
//     }

//     $approver = Employee::where('department_id', $employee->department_id)
//         ->where('is_manager', 1)
//         ->first();

//     if (!$approver) {
//         return redirect()->back()->with('error', 'Tidak ditemukan manager di departemen yang sama.');
//     }

//     $from = Carbon::parse($validated['leave_date_from']);
//     $to   = Carbon::parse($validated['leave_date_to']);

//     // Hitung durasi dalam jam (float)
//     $duration = round($from->floatDiffInHours($to), 2);

//     $timeToil = '00:00:00'; // default format TIME

//     if ($validated['type'] === 'Overtime') {
//         if ($validated['status_submissions'] === 'Cash') {
//             $hours = $duration > 4 ? $duration - 4 : 0;
//         } elseif ($validated['status_submissions'] === 'TOIL') {
//             $hours = $duration;
//         } else {
//             $hours = 0;
//         }

//         // Konversi jam float ke format HH:MM:SS
//         $totalSeconds = (int) round($hours * 3600);
//         $timeToil = gmdate('H:i:s', $totalSeconds);
//     }

//     DB::transaction(function () use ($employee, $approver, $validated, $duration, $timeToil) {
//         Submissions::create([
//             'employee_id' => $employee->id,
//             'approver_id' => $approver->id,
//             'type' => $validated['type'],
//             'status_submissions' => $validated['status_submissions'],
//             'leave_date_from' => $validated['leave_date_from'],
//             'leave_date_to' => $validated['leave_date_to'],
//             'duration' => $duration,
//             'time_toil' => $timeToil, // <-- sekarang dalam format TIME valid
//             'status' => 'Pending',
//         ]);

//         if ($validated['type'] === 'Annual Leave') {
//             $employee->pending += $duration;
//             $employee->remaining = $employee->total - ($employee->approved + $employee->pending);
//             $employee->save();
//         }
//     });

//     return redirect()->back()->with('success', 'Submission berhasil dibuat!');
// }

}
