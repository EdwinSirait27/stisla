<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Leavebalance;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class LeavebalancesController extends Controller
{
      public function index()
    {
        return view('pages.Leavesbalance.Leavesbalance');
    }
    // public function getLeavesbalances()
    // {
    //     $balances = Leavebalance::with('employees','leaves')->select(['id', 'employee_id','leave_type_id','balance_days','balance_hours','year'])
    //         ->get();
    //   return DataTables::of($balances)
    //     ->addColumn('employee_name', function ($row) {
    //         return $row->employees?->employee_name;
    //     })
    //     ->addColumn('leaves_type', function ($row) {
    //         return $row->leaves?->name ?? '-';
    //     })
    //     ->make(true);
    // }
    public function getLeavesbalances()
{
    $balances = Leavebalance::with(['employees', 'leaves'])
        ->whereHas('employees', function ($q) {
            $q->where('status', 'Active');
        })
        ->select(['id', 'employee_id','leave_type_id','balance_days','balance_hours','year'])
        ->get();

    return DataTables::of($balances)
        ->addColumn('employee_name', function ($row) {
            return $row->employees?->employee_name;
        })
        ->addColumn('leaves_type', function ($row) {
            return $row->leaves?->name ?? '-';
        })
        ->editColumn('balance_days', function ($row) {
        return rtrim(rtrim(number_format($row->balance_days, 1), '0'), '.') . ' days';
    })
        ->make(true);
}

}
