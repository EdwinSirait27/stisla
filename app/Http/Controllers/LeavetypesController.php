<?php

namespace App\Http\Controllers;

use App\Models\Leaves;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Leavetypes;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\DB;

class LeavetypesController extends Controller
{
    public function index()
    {
        return view('pages.Leavestype.Leavestype');
    }
    public function getLeavestypes()
    {
        $leavestypes = Leavetypes::select(['id', 'name','is_paid','default_balance'])
            ->get()
            ->map(function ($type) {
                $type->id_hashed = substr(hash('sha256', $type->id . env('APP_KEY')), 0, 8);
                $type->action = '
                    <a href="' . route('Leavestype.edit', $type->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit Type: ' . e($type->name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $type;
            });
        return DataTables::of($leavestypes)
            ->rawColumns(['action'])
            ->make(true);
    }

    public function edit($hashedId)
    {
        $type = Leavetypes::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$type) {
            abort(404, 'Leave Type not found.');
        }

        
        
        return view('pages.Leavestype.edit', [
            'type' => $type,
            'hashedId' => $hashedId
        ]);
    }
 
    public function create()
    {
        
        return view('pages.Leavestype.create');
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $validatedData = $request->validate([
            'name' => ['required', 'string','max:255', 'unique:leave_types_tables,name', new NoXSSInput()],
            'is_paid' => ['nullable','max:255', new NoXSSInput()],
            'default_balance' => ['nullable'],
            
        ], [
            'name.required' => 'name wajib diisi.',
            'name.string' => 'name hanya boleh berupa teks.',
            'name.max' => 'Username maksimal terdiri dari 255 karakter.',
        ]);
        try {
            DB::beginTransaction();
            $type = Leavetypes::create([
                'name' => $validatedData['name'], 
                'is_paid' => $validatedData['is_paid'] ?? 0, 
                'default_balance' => $validatedData['default_balance'] ?? null, 
            ]);
            DB::commit();
            return redirect()->route('pages.Leavestype')->with('success', 'Leavestype created Succesfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }
    public function update(Request $request, $hashedId)
    {
        $type = Leavetypes::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$type) {
            return redirect()->route('pages.Leavestype')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', new NoXSSInput()],
            'is_paid' => ['required', 'string', 'max:255', new NoXSSInput()],
            'default_balance' => ['required', 'string', 'max:255', new NoXSSInput()],

        ], [
            'name.required' => 'name wajib diisi.',
            'name.string' => 'name hanya boleh berupa teks.',
            'name.max' => 'Username maksimal terdiri dari 255 karakter.',
        ]);

        $typeData = [
            'name' => $validatedData['name'],
            'is_paid' => $validatedData['is_paid'],
            'default_balance' => $validatedData['default_balance'],
            
        ];
        DB::beginTransaction();
        $type->update($typeData);
        DB::commit();

        return redirect()->route('pages.Leavetypes')->with('success', 'Leavetypes updated successfully.');
    }
}
