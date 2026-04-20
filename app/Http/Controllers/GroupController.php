<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Groups;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
      public function index()
    {
        return view('pages.Group.Group');
    }
    public function getGroups()
    {
        $groups = Groups::select(['id', 'group_name','remark'])
            ->get()
            ->map(function ($group) {
                $group->id_hashed = substr(hash('sha256', $group->id . env('APP_KEY')), 0, 8);
                $group->action = '
                    <a href="' . route('Group.edit', $group->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit group: ' . e($group->group_name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $group;
            });
        return DataTables::of($groups)
            ->rawColumns(['action'])
            ->make(true);
    }
   
    public function edit($hashedId)
    {
        $group = Groups::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$group) {
            abort(404, 'group not found.');
        }
        
        return view('pages.Group.edit', [
            'group' => $group,
            'hashedId' => $hashedId
        ]);
    }

    public function create()
    {
        
        return view('pages.Group.create');
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $validatedData = $request->validate([
            'group_name' => ['required', 'string','max:255', new NoXSSInput()],
            'remark' => ['required', 'string','max:255', new NoXSSInput()],
            
        ], [
            'group_name.required' => 'name wajib diisi.',
            'group_name.string' => 'name hanya boleh berupa teks.',
            'group_name.max' => 'group maksimal terdiri dari 255 karakter.',
        ]);
        try {
            DB::beginTransaction();
            $group = Groups::create([
                'group_name' => $validatedData['group_name'], 
                'remark' => $validatedData['remark'], 
            ]);
            DB::commit();
            return redirect()->route('pages.Group')->with('success', 'Group created Succesfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }
    public function update(Request $request, $hashedId)
    {
        $group = Groups::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$group) {
            return redirect()->route('pages.Group')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'group_name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'remark' => ['required', 'string', 'max:255', new NoXSSInput()],
        ], [
            'group_name.required' => 'name wajib diisi.',
            'group_name.string' => 'name hanya boleh berupa teks.',
            'group_name.max' => 'name maksimal terdiri dari 255 karakter.',
        ]);

        $groupData = [
            'group_name' => $validatedData['group_name'],
            'remark' => $validatedData['remark'],
            
        ];
        DB::beginTransaction();
        $group->update($groupData);
        DB::commit();

        return redirect()->route('pages.Group')->with('success', 'Group updated successfully.');
    }
}
