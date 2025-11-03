<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Submissionposition;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\DB;

class PositionreqController extends Controller
{
       public function index()
    {
      
        return view('pages.Positionreqlist.Positionreqlist');
    }
        public function getPositionreqlists()
{
    
    $positions = Submissionposition::with(['submitter','approver1','approver2'])
        ->select(['id','employee_id','position_name','employee_id','approver_1','approver_2','status'])
        ->get()
        ->map(function ($position) {
            $position->id_hashed = substr(hash('sha256', $position->id . env('APP_KEY')), 0, 8);
            $position->action = '
                <a href="' . route('Positionreqlist.edit', $position->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user" title="Edit Positionrequest: ' . e($position->position_name) . '">
                    <i class="fas fa-user-edit text-secondary"></i>
                </a>';
            return $position;
        });

    return DataTables::of($positions)
        ->addColumn('sub', fn($e) => optional($e->submitter)->employee_name ?? 'Empty')
        ->addColumn('approver1', fn($e) => optional($e->approver1)->employee_name ?? 'Pending Approval')
        ->addColumn('approver2', fn($e) => optional($e->approver2)->employee_name ?? 'Pending Approval')
        ->rawColumns(['action'])
        ->make(true);
}
 public function edit($hashedId)
    {
        $position = Submissionposition::with('submitter','approver1','approver2')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$position) {
            abort(404, 'Position not found.');
        }
            $types= ['Full Time', 'Part Time', 'Contract','Internship','Remote','Urgent'];
            $statuses= ['Pending','On review','Reject'];
        return view('pages.Positionreqlist.edit', [
            'position' => $position,
            'statuses' => $statuses,
            'types' => $types,
            'hashedId' => $hashedId       
        ]);
    }
    //  public function update(Request $request, $hashedId)
    // {
    //     $position = Submissionposition::with('submitter','approver1','approver2')->get()->first(function ($u) use ($hashedId) {
    //         $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
    //         return $expectedHash === $hashedId;
    //     });
    //     if (!$position) {
    //         return redirect()->route('pages.Position')->with('error', 'ID tidak valid.');
    //     }
    //    $validatedData = $request->validate([
    //     'status'          => ['required', 'string', 'max:255'],
    //     'reason_reject'          => ['nullable', 'string', 'max:255'],
    // ], [
    //     'status.required' => 'status must be filled.',
    // ]);

    //     $positionData = [
    //         'status'         => $validatedData['status'],
    //         'reason_reject'         => $validatedData['reason_reject'] ?? null,
    //     ];
    //     DB::beginTransaction();
    //     $position->update($positionData);
    //     DB::commit();

    //     return redirect()->route('pages.Positionrequest')->with('success', 'Position Request Update Successfully.');
    // }
    public function update(Request $request, $hashedId)
{
    $position = Submissionposition::with('submitter', 'approver1', 'approver2')
        ->get()
        ->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

    if (!$position) {
        return redirect()->route('pages.Position')->with('error', 'ID tidak valid.');
    }

    $validatedData = $request->validate([
        'status'        => ['required', 'string', 'max:255'],
        'reason_reject' => ['nullable', 'string', 'max:255'],
    ], [
        'status.required' => 'Status must be filled.',
    ]);

    // Siapkan data dasar untuk update
    $positionData = [
        'status'        => $validatedData['status'],
        'reason_reject' => $validatedData['reason_reject'] ?? null,
    ];

    // Jika status "On review" atau "Reject", isi kolom employee_id dengan milik user login
    if (in_array($validatedData['status'], ['On review', 'Reject'])) {
        $positionData['approver_1'] = auth()->user()->employee_id;
    }

    DB::beginTransaction();
    try {
        $position->update($positionData);
        DB::commit();
        return redirect()->route('pages.Positionreqlist')->with('success', 'Position Request updated successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Failed to update Position Request: ' . $e->getMessage());
    }
}

}
