<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Yajra\DataTables\DataTables;
use App\Models\Structure;
use App\Models\User;
use App\Rules\NoXSSInput;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class SummaryController extends Controller
{
      public function index()
    {
        return view('pages.Summaries.Summaries');
    }
    public function getSummaries()
    {
        $summaries = User::whereHas('Employee', function ($q) {
            $q->whereIn('status', ['Active', 'Pending']);
        })
            ->with([
                'Employee' => function ($q) {
                    $q->whereIn('status', ['Active', 'Pending']);
                },
                'Employee.employees',
                'Employee.company',
            ])
            ->select(['id', 'employee_id'])
            ->get()
            ->map(function ($summarie) {
                $summarie->id_hashed = substr(hash('sha256', $summarie->id . env('APP_KEY')), 0, 8);
              
                return $summarie;
            });
        return DataTables::of($summaries)
            ->addColumn('employee_name', function ($summarie) {
                return !empty($summarie->Employee) && !empty($summarie->Employee->employee_name)
                    ? $summarie->Employee->employee_name
                    : 'Empty';
            })
            ->addColumn('total', function ($summarie) {
                return !empty($summarie->Employee) && !empty($summarie->Employee->total)
                    ? $summarie->Employee->total
                    : 'Empty';
            })
            ->addColumn('pending', function ($summarie) {
                return !empty($summarie->Employee) && !empty($summarie->Employee->pending)
                    ? $summarie->Employee->pending
                    : '0';
            })
            ->addColumn('approved', function ($summarie) {
                return !empty($summarie->Employee) && !empty($summarie->Employee->approved)
                    ? $summarie->Employee->approved
                    : '0';
            })
            ->addColumn('remaining', function ($summarie) {
                return !empty($summarie->Employee) && !empty($summarie->Employee->remaining)
                    ? $summarie->Employee->remaining
                    : '0';
            })
            ->rawColumns(['employee_name','total','pending','approved','remaining'])
            ->make(true);
    }
}
