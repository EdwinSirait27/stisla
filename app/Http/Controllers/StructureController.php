<?php

namespace App\Http\Controllers;
use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Structure;
use App\Models\User;
use App\Rules\NoXSSInput;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StructureController extends Controller
{
    public function index()
    {
        return view('pages.Structures.Structures');
    }
    public function getStructures()
    {
        $structures = User::whereHas('Employee', function ($q) {
            $q->whereIn('status', ['Active', 'Pending']);
        })
            ->with([
                'Employee' => function ($q) {
                    $q->whereIn('status', ['Active', 'Pending']);
                },
                'Employee.employees',
                'Employee.company',
            ])
            ->select(['id', 'employee_id'])
            ->get()
            ->map(function ($structure) {
                $structure->id_hashed = substr(hash('sha256', $structure->id . env('APP_KEY')), 0, 8);
                $structure->action = '
                    <a href="' . route('Structures.edit', $structure->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit structure: ' . e($structure->Employee->employee_name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $structure;
            });
        return DataTables::of($structures)
            ->addColumn('employee_name', function ($structure) {
                return !empty($structure->Employee) && !empty($structure->Employee->employee_name)
                    ? $structure->Employee->employee_name
                    : 'Empty';
            })

            ->addColumn('level', function ($structure) {
                return !empty($structure->Employee->employees) && !empty($structure->Employee->employees->employee_name)
                    ? $structure->Employee->employees->employee_name
                    : 'Empty';
            })
            ->addColumn('is_manager', function ($structure) {
                return !empty($structure->Employee) && !empty($structure->Employee->is_manager)
                    ? $structure->Employee->is_manager
                    : 'Empty';
            })
            ->rawColumns(['action', 'employee_name', 'level','is_manager'])
            ->make(true);
    }
    public function edit($hashedId)
    {
        $structure = User::with('Employee.employees', 'Employee.company', 'Employee')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$structure) {
            abort(404, 'Department not found.');
        }

        $employees = Employee::where('status', ['Active','Pending'])->pluck('employee_name', 'id');

        return view('pages.Structures.edit', [
            'structure' => $structure,
            'hashedId' => $hashedId,
            'employees' => $employees,
        ]);
    }
    public function update(Request $request, $hashedId)
    {
        $structure = User::with('Employee.employees', 'Employee.company')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$structure) {
            return redirect()->route('pages.Structures')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'level_id' => [
                'nullable',
                'string',
                'max:255',
                new NoXSSInput()
            ],
            'is_manager' => [
                'nullable',
                'boolean',
                new NoXSSInput()
            ],
        ]);
        $structureData = [
            'level_id' => $validatedData['level_id'],
            'is_manager'  => $validatedData['is_manager'] ?? 0,
        ];
        DB::beginTransaction();
        $structure->update($structureData);
        DB::commit();
        return redirect()->route('pages.Structures')->with('success', 'Structure Updated Successfully.');
    }
    public function create()
    {
        $employees = Employee::pluck('employee_name', 'id');

        return view('pages.Structures.create', compact('employees'));
    }
    public function store(Request $request)
    {
        // dd($request->all());
        $validatedData = $request->validate([

            // 'employee_id' => [
            //     'required',
            //     'max:255',
            //     new NoXSSInput()
            // ],

            'level_id' => [
                'nullable',
                'max:255',
                new NoXSSInput()
            ],
            'is_manager' => [
                'nullable',
                'boolean',
                new NoXSSInput()
            ],
        ]);
        try {
            DB::beginTransaction();
            $structure = Structure::create([
                // 'employee_id' => $validatedData['employee_id'],
                'level_id' => $validatedData['level_id'],
                'is_manager' => $validatedData['is_manager'] ?? 0,
            ]);
            DB::commit();
            return redirect()->route('pages.Structures')->with('success', 'Structure created Succesfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Error while creating data: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
