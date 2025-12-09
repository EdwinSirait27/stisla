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

    public function getLeavesbalances()
    {
        // $balances = Leavebalance::with(['employees', 'leaves'])
        //     ->whereHas('employees', function ($q) {
        //         $q->where('status', 'Active');
        //     })
        //     ->select(['id', 'employee_id', 'leave_type_id', 'balance_days', 'balance_hours', 'year'])
        //     ->get()
        //      ->map(function ($balance) {
        //         $balance->id_hashed = substr(hash('sha256', $balance->id . env('APP_KEY')), 0, 8);
        //         $balance->action = '
        //             <a href="' . route('Leavesbalance.edit', $balance->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit balance: ' . e($balance->balance_days) . '">
        //                 <i class="fas fa-user-edit text-secondary"></i>
        //             </a>';
        //         return $balance;
        //     });
        $balances = Leavebalance::with(['employees', 'leaves'])
    ->whereHas('employees', function ($q) {
        $q->where('status', 'Active');
    })
    ->select(['id', 'employee_id', 'leave_type_id', 'balance_days', 'balance_hours', 'year'])
    ->get()
    ->map(function ($balance) {

        $balance->id_hashed = substr(hash('sha256', $balance->id . env('APP_KEY')), 0, 8);

        // Cek employee is_manager dari relasi
        $isManager = $balance->employees->is_manager ?? 1;

        // Kalau dia manager, kosongkan tombol edit
        if ($isManager == 0) {
            $balance->action = '';
        } else {
            // Tampilkan tombol edit seperti biasa
            $balance->action = '
                <a href="' . route('Leavesbalance.edit', $balance->id_hashed) . '" 
                    class="mx-3" 
                    data-bs-toggle="tooltip" 
                    data-bs-original-title="Edit user"
                    title="Edit balance: ' . e($balance->balance_days) . '">
                    <i class="fas fa-user-edit text-secondary"></i>
                </a>';
        }

        return $balance;
    });


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
            ->rawColumns(['action'])

            ->make(true);
    }
     public function edit($hashedId)
    {
        $balance = Leavebalance::with(['employees', 'leaves'])->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$balance) {
            abort(404, 'balance not found.');
        }

        
        
        return view('pages.Leavesbalance.edit', [
            'balance' => $balance,
            'hashedId' => $hashedId
            
        ]);
    }
     public function update(Request $request, $hashedId)
    {
        $balance = Leavebalance::with(['employees', 'leaves'])->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$balance) {
            return redirect()->route('pages.Leavesbalance')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'balance_days' => ['required', 'string', 'max:255', new NoXSSInput()],

        ], [
            'balance_days.required' => 'balance days wajib diisi.',
            'balance_days.string' => 'balance days hanya boleh berupa teks.',
        ]);

        $balanceData = [
            'balance_days' => $validatedData['balance_days'],
            
        ];
        DB::beginTransaction();
        $balance->update($balanceData);
        DB::commit();

        return redirect()->route('pages.Leavesbalance')->with('success', 'Leave Balance updated successfully.');
    }
}
