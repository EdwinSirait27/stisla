<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Stores;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Submissionposition;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Mail\Sendpositionrequesttohr;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
class StructureSubmissionController extends Controller
{
      public function index()
    {
        $user = Auth::user();
        return view('pages.Positionrequest.Positionrequest', compact('user'));
    }
public function getPositionrequests()
{
    $employeeId = Auth::user()->employee_id;

    $positions = Submissionposition::with(['submitter','approver1','approver2','positionRelation','store','company','department'])
        ->select(['id','employee_id','approver_1','approver_2','status','position_id','store_id'])
        ->where('employee_id', $employeeId)
        ->get()
        ->map(function ($position) {
            $position->id_hashed = substr(hash('sha256', $position->id . env('APP_KEY')), 0, 8);

            // Daftar status yang dikunci untuk aksi edit
            $lockedStatuses = ['On Review HR','Approved HR','Done', 'Reject', 'Accepted','Sent','Done'];

            // Tombol Show selalu muncul
            $showButton = '
                <a href="' . route('Positionrequest.show', $position->id_hashed) . '" 
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
                    <a href="' . route('Positionrequest.edit', $position->id_hashed) . '" 
                       class="mx-2" 
                       data-bs-toggle="tooltip" 
                       data-bs-original-title="Edit request" 
                       title="Edit Positionrequest: ' . e($position->position_name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
            }
            // Gabungkan action
            $position->action = $showButton . $editButton;

            return $position;
        });

