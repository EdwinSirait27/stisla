<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Position;
use App\Models\PositionResponsibility;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\DB;

class PositionController extends Controller
{
    // public function index()
    // {
    //     return view('pages.Position.Position');
    // }
    // public function getPositions()
    // {
    //     $positions = Position::select(['id', 'name'])
    //         ->get()
    //         ->map(function ($position) {
    //             $position->id_hashed = substr(hash('sha256', $position->id . env('APP_KEY')), 0, 8);
    //             $position->action = '
    //                 <a href="' . route('Position.edit', $position->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit Position: ' . e($position->name) . '">
    //                     <i class="fas fa-user-edit text-secondary"></i>
    //                 </a>';
    //             return $position;
    //         });
    //     return DataTables::of($positions)
    //         ->rawColumns(['action'])
    //         ->make(true);
    // }
  
    // public function edit($hashedId)
    // {
    //     $position = Position::get()->first(function ($u) use ($hashedId) {
    //         $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
    //         return $expectedHash === $hashedId;
    //     });

    //     if (!$position) {
    //         abort(404, 'Position not found.');
    //     }

    //     $selectedName = old('name', $position->name ?? '');

        
    //     return view('pages.Position.edit', [
    //         'position' => $position,
    //         'hashedId' => $hashedId,
    //         'selectedName' => $selectedName
            
    //     ]);
    // }
 
    // public function create()
    // {
        
    //     return view('pages.Position.create');
    // }

    // public function store(Request $request)
    // {
    //     // dd($request->all());

    //     $validatedData = $request->validate([
    //         'name' => ['required', 'string','max:255', new NoXSSInput()],
            
    //     ], [
    //         'name.required' => 'name wajib diisi.',
    //         'name.string' => 'name hanya boleh berupa teks.',
    //         'name.max' => 'name maksimal terdiri dari 255 karakter.',
    //     ]);
    //     try {
    //         DB::beginTransaction();
    //         $position = Position::create([
    //             'name' => $validatedData['name'], 
    //         ]);
    //         DB::commit();
    //         return redirect()->route('pages.Position')->with('success', 'Position created Succesfully!');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()
    //             ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
    //             ->withInput();
    //     }
    // }
    // public function update(Request $request, $hashedId)
    // {
    //     $position = Position::get()->first(function ($u) use ($hashedId) {
    //         $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
    //         return $expectedHash === $hashedId;
    //     });
    //     if (!$position) {
    //         return redirect()->route('pages.Position')->with('error', 'ID tidak valid.');
    //     }
    //     $validatedData = $request->validate([
    //         'name' => ['required', 'string', 'max:255', new NoXSSInput()],

    //     ], [
    //         'name.required' => 'name wajib diisi.',
    //         'name.string' => 'name hanya boleh berupa teks.',
    //         'name.max' => 'name maksimal terdiri dari 255 karakter.',
    //     ]);

    //     $positionData = [
    //         'name' => $validatedData['name'],
            
    //     ];
    //     DB::beginTransaction();
    //     $position->update($positionData);
    //     DB::commit();
    //     return redirect()->route('pages.Position')->with('success', 'Position updated successfully.');
    // }
     public function index()
    {
        return view('pages.Position.Position');
    }

    public function getPositions()
    {
        $positions = Position::select(['id', 'name', 'role_summary'])
            ->get()
            ->map(function ($position) {
                $position->id_hashed = substr(hash('sha256', $position->id . env('APP_KEY')), 0, 8);
                $position->action = '
                    <a href="' . route('Position.edit', $position->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" title="Edit Position: ' . e($position->name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $position;
            });

        return DataTables::of($positions)
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        return view('pages.Position.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'                      => ['required', 'string', 'max:255', new NoXSSInput()],
            'role_summary'              => ['nullable', 'string', new NoXSSInput()],
            'key_respon'                => ['nullable', 'array'],
            'key_respon.*'              => ['required', 'string', 'max:255', new NoXSSInput()],
            'qualification'             => ['nullable', 'array'],
            'qualification.*'           => ['required', 'string', 'max:255', new NoXSSInput()],
        ]);

        try {
            DB::beginTransaction();

            $position = Position::create([
                'name'         => $validatedData['name'],
                'role_summary' => $validatedData['role_summary'] ?? null,
            ]);

            // Simpan key_respon
            if (!empty($validatedData['key_respon'])) {
                foreach ($validatedData['key_respon'] as $order => $description) {
                    PositionResponsibility::create([
                        'position_id' => $position->id,
                        'type'        => PositionResponsibility::TYPE_KEY_RESPON,
                        'description' => $description,
                        'order'       => $order,
                    ]);
                }
            }

            // Simpan qualification
            if (!empty($validatedData['qualification'])) {
                foreach ($validatedData['qualification'] as $order => $description) {
                    PositionResponsibility::create([
                        'position_id' => $position->id,
                        'type'        => PositionResponsibility::TYPE_QUALIFICATION,
                        'description' => $description,
                        'order'       => $order,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('pages.Position')->with('success', 'Position created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function edit($hashedId)
    {
        $position = Position::with(['responsibilities', 'qualifications'])
            ->get()
            ->first(function ($u) use ($hashedId) {
                $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
                return $expectedHash === $hashedId;
            });

        if (!$position) {
            abort(404, 'Position not found.');
        }

        return view('pages.Position.edit', [
            'position'  => $position,
            'hashedId'  => $hashedId,
        ]);
    }

    public function update(Request $request, $hashedId)
    {
        $position = Position::with(['responsibilities', 'qualifications'])
            ->get()
            ->first(function ($u) use ($hashedId) {
                $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
                return $expectedHash === $hashedId;
            });

        if (!$position) {
            return redirect()->route('pages.Position')->with('error', 'ID tidak valid.');
        }

        $validatedData = $request->validate([
            'name'            => ['required', 'string', 'max:255', new NoXSSInput()],
            'role_summary'    => ['nullable', 'string', new NoXSSInput()],
            'key_respon'      => ['nullable', 'array'],
            'key_respon.*'    => ['required', 'string', 'max:255', new NoXSSInput()],
            'qualification'   => ['nullable', 'array'],
            'qualification.*' => ['required', 'string', 'max:255', new NoXSSInput()],
        ]);

        try {
            DB::beginTransaction();

            $position->update([
                'name'         => $validatedData['name'],
                'role_summary' => $validatedData['role_summary'] ?? null,
            ]);

            // Delete existing lalu insert ulang (simplest approach)
            PositionResponsibility::where('position_id', $position->id)->delete();

            if (!empty($validatedData['key_respon'])) {
                foreach ($validatedData['key_respon'] as $order => $description) {
                    PositionResponsibility::create([
                        'position_id' => $position->id,
                        'type'        => PositionResponsibility::TYPE_KEY_RESPON,
                        'description' => $description,
                        'order'       => $order,
                    ]);
                }
            }

            if (!empty($validatedData['qualification'])) {
                foreach ($validatedData['qualification'] as $order => $description) {
                    PositionResponsibility::create([
                        'position_id' => $position->id,
                        'type'        => PositionResponsibility::TYPE_QUALIFICATION,
                        'description' => $description,
                        'order'       => $order,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('pages.Position')->with('success', 'Position updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
