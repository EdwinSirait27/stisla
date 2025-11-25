<?php

namespace App\Http\Controllers;

use App\Models\Grading;
use App\Models\Groups;
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
        $gradings = Grading::with('groups')->select(['id', 'grading_name', 'grading_code','group_id'])
            ->get()
            ->map(function ($grading) {
                $grading->id_hashed = substr(hash('sha256', $grading->id . env('APP_KEY')), 0, 8);
                $grading->action = '
                    <a href="' . route('Grading.edit', $grading->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit grading: ' . e($grading->grading_name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $grading;
            });
        return DataTables::of($gradings)
            ->addColumn('group_name', fn($e) => optional($e->groups)->group_name ?? 'Empty')
            ->addColumn('remark', fn($e) => optional($e->groups)->remark ?? 'Empty')
            ->rawColumns(['group_name','remark','action'])
            ->make(true);
    }
    public function edit($hashedId)
    {
        $grading = Grading::with('groups')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$grading) {
            abort(404, 'Grading not found.');
        }
        $groups = Groups::get();

         
        return view('pages.Grading.edit', [
            'grading' => $grading,
            'groups' => $groups,
            'hashedId' => $hashedId,
        ]);
    }
    public function create()
    {
        // $managers = User::with('Employee')->pluck('employee_name');
        $groups = Groups::get();
        
        return view('pages.Grading.create', compact('groups'));
    }
    public function store(Request $request)
    {
        // dd($request->all());
        $validatedData = $request->validate([
            // 'grading_code' => [
            //     'required',
            //     'string',
            //     'max:255',
            //     new NoXSSInput()
            // ],
            'grading_name' => [
                'required',
                'string',
                'max:255',
                new NoXSSInput()
            ],
            'group_id' => [
                'required',
                'string',
                'max:255',
                new NoXSSInput()
            ],

        ], [
            'grading_name.required' => 'Grading name is required.',
            'grading_name.string' => 'Grading name must be a string.',
            'grading_name.max' => 'grading name may not be greater than 255 characters.',
            // 'grading_code.required' => 'Grading code is required.',
            // 'grading_code.string' => 'Grading code must be a string.',
            // 'grading_code.max' => 'grading code may not be greater than 255 characters.',
            
        ]);
        try {
            DB::beginTransaction();
            $grading = Grading::create([
                'grading_name' => $validatedData['grading_name'],
                // 'grading_code' => $validatedData['grading_code'],
                'group_id' => $validatedData['group_id'],
            ]);
            DB::commit();
            return redirect()->route('pages.Grading')->with('success', 'Grading created Succesfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }
    public function update(Request $request, $hashedId)
    {
        $grading = Grading::with('groups')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$grading) {
            return redirect()->route('pages.Grading')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'grading_name' => [
                'required',
                'string',
                'max:255',
                new NoXSSInput()
            ],
            // 'grading_code' => [
            //     'required',
            //     'string',
            //     'max:255',
            //     new NoXSSInput()
            // ],
            'group_id' => [
                'required',
                'string',
                'max:255',
                new NoXSSInput()
            ],
        ], [
            'grading_name.required' => 'name must be filled.',
            // 'grading_code.required' => 'code wajib diisi.',
            'group_id.required' => 'grading diisi.',
            
        ]);
        $gradingData = [
            'grading_name' => $validatedData['grading_name'],
            // 'grading_code' => $validatedData['grading_code'],
            'group_id' => $validatedData['group_id'],
        ];
        DB::beginTransaction();
        $grading->update($gradingData);
        DB::commit();
        return redirect()->route('pages.Grading')->with('success', 'Grading Berhasil Diupdate.');
    }
}
