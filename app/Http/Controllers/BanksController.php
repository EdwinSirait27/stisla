<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Rules\NoXSSInput;
use App\Models\Banks;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BanksController extends Controller
{
    public function index()
    {
        return view('pages.Banks.Banks');
    }
    public function getBanks()
    {
        $banks = Banks::select(['id', 'name'])
            ->get()
            ->map(function ($bank) {
                $bank->id_hashed = substr(hash('sha256', $bank->id . env('APP_KEY')), 0, 8);
                $bank->action = '
                    <a href="' . route('Banks.edit', $bank->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit bank"title="Edit Banks: ' . e($bank->name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $bank;
            });
        return DataTables::of($banks)
        
            ->rawColumns(['action'])
            ->make(true);
    }
    public function edit($hashedId)
    {
        $bank = Banks::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$bank) {
            abort(404, 'Banks not found.');
        }

        return view('pages.Banks.edit', [
            'bank' => $bank,
            'hashedId' => $hashedId,
            
        ]);
    }
 
    public function create()
    {
        
        return view('pages.Banks.create');
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $validatedData = $request->validate([
            'name' => ['required', 'string','max:255', 'unique:banks_tables,name',
                new NoXSSInput()],
            
        ], [
            'name.required' => 'name wajib diisi.',
            'name.string' => 'name hanya boleh berupa teks.',
            'name.unique' => 'name harus unique .',
            
        ]);
        try {
            DB::beginTransaction();
            $bank = Banks::create([
                'name' => $validatedData['name'], 
                  
            ]);
            DB::commit();
            return redirect()->route('pages.Banks')->with('success', 'Banks created Succesfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }
    public function update(Request $request, $hashedId)
    {
        $bank = Banks::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$bank) {
            return redirect()->route('pages.Banks')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255',Rule::unique('banks_tables')->ignore($bank->id),
            new NoXSSInput()],
            
    ], [
        'name.required' => 'name wajib diisi.',
        'name.string' => 'name hanya boleh berupa teks.',
        'name.unique' => 'name sudah dipakai pakai yang unik bozzz.',
        
    ]);

        $bankData = [
            'name' => $validatedData['name'],
            
        ];
        DB::beginTransaction();
        $bank->update($bankData);
        DB::commit();

        return redirect()->route('pages.Banks')->with('success', 'Banks updated Successfully.');
    }
}
