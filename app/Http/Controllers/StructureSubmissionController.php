<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Submissionposition;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class StructureSubmissionController extends Controller
{
      public function index()
    {
        $user = Auth::user();

        return view('pages.Positionrequest.Positionrequest', compact('user'));
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
public function getPositionrequests()
{
    $employeeId = Auth::user()->employee_id;

    $positions = Submissionposition::with(['submitter','approver1','approver2'])
        ->select(['id','position_name','employee_id','approver_1','approver_2','status'])
        ->where('employee_id', $employeeId)
        ->get()
        ->map(function ($position) {
            $position->id_hashed = substr(hash('sha256', $position->id . env('APP_KEY')), 0, 8);

            // Cek apakah status termasuk yang dikunci
            $lockedStatuses = ['On review', 'Reject', 'Accepted'];

            if (in_array($position->status, $lockedStatuses)) {
                // Tidak bisa diedit → tampilkan ikon nonaktif
                $position->action = '
                    <i class="fas fa-lock text-muted mx-3" 
                       data-bs-toggle="tooltip" 
                       title="Tidak dapat diedit karena status: ' . e($position->status) . '"></i>';
            } else {
                // Bisa diedit → tampilkan tombol edit
                $position->action = '
                    <a href="' . route('Positionrequest.edit', $position->id_hashed) . '" 
                       class="mx-3" 
                       data-bs-toggle="tooltip" 
                       data-bs-original-title="Edit user" 
                       title="Edit Positionrequest: ' . e($position->position_name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
            }

            return $position;
        });

    return DataTables::of($positions)
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
        return view('pages.Positionrequest.edit', [
            'position' => $position,
            'types' => $types,
            'hashedId' => $hashedId       
        ]);
    }
    public function show($hashedId)
    {
        $position = Submissionposition::with('submitter','approver1','approver2')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$position) {
            abort(404, 'Position not found.');
        }
            $types= ['Full Time', 'Part Time', 'Contract','Internship','Remote','Urgent'];
        return view('pages.Positionrequest.show', [
            'position' => $position,
            'types' => $types,
            'hashedId' => $hashedId       
        ]);
    }
    public function create()
    {
            $types= ['Full Time', 'Part Time', 'Contract','Internship','Remote','Urgent'];
        
        return view('pages.Positionrequest.create', compact('types'));
    }

    public function store(Request $request)
    {
      
        $validatedData = $request->validate([
            'position_name' => ['required', 'string','max:255'],
            'role_summary' => ['required', 'string','max:255'],
            'key_respon' => ['required', 'string','max:255'],
            'qualifications' => ['required', 'string','max:255'],
            'work_location' => ['required', 'string','max:255'],
            'type' => ['required','max:255'],
            'notes' => ['nullable', 'string','max:255'],
            'status' => ['nullable', 'string','max:255'],
            
        ], [
            'position_name.required' => 'Position must filled.',
            'position_name.string' => 'Position text only.',
        ]);
        try {
            DB::beginTransaction();
            $position = Submissionposition::create([
                'employee_id'    => Auth::user()->employee_id,
                'position_name' => $validatedData['position_name'], 
                'role_summary' => $validatedData['role_summary'], 
                'key_respon' => $validatedData['key_respon'], 
                'qualifications' => $validatedData['qualifications'], 
                'work_location' => $validatedData['work_location'], 
                'status' => $validatedData['status'] ?? 'Pending', 
                 'type'          => is_array($validatedData['type']) 
                        ? implode(',', $validatedData['type']) 
                        : $validatedData['type'],
                'notes' => $validatedData['notes'] ?? null, 
            ]);
            DB::commit();
            return redirect()->route('pages.Positionrequest')->with('success', 'Request created Succesfully!');
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
        'position_name'   => ['required', 'string', 'max:255'],
        'role_summary'    => ['required', 'string', 'max:255'],
        'key_respon'      => ['required', 'string', 'max:255'],
        'qualifications'  => ['required', 'string', 'max:255'],
        'work_location'   => ['required', 'string', 'max:255'],
        'type'            => ['required', 'max:255'],
        'notes'           => ['nullable', 'string', 'max:255'],
        'status'          => ['nullable', 'string', 'max:255'],
    ], [
        'position_name.required' => 'Position must be filled.',
        'position_name.string'   => 'Position text only.',
    ]);

        $positionData = [
              'position_name'  => $validatedData['position_name'],
            'role_summary'   => $validatedData['role_summary'],
            'key_respon'     => $validatedData['key_respon'],
            'qualifications' => $validatedData['qualifications'],
            'work_location'  => $validatedData['work_location'],
            'type'           => is_array($validatedData['type'])
                                    ? implode(',', $validatedData['type'])
                                    : $validatedData['type'],
            'notes'          => $validatedData['notes'] ?? null,
            'status'         => $validatedData['status'] ?? $position->status,
            
        ];
        DB::beginTransaction();
        $position->update($positionData);
        DB::commit();

        return redirect()->route('pages.Positionrequest')->with('success', 'Position Request Update Successfully.');
    }
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

