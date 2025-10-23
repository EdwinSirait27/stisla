<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Departments;
use App\Models\Stores;
use App\Models\Position;
use Illuminate\Http\Request;
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
    public function getOrgChartData()
{
    $data = Structuresnew::with(['position', 'parent'])
        ->get()
        ->map(function ($s) {
            return [
                'id' => $s->id,
                'pid' => $s->parent_id,
                'name' => $s->position->name ?? 'Unknown',
                'title' => $s->structure_code,
            ];
        });

    return response()->json($data);
}

    public function getStructuresnew()
    {
        $structures = Structuresnew::with([

            'company',
            'department',
            'store',
            'position',
            'parent',
            'children',
        ])
            ->select(['id', 'position_id', 'company_id', 'department_id', 'store_id', 'structure_code', 'is_manager_store', 'is_manager_department','parent_id'])
            ->get()
            ->map(function ($structure) {
                $structure->id_hashed = substr(hash('sha256', $structure->id . env('APP_KEY')), 0, 8);
                $structure->action = '
                    <a href="' . route('Structuresnew.edit', $structure->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit structure: ' . e($structure->structure_name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
        $structure->checkbox = '<input type="checkbox" class="payroll-checkbox" name="structure_ids[]" value="' . $structure->id_hashed . '">';

                return $structure;
            });
        return DataTables::of($structures)
            ->addColumn('company_name', function ($structure) {
                return !empty($structure->company) && !empty($structure->company->nickname)
                    ? $structure->company->nickname
                    : 'Empty';
            })
            ->addColumn('department_name', function ($structure) {
                return !empty($structure->department) && !empty($structure->department->nickname)
                    ? $structure->department->nickname
                    : 'Empty';
            })
            ->addColumn('store_name', function ($structure) {
                return !empty($structure->store) && !empty($structure->store->nickname)
                    ? $structure->store->nickname
                    : 'Empty';
            })
            ->addColumn('position_name', function ($structure) {
                return !empty($structure->position) && !empty($structure->position->name)
                    ? $structure->position->name
                    : 'Empty';
            })
            ->addColumn('parent', function ($structure) {
                return !empty($structure->parent) && !empty($structure->parent->position->name)
                    ? $structure->parent->position->name
                    : 'Empty';
            })
       
       


            ->rawColumns(['action', 'position_name', 'company_name','checkbox', 'department_name', 'store_name','parent','children'])
            ->make(true);
    }
   public function bulkDelete(Request $request)
    {
        $idsRaw = $request->input('structure_ids', '');
        $ids = is_array($idsRaw) ? $idsRaw : explode(',', $idsRaw);
        if (empty($ids)) {
            return back()->with('error', 'No Data.');
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
      // ->addColumn('children', function ($structure) {
            //     return !empty($structure->children) && !empty($structure->children->position->name)
            //         ? $structure->children->position->name
            //         : 'Empty';
            // })
//             ->addColumn('children', function ($structure) {
//     if ($structure->children->isEmpty()) {
//         return 'Empty';
//     }

//     // tampilkan semua posisi anak-anaknya (misal dipisah koma)
//     return $structure->children->map(function ($child) {
//         return optional($child->position)->name ?? 'Unknown';
//     })->implode(', ');
// })



    public function edit($hashedId)
    {
        $structure = Structuresnew::with('company', 'department', 'store', 'position','parent')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$structure) {
            abort(404, 'Structure not found.');
        }

        // $employees = Employee::where('status', ['Active','Pending'])->pluck('employee_name', 'id');
        $stores = Stores::get();
        $departments = Departments::get();
        $companies = Company::get();
        $positions = Position::get();
        $parents = Structuresnew::with('position')->get();

        return view('pages.Structuresnew.edit', [
            'structure' => $structure,
            'positions' => $positions,
            'parents' => $parents,
            'hashedId' => $hashedId,
            'stores' => $stores,
            'departments' => $departments,
            'companies' => $companies,
        ]);
    }
    public function update(Request $request, $hashedId)
    {
        $structure = Structuresnew::with('company', 'department', 'store', 'Positions')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$structure) {
            return redirect()->route('pages.Structuresnew')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'company_id' => [
                'nullable',
                'string',
                'max:255',
                new NoXSSInput()
            ],
            'department_id' => [
                'nullable',
                'string',
                'max:255',
                new NoXSSInput()
            ],
            'store_id' => [
                'nullable',
                'string',
                'max:255',
                new NoXSSInput()
            ],
            'position_id' => [
                'nullable',
                'string',
                'max:255',
                new NoXSSInput()
            ],
            'structure_code' => [
                'nullable',
                'string',
                'max:255',
                new NoXSSInput()
            ],
            'is_manager_store' => [
                'nullable',
                'boolean',
                new NoXSSInput()
            ],
            'is_manager_department' => [
                'nullable',
                'boolean',
                new NoXSSInput()
            ],
        ]);
        $structureeData = [
            'company_id' => $validatedData['company_id'],
            'department_id'  => $validatedData['department_id'],
            'store_id'  => $validatedData['store_id'],
            'position_id'  => $validatedData['position_id'],
            'structure_code'  => $validatedData['structure_code'],
            'is_manager_store'  => $validatedData['is_manager_store'] ?? 0,
            'is_manager_department'  => $validatedData['is_manager_department'] ?? 0,
        ];
        DB::beginTransaction();
        $structure->update($structureeData);
        DB::commit();
        return redirect()->route('pages.Structuresnew')->with('success', 'Structure Updated Successfully.');
    }
    public function create()
    {
        $stores = Stores::pluck('nickname', 'id');
    $departments = Departments::pluck('nickname', 'id');
    $companys = Company::pluck('nickname', 'id');
    $positions = Position::pluck('name', 'id');
        // $parents = Structuresnew::with('position')->pluck('name', 'id');
        $parents = Structuresnew::with('position')->get()
    ->mapWithKeys(function ($item) {
        return [$item->id => $item->position->name ?? '-'];
    });


        // $stores = Stores::get();
        // $departments = Departments::get();
        // $companys = Company::get();
        // $positions = Position::get();
        return view('pages.Structuresnew.create', compact('departments', 'stores', 'companys', 'positions','parents'));
    }
    // public function store(Request $request)
    // {
    //     // dd($request->all());
    //     $validatedData = $request->validate([
    //         'company_id' => [
    //             'nullable',
    //             'string',
    //             'max:255',
    //             new NoXSSInput()
    //         ],
    //         'department_id' => [
    //             'nullable',
    //             'string',
    //             'max:255',
    //             new NoXSSInput()
    //         ],
    //         'store_id' => [
    //             'nullable',
    //             'string',
    //             'max:255',
    //             new NoXSSInput()
    //         ],
    //         'position_id' => [
    //             'nullable',
    //             'string',
    //             'max:255',
    //             new NoXSSInput()
    //         ],
    //         'structure_code' => [
    //             'nullable',
    //             'string',
    //             'max:255',
    //             new NoXSSInput()
    //         ],
    //         'is_manager_store' => [
    //             'nullable',
    //             'boolean',
    //             new NoXSSInput()
    //         ],
    //         'is_manager_department' => [
    //             'nullable',
    //             'boolean',
    //             new NoXSSInput()
    //         ],
    //     ]);
    //     try {
    //         DB::beginTransaction();
    //         $strucrure = Structuresnew::create([
    //             'company_id' => $validatedData['company_id'],
    //             'department_id'  => $validatedData['department_id'],
    //             'store_id'  => $validatedData['store_id'],
    //             'position_id'  => $validatedData['position_id'],
    //             'is_manager_store'  => $validatedData['is_manager_store'] ?? 0,
    //             'is_manager_department'  => $validatedData['is_manager_department'] ?? 0,
    //         ]);
    //         DB::commit();
    //         return redirect()->route('pages.Structuresnew')->with('success', 'Structure created Succesfully!');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()
    //             ->withErrors(['error' => 'Error while creating data: ' . $e->getMessage()])
    //             ->withInput();
    //     }
    // }
    public function store(Request $request)
{
        // dd($request->all());

    $validatedData = $request->validate([
        'company_id' => ['required', 'string', 'max:255', new NoXSSInput()],
        'department_id' => ['required', 'string', 'max:255', new NoXSSInput()],
        'parent_id' => ['nullable', 'string', 'max:255', new NoXSSInput()],
        'store_id' => ['required', 'string', 'max:255', new NoXSSInput()],
        'position_id' => ['nullable', 'string', 'max:255', new NoXSSInput()],
        'is_manager_store' => ['nullable', 'boolean', new NoXSSInput()],
        'is_manager_department' => ['nullable', 'boolean', new NoXSSInput()],
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
            'is_manager_store' => $validatedData['is_manager_store'] ?? 0,
            'is_manager_department' => $validatedData['is_manager_department'] ?? 0,
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