    return DataTables::of($positions)
        ->addColumn('approver1', fn($e) => optional($e->approver1)->employee_name ?? 'empty')
        ->addColumn('approver2', fn($e) => optional($e->approver2)->employee_name ?? 'empty')
        ->addColumn('position_name', fn($e) => optional($e->positionRelation)->name ?? 'empty')
        ->addColumn('store_name', fn($e) => optional($e->store)->name ?? 'empty')
        ->addColumn('remark', function ($e) {
            return match ($e->status) {
                'Sent'   => 'Your application has been sent to the HR Department',
                'Draft'     => 'Your application status is Draft, you can still edit this appication',
                'On Review HR' => 'Your application is being reviewed by the HR Department',
                'Approved HR' => 'Your application has been reviewed by the HR Department, This application will be sent to the director',
                'On Review DIR' => 'Your application has been is being reviewed by the Directors',
                'Reject'    => 'Your application has been Rejected, click show to see the reason',
                'Done'    => 'Your application has entered the structure',
                'Accepted'  => 'Your application has been approved by directors',
                default     => '-',
            };
        })
        ->rawColumns(['action'])
        ->make(true);
}


    // public function edit($hashedId)
    // {
    //     $submission = Submissionposition::with('submitter','approver1','approver2','store','position')->get()->first(function ($u) use ($hashedId) {
    //         $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
    //         return $expectedHash === $hashedId;
    //     });
    //   $stores = Stores::get()->pluck('name', 'id');
    //     $positions = Position::get()->pluck('name', 'id');


    //     if (!$submission) {
    //         abort(404, 'Position not found.');
    //     }
    //         $types= ['Full Time', 'Part Time', 'Contract','Internship','Remote','Urgent'];
    //     return view('pages.Positionrequest.edit', [
    //         'submission' => $submission,
    //         'positions' => $positions,
    //         'stores' => $stores,
    //         'types' => $types,
    //         'hashedId' => $hashedId       
    //     ]);
    // }
     public function edit($hashedId)
    {
        $submission = Submissionposition::with('submitter','approver1','approver2','store','positionRelation')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$submission) {
            abort(404, 'Position not found.');
        }
            $stores = Stores::get()->pluck('name','id');
        $positions = Position::get()->pluck('name', 'id');
            $types= ['Full Time', 'Part Time', 'Contract','Internship','Remote','Urgent'];
            $statuses= ['Draft','Sent'];
        return view('pages.Positionrequest.edit', [
            'submission' => $submission,
            'stores' => $stores,
            'statuses' => $statuses,
            'positions' => $positions,
            'types' => $types,
            'hashedId' => $hashedId
        ]);
    }
    public function show($hashedId)
    {
        $submission = Submissionposition::with('submitter','approver1','approver2','positionRelation','store','company','department')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$submission) {
            abort(404, 'submission not found.');
        }
          $badgeColors = [
        'Accepted'   => 'success',
        'Approved HR'   => 'success',
        'On Review HR'   => 'info',
        'On Review DIR'   => 'info',
        'Draft'      => 'secondary',
        'Sent'      => 'secondary',
        'Reject'      => 'danger',
        'Done'      => 'success',
        // 'Accepted'   => 'success',
        // 'On review'   => 'warning',
        // 'Pending'    => 'secondary',
        // 'Draft'      => 'info',
        // 'Reject'      => 'danger',
        //    'Sent'   => 'Your application has been sent to the HR department',
        //         'Draft'     => 'Your application status is Draft, you can still edit this appication',
        //         'On Review HR' => 'Your application is being reviewed by the HR Department',
        //         'Reviewed HR' => 'Your application has been reviewed by the HR Department, This application will be sent to the director',
        //         'On Review DIR' => 'Your application has been is being reviewed by the Directors',
        //         'Reviewed DIR' => 'Your application has been reviewed by the Directors, waiting directors approval',
        //         'Reject'    => 'Your application has been Rejected, click show to see the reason',
        //         'Accepted'  => 'Your application has been approved by directors',
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
        return view('pages.Positionrequest.show', [
            'submission' => $submission,
            'positions' => $positions,
            'stores' => $stores,
            'types' => $types,
            'hashedId' => $hashedId       
        ]);
    }
    public function create()
    {
            $types= ['Full Time', 'Part Time', 'Contract','Internship','Remote','Urgent'];
      $stores = Stores::get()->pluck('name','id');
        $positions = Position::get()->pluck('name', 'id');
        //  $stores = Stores::pluck( 'id', 'name');
        // $positions = Position::pluck( 'id', 'name');
       
        return view('pages.Positionrequest.create', compact('types','stores','positions'));
    }

    // public function store(Request $request)
    // {
      
    //     $validatedData = $request->validate([
    //         'position_id' => ['required', 'string'],
    //         'company_id' => ['nullable', 'string'],
    //         'department_id' => ['nullable', 'string'],
    //         'store_id' => ['required', 'string'],
    //         'role_summary' => ['required', 'string'],
    //         'key_respon' => ['required', 'string'],
    //         'qualifications' => ['required', 'string'],
    //         'notes' => ['nullable', 'string','max:255'],
    //         'status' => ['nullable', 'string','max:255'],
            
    //     ]);
    //     try {
    //         DB::beginTransaction();
    //         $position = Submissionposition::create([
    //             'employee_id'    => Auth::user()->employee_id,
    //             'position_id' => $validatedData['position_id'], 
    //             'store_id' => $validatedData['store_id'], 
    //             'role_summary' => $validatedData['role_summary'], 
    //             'key_respon' => $validatedData['key_respon'], 
    //             'qualifications' => $validatedData['qualifications'], 
    //             'status' => $validatedData['status'] ?? 'Draft', 
            
    //             'notes' => $validatedData['notes'] ?? null, 
    //         ]);
    //         DB::commit();
    //         return redirect()->route('pages.Positionrequest')->with('success', 'Request created Succesfully!');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()
    //             ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
    //             ->withInput();
    //     }
    // }
    public function store(Request $request)
{
    $validatedData = $request->validate([
        'position_id' => ['required', 'string'],
        'company_id' => ['nullable', 'string'],
        'department_id' => ['nullable', 'string'],
        'store_id' => ['required', 'string'],
        'role_summary' => ['required', 'string'],
        'key_respon' => ['required', 'string'],
        'qualifications' => ['required', 'string'],
        'notes' => ['nullable', 'string', 'max:255'],
        'status' => ['nullable', 'string', 'max:255'],
    ]);

    try {
        DB::beginTransaction();

        $user = Auth::user();
        $employee = $user->employee ?? null;

        $position = Submissionposition::create([
            'employee_id'   => $user->employee_id,
            'position_id'   => $validatedData['position_id'],
            'store_id'      => $validatedData['store_id'],
            'role_summary'  => $validatedData['role_summary'],
            'key_respon'    => $validatedData['key_respon'],
            'qualifications'=> $validatedData['qualifications'],
            'status'        => $validatedData['status'] ?? 'Draft',
            'notes'         => $validatedData['notes'] ?? null,

            // Tambahan: ambil dari user yang sedang login
            'company_id'    => $employee->company_id ?? $validatedData['company_id'] ?? null,
            'department_id' => $employee->department_id ?? $validatedData['department_id'] ?? null,
        ]);

        DB::commit();

        return redirect()->route('pages.Positionrequest')
            ->with('success', 'Request created successfully!');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
            ->withInput();
    }
}

    public function update(Request $request, $hashedId)
{
    $position = Submissionposition::with('submitter','approver1','approver2')->get()->first(function ($u) use ($hashedId) {
        $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
        return $expectedHash === $hashedId;
    });

    if (!$position) {
        return redirect()->route('pages.Positionrequest')->with('error', 'ID tidak valid.');
    }

    $validatedData = $request->validate([
        'role_summary'   => ['required', 'string'],
        'key_respon'     => ['required', 'string'],
        'store_id'       => ['required', 'string'],
        'position_id'    => ['required', 'string'],
        'qualifications' => ['required', 'string'],
        'status'         => ['required', 'string'],
        'notes'          => ['nullable', 'string', 'max:255'],
    ]);

    $positionData = [
        'position_id'   => $validatedData['position_id'],
        'store_id'      => $validatedData['store_id'],
        'role_summary'  => $validatedData['role_summary'],
        'key_respon'    => $validatedData['key_respon'],
        'status'        => $validatedData['status'],
        'qualifications'=> $validatedData['qualifications'],
        'notes'         => $validatedData['notes'] ?? null,
    ];

    DB::beginTransaction();
    $position->update($positionData);
    DB::commit();
if ($validatedData['status'] === 'Sent') {
    $headHRUsers = User::role('HeadHR')
        ->whereHas('employee', function ($query) {
            $query->where('status', 'Active');
        })
        ->with('employee')
        ->get();
    foreach ($headHRUsers as $hr) {
        if ($hr->employee && $hr->employee->email) {
            Mail::to($hr->employee->email)->send(new Sendpositionrequesttohr($position));
        }
    }
}
    return redirect()->route('pages.Positionrequest')->with('success', 'Position Request Update Successfully.');
}
    //     public function getPositionrequests()
