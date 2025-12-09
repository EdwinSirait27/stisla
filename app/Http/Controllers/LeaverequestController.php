<?php

namespace App\Http\Controllers;

use App\Models\Leaverequest;
use App\Models\Leavebalance;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LeaverequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
$employee = $user->employee->employee_name;
        return view('pages.Leaverequest.Leaverequest',compact('employee'));
    }
    public function create()
{
    $leaveBalances = Leavebalance::where('employee_id', auth()->user()->employee_id)->get();

    return view('pages.Leaverequest.create', compact('leaveBalances'));
}
    //  public function getLeaverequests()
    // {
    //     $employeeId = Auth::user()->employee_id;
    //     $leaves = Leaverequest::with(['leavebalance', 'approver'])
    //         ->select(['id', 'leave_balance_id', 'start_date', 'end_date', 'employee_reason','company_id','department_id'])
    //         ->get()
    //         ->map(function ($leave) {
    //             $leave->id_hashed = substr(hash('sha256', $leave->id . env('APP_KEY')), 0, 8);
    //             $showButton = '
    //     <a href="' . route('Leaverequest.show', $leave->id_hashed) . '" 
    //        class="mx-2" 
    //        data-bs-toggle="tooltip" 
    //        data-bs-original-title="View details" 
    //        title="Show Leave Request: ' . e($leave->leavebalance->employees->employee_name) . '">
    //         <i class="fas fa-eye "></i>
    //     </a>';

               
    //                 $editButton = '
    //         <a href="' . route('Positionreqlist.edit', $leave->id_hashed) . '" 
    //            class="mx-2" 
    //            data-bs-toggle="tooltip" 
    //            data-bs-original-title="Edit request" 
    //            title="Edit leave request: ' . e($leave->leavebalance->employees->employee_name) . '">
    //             <i class="fas fa-user-edit "></i>
    //         </a>';

    //             $leave->action = $showButton . $editButton;
    //             return $leave;
    //         });
       
    //       return DataTables::of($leaves)
    //         ->rawColumns(['action'])
           
    //         ->make(true);
    // }
    public function getLeaverequests()
{
    $employeeId = Auth::user()->employee_id;

    $leaves = Leaverequest::with(['leavebalance', 'approver'])
        ->whereHas('leavebalance', function ($q) use ($employeeId) {
            $q->where('employee_id', $employeeId);
        })
        ->select(['id', 'leave_balance_id', 'start_date', 'end_date','status','approved_by'])
        ->get()
        ->map(function ($leave) {

            $leave->id_hashed = substr(hash('sha256', $leave->id . env('APP_KEY')), 0, 8);
            // --- Button Show ---
            $showButton = '
                <a href="' . route('Leaverequest.show', $leave->id_hashed) . '" 
                   class="mx-2" 
                   data-bs-toggle="tooltip" 
                   data-bs-original-title="View details"
                   title="Show Leave Request: ' . e($leave->leavebalance->employees->employee_name) . '">
                    <i class="fas fa-eye"></i>
                </a>';
            // --- Button Edit ---
            $editButton = '
                <a href="' . route('Leaverequest.edit', $leave->id_hashed) . '" 
                   class="mx-2" 
                   data-bs-toggle="tooltip" 
                   data-bs-original-title="Edit request"
                   title="Edit Leave Request: ' . e($leave->leavebalance->employees->employee_name) . '">
                    <i class="fas fa-user-edit"></i>
                </a>';

            $leave->action = $showButton . $editButton;
            return $leave;
        });

    return DataTables::of($leaves)
            ->addColumn('approver', fn($e) => optional($e->approver)->employee_name ?? 'empty')
        ->rawColumns(['action'])
        ->make(true);
}

    public function store(Request $request)
    {
        $request->validate([
            'leave_balance_id' => 'required|exists:leave_balances_tables,id',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date',
            'reason'           => 'nullable|string',
        ]);

        $balance = Leavebalance::findOrFail($request->leave_balance_id);

        $start = Carbon::parse($request->start_date);
        $end   = Carbon::parse($request->end_date);
        $totalDays = $start->diffInDays($end) + 1;

        // create request
        $leaveRequest = Leaverequest::create([
            'leave_balance_id' => $balance->id,
            'employee_id'      => $balance->employee_id,
            'start_date'       => $request->start_date,
            'end_date'         => $request->end_date,
            'total_days'       => $totalDays,
            'status'           => 'Pending',
            'reason'           => $request->reason,
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => $leaveRequest,
        ]);
    }

    public function approve($id)
    {
        $leaveRequest = Leaverequest::with('leavebalance')->findOrFail($id);

        $employeeId = auth()->user()->employee_id;

        if (!$leaveRequest->canBeApprovedBy($employeeId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not allowed to approve this request'
            ], 403);
        }

        // potong saldo
        $balance = $leaveRequest->leavebalance;

        if ($balance->balance_days < $leaveRequest->total_days) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient leave balance'
            ], 400);
        }

        $balance->balance_days -= $leaveRequest->total_days;
        $balance->save();

        $leaveRequest->update([
            'status'      => 'Approved',
            'approved_by' => $employeeId,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Leave request approved'
        ]);
    }

    public function reject($id)
    {
        $leaveRequest = Leaverequest::findOrFail($id);

        $employeeId = auth()->user()->employee_id;

        if (!$leaveRequest->canBeApprovedBy($employeeId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not allowed to reject this request'
            ], 403);
        }

        $leaveRequest->update([
            'status'      => 'Rejected',
            'approved_by' => $employeeId,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Leave request rejected'
        ]);
    }
