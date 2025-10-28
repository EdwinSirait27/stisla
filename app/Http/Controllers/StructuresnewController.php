<?php

namespace App\Http\Controllers;
use App\Models\Company;
use App\Models\Departments;
use App\Models\Stores;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
use App\Models\Structuresnew;
use App\Rules\NoXSSInput;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StructuresnewController extends Controller
{
    public function index()
    {
        return view('pages.Structuresnew.Structuresnew');
    }
   
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
public function getStructuresnew()
{
    $structures = Structuresnew::with([
        'company',
        'department',
        'store',
        'position',
        'parent',
        'children.position', // pastikan load posisi anak
    ])
        ->select([
            'id', 'position_id', 'company_id', 'department_id',
            'store_id', 'structure_code', 'is_manager', 'is_head',
            'parent_id', 'status'
        ])
        ->get()
        ->map(function ($structure) {
            $structure->id_hashed = substr(hash('sha256', $structure->id . env('APP_KEY')), 0, 8);

            $structure->action = '
                <a href="' . route('Structuresnew.edit', $structure->id_hashed) . '" class="mx-3" 
                    data-bs-toggle="tooltip" data-bs-original-title="Edit Structure"
                    title="Edit structure: ' . e($structure->structure_name) . '">
                    <i class="fas fa-user-edit text-secondary"></i>
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
               ->addColumn('parent', fn($structure) =>
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
        ->rawColumns([
            'action', 'checkbox',
            'company_name', 'department_name', 'store_name',
            'position_name', 'parent', 'children'
        ])
        ->make(true);
}


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
public function getOrgChartData()
{
    $data = Structuresnew::with(['position', 'parent', 'employee','employee.store','store'])
        ->get()
        ->map(function ($s) {
            return [
                'id'        => $s->id,
                'pid'       => $s->parent_id,
                'Position'  => $s->position->name ?? 'Unknown',
                'Employee'  => $s->employee->pluck('employee_name')->join(', ') ?: 'Empty',
                //  'Location'     => $s->employee
                //                     ->pluck('store.name')
                //                     ->unique()
                //                     ->join(', ') ?: 'Empty',
                'Location'  => $s->store->name ?? 'Empty',
                'status'    => $s->status,
            ];
        });

    return response()->json($data);
}

    public function edit($hashedId)
    {
        $structure = Structuresnew::with('company', 'department', 'store', 'position', 'parent')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$structure) {
            abort(404, 'Structure not found.');
        }
        $parents = Structuresnew::with('position')->get()->pluck('position.name', 'id');
        $statuses = ['active' => 'active','inactive' => 'inactive', 'vacant' => 'vacant'];
        return view('pages.Structuresnew.edit', [
            'structure' => $structure,
            'parents' => $parents,
            'statuses' => $statuses,
            'hashedId' => $hashedId,
        ]);
    }
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
    public function update(Request $request, $hashedId)
    {
        $structure = Structuresnew::with('company', 'department', 'store', 'position')->get()->first(function ($u) use ($hashedId) {
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
                new NoXSSInput()
            ],
            'status' => [
                'required',
                'string',
                new NoXSSInput()
            ],
            'is_head' => [
                'nullable',
                'boolean',
                new NoXSSInput()
            ],
            'parent_id' => [
                'nullable',
                'string',
                'max:255',
                new NoXSSInput()
            ],
        ]);
        $structureeData = [
            'is_manager'  => $validatedData['is_manager'] ?? 0,
            'is_head'  => $validatedData['is_head'] ?? 0,
            'parent_id'  => $validatedData['parent_id'] ?? null,
            'status'  => $validatedData['status'],
        ];
        DB::beginTransaction();
        $structure->update($structureeData);
        DB::commit();
        return redirect()->route('pages.Structuresnew')->with('success', 'Structure Updated Successfully.');
    }
// public function create() 
// {
//     $usedPositionIds = Structuresnew::pluck('position_id')->unique()->toArray();
//     $companys = Company::pluck('nickname', 'id');
//     $stores = Stores::pluck('nickname', 'id');
//     $departments = Departments::pluck('nickname', 'id');
//     $positions = Position::whereNotIn('id', $usedPositionIds)->pluck('name', 'id');
//     // Ambil parent (struktur yang sudah ada)
//     $parents = Structuresnew::with('position')->get()
//         ->mapWithKeys(function ($item) {
//             return [$item->id => $item->position->name ?? '-'];
//         });
//     // Cek apakah sudah lengkap semua struktur
//     $isComplete = Structuresnew::whereNotNull('company_id')
//         ->whereNotNull('store_id')
//         ->whereNotNull('department_id')
//         ->whereNotNull('position_id')
//         ->whereNotNull('parent_id')
//         ->exists();

//     return view('pages.Structuresnew.create', compact(
//         'departments',
//         'stores',
//         'companys',
//         'positions',
//         'parents',
//         'isComplete'
//     ));
// }
// public function create()
// {
//     $existingStructures = Structuresnew::select('company_id', 'store_id', 'department_id', 'position_id')
//         ->whereNotNull('company_id')
//         ->whereNotNull('store_id')
//         ->whereNotNull('department_id')
//         ->whereNotNull('position_id')
//         ->get();

//     $companys = Company::pluck('nickname', 'id');
//     $stores = Stores::pluck('nickname', 'id');
//     $departments = Departments::pluck('nickname', 'id');
//     $positions = Position::pluck('name', 'id');

//     $parents = Structuresnew::with('position')->get()
//         ->mapWithKeys(fn($item) => [$item->id => $item->position->name ?? '-']);

//     $isComplete = Structuresnew::whereNotNull('company_id')
//         ->whereNotNull('store_id')
//         ->whereNotNull('department_id')
//         ->whereNotNull('position_id')
//         ->whereNotNull('parent_id')
//         ->exists();

//     $usedCombinations = $existingStructures->toArray();

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
// public function create()
// {
//     $existingStructures = Structuresnew::select('company_id', 'store_id', 'department_id', 'position_id')
//         ->whereNotNull('company_id')
//         ->whereNotNull('store_id')
//         ->whereNotNull('department_id')
//         ->whereNotNull('position_id')
//         ->get();

//     $companys = Company::pluck('nickname', 'id');
//     $stores = Stores::pluck('nickname', 'id');
//     $departments = Departments::pluck('nickname', 'id');

//     // ambil semua posisi terlebih dahulu
//     $positions = Position::pluck('name', 'id');

//     // ambil semua parent
//     $parents = Structuresnew::with('position')->get()
//         ->mapWithKeys(fn($item) => [$item->id => $item->position->name ?? '-']);

//     $isComplete = Structuresnew::whereNotNull('company_id')
//         ->whereNotNull('store_id')
//         ->whereNotNull('department_id')
//         ->whereNotNull('position_id')
//         ->whereNotNull('parent_id')
//         ->exists();

//     // ambil kombinasi unik berdasarkan company_id
//     $usedCombinations = $existingStructures->groupBy('company_id')->map(function ($items) {
//         return $items->map(function ($item) {
//             return [
//                 'store_id' => $item->store_id,
//                 'department_id' => $item->department_id,
//                 'position_id' => $item->position_id,
//             ];
//         });
//     });

//     // contoh: kalau sedang create untuk company tertentu (misalnya id=1),
//     // filter posisi yang sudah dipakai oleh company_id itu
//     $currentCompanyId = request()->get('company_id'); // atau bisa diubah sesuai logika create-mu
//     if ($currentCompanyId) {
//         $usedPositionIds = $existingStructures
//             ->where('company_id', $currentCompanyId)
//             ->pluck('position_id')
//             ->unique()
//             ->toArray();

//         // sembunyikan posisi yang sudah dipakai oleh company_id yang sama
//         $positions = $positions->except($usedPositionIds);
//     }

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
// public function getAvailablePositions(Request $request)
// {
//     $company_id = $request->company_id;
//     $store_id = $request->store_id;
//     $department_id = $request->department_id;

//     // Ambil semua posisi
//     $positions = Position::pluck('name', 'id');

//     // Ambil kombinasi yang sudah ada untuk company yang sama
//     $usedPositions = Structuresnew::where('company_id', $company_id)
//         ->where('store_id', $store_id)
//         ->where('department_id', $department_id)
//         ->pluck('position_id')
//         ->toArray();

//     // Hapus posisi yang sudah digunakan
//     $availablePositions = $positions->except($usedPositions);

//     return response()->json($availablePositions);
// }
// public function create()
// {
//     $companys = Company::pluck('nickname', 'id');
//     $stores = Stores::pluck('nickname', 'id');
//     $departments = Departments::pluck('nickname', 'id');

//     $parents = Structuresnew::with('position')->get()
//         ->mapWithKeys(fn($item) => [$item->id => $item->position->name ?? '-']);

//     $isComplete = Structuresnew::whereNotNull('company_id')
//         ->whereNotNull('store_id')
//         ->whereNotNull('department_id')
//         ->whereNotNull('position_id')
//         ->whereNotNull('parent_id')
//         ->exists();

//     return view('pages.Structuresnew.create', compact(
//         'departments',
//         'stores',
//         'companys',
//         'parents',
//         'isComplete'
//     ));
// }
public function create()
{
    // Langsung sembunyi/exclude kombinasi yang sudah ada
    $existingStructures = Structuresnew::select('company_id', 'store_id', 'department_id', 'position_id')
        ->whereNotNull('company_id')
        ->whereNotNull('store_id')
        ->whereNotNull('department_id')
        ->whereNotNull('position_id')
        ->get();

    $companys = Company::pluck('nickname', 'id');
    $stores = Stores::pluck('nickname', 'id');
    $departments = Departments::pluck('nickname', 'id');
    
    // Modifikasi: Gunakan Position::pluck('name', 'id') seperti remark baris 1
    $positions = Position::pluck('name', 'id');
    
    $parents = Structuresnew::with('position')->get()
        ->mapWithKeys(fn($item) => [$item->id => $item->position->name ?? '-']);

    $isComplete = Structuresnew::whereNotNull('company_id')
        ->whereNotNull('store_id')
        ->whereNotNull('department_id')
        ->whereNotNull('position_id')
        ->whereNotNull('parent_id')
        ->exists();

    // Modifikasi: Format kombinasi yang sudah digunakan
    // Untuk ditampilkan/dicek berdasarkan company_id berbeda (remark baris 2)
    $usedCombinations = $existingStructures->map(function($item) {
        return [
            'company_id' => $item->company_id,
            'store_id' => $item->store_id,
            'department_id' => $item->department_id,
            'position_id' => $item->position_id,
        ];
    })->toArray();

    return view('pages.Structuresnew.create', compact(
        'departments',
        'stores',
        'companys',
        'positions',
        'parents',
        'isComplete',
        'usedCombinations'
    ));
}







    public function store(Request $request)
    {
        // dd($request->all());

        $validatedData = $request->validate([
            'company_id' => ['required', 'string', 'max:255', new NoXSSInput()],
            'department_id' => ['required', 'string', 'max:255', new NoXSSInput()],
            'parent_id' => ['nullable', 'string', 'max:255', new NoXSSInput()],
            'store_id' => ['required', 'string', 'max:255', new NoXSSInput()],
            'position_id' => ['nullable', 'string', 'max:255', new NoXSSInput()],
            'is_manager' => ['nullable', 'boolean', new NoXSSInput()],
            'is_head' => ['nullable', 'boolean', new NoXSSInput()],
            'status' => ['nullable', 'string', new NoXSSInput()],
            // 'is_manager_department' => ['nullable', 'boolean', new NoXSSInput()],
        ]);

        try {
            DB::beginTransaction();

            // Ambil nama perusahaan, departemen, dan store
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

            // Tentukan nomor urut
            $nextNumber = 1;
            if ($lastStructure) {
                // Ambil angka terakhir dari struktur code
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
                'structure_code' => $structureCode,
                'is_manager' => $validatedData['is_manager'] ?? 0,
                'is_head' => $validatedData['is_head'] ?? 0,
                'status' => $validatedData['status'] ?? 'Active',
                // 'is_manager_department' => $validatedData['is_manager_department'] ?? 0,
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
}
