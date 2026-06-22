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
   
     public function index()
    {
           $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePositions')) {
            abort(403, 'Unauthorized');
        }
        return view('pages.Position.Position');
    }

    public function getPositions()
    {
           $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePositions')) {
            abort(403, 'Unauthorized');
        }
        $positions = Position::select(['id', 'name', 'role_summary','publish_career'])
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
           $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePositions')) {
            abort(403, 'Unauthorized');
        }
        return view('pages.Position.create');
    }

    // public function store(Request $request)
    // {
    //        $user     = auth()->user();
    //     /** @var \App\Models\User|null $user */
    //     if (!$user->hasPermissionTo('ManagePositions')) {
    //         abort(403, 'Unauthorized');
    //     }
    //     $validatedData = $request->validate([
    //         'name'                      => ['required', 'string', 'max:255', new NoXSSInput()],
    //         'role_summary'              => ['nullable', 'string', new NoXSSInput()],
    //         'vacancy'              => ['nullable', 'numeric'],
    //         'publish_career'              => ['nullable', 'boolean'],
    //         'career_start_date'              => ['nullable', 'date_format:Y-m-d'],
    //         'career_end_date'              => ['nullable', 'date_format:Y-m-d'],
    //         'key_respon'                => ['nullable', 'array'],
    //         'key_respon.*'              => ['required', 'string', 'max:255'],
    //         'qualification'             => ['nullable', 'array'],
    //         'qualification.*'           => ['required', 'string', 'max:255'],
    //         'benefit'             => ['nullable', 'array'],
    //         'benefit.*'           => ['required', 'string', 'max:255'],
    //         'requirement'             => ['nullable', 'array'],
    //         'requirement.*'           => ['required', 'string', 'max:255'],
    //         'skill'             => ['nullable', 'array'],
    //         'skill.*'           => ['required', 'string', 'max:255'],
    //         'allowance'             => ['nullable', 'array'],
    //         'allowance.*'           => ['required', 'string', 'max:255'],
    //     ]);

    //     try {
    //         DB::beginTransaction();

    //         $position = Position::create([
    //             'name'         => $validatedData['name'],
    //             'role_summary' => $validatedData['role_summary'] ?? null,
    //             'vacancy' => $validatedData['vacancy'] ?? null,
    //             'publish_career' => $validatedData['publish_career'] ?? false,
    //             'career_start_date' => $validatedData['career_start_date'] ?? null,
    //             'career_end_date' => $validatedData['career_end_date'] ?? null,
    //         ]);

    //         // Simpan key_respon
    //         if (!empty($validatedData['key_respon'])) {
    //             foreach ($validatedData['key_respon'] as $order => $description) {
    //                 PositionResponsibility::create([
    //                     'position_id' => $position->id,
    //                     'type'        => PositionResponsibility::TYPE_KEY_RESPON,
    //                     'description' => $description,
    //                     'order'       => $order,
    //                 ]);
    //             }
    //         }

    //         // Simpan qualification
    //         if (!empty($validatedData['qualification'])) {
    //             foreach ($validatedData['qualification'] as $order => $description) {
    //                 PositionResponsibility::create([
    //                     'position_id' => $position->id,
    //                     'type'        => PositionResponsibility::TYPE_QUALIFICATION,
    //                     'description' => $description,
    //                     'order'       => $order,
    //                 ]);
    //             }
    //         }
    //          if (!empty($validatedData['benefit'])) {
    //             foreach ($validatedData['benefit'] as $order => $description) {
    //                 PositionResponsibility::create([
    //                     'position_id' => $position->id,
    //                     'type'        => PositionResponsibility::TYPE_BENEFIT,
    //                     'description' => $description,
    //                     'order'       => $order,
    //                 ]);
    //             }
    //         }
    //          if (!empty($validatedData['requirement'])) {
    //             foreach ($validatedData['requirement'] as $order => $description) {
    //                 PositionResponsibility::create([
    //                     'position_id' => $position->id,
    //                     'type'        => PositionResponsibility::TYPE_REQUIREMENT,
    //                     'description' => $description,
    //                     'order'       => $order,
    //                 ]);
    //             }
    //         }
    //          if (!empty($validatedData['skill'])) {
    //             foreach ($validatedData['skill'] as $order => $description) {
    //                 PositionResponsibility::create([
    //                     'position_id' => $position->id,
    //                     'type'        => PositionResponsibility::TYPE_SKILL,
    //                     'description' => $description,
    //                     'order'       => $order,
    //                 ]);
    //             }
    //         }
    //          if (!empty($validatedData['allowance'])) {
    //             foreach ($validatedData['allowance'] as $order => $description) {
    //                 PositionResponsibility::create([
    //                     'position_id' => $position->id,
    //                     'type'        => PositionResponsibility::TYPE_ALLOWANCE,
    //                     'description' => $description,
    //                     'order'       => $order,
    //                 ]);
    //             }
    //         }

    //         DB::commit();
    //         return redirect()->route('pages.Position')->with('success', 'Position created successfully!');

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()
    //             ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
    //             ->withInput();
    //     }
    // }
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
           $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePositions')) {
            abort(403, 'Unauthorized');
        }
        $position = Position::with(['responsibilities', 'qualifications','benefits','requirements','skills','allowances'])
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
           $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePositions')) {
            abort(403, 'Unauthorized');
        }
        $position = Position::with(['responsibilities', 'qualifications','benefits','requirements','skills','allowances'])
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
             'vacancy'              => ['nullable', 'numeric'],
            'publish_career'              => ['nullable', 'boolean'],
            'career_start_date'              => ['nullable', 'date_format:Y-m-d'],
            'career_end_date'              => ['nullable', 'date_format:Y-m-d'],

            'key_respon'      => ['nullable', 'array'],
            'key_respon.*'    => ['required', 'string', 'max:255', new NoXSSInput()],
            'qualification'   => ['nullable', 'array'],
            'qualification.*' => ['required', 'string', 'max:255', new NoXSSInput()],
             'benefit'             => ['nullable', 'array'],
            'benefit.*'           => ['required', 'string', 'max:255'],
            'requirement'             => ['nullable', 'array'],
            'requirement.*'           => ['required', 'string', 'max:255'],
            'skill'             => ['nullable', 'array'],
            'skill.*'           => ['required', 'string', 'max:255'],
            'allowance'             => ['nullable', 'array'],
            'allowance.*'           => ['required', 'string', 'max:255'],
        ]);

        try {
            DB::beginTransaction();

            $position->update([
                'name'         => $validatedData['name'],
                'role_summary' => $validatedData['role_summary'] ?? null,
                'vacancy' => $validatedData['vacancy'] ?? null,
                'publish_career' => $validatedData['publish_career'] ?? false,
                'career_start_date' => $validatedData['career_start_date'] ?? null,
                'career_end_date' => $validatedData['career_end_date'] ?? null,
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
            if (!empty($validatedData['benefit'])) {
                foreach ($validatedData['benefit'] as $order => $description) {
                    PositionResponsibility::create([
                        'position_id' => $position->id,
                        'type'        => PositionResponsibility::TYPE_BENEFIT,
                        'description' => $description,
                        'order'       => $order,
                    ]);
                }
            }
             if (!empty($validatedData['requirement'])) {
                foreach ($validatedData['requirement'] as $order => $description) {
                    PositionResponsibility::create([
                        'position_id' => $position->id,
                        'type'        => PositionResponsibility::TYPE_REQUIREMENT,
                        'description' => $description,
                        'order'       => $order,
                    ]);
                }
            }
             if (!empty($validatedData['skill'])) {
                foreach ($validatedData['skill'] as $order => $description) {
                    PositionResponsibility::create([
                        'position_id' => $position->id,
                        'type'        => PositionResponsibility::TYPE_SKILL,
                        'description' => $description,
                        'order'       => $order,
                    ]);
                }
            }
             if (!empty($validatedData['allowance'])) {
                foreach ($validatedData['allowance'] as $order => $description) {
                    PositionResponsibility::create([
                        'position_id' => $position->id,
                        'type'        => PositionResponsibility::TYPE_ALLOWANCE,
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
