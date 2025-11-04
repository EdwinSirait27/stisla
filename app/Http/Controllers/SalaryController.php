<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Salary;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\DB;

class SalaryController extends Controller
{
     public function index()
    {
        return view('pages.Salary.Salary');
    }
    public function getSalaries()
    {
        $salaries = Salary::select(['id', 'salary_start','salary_end'])
            ->get()
            ->map(function ($salary) {
                $salary->id_hashed = substr(hash('sha256', $salary->id . env('APP_KEY')), 0, 8);
                $salary->action = '
                    <a href="' . route('Salary.edit', $salary->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit Salary: ' . e($salary->salary_start) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $salary;
            });
        return DataTables::of($salaries)
            ->rawColumns(['action'])
            ->make(true);
    }
    public function edit($hashedId)
    {
        $salary = Salary::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$salary) {
            abort(404, 'salary not found.');
        }


        
        return view('pages.Salary.edit', [
            'salary' => $salary,
            'hashedId' => $hashedId
            
        ]);
    }
 
    public function create()
    {
        
        return view('pages.Salary.create');
    }

    public function store(Request $request)
    {
        
        $validatedData = $request->validate([
            'salary_start' => ['required', 'string','max:255', new NoXSSInput()],
            'salary_end' => ['required', 'string','max:255', new NoXSSInput()],
            
        ], [
            'salary_start.required' => 'Salary must be filled.',
            'salary_start.string' => ' hanya boleh berupa teks.',
            'salary_start.max' => 'maksimal terdiri dari 255 karakter.',
        ]);
        try {
            DB::beginTransaction();
            $salary = Salary::create([
                'salary_start' => $validatedData['salary_start'], 
                'salary_end' => $validatedData['salary_end'], 
            ]);
            DB::commit();
            return redirect()->route('pages.Salary')->with('success', 'Salary created Succesfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }
    public function update(Request $request, $hashedId)
    {
        $salary = Salary::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$salary) {
            return redirect()->route('pages.Salary')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'salary_start' => ['required', 'string', 'max:255', new NoXSSInput()],
            'salary_end' => ['required', 'string', 'max:255', new NoXSSInput()],

        ]);

        $positionData = [
            'salary_start' => $validatedData['salary_start'],
            'salary_end' => $validatedData['salary_end'],
            
        ];
        DB::beginTransaction();
        $salary->update($positionData);
        DB::commit();

        return redirect()->route('pages.Salary')->with('success', 'Salary updated successfully.');
    }
}
