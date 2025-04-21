<?php

namespace App\Http\Controllers;

use App\Models\Shifts;
use Illuminate\Http\Request;
use App\Models\Stores;
use Yajra\DataTables\DataTables;
use App\Models\Departments;
use App\Rules\NoXSSInput;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    public function index()
    {
        return view('pages.Shift.Shift');
    }
    public function getShifts()
    {
        $shifts = Shifts::with('store')->select(['id', 'store_id','shift_name','start_name','end_time','last_sync','is_holiday'])
            ->get()
            ->map(function ($shift) {
                $shift->id_hashed = substr(hash('sha256', $shift->id . env('APP_KEY')), 0, 8);
                $shift->action = '
                    <a href="' . route('Shift.edit', $shift->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit Shift: ' . e($shift->shift_name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $shift;
            });
        return DataTables::of($shifts)
        ->addColumn('name', function ($shift) {
            return !empty($shift->store->name) && !empty($shift->store->name)
                ? $shift->store->name
                : 'Empty';
        })
            ->rawColumns(['action','name'])
            ->make(true);
    }
    public function edit($hashedId)
    {
        $shift = Shifts::with('store')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr( hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$shift) {
            abort(404, 'Shift not found.');
        }

        $stores = Stores::with('user')->get();
        return view('pages.Shift.edit', [
            'shift' => $shift,
            'hashedId' => $hashedId,
            'stores' => $stores,
        ]);
    }
 
    public function create()
    {
        $stores = Stores::with('user')->get();
        return view('pages.Shift.create',compact('stores'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validatedData = $request->validate([
            'store_id' => ['nullable','max:255', 'unique:shifts_tables,store_id',
                new NoXSSInput()],
            'shift_name' => ['required', 'string','max:255', 'unique:shifts_tables,shift_name',
                new NoXSSInput()],
                'start_time' => 'required|date_format:H:i',
                'end_time' => [
                    'required',
                    'date_format:H:i',
                    Rule::when($request->start_time < $request->end_time, [
                        'after:start_time'
                    ], [])
                ],
        ], [
            'department_name.required' => 'name wajib diisi.',
            'department_name.string' => 'name hanya boleh berupa teks.',
            'department_name.max' => 'Username maksimal terdiri dari 255 karakter.',
        ]);
        try {
            DB::beginTransaction();
            $department = Departments::create([
                'department_name' => $validatedData['department_name'], 
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
        $department = Departments::with('user.Employee')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$department) {
            return redirect()->route('pages.Department')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'department_name' => ['required', 'string', 'max:255',Rule::unique('departments_tables')->ignore($department->id),
            new NoXSSInput()],
            'manager_id' => ['required', 'string', 'max:255', Rule::unique('departments_tables')->ignore($department->id),
            new NoXSSInput()],

        ], [
            'department_name.required' => 'name wajib diisi.',
            'manager_id.required' => 'Manager wajib diisi.',
            'department_name.string' => 'name hanya boleh berupa teks.',
            
        ]);

        $departmentData = [
            'department_name' => $validatedData['department_name'],
            'manager_id' => $validatedData['manager_id'],
            
        ];
        DB::beginTransaction();
        $department->update($departmentData);
        DB::commit();

        return redirect()->route('pages.Department')->with('success', 'Department Berhasil Diupdate.');
    }
}
