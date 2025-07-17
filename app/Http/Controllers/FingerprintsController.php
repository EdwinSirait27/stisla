<?php

namespace App\Http\Controllers;
use App\Models\Fingerprints;
use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class FingerprintsController extends Controller
{
    public function index()
    {
        return view('pages.Fingerprints.Fingerprints');
    }


public function getFingerprints(Request $request)
{
    ini_set('memory_limit', '1024M');

    // Ambil semua employee + relasi position
    $employees = Employee::with('position')
        ->select('pin', 'employee_name', 'position_id')
        ->get()
        ->keyBy('pin');
$startDate = $request->input('start_date', '2025-07-01');
$endDate = $request->input('end_date', now()->toDateString());

    // Ambil fingerprint + relasi devicefingerprints
    $fingerprints = Fingerprints::with('devicefingerprints')
        ->select(['sn', 'scan_date', 'pin', 'inoutmode'])
        ->whereBetween('scan_date', [$startDate, $endDate])
        ->orderBy('scan_date')
        ->get();

    // Kelompokkan berdasarkan PIN + tanggal (tanpa jam)
    $grouped = $fingerprints->groupBy(function ($item) {
        return $item->pin . '_' . Carbon::parse($item->scan_date)->toDateString();
    });

    $result = [];

    foreach ($grouped as $group) {
        $first = $group->first();
        $pin = $first->pin;
        $scanDate = Carbon::parse($first->scan_date)->toDateString();

        $employee = $employees->get($pin);

        $row = [
            'pin' => $pin,
            'employee_name' => $employee->employee_name ?? 'No Data',
            // 'position_name' => optional($employee->position)->name ?? '-',
            'position_name' => $employee ? optional($employee->position)->name : '-',
            'device_name' => optional($first->devicefingerprints)->device_name ?? '-',
            'scan_date' => $scanDate,
        ];

        // Inisialisasi in_1 sampai in_10
        for ($i = 1; $i <= 10; $i++) {
            $row['in_' . $i] = null;
        }

        // Kelompokkan berdasarkan inoutmode dan ambil waktu scan paling awal
        $byMode = $group->groupBy('inoutmode');
        foreach ($byMode as $mode => $items) {
            if ($mode >= 1 && $mode <= 10) {
                $row['in_' . $mode] = $items->min('scan_date'); // lebih efisien dari sortBy()->first()
            }
        }

        // Hitung durasi dari scan pertama ke scan terakhir
        $scanTimes = collect(range(1, 10))
            ->map(fn($i) => $row['in_' . $i])
            ->filter()
            ->sort()
            ->values();

        $row['duration'] = $scanTimes->count() >= 2
            ? Carbon::parse($scanTimes->first())->diffForHumans(Carbon::parse($scanTimes->last()), true)
            : 'invalid';

        $result[] = $row;
    }

    // Urutkan berdasarkan scan_date
    $result = collect($result)->sortBy('scan_date')->values();

    return DataTables::of($result)->make(true);
}

// public function getFingerprints(Request $request)
// {
//     ini_set('memory_limit', '1024M');

//     // Ambil semua employee + position
//     $employees = Employee::with('position')
//         ->select('pin', 'employee_name', 'position_id')
//         ->get()
//         ->keyBy('pin');

//     // Ambil fingerprint + relasi devicefingerprints
//     $fingerprints = Fingerprints::with('devicefingerprints')
//         ->select(['sn', 'scan_date', 'pin', 'inoutmode'])
//         ->whereDate('scan_date', '>=', '2025-07-01')
//         ->orderBy('scan_date')
//         ->get();

//     // Kelompokkan berdasarkan PIN + tanggal (tanpa jam)
//     $grouped = $fingerprints->groupBy(function ($item) {
//         return $item->pin . '_' . date('Y-m-d', strtotime($item->scan_date));
//     });

//     $result = [];

//     foreach ($grouped as $group) {
//         $first = $group->first();
//         $pin = $first->pin;
//         // $scanDate = date('Y-m-d', strtotime($first->scan_date));
//         $scanDate = Carbon::parse($first->scan_date)->toDateString();


//         $employee = $employees->get($pin);

//         $row = [
//             'pin' => $pin,
//             'employee_name' => $employee->employee_name ?? 'Belum Masuk Sistem',
//             'position_name' => $employee->position->name ?? '-',
//             'device_name' => $first->devicefingerprints->device_name ?? '-',
//             'scan_date' => $scanDate,
//         ];
//         // Inisialisasi in_1 sampai in_10
//         for ($i = 1; $i <= 10; $i++) {
//             $row['in_' . $i] = null;
//         }

//         // Kelompokkan berdasarkan inoutmode dan ambil scan_date pertama
//         $byMode = $group->groupBy('inoutmode');
//         foreach ($byMode as $mode => $items) {
//             if ($mode >= 1 && $mode <= 10) {
//                 $firstScan = $items->sortBy('scan_date')->first();
//                 $row['in_' . $mode] = $firstScan->scan_date;
//             }
//         }

//         // Hitung durasi dari scan pertama ke scan terakhir
//         $scanTimes = collect(range(1, 10))
//             ->map(fn($i) => $row['in_' . $i])
//             ->filter()
//             ->sort()
//             ->values();

//         if ($scanTimes->count() >= 2) {
//             $start = Carbon::parse($scanTimes->first());
//             $end = Carbon::parse($scanTimes->last());
//             $row['duration'] = $start->diffForHumans($end, true); // contoh: '10 jam 3 menit'
//         } else {
//             $row['duration'] = 'invalid';
//         }

//         $result[] = $row;
//     }

//     // Urutkan berdasarkan scan_date
//     usort($result, function ($a, $b) {
//         return strtotime($a['scan_date']) <=> strtotime($b['scan_date']);
//     });

//     return DataTables::of(collect($result))->make(true);
// }

//   public function getFingerprints(Request $request)
// {
//     ini_set('memory_limit', '1024M');

//     // Ambil semua employee + position
//     $employees = Employee::with('position')
//         ->select('pin', 'employee_name', 'position_id')
//         ->get()
//         ->keyBy('pin');

//     // Ambil fingerprint + relasi devicefingerprints
//     $fingerprints = Fingerprints::with('devicefingerprints')
//         ->select(['sn', 'scan_date', 'pin', 'inoutmode'])
//         ->whereDate('scan_date', '>=', '2025-07-01')
//         ->orderBy('scan_date')
//         ->get();

//     // Kelompokkan berdasarkan PIN + tanggal (tanpa jam)
//     $grouped = $fingerprints->groupBy(function ($item) {
//         return $item->pin . '_' . date('Y-m-d', strtotime($item->scan_date));
//     });

//     $result = [];

//     foreach ($grouped as $group) {
//         $first = $group->first();
//         $pin = $first->pin;
//         $scanDate = date('Y-m-d', strtotime($first->scan_date));

//         $employee = $employees->get($pin);

//         $row = [
//             'pin' => $pin,
//             'employee_name' => $employee->employee_name ?? '-',
//             'position_name' => $employee->position->name ?? '-',
//             'device_name' => $first->devicefingerprints->device_name ?? '-',
//             'scan_date' => $scanDate,
//         ];

//         // Inisialisasi kolom in_1 sampai in_10
//         for ($i = 1; $i <= 10; $i++) {
//             $row['in_' . $i] = null;
//         }

//         // Kelompokkan group fingerprint berdasarkan inoutmode
//         $byMode = $group->groupBy('inoutmode');

//         foreach ($byMode as $mode => $items) {
//             if ($mode >= 1 && $mode <= 10) {
//                 $firstScan = $items->sortBy('scan_date')->first();
//                 $row['in_' . $mode] = $firstScan->scan_date;
//             }
//         }

//         $result[] = $row;
//     }

//     // Urutkan berdasarkan scan_date ASC
//     usort($result, function ($a, $b) {
//         return strtotime($a['scan_date']) <=> strtotime($b['scan_date']);
//     });

//     return DataTables::of(collect($result))->make(true);
// }




    // public function getFingerprints(Request $request)
    // {
    //     // Ambil semua employee + position dalam 1 query
    //     ini_set('memory_limit', '1024M');
    //     $employees = Employee::with('position')->get()->keyBy('pin');

    //     $query = Fingerprints::with('devicefingerprints')
    //         ->select([
    //             'sn',
    //             DB::raw('DATE(scan_date) as scan_date'),
    //             'pin',
    //             'verifymode',
    //             'inoutmode'
    //         ])
    //         ->whereDate('scan_date', '>=', '2025-07-01');

    //     return DataTables::of($query)
    //         ->addColumn('employee_name', function ($row) use ($employees) {
    //             return $employees[$row->pin]->employee_name ?? '-';
    //         })
    //         ->addColumn('position_name', function ($row) use ($employees) {
    //             return $employees[$row->pin]->position->name ?? '-';
    //         })
    //         ->addColumn('device_name', function ($row) {
    //             return $row->devicefingerprints->device_name ?? '-';
    //         })
    //         ->filterColumn('employee_name', function ($query, $keyword) {
    //             $pins = DB::table('employees_tables')
    //                 ->where('employee_name', 'like', "%{$keyword}%")
    //                 ->pluck('pin');

    //             $query->whereIn('pin', $pins);
    //         })
    //         ->filterColumn('device_name', function ($query, $keyword) {
    //             $sns = DB::connection('mysql_second')
    //                 ->table('device')
    //                 ->where('device_name', 'like', "%{$keyword}%")
    //                 ->pluck('sn');

    //             $query->whereIn('sn', $sns);
    //         })
    //         ->make(true);
    // }
    

// public function getFingerprints(Request $request)
// {
//     ini_set('memory_limit', '1024M');

//     $employees = Employee::with('position')->get()->keyBy('pin');

//     $rows = DB::table('fingerprints')
//         ->select('pin', 'scan_date', 'inoutmode')
//         ->whereDate('scan_date', '>=', '2025-07-01')
//         ->orderBy('scan_date')
//         ->get()
//         ->groupBy(function ($item) {
//             return $item->pin . '_' . date('Y-m-d', strtotime($item->scan_date)); // Group by pin + date
//         });

//     $data = [];

//     foreach ($rows as $key => $groupedRows) {
//         $first = $groupedRows->first();
//         $pin = $first->pin;
//         $date = date('Y-m-d', strtotime($first->scan_date));

//         $row = [
//             'pin' => $pin,
//             'employee_name' => $employees[$pin]->employee_name ?? '-',
//             'position_name' => $employees[$pin]->position->name ?? '-',
//         ];

//         // mapping setiap inoutmode jadi kolom
//         foreach ($groupedRows as $item) {
//             $modeKey = 'in_' . $item->inoutmode;
//             $row[$modeKey] = $item->scan_date;
//         }

//         $data[] = $row;
//     }

//     return DataTables::of(collect($data))
//         ->make(true);
// }


}