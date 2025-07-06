<?php

namespace App\Http\Controllers;

use App\Models\Attendances;
use App\Models\Employee;
use Illuminate\Http\Request;
    use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SinkronPinFingerspotImport;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;
class AttendanceController extends Controller
{
    public function getAttendances()
{
    
    $attendances = Attendances::with([
        'Employee.position',
        'Employee.department',
    ])
    ->select(['id', 'employee_id','tanggal', 'kantor','jam_masuk','jam_keluar', 'jam_masuk2','jam_keluar2', 'jam_masuk3','jam_keluar3', 'jam_masuk4','jam_keluar4', 'jam_masuk5','jam_keluar5', 'jam_masuk6','jam_keluar6', 'jam_masuk7','jam_keluar7', 'jam_masuk8','jam_keluar8', 'jam_masuk9','jam_keluar9', 'jam_masuk10','jam_keluar10'])
    ->get();
    
    return DataTables::of($attendances)
        ->addColumn('position_name', fn($e) => optional(optional($e->Employee)->position)->name ?? 'Empty')
        ->addColumn('department_name', fn($e) => optional(optional($e->Employee)->department)->department_name ?? 'Empty')
        ->addColumn('employee_name', fn($e) => optional($e->Employee)->employee_name ?? 'Empty')
        ->rawColumns(['position_name','department_name', 'employee_name' ])
        ->make(true);
}
public function index(){
    return view ('pages.Attendance.Attendance');
}
}
