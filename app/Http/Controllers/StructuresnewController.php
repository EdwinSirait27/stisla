<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Departments;
use App\Models\Stores;
use App\Models\Position;
use App\Models\Salary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
use App\Models\Structuresnew;
use App\Models\Submissionposition;
use App\Rules\NoXSSInput;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class StructuresnewController extends Controller
{
    public function index()
    {
        return view('pages.Structuresnew.Structuresnew');
    }
    public function getStructuresativities(Request $request)
    {
        if ($request->ajax()) {
            $query = Activity::where('log_name', 'Structuresnew')
                ->with(['causer.employee'])
                ->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('description', function ($row) {
                    return $row->description ?? '-';
                })
                ->addColumn('causer', function ($row) {
                    return $row->causer->employee->employee_name;
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at->format('d M Y H:i');
                })
                ->addColumn('changes', function ($row) {
                    return json_encode($row->properties['attributes'] ?? []);
                })
                ->filter(function ($instance) use ($request) {
                    if ($request->has('search') && $request->get('search')['value'] != '') {
                        $search = $request->get('search')['value'];
                        $instance->where(function ($q) use ($search) {
                            $q->where('description', 'like', "%{$search}%")
                                ->orWhereHas('causer.employee', function ($q2) use ($search) {
                                    $q2->where('employee_name', 'like', "%{$search}%");
                                })
                                ->orWhereHas('causer.employee', function ($q3) use ($search) {
                                    $q3->where('employee_name', 'like', "%{$search}%");
                                });
                        });
                    }
                })
                ->rawColumns(['description'])
                ->make(true);
        }
    }
    public function getStructuresnew()
    {
        $structures = Structuresnew::with([
            'parent.submissionposition.positionRelation',
            'parent',
            'children.submissionposition.positionRelation',
            'allChildren.submissionposition.positionRelation',
            'submissionposition.submitter',
            'submissionposition',
            'employees',
        ])
            ->select([
                'id',
                'structure_code',
                'parent_id',
                'is_manager',
                'submission_position_id',
                'status'
            ])
            ->get()
            ->map(function ($structure) {
                $structure->id_hashed = substr(hash('sha256', $structure->id . env('APP_KEY')), 0, 8);
                $structure->action = '
                <a href="' . route('Structuresnew.edit', $structure->id_hashed) . '" class="mx-3" 
                    data-bs-toggle="tooltip" data-bs-original-title="Edit Structure"
                    title="Edit structure: ' . e($structure->structure_name) . '">
                    <i class="fas fa-user-edit text-secondary"></i>
                </a> 
                <a href="' . route('Structuresnew.show', $structure->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" title="show Structures: ' . e($structure->structure_name) . '">
                    <i class="fas fa-eye text-secondary"></i>
               </a>';
                $structure->checkbox = '<input type="checkbox" class="payroll-checkbox" 
                name="structure_ids[]" 
                value="' . $structure->id_hashed . '">';
                return $structure;
            });
        return DataTables::of($structures)
            ->addColumn('company_name', fn($s) => $s->submissionposition->company->name ?? 'Empty')
            ->addColumn('department_name', fn($s) => $s->submissionposition->department->nickname ?? 'Empty')
            ->addColumn('store_name', fn($s) => $s->submissionposition->store->nickname ?? 'Empty')
            ->addColumn('position_name', fn($s) => $s->submissionposition->positionRelation->name ?? 'Empty')
            ->addColumn('employee_name', fn($s) => $s->employees->employee_name ?? 'Empty')
            ->addColumn(
                'parent',
                fn($structure) =>
                !empty($structure->parent->submissionposition->positionRelation) && !empty($structure->parent->submissionposition->positionRelation->name)
                    ? $structure->parent->submissionposition->positionRelation->name : 'Empty'
            )
            ->addColumn('children', function ($s) {
                if ($s->children->isEmpty()) {
                    return '<span class="text-muted">No Subordinates</span>';
                }
                $childPositions = $s->children->map(function ($child) {
                    return e(optional($child->submissionposition->positionRelation)->name ?? 'Unknown');
                })->implode(', ');
                return $childPositions;
            })
            ->addColumn('allChildren', function ($s) {
                $getAllPositions = function ($node) use (&$getAllPositions) {
                    $positions = collect();
                    foreach ($node->children as $child) {
                        if ($child->submissionposition->positionRelation) {
                            $positions->push($child->submissionposition->positionRelation->name);
                        }
                        $positions = $positions->merge($getAllPositions($child));
                    }
                    return $positions;
                };
                $allPositions = $getAllPositions($s);
                if ($allPositions->isEmpty()) {
                    return '<span class="text-muted">No Subordinates</span>';
                }
                return e($allPositions->implode(', '));
            })
            ->rawColumns([
                'action',
                'checkbox',
                'company_name',
                'department_name',
                'store_name',
                'position_name',
                'parent',
                'children',
                'allChildren'
            ])
            ->make(true);
    }
    public function getPositionreqs()
    {
        $positions = Submissionposition::with(['submitter', 'approver1', 'approver2', 'positionRelation', 'store'])
            ->select([
                'id',
                'employee_id',
                'approver_1',
                'approver_2',
                'status',
                'position_id',
                'store_id',
                'key_respon',
                'role_summary',
                'qualifications',
                'salary_counter',
                'salary_counter_end'
            ])
            ->where('status', 'Accepted')
            ->get()
            ->map(function ($position) {
                $position->id_hashed = substr(hash('sha256', $position->id . env('APP_KEY')), 0, 8);
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
                    'Draft' => 'You have approved this application',
                    'On review' => 'Awaiting director approval',
                    'Accepted' => 'Accepted by directors',
                    default => '-',
                };
            })
            ->addColumn('action', function ($e) {
                return '
                <button class="btn btn-sm btn-dark preview-btn" 
                    data-id="' . $e->id_hashed . '" 
                    data-company="' . (optional($e->submitter->company)->name ?? '-') . '"
                    data-department="' . (optional($e->submitter->department)->department_name ?? '-') . '"
                    data-submitter="' . (optional($e->submitter)->employee_name ?? '-') . '"
                    data-position="' . (optional($e->positionRelation)->name ?? '-') . '"
                    data-store="' . (optional($e->store)->name ?? '-') . '"
                    data-role-summary="' . htmlspecialchars(json_encode($e->role_summary), ENT_QUOTES, 'UTF-8') . '"
                    data-key-responsibility="' . htmlspecialchars(json_encode($e->key_respon), ENT_QUOTES, 'UTF-8') . '"
                    data-qualifications="' . htmlspecialchars(json_encode($e->qualifications), ENT_QUOTES, 'UTF-8') . '"
                    data-approver1="' . (optional($e->approver1)->employee_name ?? '-') . '"
                    data-approver2="' . (optional($e->approver2)->employee_name ?? '-') . '"
                    data-salary="' . $e->salary_counter . '|' . $e->salary_counter_end . '"
                    data-status="' . $e->status . '">
                    <i class="fas fa-eye"></i> Preview
                </button>
                <button class="btn btn-sm btn-success store-btn" data-id="' . $e->id_hashed . '">
                    <i class="fas fa-save"></i> Import
                </button>
            ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
public function storeToStructure($hashedId)
{
    $submission = Submissionposition::with([
        'submitter.company',
        'submitter.department',
        'company',
        'department',
        'store'
    ])->get()->first(function ($item) use ($hashedId) {
        $check = substr(hash('sha256', $item->id . env('APP_KEY')), 0, 8);
        return $check === $hashedId;
    });

    if (!$submission) {
        return response()->json([
            'success' => false,
            'message' => 'Data not found'
        ], 404);
    }
    
    if (Structuresnew::where('submission_position_id', $submission->id)->exists()) {
        return response()->json([
            'success' => false,
            'message' => 'This submission has already been stored'
        ], 409);
    }
    
    $company = $submission?->company;
    $department = $submission?->department;
    $store = $submission->store;
    
    if (!$company || !$department || !$store) {
        return response()->json([
            'success' => false,
            'message' => 'Missing company, department, or store data'
        ], 422);
    }
    
    $companyCode = strtoupper(preg_replace('/\s+/', '', $company->nickname));
    $departmentCode = strtoupper(preg_replace('/\s+/', '', $department->nickname));
    $storeCode = strtoupper(preg_replace('/\s+/', '', $store->nickname));
    $prefix = $companyCode . $departmentCode . $storeCode;
    
    Log::info('=== DEBUG STRUCTURE CODE ===');
    Log::info('Prefix: ' . $prefix);
    
    // GANTI QUERY INI - langsung cari berdasarkan prefix di structure_code saja
    $lastStructure = Structuresnew::where('structure_code', 'like', $prefix . '%')
        ->orderByRaw('CAST(SUBSTRING(structure_code, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
        ->first();
    
    Log::info('Query executed');
    
    $nextNumber = 1;
    $numberPart = null;
    
    if ($lastStructure) {
        Log::info('Last structure found: ' . $lastStructure->structure_code);
        
        // Hapus prefix dulu, baru ambil angkanya
        $lastCode = $lastStructure->structure_code;
        $numberPart = substr($lastCode, strlen($prefix));
        $lastNumber = (int) $numberPart;
        $nextNumber = $lastNumber + 1;
        
        Log::info('Number part: ' . $numberPart);
        Log::info('Last number (int): ' . $lastNumber);
        Log::info('Next number: ' . $nextNumber);
    } else {
        Log::info('No last structure found, starting from 1');
    }
    
    $structureCode = $prefix . $nextNumber;
    Log::info('New structure code: ' . $structureCode);
    Log::info('=== END DEBUG ===');
    
    $structure = Structuresnew::create([
        'submission_position_id' => $submission->id,
        'structure_code' => $structureCode,
        'status' => 'vacant',
    ]);
    
    // Update status submission
    $submission->update(['status' => 'Done']);

    return response()->json([
        'success' => true,
        'message' => 'Data successfully stored to Structuresnew!',
        'data' => $structure
    ]);
}
// public function storeToStructure($hashedId)
// {
//     $submission = Submissionposition::with([
//         'submitter.company',
//         'submitter.department',
//         'store'
//     ])->get()->first(function ($item) use ($hashedId) {
//         $check = substr(hash('sha256', $item->id . env('APP_KEY')), 0, 8);
//         return $check === $hashedId;
//     });

//     if (!$submission) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Data not found'
//         ], 404);
//     }
    
//     if (Structuresnew::where('submission_position_id', $submission->id)->exists()) {
//         return response()->json([
//             'success' => false,
//             'message' => 'This submission has already been stored'
//         ], 409);
//     }
    
//     $company = $submission->submitter?->company;
//     $department = $submission->submitter?->department;
//     $store = $submission->store;
    
//     if (!$company || !$department || !$store) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Missing company, department, or store data'
//         ], 422);
//     }
    
//     $companyCode = strtoupper(preg_replace('/\s+/', '', $company->nickname));
//     $departmentCode = strtoupper(preg_replace('/\s+/', '', $department->nickname));
//     $storeCode = strtoupper(preg_replace('/\s+/', '', $store->nickname));
//     $prefix = $companyCode . $departmentCode . $storeCode;
    
//     Log::info('=== DEBUG STRUCTURE CODE ===');
//     Log::info('Prefix: ' . $prefix);
    
//     // GANTI QUERY INI - langsung cari berdasarkan prefix di structure_code saja
//     $lastStructure = Structuresnew::where('structure_code', 'like', $prefix . '%')
//         ->orderByRaw('CAST(SUBSTRING(structure_code, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
//         ->first();
    
//     Log::info('Query executed');
    
//     $nextNumber = 1;
//     $numberPart = null;
    
//     if ($lastStructure) {
//         Log::info('Last structure found: ' . $lastStructure->structure_code);
        
//         // Hapus prefix dulu, baru ambil angkanya
//         $lastCode = $lastStructure->structure_code;
//         $numberPart = substr($lastCode, strlen($prefix));
//         $lastNumber = (int) $numberPart;
//         $nextNumber = $lastNumber + 1;
        
//         Log::info('Number part: ' . $numberPart);
//         Log::info('Last number (int): ' . $lastNumber);
//         Log::info('Next number: ' . $nextNumber);
//     } else {
//         Log::info('No last structure found, starting from 1');
//     }
    
//     $structureCode = $prefix . $nextNumber;
//     Log::info('New structure code: ' . $structureCode);
//     Log::info('=== END DEBUG ===');
    
//     $structure = Structuresnew::create([
//         'submission_position_id' => $submission->id,
//         'structure_code' => $structureCode,
//         'status' => 'vacant',
//     ]);
    
//     // Update status submission
//     $submission->update(['status' => 'Done']);

//     return response()->json([
//         'success' => true,
//         'message' => 'Data successfully stored to Structuresnew!',
//         'data' => $structure
//     ]);
// }


    public function bulkDelete(Request $request)
    {
        $idsRaw = $request->input('structure_ids', '');
        $ids = is_array($idsRaw) ? $idsRaw : explode(',', $idsRaw);
        if (empty($ids)) {
            return back()->with('error', 'Tidak ada data yang dipilih.');
        }
        $matchedIds = [];
        Structuresnew::chunk(100, function ($structures) use (&$matchedIds, $ids) {
            foreach ($structures as $structure) {
                $hash = substr(hash('sha256', $structure->id . env('APP_KEY')), 0, 8);
                if (in_array($hash, $ids)) {
                    $matchedIds[] = $structure->id;
                }
            }
        });

        $deleted = Structuresnew::whereIn('id', $matchedIds)->delete();

        return back()->with('success', "$deleted data berhasil dihapus.");
    }
    // public function getOrgChartData()
    // {
    //     $gradingPriority = [
    //         'Director' => 1,
    //         'Head' => 2,
    //         'Senior Manager' => 3,
    //         'Manager' => 4,
    //         'Assistant Manager' => 5,
    //         'Supervisor' => 6,
    //         'Staff' => 7,
    //         'Daily Worker' => 8,
    //     ];

    //     $data = Structuresnew::with([
    //         'parent',
    //         'employee',
    //         'employee.store',
    //         'submissionposition',
    //         'employee.grading',
    //         'submissionposition.positionRelation',
    //         'submissionposition.store',
    //         'secondarySupervisors',
    //     ])->get()->map(function ($s) use ($gradingPriority) {

    //         $gradingName = collect($s->employee)->pluck('grading.grading_name')->first() ?? 'Empty';
    //         $level = $gradingPriority[$gradingName] ?? 999;
    //         $secondaryIds = $s->secondarySupervisors->pluck('id')->toArray();
    //         return [
    //             'id'         => $s->id,
    //             'pid'        => $s->parent_id,
    //             'Position'   => optional(optional($s->submissionposition)->positionRelation)->name ?? 'Unknown',
    //             'Employee'   => collect($s->employee)->pluck('employee_name')->join(', ') ?: 'Empty',
    //             'Grading'    => $gradingName,
    //             'Location'   => optional(optional($s->submissionposition)->store)->name ?? 'Empty',
    //             'status'     => $s->status,
    //             'photo'      => collect($s->employee)->pluck('photo')->first() ?? '/default-avatar.png',
    //             'secondary'  => $secondaryIds 
    //         ];
    //     });

    //     $sortedData = $data->sortBy('level')->values();

    //     return response()->json($sortedData);
    // }
    // 'employee.grading',
    // 'employee',
        // 'employee.store',
    public function getOrgChartData()
{
    $gradingPriority = [
        'Director' => 1,
        'Head' => 2,
        'Senior Manager' => 3,
        'Manager' => 4,
        'Assistant Manager' => 5,
        'Supervisor' => 6,
        'Staff' => 7,
        'Daily Worker' => 8,
    ];
    $data = Structuresnew::with([
        'parent',
        'submissionposition',
        'submissionposition.positionRelation',
        'submissionposition.store',
        'secondarySupervisors',
        'secondarySupervisors.employees', 
        'secondarySupervisors.employees.grading', 
        'secondarySupervisors.submissionposition.positionRelation',
    ])->get()->map(function ($s) use ($gradingPriority) {

        // $gradingName = optional($s->employees->grading)->grading_name ?? 'Empty';
        $gradingName = optional(optional($s->employees)->grading)->grading_name ?? 'Empty';

        $level = $gradingPriority[$gradingName] ?? 999;
        
        $secondaryData = $s->secondarySupervisors->map(function($secondary) {
            return [
                'id' => $secondary->id,
                'employee_name' => optional($secondary->employees)->employee_name ?? 'Unknown',
                'position' => optional(optional($secondary->submissionposition)->positionRelation)->name ?? 'Unknown',
            ];
        })->toArray();

        return [
            'id'         => $s->id,
            'pid'        => $s->parent_id,
            'Position'   => optional(optional($s->submissionposition)->positionRelation)->name ?? 'Unknown',
            'Employee' => optional($s->employees)->employee_name ?? 'Empty',
            'Grading'    => $gradingName,
            'Location'   => optional(optional($s->submissionposition)->store)->name ?? 'Empty',
            'status'     => $s->status,
            'level'      => $level,
            'secondary'  => $secondaryData ];
    });

    $sortedData = $data->sortBy('level')->values();

    return response()->json($sortedData);
}




//     public function edit($hashedId)
//     {
//         $structure = Structuresnew::with([
//             'parent',
//             'submissionposition',
//             'submissionposition.stores',
//             'secondarySupervisors', // <-- tambahkan ini supaya data kebaca
//         ])->get()->first(function ($u) use ($hashedId) {
//             $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
//             return $expectedHash === $hashedId;
//         });

//         if (!$structure) {
//             abort(404, 'Structure not found.');
//         }

//         // LIST UNTUK DROPDOWN PARENT DAN SECONDARY SUPERVISOR
//         $parents = Structuresnew::with('submissionposition', 'submissionposition.positionRelation')
//             ->get()
//             ->pluck('submissionposition.positionRelation.name', 'id');

//         $statuses = ['active' => 'active', 'inactive' => 'inactive', 'vacant' => 'vacant'];
//         $types = ['Full Time', 'Part Time', 'Contract', 'Internship', 'Remote', 'Urgent'];

//         $salaries = Salary::all()->mapWithKeys(function ($item) {
//             return [
//                 $item->id => "{$item->salary_start} - {$item->salary_end}"
//             ];
//         });
// $allStores = Stores::select('id', 'name')->orderBy('name')->get();

//     // Ambil store yg sudah dipilih di pivot
//     $selectedStores = $structure->submissionposition->stores->pluck('id')->toArray();

//         return view('pages.Structuresnew.edit', [
//             'structure' => $structure,
//             'parents' => $parents,
//             'types' => $types,
//             'salaries' => $salaries,
//             'statuses' => $statuses,
//             'hashedId' => $hashedId,

//             // Kirim data tambahan ini
//             'selectedSecondarySupervisors' => $structure->secondarySupervisors->pluck('id')->toArray(),
//              'allStores' => $allStores,
//         'selectedStores' => $selectedStores,
//         ]);
//     }
public function edit($hashedId) 
{
    $structure = Structuresnew::with([
        'parent',
        'submissionposition',
        'submissionposition.stores',
        'secondarySupervisors',
    ])->get()->first(function ($u) use ($hashedId) {
        $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
        return $expectedHash === $hashedId;
    });

    if (!$structure) {
        abort(404, 'Structure not found.');
    }

    // LIST PARENT
    $parents = Structuresnew::with('submissionposition', 'submissionposition.positionRelation')
        ->get()
        ->pluck('submissionposition.positionRelation.name', 'id');

    $statuses = ['active' => 'active', 'inactive' => 'inactive', 'vacant' => 'vacant'];
    $types = ['Full Time', 'Part Time', 'Contract', 'Internship', 'Remote', 'Urgent'];

    $salaries = Salary::all()->mapWithKeys(function ($item) {
        return [
            $item->id => "{$item->salary_start} - {$item->salary_end}"
        ];
    });

    // AMBIL SEMUA STORE
    $allStores = Stores::select('id', 'name')->orderBy('name')->get();

    // AMBIL STORE YANG SUDAH DIPILIH
    $selectedStores = optional(optional($structure->submissionposition)->stores)
        ->pluck('id')
        ->toArray() ?? [];

    return view('pages.Structuresnew.edit', [
        'structure' => $structure,
        'parents' => $parents,
        'types' => $types,
        'salaries' => $salaries,
        'statuses' => $statuses,
        'hashedId' => $hashedId,

        'selectedSecondarySupervisors' => $structure->secondarySupervisors->pluck('id')->toArray(),

        'allStores' => $allStores,
        'selectedStores' => $selectedStores,
    ]);
}



    public function see($idHashed)
    {
        $structure = Submissionposition::with(['submitter', 'approver1', 'approver2', 'positionRelation', 'store'])
            ->get()
            ->first(function ($pos) use ($idHashed) {
                return substr(hash('sha256', $pos->id . env('APP_KEY')), 0, 8) === $idHashed;
            });

        if (!$structure) {
            return response('<p class="text-danger">Data not found.</p>', 404);
        }

        return view('pages.Structuresnew.partials.see', compact('structure'));
    }



    public function show($hashedId)
    {
        $structure = Structuresnew::with('parent', 'submissionposition', 'submissionposition.positionRelation', 'employees')
            ->get()
            ->first(function ($u) use ($hashedId) {
                $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
                return $expectedHash === $hashedId;
            });

        if (!$structure) {
            abort(404, 'Structure not found.');
        }

        $parents = Structuresnew::with('submissionposition.positionRelation')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->id => optional($item->submissionposition->positionRelation)->name,
                ];
            });

        $statuses = ['active' => 'active', 'inactive' => 'inactive', 'vacant' => 'vacant'];
        $types = ['Full Time', 'Part Time', 'Contract', 'Internship', 'Remote', 'Urgent'];

        $salaries = Salary::all()->mapWithKeys(function ($item) {
            return [
                $item->id => "{$item->salary_start} - {$item->salary_end}"
            ];
        });

        // 💡 Pisahkan dan beri warna sesuai tipe
        $badgeColors = [
            'Full Time'   => 'success',
            'Part Time'   => 'info',
            'Contract'    => 'warning',
            'Internship'  => 'secondary',
            'Remote'      => 'dark',
            'Urgent'      => 'danger',
        ];

        // Pastikan field `type` jadi array + beri warna
        $structure->submissionposition->type_badges = collect(
            is_array($structure->submissionposition->type) ? $structure->submissionposition->type : explode(',', $structure->submissionposition->type)
        )->map(function ($t) use ($badgeColors) {
            $t = trim($t);
            return [
                'name' => $t,
                'color' => $badgeColors[$t] ?? 'primary',
            ];
        });

        return view('pages.Structuresnew.show', compact(
            'structure',
            'parents',
            'types',
            'salaries',
            'statuses',
            'hashedId'
        ));
    }


    public function store(Request $request)
    {
        // dd($request->all());

        $validatedData = $request->validate([
            'company_id' => ['required', 'string'],
            'department_id' => ['required', 'string'],
            'parent_id' => ['nullable', 'string'],
            'store_id' => ['required', 'string'],
            'position_id' => ['required', 'string'],
            'salary_id' => ['required', 'string'],
            'type' => ['required'],
            'role_summary' => ['required', 'string'],
            'key_respon' => ['required', 'string'],
            'qualifications' => ['required', 'string'],
            'is_manager' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string'],

        ]);

        try {
            DB::beginTransaction();
            $company = Company::find($validatedData['company_id']);
            $department = Departments::find($validatedData['department_id']);
            $store = Stores::find($validatedData['store_id']);

            if (!$company || !$department || !$store) {
                throw new \Exception("Company, Department, atau Store tidak ditemukan");
            }

            // Format dasar kode (hapus spasi, ambil huruf besar saja)
            $companyCode = strtoupper(preg_replace('/\s+/', '', $company->nickname));
            $departmentCode = strtoupper(preg_replace('/\s+/', '', $department->nickname));
            $storeCode = strtoupper(preg_replace('/\s+/', '', $store->nickname));

            // Buat prefix code
            $prefix = $companyCode . $departmentCode . $storeCode;

            // Ambil code terakhir dengan prefix yang sama
            $lastStructure = Structuresnew::whereHas('company', fn($q) => $q->where('nickname', $company->nickname))
                ->whereHas('department', fn($q) => $q->where('nickname', $department->nickname))
                ->whereHas('store', fn($q) => $q->where('nickname', $store->nickname))
                ->where('structure_code', 'like', $prefix . '%')
                ->orderBy('structure_code', 'desc')
                ->first();

            $nextNumber = 1;
            if ($lastStructure) {
                $lastNumber = (int) preg_replace('/\D/', '', $lastStructure->structure_code);
                $nextNumber = $lastNumber + 1;
            }
            $structureCode = $prefix . $nextNumber;
            // Simpan ke database
            $structure = Structuresnew::create([
                'company_id' => $validatedData['company_id'],
                'department_id' => $validatedData['department_id'],
                'store_id' => $validatedData['store_id'],
                'parent_id' => $validatedData['parent_id'],
                'position_id' => $validatedData['position_id'],
                'salary_id' => $validatedData['salary_id'],
                'type'          => is_array($validatedData['type'])
                    ? implode(',', $validatedData['type'])
                    : $validatedData['type'],
                'role_summary' => $validatedData['role_summary'],
                'key_respon' => $validatedData['key_respon'],
                'qualifications' => $validatedData['qualifications'],
                // 'work_location' => $validatedData['work_location'],
                'structure_code' => $structureCode,
                'is_manager' => $validatedData['is_manager'] ?? 0,
                'status' => $validatedData['status'] ?? 'vacant',
            ]);

            DB::commit();
            return redirect()->route('pages.Structuresnew')->with('success', 'Structure created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Error while creating data: ' . $e->getMessage()])
                ->withInput();
        }
    }


    // public function update(Request $request, $hashedId)
    // {
    //     Log::info('Update Structure - Request received', [
    //         'hashedId' => $hashedId,
    //         'request_all' => $request->all()
    //     ]);

    //     $structure = Structuresnew::with('company', 'department', 'store', 'position')
    //         ->get()
    //         ->first(function ($u) use ($hashedId) {
    //             $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
    //             return $expectedHash === $hashedId;
    //         });

    //     Log::info('Structure matching result', [
    //         'found' => $structure ? true : false,
    //         'structure_id' => $structure->id ?? null
    //     ]);

    //     if (!$structure) {
    //         Log::warning('Invalid hashed ID during update', ['hashedId' => $hashedId]);
    //         return redirect()->route('pages.Structuresnew')->with('error', 'ID tidak valid.');
    //     }

    //     $validated = $request->validate([
    //         'is_manager' => ['nullable', 'boolean'],
    //         'parent_id' => ['nullable', 'string', 'max:255'],
    //         'secondary_supervisors' => ['nullable', 'array'],
    //         'secondary_supervisors.*' => ['string'],
        
    //     ]);
    //     Log::info('Validated data', $validated);
    //     DB::beginTransaction();
    //     try {
    //         Log::info('Updating structure', [
    //             'structure_id' => $structure->id,
    //             'update_data' => [
    //                 'is_manager' => $validated['is_manager'] ?? 0,
    //                 'parent_id' => $validated['parent_id'] ?? null,
    //             ]
    //         ]);
    //         $structure->update([
    //             'is_manager' => $validated['is_manager'] ?? 0,
    //             'parent_id' => $validated['parent_id'] ?? null,
    //         ]);
    //         Log::info('Sync secondary supervisors', [
    //             'structure_id' => $structure->id,
    //             'secondary_supervisors' => $validated['secondary_supervisors'] ?? []
    //         ]);
    //         $structure->secondarySupervisors()->sync(
    //             $validated['secondary_supervisors'] ?? []
    //         );

    //         DB::commit();

    //         Log::info('Structure updated successfully', [
    //             'structure_id' => $structure->id
    //         ]);

    //         return redirect()->route('pages.Structuresnew')
    //             ->with('success', 'Structure Updated Successfully.');
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         Log::error('Update structure failed', [
    //             'structure_id' => $structure->id ?? null,
    //             'error_message' => $e->getMessage(),
    //             'stack' => $e->getTraceAsString()
    //         ]);

    //         return redirect()->route('pages.Structuresnew')
    //             ->with('error', 'Gagal update: ' . $e->getMessage());
    //     }
    // }
    public function update(Request $request, $hashedId)
{
    Log::info('Update Structure - Request received', [
        'hashedId' => $hashedId,
        'request_all' => $request->all()
    ]);

    $structure = Structuresnew::with([
        'submissionposition.stores'
        
    ])
    ->get()
    ->first(function ($u) use ($hashedId) {
        $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
        return $expectedHash === $hashedId;
    });

    if (!$structure) {
        return redirect()->route('pages.Structuresnew')
            ->with('error', 'ID tidak valid.');
    }

    $validated = $request->validate([
        'is_manager' => ['nullable', 'boolean'],
        'parent_id' => ['nullable', 'string'],
        'secondary_supervisors' => ['nullable', 'array'],
        'secondary_supervisors.*' => ['string'],
        'stores' => ['nullable', 'array'],          // <--- tambahan
        'stores.*' => ['string'],                   // <--- tambahan
    ]);

    DB::beginTransaction();
    try {

        // Update structure data
        $structure->update([
            'is_manager' => $validated['is_manager'] ?? 0,
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        // Update secondary supervisors
        $structure->secondarySupervisors()->sync(
            $validated['secondary_supervisors'] ?? []
        );

        // 🚀 Update MULTI STORES (via submissionposition)
        if ($structure->submissionposition) {

            Log::info('Syncing multi-stores...', [
                'submission_position_id' => $structure->submissionposition->id,
                'selected' => $validated['stores'] ?? []
            ]);

            $structure->submissionposition->stores()->sync(
                $validated['stores'] ?? []
            );
        }

        DB::commit();

        return redirect()->route('pages.Structuresnew')
            ->with('success', 'Structure Updated Successfully.');

    } catch (\Exception $e) {
        DB::rollBack();

        return redirect()->route('pages.Structuresnew')
            ->with('error', 'Gagal update: ' . $e->getMessage());
    }
}



  
    // public function update(Request $request, $hashedId)
    // {
    //     $structure = Structuresnew::with('company', 'department', 'store', 'position')->get()->first(function ($u) use ($hashedId) {
    //         $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
    //         return $expectedHash === $hashedId;
    //     });
    //     if (!$structure) {
    //         return redirect()->route('pages.Structuresnew')->with('error', 'ID tidak valid.');
    //     }
    //     $validatedData = $request->validate([
    //         'is_manager' => [
    //             'nullable',
    //             'boolean',
    //         ],
    //         'parent_id' => [
    //             'nullable',
    //             'string',
    //             'max:255',
    //         ],

    //     ]);
    //     $structureeData = [
    //         'is_manager'  => $validatedData['is_manager'] ?? 0,
    //         'parent_id'  => $validatedData['parent_id'] ?? null,
    //        ];
    //     DB::beginTransaction();
    //     $structure->update($structureeData);
    //     DB::commit();
    //     return redirect()->route('pages.Structuresnew')->with('success', 'Structure Updated Successfully.');
    // }
    //     public function update(Request $request, $hashedId)
    // {
    //     $structure = Structuresnew::with('company', 'department', 'store', 'position')->get()->first(function ($u) use ($hashedId) {
    //         $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
    //         return $expectedHash === $hashedId;
    //     });

    //     if (!$structure) {
    //         return redirect()->route('pages.Structuresnew')->with('error', 'ID tidak valid.');
    //     }

    //     $validated = $request->validate([
    //         'is_manager' => ['nullable', 'boolean'],
    //         'parent_id' => ['nullable', 'string', 'max:255'],

    //         'secondary_supervisors' => ['nullable', 'array'],
    //         'secondary_supervisors.*' => ['string'], 
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         $structure->update([
    //             'is_manager' => $validated['is_manager'] ?? 0,
    //             'parent_id' => $validated['parent_id'] ?? null,
    //         ]);

    //         $structure->secondarySupervisors()->sync(
    //             $validated['secondary_supervisors'] ?? []
    //         );

    //         DB::commit();

    //         return redirect()->route('pages.Structuresnew')->with('success', 'Structure Updated Successfully.');

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->route('pages.Structuresnew')->with('error', 'Gagal update: ' . $e->getMessage());
    //     }
    // }

    // public function getOrgChartData()
    // {
    //     $gradingPriority = [
    //         'Director' => 1,
    //         'Head' => 2,
    //         'Senior Manager' => 3,
    //         'Manager' => 4,
    //         'Assistant Manager' => 5,
    //         'Supervisor' => 6,
    //         'Staff' => 7,
    //         'Daily Worker' => 8,
    //     ];

    //    $data = Structuresnew::with([
    //     'parent',
    //     'employee',
    //     'employee.store',
    //     'submissionposition',
    //     'employee.grading',
    //     'submissionposition.positionRelation',
    //     'submissionposition.store',
    //     'secondarySupervisors'
    // ])->get()->map(function ($s) use ($gradingPriority) {

    //     $gradingName = collect($s->employee)->pluck('grading.grading_name')->first() ?? 'Empty';
    //     $level = $gradingPriority[$gradingName] ?? 999;

    //     return [
    //         'id'         => $s->id,
    //         'pid'        => $s->parent_id,
    //         'Position'   => optional(optional($s->submissionposition)->positionRelation)->name ?? 'Unknown',
    //         'Employee'   => collect($s->employee)->pluck('employee_name')->join(', ') ?: 'Empty',
    //         'Grading'    => $gradingName,
    //         'Location'   => optional(optional($s->submissionposition)->store)->name ?? 'Empty',
    //         'status'     => $s->status,
    //         'level'      => $level,
    //         'secondary'  => $s->secondarySupervisors->pluck('id') // ⬅ id supervisor kedua
    //     ];
    // });


    //     $sortedData = $data->sortBy('level')->values();

    //     return response()->json($sortedData);
    // }
    // public function getOrgChartData()
    // {
    //     // Tentukan urutan level berdasarkan Grading
    //     $gradingPriority = [
    //         'Director' => 1,
    //         'Head' => 2,
    //         'Senior Manager' => 3,
    //         'Manager' => 4,
    //         'Assistant Manager' => 5,
    //         'Supervisor' => 6,
    //         'Staff' => 7,
    //         'Daily Worker' => 8,
    //     ];

    //     $data = Structuresnew::with([
    //         // 'position',
    //         'parent',
    //         'employee',
    //         'employee.store',
    //         // 'store',
    //         'submissionposition',
    //         'employee.grading',
    //         'submissionposition.positionRelation',
    //         'submissionposition.store',
    //     ])->get()->map(function ($s) use ($gradingPriority) {
    //         $gradingName = collect($s->employee)->pluck('grading.grading_name')->first() ?? 'Empty';
    //         $level = $gradingPriority[$gradingName] ?? 999; // default di bawah kalau grading tidak dikenali

    //         return [
    //             'id'         => $s->id,
    //             'pid'        => $s->parent_id,
    //             'Position'   => optional(optional($s->submissionposition)->positionRelation)->name ?? 'Unknown',
    //             'Employee'   => collect($s->employee)->pluck('employee_name')->join(', ') ?: 'Empty',
    //             'Grading'    => $gradingName,
    //             'Location'   => optional(optional($s->submissionposition)->store)->name ?? 'Empty',
    //             'status'     => $s->status,
    //             'level'      => $level, 
    //         ];
    //     });

    //     $sortedData = $data->sortBy('level')->values();

    //     return response()->json($sortedData);
    // }


    // public function edit($hashedId)
    // {
    //     $structure = Structuresnew::with('parent','submissionposition')->get()->first(function ($u) use ($hashedId) {
    //         $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
    //         return $expectedHash === $hashedId;
    //     });
    //     if (!$structure) {
    //         abort(404, 'Structure not found.');
    //     }
    //     $parents = Structuresnew::with('submissionposition','submissionposition.positionRelation')->get()->pluck('submissionposition.positionRelation.name', 'id');
    //     $statuses = ['active' => 'active', 'inactive' => 'inactive', 'vacant' => 'vacant'];
    //     $types = ['Full Time', 'Part Time', 'Contract', 'Internship', 'Remote', 'Urgent'];
    //     $salaries = Salary::all()->mapWithKeys(function ($item) {
    //         return [
    //             $item->id => "{$item->salary_start} - {$item->salary_end}"
    //         ];
    //     });
    //     return view('pages.Structuresnew.edit', [
    //         'structure' => $structure,
    //         'parents' => $parents,
    //         'types' => $types,
    //         'salaries' => $salaries,
    //         'statuses' => $statuses,
    //         'hashedId' => $hashedId,
    //     ]);
    // }
    //     public function create()
    //     {
    //         $companys = Company::pluck('nickname', 'id', 'name');
    //         $stores = Stores::pluck('nickname', 'id', 'name');

    //         $salaries = Salary::all()->mapWithKeys(function ($item) {
    //     return [
    //         $item->id => "{$item->salary_start} - {$item->salary_end}"
    //     ];
    // });

    //         $departments = Departments::pluck('nickname', 'id', 'department_name');
    //         $positions = Position::pluck('name', 'id', 'name');
    //             $types= ['Full Time', 'Part Time', 'Contract','Internship','Remote','Urgent'];

    //         $parents = Structuresnew::with('position')->get()
    //             ->mapWithKeys(function ($item) {
    //                 return [$item->id => $item->position->name ?? '-'];
    //             });
    //         return view('pages.Structuresnew.create', compact(
    //             'departments',
    //             'stores',
    //             'salaries',
    //             'companys',
    //             'positions',
    //             'types',
    //             'parents'
    //         ));
    //     }

    //     public function show($hashedId)
    //     {
    //         $structure = Structuresnew::with('company', 'department', 'store', 'position', 'parent', 'salary')->get()->first(function ($u) use ($hashedId) {
    //             $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
    //             return $expectedHash === $hashedId;
    //         });
    //         if (!$structure) {
    //             abort(404, 'Structure not found.');
    //         }
    //         $parents = Structuresnew::with('position')->get()->pluck('position.name', 'id');
    //         $statuses = ['active' => 'active', 'inactive' => 'inactive', 'vacant' => 'vacant'];
    //         $types = ['Full Time', 'Part Time', 'Contract', 'Internship', 'Remote', 'Urgent'];
    //          $salaries = Salary::all()->mapWithKeys(function ($item) {
    //     return [
    //         $item->id => "{$item->salary_start} - {$item->salary_end}"
    //     ];
    // });
    //         return view('pages.Structuresnew.show', [
    //             'structure' => $structure,
    //             'parents' => $parents,
    //             'types' => $types,
    //             'salaries' => $salaries,
    //             'statuses' => $statuses,
    //             'hashedId' => $hashedId,
    //         ]);
    //     }
    //     public function see($idHashed)
    // {
    //     $position = Submissionposition::with(['submitter', 'approver1', 'approver2', 'positionRelation', 'store'])
    //         ->get()
    //         ->first(function ($pos) use ($idHashed) {
    //             return substr(hash('sha256', $pos->id . env('APP_KEY')), 0, 8) === $idHashed;
    //         });

    //     if (!$position) {
    //         return response('<p class="text-danger">Data not found.</p>', 404);
    //     }

    //     return view('Structurenew.partials.see', compact('position'));
    // }
    //    public function getPositionreqs()
    // {

    //     $positions = Submissionposition::with(['submitter','approver1','approver2','positionRelation','store'])
    //         ->select(['id','employee_id','approver_1','approver_2','status','position_id','store_id'])->where('status','Accepted')
    //         ->get()
    //         ->map(function ($position) {
    //             $position->id_hashed = substr(hash('sha256', $position->id . env('APP_KEY')), 0, 8);

    //             $lockedStatuses = ['On review', 'Accepted'];
    //             $showButton = '
    //                 <a href="' . route('Positionreqlist.show', $position->id_hashed) . '" 
    //                    class="mx-2" 
    //                    data-bs-toggle="tooltip" 
    //                    data-bs-original-title="View details" 
    //                    title="Show Position Request: ' . e($position->positionRelation->name) . '">
    //                     <i class="fas fa-eye "></i>
    //                 </a>';
    //            if (in_array($position->status, $lockedStatuses)) {
    //                 $editButton = '
    //                     <i class="fas fa-lock text-muted mx-2" 
    //                        data-bs-toggle="tooltip" 
    //                        title="Edit locked because status: ' . e($position->status) . '"></i>';
    //             } else {
    //                 $editButton = '
    //                     <a href="' . route('Positionreqlist.edit', $position->id_hashed) . '" 
    //                        class="mx-2" 
    //                        data-bs-toggle="tooltip" 
    //                        data-bs-original-title="Edit request" 
    //                        title="Edit Positionrequest: ' . e($position->positionRelation->name) . '">
    //                         <i class="fas fa-user-edit text-secondary"></i>
    //                     </a>';
    //             }

    //             // Gabungkan action
    //             $position->action = $showButton . $editButton;

    //             return $position;
    //         });

    //     return DataTables::of($positions)
    //        ->addColumn('sub', fn($e) => optional($e->submitter)->employee_name ?? 'Empty')
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
    // public function getPositionreqs()
    // {
    //     $positions = Submissionposition::with(['submitter', 'approver1', 'approver2', 'positionRelation', 'store'])
    //         ->select(['id', 'employee_id', 'approver_1', 'approver_2', 'status', 'position_id', 'store_id'])
    //         ->where('status', 'Accepted')
    //         ->get()
    //         ->map(function ($position) {
    //             $position->id_hashed = substr(hash('sha256', $position->id . env('APP_KEY')), 0, 8);
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
    //                 'Draft' => 'You have approved this application',
    //                 'On review' => 'Awaiting director approval',
    //                 'Accepted' => 'Accepted by directors',
    //                 default => '-',
    //             };
    //         })
    //         ->rawColumns(['action'])
    //         ->make(true);
    // }
    // public function create()
    // {
    //     // Langsung sembunyi/exclude kombinasi yang sudah ada
    //     $existingStructures = Structuresnew::select('company_id', 'store_id', 'department_id', 'position_id')
    //         ->whereNotNull('company_id')
    //         ->whereNotNull('store_id')
    //         ->whereNotNull('department_id')
    //         ->whereNotNull('position_id')
    //         ->get();

    //     $companys = Company::pluck('nickname', 'id');
    //     $stores = Stores::pluck('nickname', 'id');
    //     $departments = Departments::pluck('nickname', 'id');

    //     // Modifikasi: Gunakan Position::pluck('name', 'id') seperti remark baris 1
    //     $positions = Position::pluck('name', 'id');

    //     $parents = Structuresnew::with('position')->get()
    //         ->mapWithKeys(fn($item) => [$item->id => $item->position->name ?? '-']);

    //     $isComplete = Structuresnew::whereNotNull('company_id')
    //         ->whereNotNull('store_id')
    //         ->whereNotNull('department_id')
    //         ->whereNotNull('position_id')
    //         ->whereNotNull('parent_id')
    //         ->exists();

    //     // Modifikasi: Format kombinasi yang sudah digunakan
    //     // Untuk ditampilkan/dicek berdasarkan company_id berbeda (remark baris 2)
    //     $usedCombinations = $existingStructures->map(function($item) {
    //         return [
    //             'company_id' => $item->company_id,
    //             'store_id' => $item->store_id,
    //             'department_id' => $item->department_id,
    //             'position_id' => $item->position_id,
    //         ];
    //     })->toArray();

    //     return view('pages.Structuresnew.create', compact(
    //         'departments',
    //         'stores',
    //         'companys',
    //         'positions',
    //         'parents',
    //         'isComplete',
    //         'usedCombinations'
    //     ));
    // }





    // public function bulkDelete(Request $request)
    // {
    //     $idsRaw = $request->input('structure_ids', '');
    //     $ids = is_array($idsRaw) ? $idsRaw : explode(',', $idsRaw);
    //     if (empty($ids)) {
    //         return back()->with('error', 'No Data.');
    //     }
    //     $matchedIds = [];
    //     Structuresnew::chunk(100, function ($structures) use (&$matchedIds, $ids) {
    //         foreach ($structures as $structure) {
    //             $hash = hash('sha256', $structure->id . env('APP_KEY'));
    //             if (in_array($hash, $ids)) {
    //                 $matchedIds[] = $structure->id;
    //             }
    //         }
    //     });

    //     if (empty($matchedIds)) {
    //         return back()->with('error', 'No matching data found.');
    //     }

    //     $deleted = Structuresnew::whereIn('id', $matchedIds)->delete();

    //     return back()->with('success', "$deleted data berhasil dihapus.");
    // }
    // public function bulkDelete(Request $request)
    // {
    //     Log::info('=== BULK DELETE STARTED ===');

    //     $idsRaw = $request->input('structure_ids', '');
    //     Log::info('Raw IDs from request:', ['idsRaw' => $idsRaw]);

    //     $ids = is_array($idsRaw) ? $idsRaw : explode(',', $idsRaw);
    //     Log::info('Processed IDs array:', ['ids' => $ids]);

    //     if (empty($ids) || count(array_filter($ids)) === 0) {
    //         Log::warning('No IDs provided for deletion.');
    //         return back()->with('error', 'No Data.');
    //     }

    //     $matchedIds = [];
    //     $totalChecked = 0;

    //     Structuresnew::chunk(100, function ($structures) use (&$matchedIds, $ids, &$totalChecked) {
    //         foreach ($structures as $structure) {
    //             $totalChecked++;
    //             $hash = hash('sha256', $structure->id . env('APP_KEY'));
    //             if (in_array($hash, $ids)) {
    //                 $matchedIds[] = $structure->id;
    //                 Log::info('Matched ID found:', [
    //                     'structure_id' => $structure->id,
    //                     'hash' => $hash
    //                 ]);
    //             }
    //         }
    //     });

    //     Log::info('Total structures checked:', ['count' => $totalChecked]);
    //     Log::info('Matched IDs for deletion:', ['matchedIds' => $matchedIds]);

    //     if (empty($matchedIds)) {
    //         Log::warning('No matching data found for deletion.');
    //         return back()->with('error', 'No matching data found.');
    //     }

    //     $deleted = Structuresnew::whereIn('id', $matchedIds)->delete();

    //     Log::info('Bulk delete completed.', [
    //         'deleted_count' => $deleted,
    //         'deleted_ids' => $matchedIds
    //     ]);

    //     Log::info('=== BULK DELETE FINISHED ===');

    //     return back()->with('success', "$deleted data berhasil dihapus.");
    // }

    // public function getStructuresnew()
    // {
    //     $structures = Structuresnew::with([

    //         'company',
    //         'department',
    //         'store',
    //         'position',
    //         'parent',
    //         'children',
    //     ])
    //         ->select(['id', 'position_id', 'company_id', 'department_id', 'store_id', 'structure_code', 'is_manager_store', 'parent_id'])
    //         ->get()
    //         ->map(function ($structure) {
    //             $structure->id_hashed = substr(hash('sha256', $structure->id . env('APP_KEY')), 0, 8);
    //             $structure->action = '
    //                 <a href="' . route('Structuresnew.edit', $structure->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit structure: ' . e($structure->structure_name) . '">
    //                     <i class="fas fa-user-edit text-secondary"></i>
    //                 </a>';
    //             $structure->checkbox = '<input type="checkbox" class="payroll-checkbox" name="structure_ids[]" value="' . $structure->id_hashed . '">';

    //             return $structure;
    //         });
    //     return DataTables::of($structures)
    //         ->addColumn('company_name', function ($structure) {
    //             return !empty($structure->company) && !empty($structure->company->name)
    //                 ? $structure->company->name
    //                 : 'Empty';
    //         })
    //         ->addColumn('department_name', function ($structure) {
    //             return !empty($structure->department) && !empty($structure->department->nickname)
    //                 ? $structure->department->nickname
    //                 : 'Empty';
    //         })
    //         ->addColumn('store_name', function ($structure) {
    //             return !empty($structure->store) && !empty($structure->store->nickname)
    //                 ? $structure->store->nickname
    //                 : 'Empty';
    //         })
    //         ->addColumn('position_name', function ($structure) {
    //             return !empty($structure->position) && !empty($structure->position->name)
    //                 ? $structure->position->name
    //                 : 'Empty';
    //         })
    //         ->addColumn('parent', function ($structure) {
    //             return !empty($structure->parent) && !empty($structure->parent->position->name)
    //                 ? $structure->parent->position->name
    //                 : 'Empty';
    //         })
    //         ->rawColumns(['action', 'position_name', 'company_name', 'checkbox', 'department_name', 'store_name', 'parent', 'children'])
    //         ->make(true);
    // }
    // public function bulkDelete(Request $request)
    // {
    //     $idsRaw = $request->input('structure_ids', '');
    //     $ids = is_array($idsRaw) ? $idsRaw : explode(',', $idsRaw);
    //     if (empty($ids)) {
    //         return back()->with('error', 'No Data.');
    //     }
    //     $matchedIds = [];
    //     Structuresnew::chunk(100, function ($structures) use (&$matchedIds, $ids) {
    //         foreach ($structures as $structure) {
    //             $hash = substr(hash('sha256', $structure->id . env('APP_KEY')), 0, 8);
    //             if (in_array($hash, $ids)) {
    //                 $matchedIds[] = $structure->id;
    //             }
    //         }
    //     });

    //     $deleted = Structuresnew::whereIn('id', $matchedIds)->delete();

    //     return back()->with('success', "$deleted data berhasil dihapus.");
    // }
    //     public function getStructuresnew()
    // {
    //     $structures = Structuresnew::with([
    //         'company',
    //         'department',
    //         'store',
    //         'position',
    //         'parent',
    //         'children',
    //     ])
    //         ->select(['id', 'position_id', 'company_id', 'department_id', 'store_id', 'structure_code', 'is_manager','is_head', 'parent_id','status'])
    //         ->get()
    //         ->map(function ($structure) {
    //                $structure->id_hashed = substr(hash('sha256', $structure->id . env('APP_KEY')), 0, 8);


    //             $structure->action = '
    //                 <a href="' . route('Structuresnew.edit', $structure->id_hashed) . '" class="mx-3" 
    //                     data-bs-toggle="tooltip" data-bs-original-title="Edit Structure"
    //                     title="Edit structure: ' . e($structure->structure_name) . '">
    //                     <i class="fas fa-user-edit text-secondary"></i>
    //                 </a>';

    //             $structure->checkbox = '<input type="checkbox" class="payroll-checkbox" 
    //                 name="structure_ids[]" 
    //                 value="' . $structure->id_hashed . '">';

    //             return $structure;
    //         });

    //     return DataTables::of($structures)
    //         ->addColumn('company_name', fn($structure) =>
    //             !empty($structure->company) && !empty($structure->company->name)
    //                 ? $structure->company->name : 'Empty'
    //         )
    //         ->addColumn('department_name', fn($structure) =>
    //             !empty($structure->department) && !empty($structure->department->nickname)
    //                 ? $structure->department->nickname : 'Empty'
    //         )
    //         ->addColumn('store_name', fn($structure) =>
    //             !empty($structure->store) && !empty($structure->store->nickname)
    //                 ? $structure->store->nickname : 'Empty'
    //         )
    //         ->addColumn('position_name', fn($structure) =>
    //             !empty($structure->position) && !empty($structure->position->name)
    //                 ? $structure->position->name : 'Empty'
    //         )
    //         ->addColumn('parent', fn($structure) =>
    //             !empty($structure->parent) && !empty($structure->parent->position->name)
    //                 ? $structure->parent->position->name : 'Empty'
    //         )

    //         ->rawColumns(['action', 'position_name', 'company_name', 'checkbox', 'department_name', 'store_name', 'parent', 'children'])
    //         ->make(true);
    // }


}
//    public function getOrgChartData()
// {
//     $data = Structuresnew::with(['position', 'parent','employee'])
//         ->get()
//         ->map(function ($s) {
//             return [
//                 'id' => $s->id,
//                 'pid' => $s->parent_id,
//                 'Position' => $s->position->name ?? 'Unknown',
//                 'Employee' => $s->employee->employee_name ?? 'Empty',
//                 'title' => $s->employee->pin,
//                 'status' => $s->status,
//             ];
//         });
//     return response()->json($data);
// }
//  public function edit($hashedId)
    // {
    //     $structure = Structuresnew::with('company', 'department', 'store', 'position')->first(function ($u) use ($hashedId) {
    //         $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
    //         return $expectedHash === $hashedId;
    //     });

    //     if (!$structure) {
    //         abort(404, 'Position not found.');
    //     }

    //            $parents = Structuresnew::with('position')->get()->pluck('position.name', 'id');


    //     // Dapatkan role pertama user (untuk selected value)
        
    //     return view('pages.Structuresnew.edit', [
    //         'structure' => $structure,
    //         'hashedId' => $hashedId,
    //         // 'selectedName' => $selectedName
    //         'parents' => $parents,
            
    //     ]);
    // }