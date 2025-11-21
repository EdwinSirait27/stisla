<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\MasterSubmission;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\DB;


class MasterSubmissionController extends Controller
{
      public function index()
    {
        return view('pages.MasterSubmission.MasterSubmission');
    }
    public function getMasterSubmissions()
    {
        $leaves = MasterSubmission::select(['id', 'name'])
            ->get()
            ->map(function ($leave) {
                $leave->id_hashed = substr(hash('sha256', $leave->id . env('APP_KEY')), 0, 8);
                $leave->action = '
                    <a href="' . route('MasterSubmission.edit', $leave->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit leave"title="Edit leave: ' . e($leave->name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $leave;
            });
        return DataTables::of($leaves)
            ->rawColumns(['action'])
            ->make(true);
    }
    public function edit($hashedId)
    {
        $leave = MasterSubmission::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$leave) {
            abort(404, 'leave not found.');
        }

        
        
        return view('pages.MasterSubmission.edit', [
            'leave' => $leave,
            'hashedId' => $hashedId            
        ]);
    }
 
    public function create()
    {
        
        return view('pages.MasterSubmission.create');
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $validatedData = $request->validate([
            'name' => ['required', 'string','max:255', new NoXSSInput()],
            
        ], [
            'name.required' => 'name wajib diisi.',
            'name.string' => 'name hanya boleh berupa teks.',
            'name.max' => 'Username maksimal terdiri dari 255 karakter.',
        ]);
        try {
            DB::beginTransaction();
            $leave = MasterSubmission::create([
                'name' => $validatedData['name'], 
            ]);
            DB::commit();
            return redirect()->route('pages.MasterSubmission')->with('success', 'MasterSubmission created Succesfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }
    public function update(Request $request, $hashedId)
    {
        $leave = MasterSubmission::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$leave) {
            return redirect()->route('pages.MasterSubmission')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', new NoXSSInput()],

        ], [
            'name.required' => 'name wajib diisi.',
            'name.string' => 'name hanya boleh berupa teks.',
            'name.max' => 'Username maksimal terdiri dari 255 karakter.',
        ]);

        $leaveData = [
            'name' => $validatedData['name'],
            
        ];
        DB::beginTransaction();
        $leave->update($leaveData);
        DB::commit();

        return redirect()->route('pages.MasterSubmission')->with('success', 'MasterSubmission updated successfully.');
    }
}
