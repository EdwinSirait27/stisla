<?php

namespace App\Http\Controllers;
use App\Models\Fingerprints;
use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Rules\NoXSSInput;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
class FingerprintsController extends Controller
{
    public function index()
    {
        return view('pages.Fingerprints.Fingerprints');
    }


    // public function getFingerprints(Request $request)
// {
//     $query = Fingerprints::query()
//         ->select([
//             'sn',
//             DB::raw('DATE(scan_date) as scan_date'),
//             'pin',
//             'verifymode',
//             'inoutmode',
//             'reserved',
//             'work_code',
//             'att_id'
//         ])
//         ->whereDate('scan_date', '>=', '2025-07-07');

    //     return DataTables::of($query)
//         ->addColumn('employee_name', function ($row) {
//             $employee = DB::table('employees_tables')->where('pin', $row->pin)->first();
//             return $employee?->employee_name ?? '-';
//         })
//         ->make(true);
// }
// public function getFingerprints(Request $request)
// {
//     $query = Fingerprints::with('device')->query()
//         ->select([
//             'sn',
//             DB::raw('DATE(scan_date) as scan_date'),
//             'pin',
//             'verifymode',
//             'inoutmode'
//         ])
//         ->whereDate('scan_date', '>=', '2025-07-07');

    //     return DataTables::of($query)
//         ->addColumn('employee_name', function ($row) {
//             $employee = DB::table('employees_tables')->where('pin', $row->pin)->first();
//             return $employee?->employee_name ?? '-';
//         })

    //         // 🟡 Tambahkan filter manual untuk kolom employee_name
//         ->filterColumn('employee_name', function ($query, $keyword) {
//             $pins = DB::table('employees_tables')
//                 ->where('employee_name', 'like', "%{$keyword}%")
//                 ->pluck('pin');

    //             $query->whereIn('pin', $pins);
//         })

    //         ->make(true);
// }
// public function getFingerprints(Request $request)
// {
//     $query = Fingerprints::with('devicefingerprints') // relasi ke tabel device
//         ->select([
//             'sn',
//             DB::raw('DATE(scan_date) as scan_date'),
//             'pin',
//             'verifymode',
//             'inoutmode'
//         ])
//         ->whereDate('scan_date', '>=', '2025-01-01');

    //     return DataTables::of($query)
//         ->addColumn('employee_name', function ($row) {
//             $employee = DB::table('employees_tables')->where('pin', $row->pin)->first();
//             return $employee?->employee_name ?? '-';
//         })
//         ->addColumn('position_name', function ($row) {
//     $employee = Employee::with('position')->where('pin', $row->pin)->first();
//     return $employee?->position?->name ?? '-';
// })

    //         ->filterColumn('employee_name', function ($query, $keyword) {
//             $pins = DB::table('employees_tables')
//                 ->where('employee_name', 'like', "%{$keyword}%")
//                 ->pluck('pin');

    //             $query->whereIn('pin', $pins);
//         })

    //         ->addColumn('device_name', function ($row) {
//             return $row->devicefingerprints->device_name ?? '-';
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

    public function getFingerprints(Request $request)
    {
        // Ambil semua employee + position dalam 1 query
        ini_set('memory_limit', '1024M');
        $employees = Employee::with('position')->get()->keyBy('pin');

        $query = Fingerprints::with('devicefingerprints')
            ->select([
                'sn',
                DB::raw('DATE(scan_date) as scan_date'),
                'pin',
                'verifymode',
                'inoutmode'
            ])
            ->whereDate('scan_date', '>=', '2025-07-01');

        return DataTables::of($query)
            ->addColumn('employee_name', function ($row) use ($employees) {
                return $employees[$row->pin]->employee_name ?? '-';
            })
            ->addColumn('position_name', function ($row) use ($employees) {
                return $employees[$row->pin]->position->name ?? '-';
            })
            ->addColumn('device_name', function ($row) {
                return $row->devicefingerprints->device_name ?? '-';
            })
            ->filterColumn('employee_name', function ($query, $keyword) {
                $pins = DB::table('employees_tables')
                    ->where('employee_name', 'like', "%{$keyword}%")
                    ->pluck('pin');

                $query->whereIn('pin', $pins);
            })
            ->filterColumn('device_name', function ($query, $keyword) {
                $sns = DB::connection('mysql_second')
                    ->table('device')
                    ->where('device_name', 'like', "%{$keyword}%")
                    ->pluck('sn');

                $query->whereIn('sn', $sns);
            })
            ->make(true);
    }


}