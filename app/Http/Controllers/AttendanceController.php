<?php
namespace App\Http\Controllers;
use App\Models\Attendances;
use App\Models\Stores;
use App\Models\Attendancetotal;
use App\Models\Fingerprints;
use App\Models\Employee;
use App\Models\EditedFingerprint;
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

 public function indexattendance()
    {
        $stores = Stores::select('id', 'name')
            ->whereNotNull('name')
            ->distinct()
            ->pluck('name');
        return view('pages.Attendanceall.Attendanceall', compact('stores'));
    }
public function getAttendancealls(Request $request)
{
    ini_set('memory_limit', '1024M');
    
    $user = auth()->user(); // Ambil user login

    $storeName = $request->input('store_name');
    $startDate = $request->input('start_date', '2025-07-01');
    $endDate = $request->input('end_date', now()->toDateString());

    // Ambil data edited
    $edited = EditedFingerprint::select('pin', 'scan_date')->get()
        ->map(fn($item) => $item->pin . '_' . Carbon::parse($item->scan_date)->toDateString())
        ->toArray();

    // Ambil PIN milik user login (jika punya relasi employee)
    $userPin = optional($user->employee)->pin;

    // Query employee
    $employeesQuery = Employee::with('position', 'store')
        ->select('pin', 'employee_name', 'employee_pengenal', 'position_id', 'store_id');

    if ($storeName) {
        $employeesQuery->whereHas('store', function ($q) use ($storeName) {
            $q->where('name', $storeName);
        });
    }

    // Jika user hanya boleh melihat datanya sendiri
    if ($userPin) {
        $employeesQuery->where('pin', $userPin);
    }

    $employees = $employeesQuery->get()->keyBy('pin');

    // Ambil fingerprint sesuai PIN & tanggal
    $fingerprintQuery = Fingerprints::with('devicefingerprints')
        ->select(['sn', 'scan_date', 'pin', 'inoutmode'])
        ->whereBetween('scan_date', [$startDate, $endDate]);

    if ($userPin) {
        $fingerprintQuery->where('pin', $userPin);
    }

    $fingerprints = $fingerprintQuery
        ->orderBy('scan_date')
        ->get();

    // Lanjutkan kode seperti biasa...
    // Grouping berdasarkan pin_tanggal
    $grouped = $fingerprints->groupBy(function ($item) {
        return $item->pin . '_' . Carbon::parse($item->scan_date)->toDateString();
    });

    // Hitung total hari per pin
    $totalHariPerPin = $fingerprints
        ->groupBy(function ($item) {
            return $item->pin . '_' . Carbon::parse($item->scan_date)->toDateString();
        })
        ->map(function ($items) {
            $first = $items->first();
            return [
                'pin' => $first->pin,
                'date' => Carbon::parse($first->scan_date)->toDateString()
            ];
        })
        ->groupBy('pin')
        ->map(fn($items) => collect($items)->pluck('date')->unique()->count());

    $result = [];

    foreach ($grouped as $group) {
        $first = $group->first();
        $pin = $first->pin;
        $scanDate = Carbon::parse($first->scan_date)->toDateString();
        $employee = $employees->get($pin);
        if (!$employee) {
            continue;
        }

        $totalHari = $totalHariPerPin[$pin] ?? 0;

        $row = [
            'pin' => $pin,
            'employee_name' => $employee->employee_name ?? 'No Data',
            'employee_pengenal' => $employee->employee_pengenal ?? 'No Data',
            'name' => $employee->store->name ?? 'No Data',
            'position_name' => optional($employee->position)->name ?? '-',
            'device_name' => optional($first->devicefingerprints)->device_name ?? '-',
            'scan_date' => $scanDate,
        ];

        // Inisialisasi in_1 - in_10
        for ($i = 1; $i <= 10; $i++) {
            $row['in_' . $i] = null;
        }

        $byMode = $group->groupBy('inoutmode');

        foreach ($byMode as $mode => $items) {
            if ($mode >= 1 && $mode <= 10) {
                $earliest = $items->sortBy('scan_date')->first();

                $row['in_' . $mode] = $earliest ? Carbon::parse($earliest->scan_date)->format('H:i:s') : '';
                $row['device_' . $mode] = optional($earliest->devicefingerprints)->device_name ?? '';
            }
        }

        for ($i = 1; $i <= 10; $i++) {
            $jam = $row['in_' . $i] ?? '';
            $device = $row['device_' . $i] ?? '';
            $row['combine_' . $i] = $jam . ' ' . $device;
        }

        $scanTimes = collect(range(1, 10))
            ->map(fn($i) => $row['in_' . $i])
            ->filter()
            ->sort()
            ->values();

        if ($scanTimes->count() >= 2) {
            $start = Carbon::parse($scanTimes->first());
            $end = Carbon::parse($scanTimes->last());
            $diffInMinutes = $start->diffInMinutes($end);
            $hours = floor($diffInMinutes / 60);
            $minutes = $diffInMinutes % 60;

            $row['duration'] = ($hours ? $hours . ' hour' . ($hours > 1 ? 's' : '') : '') .
                ($minutes ? ' ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '') : '');
            $row['duration'] = trim($row['duration']) ?: '0 minutes';
        } else {
            $row['duration'] = 'invalid';
        }

        $isUpdated = in_array($pin . '_' . $scanDate, $edited);
        $row['updated'] = $isUpdated ? '✔️ Updated' : '❌ Original';
        $row['is_updated'] = $isUpdated;

        $result[] = $row;
    }

    $result = collect($result)->sortBy('scan_date')->values();

    return DataTables::of($result)
        ->addColumn('action', function ($row) {
            $editBtn = $row['is_updated']
                ? '<button class="btn btn-sm btn-secondary" disabled>Edited</button>'
                : '<a href="' . route('pages.Fingerprints.edit', [
                    'pin' => $row['pin'],
                    'scan_date' => $row['scan_date'],
                ]) . '" class="btn btn-sm btn-primary me-1">Edit</a>';

            $lihatBtn = '<button class="btn btn-sm btn-info lihat-total"
                            data-pin="' . $row['pin'] . '"
                            data-employee="' . e($row['employee_name']) . '">
                            Lihat Total
                        </button>';

            return $editBtn . $lihatBtn;
        })
        ->addColumn('updated_status', fn($row) => $row['updated'])
        ->rawColumns(['action', 'updated_status'])
        ->make(true);
}


}
