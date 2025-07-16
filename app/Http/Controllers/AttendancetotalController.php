<?php

namespace App\Http\Controllers;

use App\Models\Attendancetotal;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;


class AttendancetotalController extends Controller
{
     public function index()
    {
        
        return view('pages.Attendancetotal.Attendancetotal');
    }
     public function getAttendancetotals(Request $request)
    {
    
        $totalattendances = Attendancetotal::with([
            'attendance',
            'attendance.employee',
            
        ])
            ->select([
                'id',
                'attendance_id',
                'month',
                'total'
                
            ])
            ->get();
        return DataTables::of($totalattendances)
            ->addColumn('employee_name', fn($e) => optional(optional($e->attendance)->employee)->name ?? 'Empty')
            ->addColumn('department_name', fn($e) => optional(optional($e->attendance)->employee->department)->department_name ?? 'Empty')
            ->rawColumns(['employee_name'])
            ->make(true);
    }
    
}
