<?php
namespace App\Http\Controllers;
use App\Models\Attendances;
use App\Models\Attendancetotal;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;



use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;
class AttendanceController extends Controller
{
 
    public function getAttendances(Request $request)
    {
        \Log::info('DATE FILTER', [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'kantor' => $request->kantor,
        ]);
        $attendances = Attendances::with([
            'Employee.position',
            'Employee.department',
        ])
            ->select([
                'id',
                'employee_id',
                'tanggal',
                'kantor',
                'jam_masuk',
                'jam_keluar',
                'jam_masuk2',
                'jam_keluar2',
                'jam_masuk3',
                'jam_keluar3',
                'jam_masuk4',
                'jam_keluar4',
                'jam_masuk5',
                'jam_keluar5',
                'jam_masuk6',
                'jam_keluar6',
                'jam_masuk7',
                'jam_keluar7',
                'jam_masuk8',
                'jam_keluar8',
                'jam_masuk9',
                'jam_keluar9',
                'jam_masuk10',
                'jam_keluar10'
            ])
            ->when($request->kantor, function ($query) use ($request) {
                $query->where('kantor', $request->kantor);
            })
            ->when($request->start_date && $request->end_date, function ($query) use ($request) {
                $query->whereBetween('tanggal', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            })
            ->get();

        return DataTables::of($attendances)
            ->addColumn('position_name', fn($e) => optional(optional($e->Employee)->position)->name ?? 'Empty')
            ->addColumn('department_name', fn($e) => optional(optional($e->Employee)->department)->department_name ?? 'Empty')
            ->addColumn('employee_name', fn($e) => optional($e->Employee)->employee_name ?? 'Empty')
            ->addColumn('pin', fn($e) => optional($e->Employee)->pin ?? 'Empty')
            ->rawColumns(['position_name', 'department_name', 'employee_name', 'pin'])
            ->make(true);
    }
    public function index()
    {
        $kantors = Attendances::select('kantor')
            ->whereNotNull('kantor')
            ->distinct()
            ->pluck('kantor');
        return view('pages.Attendance.Attendance', compact('kantors'));
    }

public function storeAttendanceSummary(Request $request)
{
    \Log::info('STORE ATTENDANCE SUMMARY REQUEST', [
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'kantor' => $request->kantor,
    ]);

    $month = Carbon::parse($request->start_date)->startOfMonth()->format('Y-m-d');

    // Ambil semua data attendance sesuai filter
    $attendances = Attendances::select('id', 'employee_id', 'tanggal', 'kantor')
        ->when($request->kantor, function ($query) use ($request) {
            $query->where('kantor', $request->kantor);
        })
        ->when($request->start_date && $request->end_date, function ($query) use ($request) {
            $query->whereBetween('tanggal', [
                $request->start_date,
                $request->end_date,
            ]);
        })
        ->get();

    // Hitung jumlah kehadiran per employee_id
    $grouped = $attendances->groupBy('employee_id');
    $rekapList = [];

    foreach ($grouped as $employeeId => $items) {
        $attendanceId = $items->first()->id;
        $total = $items->count();

        $rekap = Attendancetotal::updateOrCreate(
            [
                'month' => $month,
                'attendance_id' => $attendanceId,
            ],
            [
                'id' => Str::uuid(),
                'total' => $total,
            ]
        );

        $rekapList[] = [
            'employee_id' => $employeeId,
            'total' => $total,
            'attendance_id' => $attendanceId,
            'rekap_id' => $rekap->id,
        ];
    }

    \Log::info('ATTENDANCE TOTAL PER EMPLOYEE DISIMPAN', $rekapList);

    return response()->json([
        'message' => 'Attendance total per employee saved successfully.',
        'month' => Carbon::parse($month)->format('Y-m'),
        'rekap' => $rekapList
    ]);
}


// public function storeAttendanceSummary(Request $request)
// {
//     \Log::info('STORE ATTENDANCE SUMMARY REQUEST', [
//         'start_date' => $request->start_date,
//         'end_date' => $request->end_date,
//         'kantor' => $request->kantor,
//     ]);

//     $attendances = Attendances::select('id', 'employee_id', 'tanggal', 'kantor')
//         ->when($request->kantor, function ($query) use ($request) {
//             $query->where('kantor', $request->kantor);
//         })
//         ->when($request->start_date && $request->end_date, function ($query) use ($request) {
//             $query->whereBetween('tanggal', [
//                 $request->start_date . ' 00:00:00',
//                 $request->end_date . ' 23:59:59',
//             ]);
//         })
//         ->get();

//     \Log::info('TOTAL ATTENDANCES DITEMUKAN', [
//         'count' => $attendances->count(),
//         'employee_ids' => $attendances->pluck('employee_id')->values()->all(),
//     ]);

//     // $totalEmployee = $attendances->pluck('employee_id')->unique()->count();
//     $totalEmployee = $attendances->count(); // hasil = 2

//     $attendanceId = $attendances->first()?->id;

//     // ✅ Perbaikan: Simpan awal bulan sebagai DATE (format 'Y-m-d')
//     $month = Carbon::parse($request->start_date)->startOfMonth()->format('Y-m-d');

//     $rekap = Attendancetotal::updateOrCreate(
//         [
//             'month' => $month,
//             'attendance_id' => $attendanceId,
//         ],
//         [
//             'id' => Str::uuid(),
//             'total' => $totalEmployee,
//         ]
//     );

//     \Log::info('ATTENDANCETOTAL DISIMPAN', [
//         'attendance_id' => $attendanceId,
//          'month' => Carbon::parse($month)->format('Y-m'),
//         'total' => $totalEmployee,
//         'rekap_id' => $rekap->id,
//     ]);

//     return response()->json([
//         'message' => 'Attendance total saved successfully.',
//         'month' => Carbon::parse($month)->format('Y-m'),
//         'total_employee' => $totalEmployee
//     ]);
// }




}
