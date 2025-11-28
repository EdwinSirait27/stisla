<?php

namespace App\Http\Controllers;

use App\Models\Leaverequest;
use App\Models\LeaveRequestApproval;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaverequestController extends Controller
{
    public function index()
    {
        return view('pages.Leaverequest.Leaverequest');
    }
    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date',
            'reason'        => 'required|string',
        ]);

        $employee = auth()->user()->employee;

        // PANGGIL method di model Employee — bukan fungsi global
        $supervisors = $employee->getAllSupervisors();

        if ($supervisors->isEmpty()) {
            return back()->with('error', 'Anda belum memiliki atasan yang terdaftar.');
        }

        $start = Carbon::parse($request->start_date);
        $end   = Carbon::parse($request->end_date);
        $totalDays = $start->diffInDays($end) + 1;

        $leave = Leaverequest::create([
            'employee_id'   => $employee->id,
            'leave_type_id' => $request->leave_type_id,
            'start_date'    => $start->toDateString(),
            'end_date'      => $end->toDateString(),
            'total_days'    => $totalDays,
            'reason'        => $request->reason,
            'status'        => 'Pending',
        ]);

        // buat approval rows (sequence sesuai urutan collection)
        foreach ($supervisors as $i => $sup) {
            LeaveRequestApproval::create([
                'leave_request_id' => $leave->id,
                'supervisor_id'    => $sup->id,
                'sequence'         => $i + 1,
                'status'           => 'Pending',
            ]);
        }

        return redirect()->route('leave.index')->with('success', 'Leave request submitted.');
    }
}
