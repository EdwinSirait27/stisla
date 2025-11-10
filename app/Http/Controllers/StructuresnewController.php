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

class StructuresnewController extends Controller
{
    public function index()
    {
        return view('pages.Structuresnew.Structuresnew');
    }

    public function getStructuresnew()
    {
        $structures = Structuresnew::with([
            'company',
            'department',
            'store',
            'position',
            'parent',
            'children.position',
            'allChildren.position',
        ])
            ->select([
                'id',
                'position_id',
                'company_id',
                'department_id',
                'store_id',
                'structure_code',
                'is_manager',
                'parent_id',
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
            ->addColumn('company_name', fn($s) => $s->company->name ?? 'Empty')
            ->addColumn('department_name', fn($s) => $s->department->nickname ?? 'Empty')
            ->addColumn('store_name', fn($s) => $s->store->nickname ?? 'Empty')
            ->addColumn('position_name', fn($s) => $s->position->name ?? 'Empty')
            ->addColumn(
                'parent',
                fn($structure) =>
                !empty($structure->parent) && !empty($structure->parent->position->name)
                    ? $structure->parent->position->name : 'Empty'
            )
            ->addColumn('children', function ($s) {
                if ($s->children->isEmpty()) {
                    return '<span class="text-muted">No Subordinates</span>';
                }

                $childPositions = $s->children->map(function ($child) {
                    return e(optional($child->position)->name ?? 'Unknown');
                })->implode(', ');

                return $childPositions;
            })
            //     ->addColumn('allChildren', function ($s) {
            //     $getAllPositions = function ($node) use (&$getAllPositions) {
            //         $positions = collect();

            //         foreach ($node->children as $child) {
            //             if ($child->position) {
            //                 $positions->push($child->position->name);
            //             }
            //             $positions = $positions->merge($getAllPositions($child)); // lanjut ke level bawah
            //         }

            //         return $positions;
            //     };

            //     $allPositions = $getAllPositions($s)->unique();

            //     if ($allPositions->isEmpty()) {
            //         return '<span class="text-muted">No Subordinates</span>';
            //     }

            //     return e($allPositions->implode(', '));
            // })
            ->addColumn('allChildren', function ($s) {
                $getAllPositions = function ($node) use (&$getAllPositions) {
                    $positions = collect();

                    foreach ($node->children as $child) {
                        if ($child->position) {
                            $positions->push($child->position->name);
                        }
                        $positions = $positions->merge($getAllPositions($child)); // lanjut ke level bawah
                    }

                    return $positions;
                };

                // $allPositions = $getAllPositions($s)->unique();
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
    
//     public function getPositionreqs()
// {
//     $positions = Submissionposition::with(['submitter', 'approver1', 'approver2', 'positionRelation', 'store'])
//         ->select(['id', 'employee_id', 'approver_1', 'approver_2', 'status', 'position_id', 'store_id','key_respon','role_summary','qualifications','salary_counter','salary_counter_end'])
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
//         ->addColumn('action', function ($e) {
//             return '<button class="btn btn-sm btn-dark preview-btn" 
//                     data-id="'.$e->id_hashed.'" 
//                             data-company="'.(optional($e->submitter->company)->name ?? '-').'"
//                             data-department="'.(optional($e->submitter->department)->department_name ?? '-').'"
//                             data-submitter="'.(optional($e->submitter)->employee_name ?? '-').'"
//                             data-position="'.(optional($e->positionRelation)->name ?? '-').'"
//                             data-store="'.(optional($e->store)->name ?? '-').'"
//                              data-role-summary="'.htmlspecialchars(json_encode($e->role_summary), ENT_QUOTES, 'UTF-8').'"
//         data-key-responsibility="'.htmlspecialchars(json_encode($e->key_respon), ENT_QUOTES, 'UTF-8').'"
//         data-qualifications="'.htmlspecialchars(json_encode($e->qualifications), ENT_QUOTES, 'UTF-8').'"
//                             data-approver1="'.(optional($e->approver1)->employee_name ?? '-').'"
//                             data-approver2="'.(optional($e->approver2)->employee_name ?? '-').'"
//                        data-salary="'.$e->salary_counter.'|'.$e->salary_counter_end.'"
//                             data-status="'.$e->status.'">
//                         <i class="fas fa-eye"></i> Preview
//                     </button>';
//         })
//         ->rawColumns(['action'])
//         ->make(true);
// }
public function getPositionreqs()
{
    $positions = Submissionposition::with(['submitter', 'approver1', 'approver2', 'positionRelation', 'store'])
        ->select([
            'id', 'employee_id', 'approver_1', 'approver_2', 'status',
            'position_id', 'store_id', 'key_respon', 'role_summary',
            'qualifications', 'salary_counter', 'salary_counter_end'
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
                    data-id="'.$e->id_hashed.'" 
                    data-company="'.(optional($e->submitter->company)->name ?? '-').'"
                    data-department="'.(optional($e->submitter->department)->department_name ?? '-').'"
                    data-submitter="'.(optional($e->submitter)->employee_name ?? '-').'"
                    data-position="'.(optional($e->positionRelation)->name ?? '-').'"
                    data-store="'.(optional($e->store)->name ?? '-').'"
                    data-role-summary="'.htmlspecialchars(json_encode($e->role_summary), ENT_QUOTES, 'UTF-8').'"
                    data-key-responsibility="'.htmlspecialchars(json_encode($e->key_respon), ENT_QUOTES, 'UTF-8').'"
                    data-qualifications="'.htmlspecialchars(json_encode($e->qualifications), ENT_QUOTES, 'UTF-8').'"
                    data-approver1="'.(optional($e->approver1)->employee_name ?? '-').'"
                    data-approver2="'.(optional($e->approver2)->employee_name ?? '-').'"
                    data-salary="'.$e->salary_counter.'|'.$e->salary_counter_end.'"
                    data-status="'.$e->status.'">
                    <i class="fas fa-eye"></i> Preview
                </button>
                <button class="btn btn-sm btn-success store-btn" data-id="'.$e->id_hashed.'">
                    <i class="fas fa-save"></i> Store
                </button>
            ';
        })
        ->rawColumns(['action'])
        ->make(true);
}
public function storeToStructure($hashedId)
{
    // Cari data submission berdasarkan hashed ID
    $submission = Submissionposition::with(['submitter', 'approver1', 'approver2', 'positionRelation', 'store'])
        ->get()
        ->first(function ($item) use ($hashedId) {
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
    $structure = Structuresnew::create([
        'submission_position_id' => $submission->id,
        'status' => 'vacant',
    ]);

    $submission->update(['status' => 'Done']);

    return response()->json([
        'success' => true,
        'message' => 'Data successfully stored to Structuresnew!',
        'data' => $structure
    ]);
}

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

    public function getOrgChartData()
    {
        $data = Structuresnew::with(['position', 'parent', 'employee', 'employee.store', 'store', 'employee.grading'])
            ->get()
            ->map(function ($s) {
                return [
                    'id'        => $s->id,
                    'pid'       => $s->parent_id,
                    'Position'  => $s->position->name ?? 'Unknown',
                    'Employee'  => $s->employee->pluck('employee_name')->join(', ') ?: 'Empty',
                    'Grading'  => $s->employee->pluck('grading.grading_name')->join(', ') ?: 'Empty',
                    'Location'  => $s->store->name ?? 'Empty',
                    'status'    => $s->status,
                ];
            });

        return response()->json($data);
    }

    public function edit($hashedId)
    {
        $structure = Structuresnew::with('company', 'department', 'store', 'position', 'parent', 'salary')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$structure) {
            abort(404, 'Structure not found.');
        }
        $parents = Structuresnew::with('position')->get()->pluck('position.name', 'id');
        $statuses = ['active' => 'active', 'inactive' => 'inactive', 'vacant' => 'vacant'];
        $types = ['Full Time', 'Part Time', 'Contract', 'Internship', 'Remote', 'Urgent'];
        $salaries = Salary::all()->mapWithKeys(function ($item) {
            return [
                $item->id => "{$item->salary_start} - {$item->salary_end}"
            ];
        });
        return view('pages.Structuresnew.edit', [
            'structure' => $structure,
            'parents' => $parents,
            'types' => $types,
            'salaries' => $salaries,
            'statuses' => $statuses,
            'hashedId' => $hashedId,
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
        $structure = Structuresnew::with('company', 'department', 'store', 'position', 'parent', 'salary')
            ->get()
            ->first(function ($u) use ($hashedId) {
                $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
                return $expectedHash === $hashedId;
            });

        if (!$structure) {
            abort(404, 'Structure not found.');
        }

        $parents = Structuresnew::with('position')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->id => optional($item->position)->name,
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
        $structure->type_badges = collect(
            is_array($structure->type) ? $structure->type : explode(',', $structure->type)
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
            // 'work_location' => ['required', 'string'],
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
    public function update(Request $request, $hashedId)
    {
        $structure = Structuresnew::with('company', 'department', 'store', 'position', 'types')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$structure) {
            return redirect()->route('pages.Structuresnew')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([

            'is_manager' => [
                'nullable',
                'boolean',

            ],
            'salary_id' => ['required', 'string'],
            'type' => ['required', 'string'],
            'role_summary' => ['required', 'string'],
            'key_respon' => ['required', 'string'],
            'qualifications' => ['required', 'string'],
            // 'work_location' => ['required', 'string' ],
            // 'status' => [
            //     'required',
            //     'string',
            //     
            // ],
            // 'is_head' => [
            //     'nullable',
            //     'boolean',
            //     
            // ],
            'parent_id' => [
                'nullable',
                'string',
                'max:255',

            ],
        ]);
        $structureeData = [
            'salary_id' => $validatedData['salary_id'],
            'role_summary' => $validatedData['role_summary'],
            'key_respon' => $validatedData['key_respon'],
            'qualifications' => $validatedData['qualifications'],
            // 'work_location' => $validatedData['work_location'],

            'is_manager'  => $validatedData['is_manager'] ?? 0,
            'type'          => is_array($validatedData['type'])
                ? implode(',', $validatedData['type'])
                : $validatedData['type'],
            // 'is_head'  => $validatedData['is_head'] ?? 0,
            'parent_id'  => $validatedData['parent_id'] ?? null,
            // 'status'  => $validatedData['status'],
        ];
        DB::beginTransaction();
        $structure->update($structureeData);
        DB::commit();
        return redirect()->route('pages.Structuresnew')->with('success', 'Structure Updated Successfully.');
    }
    public function create()
    {
        $companys = Company::pluck('nickname', 'id', 'name');
        $stores = Stores::pluck('nickname', 'id', 'name');

        $salaries = Salary::all()->mapWithKeys(function ($item) {
            return [
                $item->id => "{$item->salary_start} - {$item->salary_end}"
            ];
        });

        $departments = Departments::pluck('nickname', 'id', 'department_name');
        $positions = Position::pluck('name', 'id', 'name');
        $types = ['Full Time', 'Part Time', 'Contract', 'Internship', 'Remote', 'Urgent'];

        $parents = Structuresnew::with('position')->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => $item->position->name ?? '-'];
            });
        return view('pages.Structuresnew.create', compact(
            'departments',
            'stores',
            'salaries',
            'companys',
            'positions',
            'types',
            'parents'
        ));
    }
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