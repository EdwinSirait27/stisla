<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Submissionposition;
use App\Models\Stores;
use App\Models\Position;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\DB;

class PositionreqController extends Controller
{
       public function index()
    {
      
        return view('pages.Positionreqlist.Positionreqlist');
    }

// public function getPositionreqlists()
// {
//     $positions = Submissionposition::with(['submitter', 'approver1', 'approver2','positionRelation','store'])
//         ->select(['id', 'employee_id','employee_id', 'approver_1', 'approver_2', 'status'])
//         ->get()
//         ->map(function ($position) {
//             $position->id_hashed = substr(hash('sha256', $position->id . env('APP_KEY')), 0, 8);
//   $showButton = '
//                 <a href="' . route('Positionreqlist.show', $position->id_hashed) . '" 
//                    class="mx-2" 
//                    data-bs-toggle="tooltip" 
//                    data-bs-original-title="View details" 
//                    title="Show Position Request: ' . e($position->positionRelation->name) . '">
//                     <i class="fas fa-eye "></i>
//                 </a>';
//             if (in_array($position->status, ['On review', 'Accepted'])) {
//                 $position->action = '
//                     <a href="javascript:void(0)" class="mx-3 text-muted" 
//                         data-bs-toggle="tooltip" title="Action locked for this status">
//                         <i class="fas fa-lock"></i>
//                     </a>';
//             } else {
//                 $position->action = '
//                     <a href="' . route('Positionreqlist.edit', $position->id_hashed) . '" 
//                         class="mx-3" data-bs-toggle="tooltip" 
//                         data-bs-original-title="Edit user" 
//                         title="Edit Positionrequest: ' . e($position->position_name) . '">
//                         <i class="fas fa-user-edit text-secondary"></i>
//                     </a>';
//             }

//             return $position;
//         });

//     return DataTables::of($positions)
//         ->addColumn('sub', fn($e) => optional($e->submitter)->employee_name ?? 'Empty')
//         ->addColumn('position_name', fn($e) => optional($e->positionRelation)->name ?? 'Pending Approval')
//         ->addColumn('store_name', fn($e) => optional($e->store)->name ?? 'Pending Approval')
//         ->addColumn('approver1', fn($e) => optional($e->approver1)->employee_name ?? 'Pending Approval')
//         ->addColumn('approver2', fn($e) => optional($e->approver2)->employee_name ?? 'Pending Approval')
//         ->addColumn('remark', function ($e) {
//             return match ($e->status) {
//                 'Pending' => 'Do your Duty',
//                 'Draft' => ' you have approved this application',
//                 'On review' => 'This application has been approved by you, awaiting directors approval',
//                 'Accepted' => 'This application has been accepted by directors',
//                 default => '-',
//             };
//         })
//         ->rawColumns(['action'])
//         ->make(true);
// }
public function getPositionreqlists()
{
    
    $positions = Submissionposition::with(['submitter','approver1','approver2','positionRelation','store'])
        ->select(['id','employee_id','approver_1','approver_2','status','position_id','store_id'])
        ->get()
        ->map(function ($position) {
            $position->id_hashed = substr(hash('sha256', $position->id . env('APP_KEY')), 0, 8);

            // Daftar status yang dikunci untuk aksi edit
            $lockedStatuses = ['On review', 'Accepted'];

            // Tombol Show selalu muncul
            $showButton = '
                <a href="' . route('Positionreqlist.show', $position->id_hashed) . '" 
                   class="mx-2" 
                   data-bs-toggle="tooltip" 
                   data-bs-original-title="View details" 
                   title="Show Position Request: ' . e($position->positionRelation->name) . '">
                    <i class="fas fa-eye "></i>
                </a>';

            // Jika status dikunci, edit digantikan dengan icon lock
            if (in_array($position->status, $lockedStatuses)) {
                $editButton = '
                    <i class="fas fa-lock text-muted mx-2" 
                       data-bs-toggle="tooltip" 
                       title="Edit locked because status: ' . e($position->status) . '"></i>';
            } else {
                $editButton = '
                    <a href="' . route('Positionreqlist.edit', $position->id_hashed) . '" 
                       class="mx-2" 
                       data-bs-toggle="tooltip" 
                       data-bs-original-title="Edit request" 
                       title="Edit Positionrequest: ' . e($position->positionRelation->name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
            }

            // Gabungkan action
            $position->action = $showButton . $editButton;

            return $position;
        });

    return DataTables::of($positions)
       ->addColumn('sub', fn($e) => optional($e->submitter)->employee_name ?? 'Empty')
        ->addColumn('position_name', fn($e) => optional($e->positionRelation)->name ?? 'Pending Approval')
        ->addColumn('store_name', fn($e) => optional($e->store)->name ?? 'Pending Approval')
        ->addColumn('approver1', fn($e) => optional($e->approver1)->employee_name ?? 'Pending Approval')
        ->addColumn('approver2', fn($e) => optional($e->approver2)->employee_name ?? 'Pending Approval')
        ->addColumn('remark', function ($e) {
            return match ($e->status) {
                'Pending' => 'Do your Duty',
                'Draft' => ' you have approved this application',
                'On review' => 'This application has been approved by you, awaiting directors approval',
                'Accepted' => 'This application has been accepted by directors',
                default => '-',
            };
        })
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
            $statuses= ['Pending','On review','Reject','Draft'];
        return view('pages.Positionreqlist.edit', [
            'position' => $position,
            'statuses' => $statuses,
            'types' => $types,
            'hashedId' => $hashedId       
        ]);
    }
       public function show($hashedId)
    {
        $submission = Submissionposition::with('submitter','approver1','approver2','positionRelation','store')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$submission) {
            abort(404, 'submission not found.');
        }
          $badgeColors = [
        'Accepted'   => 'success',
        'On review'   => 'warning',
        'Pending'    => 'secondary',
        'Draft'      => 'info',
        'Reject'      => 'danger',
    ];
     $submission->type_badges = collect(
        is_array($submission->status) ? $submission->status : explode(',', $submission->status)
    )->map(function ($t) use ($badgeColors) {
        $t = trim($t);
        return [
            'name' => $t,
            'color' => $badgeColors[$t] ?? 'primary',
        ];
    });
  $stores = Stores::get()->pluck('name','id');
        $positions = Position::get()->pluck('name', 'id');
            $types= ['Full Time', 'Part Time', 'Contract','Internship','Remote','Urgent'];
        return view('pages.Positionreqlist.show', [
            'submission' => $submission,
            'positions' => $positions,
            'stores' => $stores,
            'types' => $types,
            'hashedId' => $hashedId       
        ]);
    }
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
    if (in_array($validatedData['status'], ['On review', 'Reject','Draft'])) {
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
