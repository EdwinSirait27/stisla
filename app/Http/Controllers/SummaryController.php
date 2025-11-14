<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;


class SummaryController extends Controller
{

    protected function totalTimeToil(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value
                ? sprintf('%d Hours %d Minutes', ...sscanf($value, '%d:%d:%d'))
                : '0 Hours 0 Minutes'
        );
    }


    public function index()
    {
        return view('pages.Summaries.Summaries');
    }
    public function getSummaries()
{
    $summaries = User::whereHas('Employee', function ($q) {
        $q->whereIn('status', ['Active','Pending','Mutation'])
          ->whereDate('join_date', '<=', now()->subYear()); // hanya yang join_date lebih dari 1 tahun
    })
        ->with([
            'Employee' => function ($q) {
                $q->whereIn('status', ['Active', 'Pending'])
                  ->whereDate('join_date', '<=', now()->subYear());
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
        ->addColumn('total_toil', function ($summarie) {
            $time = $summarie->Employee->total_time_toil ?? '00:00:00';
            if (is_numeric($time)) {
                $time = str_pad($time, 6, '0', STR_PAD_LEFT);
                $time = substr($time, 0, 2) . ':' . substr($time, 2, 2) . ':' . substr($time, 4, 2);
            }
            return $time;
        })
        ->rawColumns(['employee_name', 'total', 'pending', 'approved', 'remaining', 'total_toil'])
        ->make(true);
}

}