//     public function index()
//     {
//         return view('pages.Leaverequest.Leaverequest');
//     }
   
//     public function store(Request $request)
// {
//     $request->validate([
//         'leave_balance_id' => 'required|exists:leave_balances_tables,id',
//         'start_date' => 'required|date',
//         'end_date'   => 'required|date',
//         'reason'     => 'nullable|string',
//     ]);

//     $leaveRequest = LeaveRequest::create([
//         'leave_balance_id' => $request->leave_balance_id,
//         'start_date'        => $request->start_date,
//         'end_date'          => $request->end_date,
//         'reason'            => $request->reason,
//     ]);

//     return response()->json([
//         'status' => 'success',
//         'data'   => $leaveRequest,
//     ]);
// }
// public function approve($id)
// {
//     $leaveRequest = LeaveRequest::findOrFail($id);

//     $employeeId = auth()->user()->employee_id; // dari login

//     if (!$leaveRequest->canBeApprovedBy($employeeId)) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'You are not allowed to approve this request'
//         ], 403);
//     }

//     $leaveRequest->update([
//         'status' => 'Approved',
//         'approved_by' => $employeeId,
//     ]);

//     return response()->json([
//         'status' => 'success',
//         'message' => 'Leave request approved'
//     ]);
// }
// public function reject($id)
// {
//     $leaveRequest = LeaveRequest::findOrFail($id);

//     $employeeId = auth()->user()->employee_id;

//     if (!$leaveRequest->canBeApprovedBy($employeeId)) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'You are not allowed to reject this request'
//         ], 403);
//     }

//     $leaveRequest->update([
//         'status' => 'Rejected',
//         'approved_by' => $employeeId,
//     ]);

//     return response()->json([
//         'status' => 'success',
//         'message' => 'Leave request rejected'
//     ]);
// }

 // public function store(Request $request)
    // {
    //     $request->validate([
    //         'leave_type_id' => 'required',
    //         'start_date'    => 'required|date',
    //         'end_date'      => 'required|date',
    //         'reason'        => 'required|string',
    //     ]);

    //     $employee = auth()->user()->employee;

    //     // PANGGIL method di model Employee — bukan fungsi global
    //     $supervisors = $employee->getAllSupervisors();

    //     if ($supervisors->isEmpty()) {
    //         return back()->with('error', 'Anda belum memiliki atasan yang terdaftar.');
    //     }

    //     $start = Carbon::parse($request->start_date);
    //     $end   = Carbon::parse($request->end_date);
    //     $totalDays = $start->diffInDays($end) + 1;

    //     $leave = Leaverequest::create([
    //         'employee_id'   => $employee->id,
    //         'leave_type_id' => $request->leave_type_id,
    //         'start_date'    => $start->toDateString(),
    //         'end_date'      => $end->toDateString(),
    //         'total_days'    => $totalDays,
    //         'reason'        => $request->reason,
    //         'status'        => 'Pending',
    //     ]);

    //     // buat approval rows (sequence sesuai urutan collection)
    //     foreach ($supervisors as $i => $sup) {
    //         LeaveRequestApproval::create([
    //             'leave_request_id' => $leave->id,
    //             'supervisor_id'    => $sup->id,
    //             'sequence'         => $i + 1,
    //             'status'           => 'Pending',
    //         ]);
    //     }

    //     return redirect()->route('leave.index')->with('success', 'Leave request submitted.');
    // }
}
