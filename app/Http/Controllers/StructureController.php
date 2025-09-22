<?php
namespace App\Http\Controllers;
use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Structure;
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
        $structures = Structure::with('employee','level','employee.department')->select(['id', 'employee_id', 'level_id', 'is_manager'])
            ->get()
            ->map(function ($structure) {
                $structure->id_hashed = substr(hash('sha256', $structure->id . env('APP_KEY')), 0, 8);
                $structure->action = '
                    <a href="' . route('Structures.edit', $structure->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit structure: ' . e($structure->employee->employee_name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $structure;
            });
        return DataTables::of($structures)
            ->addColumn('employee_name', function ($structure) {
                return !empty($structure->employee) && !empty($structure->employee->employee_name)
                    ? $structure->employee->employee_name
                    : 'Empty';
            })
            ->addColumn('level', function ($structure) {
                return !empty($structure->level) && !empty($structure->level->employee_name)
                    ? $structure->level->employee_name
                    : 'Empty';
            })
            ->addColumn('department_name', function ($structure) {
                return !empty($structure->employee->department) && !empty($structure->employee->department->department_name)
                    ? $structure->employee->department->department_name
                    : 'Empty';
            })
            ->rawColumns(['action', 'employee_name','level','department_name'])
            ->make(true);
    }
    public function edit($hashedId)
    {
        $structure = Structure::with('employee','level')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$structure) {
            abort(404, 'Department not found.');
        }

                $employees = Employee::pluck('employee_name', 'id');

        return view('pages.Structures.edit', [
            'structure' => $structure,
            'hashedId' => $hashedId,
            'employees' => $employees,
        ]);
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

            'employee_id' => [
                'required',
                'max:255',
                new NoXSSInput()
            ],
          
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
                'employee_id' => $validatedData['employee_id'],
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
    public function update(Request $request, $hashedId)
    {
        $structure = Structure::with('employee','level')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$structure) {
            return redirect()->route('pages.Structures')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'employee_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('structure')->ignore($structure->id),
                new NoXSSInput()
            ],
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
            'employee_id' => $validatedData['employee_id'],
            'level_id' => $validatedData['level_id'],
            'is_manager'  => $validatedData['is_manager'] ?? 0,
        ];
        DB::beginTransaction();
        $structure->update($structureData);
        DB::commit();
        return redirect()->route('pages.Structures')->with('success', 'Structure Updated Successfully.');
    }
}