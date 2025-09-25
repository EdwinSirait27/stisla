<?php

namespace App\Http\Controllers;

use App\Models\Grading;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Rules\NoXSSInput;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class GradingController extends Controller
{
     public function index()
    {
        return view('pages.Grading.Grading');
    }
    public function getGradings()
    {
        $gradings = Grading::select(['id', 'grading_name', 'grading_code'])
            ->get();
            // ->map(function ($grading) {
            //     // $grading->id_hashed = substr(hash('sha256', $grading->id . env('APP_KEY')), 0, 8);
            //     // $grading->action = '
            //     //     <a href="' . route('Grading.edit', $grading->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit grading: ' . e($grading->grading_name) . '">
            //     //         <i class="fas fa-user-edit text-secondary"></i>
            //     //     </a>';
            //     // return $grading;
            // });
        return DataTables::of($gradings)

            ->make(true);
    }
    // public function edit($hashedId)
    // {
    //     $grading = Grading::get()->first(function ($u) use ($hashedId) {
    //         $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
    //         return $expectedHash === $hashedId;
    //     });

    //     if (!$grading) {
    //         abort(404, 'Grading not found.');
    //     }

         
    //     return view('pages.Grading.edit', [
    //         'grading' => $grading,
    //         'hashedId' => $hashedId,
    //     ]);
    // }
    // public function create()
    // {
    //     // $managers = User::with('Employee')->pluck('employee_name');
        
    //     return view('pages.Grading.create');
    // }
    // public function store(Request $request)
    // {
    //     // dd($request->all());
    //     $validatedData = $request->validate([
    //         'grading_code' => [
    //             'required',
    //             'string',
    //             'max:255',
    //             'unique:grading,grading_code',
    //             new NoXSSInput()
    //         ],
    //         'grading_name' => [
    //             'required',
    //             'string',
    //             'max:255',
    //             'unique:grading,grading_name',
    //             new NoXSSInput()
    //         ],

    //     ], [
    //         'grading_name.required' => 'Grading name is required.',
    //         'grading_name.string' => 'Grading name must be a string.',
    //         'grading_name.max' => 'grading name may not be greater than 255 characters.',
    //         'grading_name.unique' => 'grading name must be unique or already exists.',
    //         'grading_code.required' => 'Grading code is required.',
    //         'grading_code.string' => 'Grading code must be a string.',
    //         'grading_code.max' => 'grading code may not be greater than 255 characters.',
    //         'grading_code.unique' => 'grading code must be unique or already exists.',
            
    //     ]);
    //     try {
    //         DB::beginTransaction();
    //         $grading = Grading::create([
    //             'grading_name' => $validatedData['grading_name'],
    //             'grading_code' => $validatedData['grading_code'],
    //         ]);
    //         DB::commit();
    //         return redirect()->route('pages.Grading')->with('success', 'Grading created Succesfully!');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()
    //             ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
    //             ->withInput();
    //     }
    // }
    // public function update(Request $request, $hashedId)
    // {
    //     $grading = Grading::get()->first(function ($u) use ($hashedId) {
    //         $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
    //         return $expectedHash === $hashedId;
    //     });
    //     if (!$grading) {
    //         return redirect()->route('pages.Grading')->with('error', 'ID tidak valid.');
    //     }
    //     $validatedData = $request->validate([
    //         'grading_name' => [
    //             'required',
    //             'string',
    //             'max:255',
    //             Rule::unique('grading')->ignore($grading->id),
    //             new NoXSSInput()
    //         ],
    //         'grading_code' => [
    //             'required',
    //             'string',
    //             'max:255',
    //             Rule::unique('grading')->ignore($grading->id),
    //             new NoXSSInput()
    //         ],
    //     ], [
    //         'grading_name.required' => 'name must be filled.',
    //         'grading_code.required' => 'code wajib diisi.',
            
    //     ]);
    //     $gradingData = [
    //         'grading_name' => $validatedData['grading_name'],
    //         'grading_code' => $validatedData['grading_code'],
    //     ];
    //     DB::beginTransaction();
    //     $grading->update($gradingData);
    //     DB::commit();
    //     return redirect()->route('pages.Grading')->with('success', 'Grading Berhasil Diupdate.');
    // }
}
