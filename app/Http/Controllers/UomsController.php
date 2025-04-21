<?php

namespace App\Http\Controllers;

use App\Models\Uoms;
use Illuminate\Http\Request;

use Yajra\DataTables\DataTables;
use App\Models\Departments;
use App\Rules\NoXSSInput;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\DB;

class UomsController extends Controller
{
    public function index()
    {
        return view('pages.Uoms.Uoms');
    }
    // public function getUoms()
    // {
    //     $uoms = Uoms::select(['id', 'uom_code','uom','conversion_factor'])
    //         ->get()
    //         ->map(function ($uom) {
    //             $uom->id_hashed = substr(hash('sha256', $uom->id . env('APP_KEY')), 0, 8);
    //             $uom->action = '
    //                 <a href="' . route('Uoms.edit', $uom->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit Department: ' . e($uom->department_name) . '">
    //                     <i class="fas fa-user-edit text-secondary"></i>
    //                 </a>';
    //             return $uom;
    //         });
    //     return DataTables::of($uoms)
        
    //         ->rawColumns(['action'])
    //         ->make(true);
    // }
    public function getUoms(Request $request)
    {
        $query = Uoms::select(['id','uom_code', 'uom','conversion_factor']);

        if ($request->has('uom') && in_array($request->uom, ['Piece','Dozen','Pack','Box','Kg','Gram','Liter','MLiter','Meter','MMeter'])) {
            $query->where('uom', $request->uom);
        }
        $uom = $query->get()
            ->map(function ($uom) {
                $uom->id_hashed = substr(hash('sha256', $uom->id . env('APP_KEY')), 0, 8);
                $uom->action = '
            <a href="' . route('Uoms.edit', $uom->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit Uoms">
                <i class="fas fa-user-edit text-secondary"></i>
            </a>';
                return $uom;
            });
        return DataTables::of($uom)
        ->rawColumns(['action'])

            ->make(true);
    }
    public function edit($hashedId)
    {
        $uom = Uoms::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$uom) {
            abort(404, 'Uom not found.');
        }
    $uoms = ['Piece','Dozen','Pack','Box','Kg','Gram','Liter','MLiter','Meter','MMeter'];


        return view('pages.Uoms.edit', [
            'hashedId' => $hashedId,
            'uoms' => $uoms,
            'uom' => $uom,
        ]);
    }
 
    public function create()
    {
    $uoms = ['Piece','Dozen','Pack','Box','Kg','Gram','Liter','MLiter','Meter','MMeter'];

        return view('pages.Uoms.create',compact('uoms'));
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'uom_code' => ['required', 'string','max:255', 'unique:uoms_tables,department_name',
                new NoXSSInput()],
            'uom' => ['required', 'string','max:255', 'unique:uoms_tables,department_name',
                new NoXSSInput()],
            
            'conversion_factor' => ['required',
                new NoXSSInput()],
            
        ], [
            'uom_code.required' => 'uom_code wajib diisi.',
            'uom_code.string' => 'uom_code hanya boleh berupa teks.',
            'uom_code.max' => 'uom_code maksimal terdiri dari 255 karakter.',
            'uom_code.unique' => 'uom_code harus unique.',
            'uom.required' => 'uom_code wajib diisi.',
            'uom.string' => 'uom_code hanya boleh berupa teks.',
            'uom.max' => 'uom_code maksimal terdiri dari 255 karakter.',
            'uom.unique' => 'code harus unique.',
            'conversion_factor.required' => 'conversion_factor harus terisi.',
        ]);
        try {
            DB::beginTransaction();
            $uom = Uoms::create([
                'uom_code' => $validatedData['uom_code'], 
                'uom' => $validatedData['uom'], 
                'conversion_factor' => $validatedData['conversion_factor'], 
            ]);
            DB::commit();
            return redirect()->route('pages.Uoms')->with('success', 'Uoms created Succesfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }
    public function update(Request $request, $hashedId)
    {
        $uoms = Uoms::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$uoms) {
            return redirect()->route('pages.Uoms')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'uom_code' => ['required', 'string', 'max:255',Rule::unique('uoms_tables')->ignore($uoms->id),
            new NoXSSInput()],
            'uom' => ['required', 'string', 'max:255', Rule::unique('uoms_tables')->ignore($uoms->id),
            new NoXSSInput()],
            'conversion_factor' => ['required',
            new NoXSSInput()],

        ], [
            'uom_code.required' => 'uom_code wajib diisi.',
            'uom_code.string' => 'uom_code hanya boleh berupa teks.',
            'uom_code.max' => 'uom_code maksimal terdiri dari 255 karakter.',
            'uom_code.unique' => 'uom_code harus unique.',
            'uom.required' => 'uom_code wajib diisi.',
            'uom.string' => 'uom_code hanya boleh berupa teks.',
            'uom.max' => 'uom_code maksimal terdiri dari 255 karakter.',
            'uom.unique' => 'code harus unique.',
            'conversion_factor.required' => 'conversion_factor harus terisi.',
        ]);

        $uomsData = [
            'uom_code' => $validatedData['uom_code'],
            'uom' => $validatedData['uom'],
            'conversion_factor' => $validatedData['conversion_factor'],
            
        ];
        DB::beginTransaction();
        $uoms->update($uomsData);
        DB::commit();

        return redirect()->route('pages.Uoms')->with('success', 'Uoms Berhasil Diupdate.');
    }
}
