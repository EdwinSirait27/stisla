<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Departments;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function index()
    {
        return view('pages.Department.Department');
    }
    public function getDepartments()
    {
        $departments = Departments::with('user.Employee')->select(['id', 'department_name','manager_id'])
            ->get()
            ->map(function ($department) {
                $department->id_hashed = substr(hash('sha256', $department->id . env('APP_KEY')), 0, 8);
                $department->action = '
                    <a href="' . route('Department.edit', $department->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit Department: ' . e($department->department_name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $department;
            });
        return DataTables::of($departments)
        ->addColumn('employee_name', function ($department) {
            return !empty($department->user->Employee) && !empty($department->user->Employee->employee_name)
                ? $department->user->Employee->employee_name
                : 'Empty';
        })
            ->rawColumns(['action','employee_name'])
            ->make(true);
    }
    public function edit($hashedId)
    {
        $department = Departments::with('user.Employee')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$department) {
            abort(404, 'Department not found.');
        }

        $selectedName = old('department_name', $department->department_name ?? '');

        $selectedManager = old('employee_name', optional($department->user->Employee->first())->employee_name ?? '');

        return view('pages.Department.edit', [
            'department' => $department,
            'hashedId' => $hashedId,
            'selectedManager' => $selectedManager,
            // 'stores' => $stores,
            'selectedName' => $selectedName
        ]);
    }
 
    public function create()
    {
        $managers = User::with('Employee')->get();
        return view('pages.Department.create',compact('managers'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $validatedData = $request->validate([
            'department_name' => ['required', 'string','max:255', new NoXSSInput()],
            'manager_id' => ['nullable','max:255', new NoXSSInput()],
            
        ], [
            'name.required' => 'name wajib diisi.',
            'name.string' => 'name hanya boleh berupa teks.',
            'name.max' => 'Username maksimal terdiri dari 255 karakter.',
        ]);
        try {
            DB::beginTransaction();
            $department = Departments::create([
                'name' => $validatedData['name'], 
                'manager_id' => $validatedData['manager_id'], 
            ]);
            DB::commit();
            return redirect()->route('pages.Department')->with('success', 'Department created Succesfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }
    public function update(Request $request, $hashedId)
    {
        $position = Departments::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$position) {
            return redirect()->route('pages.Position')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', new NoXSSInput()],

        ], [
            'name.required' => 'name wajib diisi.',
            'name.string' => 'name hanya boleh berupa teks.',
            'name.max' => 'Username maksimal terdiri dari 255 karakter.',
        ]);

        $positionData = [
            'name' => $validatedData['name'],
            
        ];
        DB::beginTransaction();
        $position->update($positionData);
        DB::commit();

        return redirect()->route('pages.Department')->with('success', 'Department Berhasil Diupdate.');
    }
}
