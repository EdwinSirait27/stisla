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
        'leave_date_from' => 'required|date',
        'leave_date_to' => 'required|date|after_or_equal:leave_date_from',
    ]);

    $user = Auth::user();
    $employee = $user->employee;

    if (!$employee) {
        return redirect()->back()->with('error', 'Data karyawan tidak ditemukan.');
    }

    $approver = Employee::where('department_id', $employee->department_id)
        ->where('is_manager', 1)
        ->first();

    if (!$approver) {
        return redirect()->back()->with('error', 'Tidak ditemukan manager di departemen yang sama.');
    }

    $from = Carbon::parse($validated['leave_date_from']);
    $to   = Carbon::parse($validated['leave_date_to']);

    // Hitung durasi dalam jam (float)
    $duration = round($from->floatDiffInHours($to), 2);

    $timeToil = '00:00:00'; // default format TIME

    if ($validated['type'] === 'Overtime') {
        if ($validated['status_submissions'] === 'Cash') {
            $hours = $duration > 4 ? $duration - 4 : 0;
        } elseif ($validated['status_submissions'] === 'TOIL') {
            $hours = $duration;
        } else {
            $hours = 0;
        }

        // Konversi jam float ke format HH:MM:SS
        $totalSeconds = (int) round($hours * 3600);
        $timeToil = gmdate('H:i:s', $totalSeconds);
    }

    DB::transaction(function () use ($employee, $approver, $validated, $duration, $timeToil) {
        Submissions::create([
            'employee_id' => $employee->id,
            'approver_id' => $approver->id,
            'type' => $validated['type'],
            'status_submissions' => $validated['status_submissions'],
            'leave_date_from' => $validated['leave_date_from'],
            'leave_date_to' => $validated['leave_date_to'],
            'duration' => $duration,
            'time_toil' => $timeToil, // <-- sekarang dalam format TIME valid
            'status' => 'Pending',
        ]);

        if ($validated['type'] === 'Annual Leave') {
            $employee->pending += $duration;
            $employee->remaining = $employee->total - ($employee->approved + $employee->pending);
            $employee->save();
        }
    });

    return redirect()->back()->with('success', 'Submission berhasil dibuat!');
}

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

//     // Hitung durasi dalam jam
//     $duration = round($from->floatDiffInHours($to), 2);

//     $timeToil = 0;

//     if ($validated['type'] === 'Overtime') {
//         if ($validated['status_submissions'] === 'Cash') {
//             $timeToil = $duration > 4 ? $duration - 4 : 0;
//         } elseif ($validated['status_submissions'] === 'TOIL') {
//             $timeToil = $duration;
//         }
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
//             'time_toil' => $timeToil,
//             'status' => 'Pending',
//         ]);

//         // Update pending & remaining hanya untuk Annual Leave
//         if ($validated['type'] === 'Annual Leave') {
//             $employee->pending += $duration;
//             $employee->remaining = $employee->total - ($employee->approved + $employee->pending);
//             $employee->save();
//         }
//     });

//     return redirect()->back()->with('success', 'Submission berhasil dibuat!');
// }

}