// {
//     $employeeId = Auth::user()->employee_id;

//     $positions = Submissionposition::with(['submitter','approver1','approver2'])
//         ->select(['id','position_name','employee_id','approver_1','approver_2','status'])
//         ->where('employee_id', $employeeId)
//         ->get()
//         ->map(function ($position) {
//             $position->id_hashed = substr(hash('sha256', $position->id . env('APP_KEY')), 0, 8);
//             $position->action = '
//                 <a href="' . route('Positionrequest.edit', $position->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user" title="Edit Positionrequest: ' . e($position->position_name) . '">
//                     <i class="fas fa-user-edit text-secondary"></i>
//                 </a>';
//             return $position;
//         });

//     return DataTables::of($positions)
//         ->addColumn('approver1', fn($e) => optional($e->approver1)->employee_name ?? 'Empty')
//         ->addColumn('approver2', fn($e) => optional($e->approver2)->employee_name ?? 'Empty')
//         ->rawColumns(['action'])
//         ->make(true);
// }
// public function getPositionrequests()
// {
//     $employeeId = Auth::user()->employee_id;

//     $positions = Submissionposition::with(['submitter','approver1','approver2','positionRelation','store'])
//         ->select(['id','position_name','employee_id','approver_1','approver_2','status','position_id','store_id'])
//         ->where('employee_id', $employeeId)
//         ->get()
//         ->map(function ($position) {
//             $position->id_hashed = substr(hash('sha256', $position->id . env('APP_KEY')), 0, 8);

//             // Cek apakah status termasuk yang dikunci
//             $lockedStatuses = ['On review', 'Reject', 'Accepted','Draft'];

//             if (in_array($position->status, $lockedStatuses)) {
//                 $position->action = '
//                     <i class="fas fa-lock text-muted mx-3" 
//                        data-bs-toggle="tooltip" 
//                        title="locked because status: ' . e($position->status) . '"></i>';
//             } else {
//                 $position->action = '
//                     <a href="' . route('Positionrequest.edit', $position->id_hashed) . '" 
//                        class="mx-3" 
//                        data-bs-toggle="tooltip" 
//                        data-bs-original-title="Edit user" 
//                        title="Edit Positionrequest: ' . e($position->position_name) . '">
//                         <i class="fas fa-user-edit text-secondary"></i>
//                     </a>
//                     <a href="' . route('Positionrequest.show', $position->id_hashed) . '" 
//                        class="mx-3" 
//                        data-bs-toggle="tooltip" 
//                        data-bs-original-title="Show user" 
//                        title="Edit Positionrequest: ' . e($position->position_name) . '">
//                         <i class="fas fa-show text-secondary"></i>
//                     </a>
//                     ';
//             }

//             return $position;
//         });

