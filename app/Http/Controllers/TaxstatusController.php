<?php

namespace App\Http\Controllers;


use App\Models\Taxstatus;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Rules\NoXSSInput;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\DB;
class TaxstatusController extends Controller
{
    public function index()
    {
        return view('pages.Taxstatus.Taxstatus');
    }
    public function getTaxstatuses(Request $request)
    {
        $taxs = Taxstatus::select(['id', 'taxstatus'])
        ->get()
        ->map(function ($tax) {
            $tax->id_hashed = substr(hash('sha256', $tax->id . env('APP_KEY')), 0, 8);
            $tax->action = '
                <a href="' . route('Taxstatus.edit', $tax->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit Taxstatus: ' . e($tax->taxstatus) . '">
                    <i class="fas fa-user-edit text-secondary"></i>
                </a>';
            return $tax;
        });
    return DataTables::of($taxs)
   
        ->rawColumns(['action'])
        ->make(true);
    }
    public function edit($hashedId)
    {
        $tax = Taxstatus::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$tax) {
            abort(404, 'Taxstatus not found.');
        }


        return view('pages.Taxstatus.edit', [
            'hashedId' => $hashedId,
            'tax' => $tax,
        ]);
    }

    public function create()
    {

        return view('pages.Taxstatus.create');
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'taxstatus' => [
                'required',
                'string',
                'max:255',
                'unique:taxstatus_tables,taxstatus',
                new NoXSSInput()
            ],
          

        ], [
            'taxstatus.required' => 'tax status wajib diisi.',
            'taxstatus.string' => 'tax status hanya boleh berupa teks.',
            'taxstatus.max' => 'tax status maksimal terdiri dari 255 karakter.',
            'taxstatus.unique' => 'tax status harus unique.',
          
        ]);
        try {
            DB::beginTransaction();
            $tax = Taxstatus::create([
                'taxstatus' => $validatedData['taxstatus'],
            ]);
            DB::commit();
            return redirect()->route('pages.Taxstatus')->with('success', 'Taxstatus created Succesfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }
    public function update(Request $request, $hashedId)
    {
        $taxs = Taxstatus::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$taxs) {
            return redirect()->route('pages.Taxstatus')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'taxstatus' => [
                'required',
                'string',
                'max:255',
                Rule::unique('taxstatus_tables')->ignore($taxs->id),
                new NoXSSInput()
            ],
         
        ], [
            'taxstatus.required' => 'tax status wajib diisi.',
            'taxstatus.string' => 'tax status hanya boleh berupa teks.',
            'taxstatus.max' => 'tax status maksimal terdiri dari 255 karakter.',
            'taxstatus.unique' => 'tax status harus unique.',
            
        ]);
        $taxsData = [
            'taxstatus' => $validatedData['taxstatus'],
            
        ];
        DB::beginTransaction();
        $taxs->update($taxsData);
        DB::commit();
        return redirect()->route('pages.Taxstatus')->with('success', 'Tax status Berhasil Diupdate.');
    }
}
