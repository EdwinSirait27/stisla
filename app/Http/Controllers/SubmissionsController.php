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
//    public function store(Request $request)
//     {
//         // ✅ Validasi input dari form
//         $validated = $request->validate([
//             'type' => 'required|string|max:255',
//             'leave_date_from' => 'required|date',
//             'leave_date_to' => 'required|date|after_or_equal:leave_date_from',
//         ]);

//         // ✅ Ambil employee yang sedang login
//         $user = Auth::user();
//         $employee = $user->employee;

//         if (!$employee) {
//             return redirect()->back()->with('error', 'Data karyawan tidak ditemukan.');
//         }

//         // ✅ Cari approver = manager di department yang sama
//         $approver = Employee::where('department_id', $employee->department_id)
//             ->where('is_manager', 1)
//             ->first();

//         if (!$approver) {
//             return redirect()->back()->with('error', 'Tidak ditemukan manager di departemen yang sama.');
//         }

//         // ✅ Hitung durasi cuti (jumlah hari inklusif)
//         $from = Carbon::parse($validated['leave_date_from']);
//         $to = Carbon::parse($validated['leave_date_to']);
//         $duration = $from->diffInDays($to) + 1;

//         // ✅ Simpan data ke tabel submissions
//         Submissions::create([
//             'employee_id' => $employee->id,
//             'approver_id' => $approver->id,
//             'type' => $validated['type'],
//             'leave_date_from' => $validated['leave_date_from'],
//             'leave_date_to' => $validated['leave_date_to'],
//             'duration' => $duration,
//             'status' => 'pending',
//         ]);

//         return redirect()->back()->with('success', 'Submission berhasil dibuat!');
//     }

// public function store(Request $request)
// {
//     $validated = $request->validate([
//         'type' => 'required|string|max:255',
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
//     $to = Carbon::parse($validated['leave_date_to']);
//     $duration = $from->diffInDays($to) + 1;

//     DB::transaction(function () use ($employee, $approver, $validated, $duration) {
//         // ✅ Simpan submission baru
//         Submissions::create([
//             'employee_id' => $employee->id,
//             'approver_id' => $approver->id,
//             'type' => $validated['type'],
//             'leave_date_from' => $validated['leave_date_from'],
//             'leave_date_to' => $validated['leave_date_to'],
//             'duration' => $duration,
//             'status' => 'pending',
//         ]);

//         $employee->pending += $duration;
//         $employee->remaining = $employee->total - ($employee->approved + $employee->pending);
//         $employee->save();
//     });

//     return redirect()->back()->with('success', 'Submission berhasil dibuat!');
// }
public function store(Request $request)
{
    $validated = $request->validate([
        'type' => 'required|string|max:255',
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

    // Tentukan format input (apakah ada jam)
    $isTimeIncluded = str_contains($validated['leave_date_from'], ':') || str_contains($validated['leave_date_to'], ':');

    if ($isTimeIncluded) {
        // hitung durasi dalam jam dengan desimal
        $duration = round($from->floatDiffInHours($to), 2); 
    } else {
        // hitung durasi dalam hari (inclusif)
        $duration = $from->diffInDays($to) + 1;
    }

    DB::transaction(function () use ($employee, $approver, $validated, $duration) {
        Submissions::create([
            'employee_id' => $employee->id,
            'approver_id' => $approver->id,
            'type' => $validated['type'],
            'leave_date_from' => $validated['leave_date_from'],
            'leave_date_to' => $validated['leave_date_to'],
            'duration' => $duration,
            'status' => 'pending',
        ]);

        $employee->pending += $duration;
        $employee->remaining = $employee->total - ($employee->approved + $employee->pending);
        $employee->save();
    });

    return redirect()->back()->with('success', 'Submission berhasil dibuat!');
}

}