//     return DataTables::of($positions)
//         ->addColumn('approver1', fn($e) => optional($e->approver1)->employee_name ?? 'Pending Approval')
//         ->addColumn('approver2', fn($e) => optional($e->approver2)->employee_name ?? 'Pending Approval')
//         ->addColumn('position_name', fn($e) => optional($e->positionRelation)->name ?? 'empty')
//         ->addColumn('store_name', fn($e) => optional($e->store)->name ?? 'empty')
//         ->addColumn('remark', function ($e) {
//             return match ($e->status) {
//                 'Pending' => 'Your application has not been reviewed by the HR Department',
//                 'Draft' => 'Your application is being reviewed by the HR Department',
//                 'On review' => 'Your application has been approved by the HR Department, awaiting directors approval',
//                 'Reject' => 'Your application has been Rejected, click show to see the reason',
//                 'Accepted' => 'Your application has been approved by the HR Department, awaiting directors approval',
//                 default => '-',
//             };
//         })
//         ->rawColumns(['action'])
//         ->make(true);
// }
    // public function index()
    // {
    //     return view('pages.Submissionstructure.Submissionstructure');
    // }
// public function getSubmissionstructures()
//     {
//         $submissions = Structuresnew::with('position','approval1.structuresnew.position','approval2.structuresnew.position')->select(['id','position_id','approval_1','approval_2','reason_reject','submission_status'])
//             ->get()
//             ->map(function ($submission) {
//                 $submission->id_hashed = substr(hash('sha256', $submission->id . env('APP_KEY')), 0, 8);
//                 $submission->action = '
//                     <a href="' . route('Submissionstructure.edit', $submission->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit Submissions structure: ' . e($submission->position->name) . '">
//                         <i class="fas fa-user-edit text-secondary"></i>
//                     </a>';
//                 return $submission;
//             });
//         return DataTables::of($submissions)
//             ->rawColumns(['action'])
//             ->make(true);
//     }
// public function getSubmissionstructures()
// {
//     // Ambil employee_id dari user yang sedang login
//     $employeeId = auth()->user()->employee_id;

//     // Ambil data Structuresnew yang submitter-nya sama dengan employee login
//     $submissions = Structuresnew::with([
//             'position',
//             'approval1.structuresnew.position',
//             'approval2.structuresnew.position'
//         ])
//         ->where('submitter', $employeeId)
//         ->select(['id','position_name','position_id','approval_1','approval_2','reason_reject','submission_status'])
//         ->get()
//         ->map(function ($submission) {
//             $submission->id_hashed = substr(hash('sha256', $submission->id . env('APP_KEY')), 0, 8);
//             $submission->action = '
//                 <a href="' . route('Submissionstructure.edit', $submission->id_hashed) . '" 
//                    class="mx-3" 
//                    data-bs-toggle="tooltip" 
//                    data-bs-original-title="Edit user"
//                    title="Edit Submissions structure: ' . e($submission->position->name) . '">
//                     <i class="fas fa-user-edit text-secondary"></i>
//                 </a>';
//             return $submission;
//         });
//     return DataTables::of($submissions)
//         ->rawColumns(['action'])
//         ->make(true);
// }
//   public function edit($hashedId)
//     {
//         $submission = Structuresnew::get()->first(function ($u) use ($hashedId) {
//             $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
//             return $expectedHash === $hashedId;
//         });
//         if (!$submission) {
//             abort(404, 'Submission Structure not found.');
//         }
//         return view('pages.Submissionstructure.edit', [
//             'submission' => $submission,
//             'hashedId' => $hashedId
//         ]);
//     }
//     public function create()
//     {
        
//         return view('pages.Submissionstructure.create');
//     }
//      public function store(Request $request)
//     {
        
//         $validatedData = $request->validate([
//             'position_name' => ['required', 'string','max:255', new NoXSSInput()],
            
//         ], [
//             'name.required' => 'name wajib diisi.',
//             'name.string' => 'name hanya boleh berupa teks.',
//             'name.max' => 'Username maksimal terdiri dari 255 karakter.',
//         ]);
//         try {
//             DB::beginTransaction();
//             $submission = Structuresnew::create([
//                 'name' => $validatedData['name'], 
//             ]);
//             DB::commit();
//             return redirect()->route('pages.Submissionstructure')->with('success', 'Submissions Structure created Succesfully!');
//         } catch (\Exception $e) {
//             DB::rollBack();
//             return redirect()->back()
//                 ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
//                 ->withInput();
//         }
//     }

}

