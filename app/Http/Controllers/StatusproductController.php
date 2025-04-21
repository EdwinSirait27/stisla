<?php

namespace App\Http\Controllers;
use App\Models\Statusproduct;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Rules\NoXSSInput;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StatusproductController extends Controller
{
    public function index()
    {
        return view('pages.Statusproduct.Statusproduct');
    }
    public function getStatusproducts(Request $request)
    {
        $statuses = Statusproduct::select(['id', 'status'])
        ->get()
        ->map(function ($status) {
            $status->id_hashed = substr(hash('sha256', $status->id . env('APP_KEY')), 0, 8);
            $status->action = '
                <a href="' . route('Statusproduct.edit', $status->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit Status product: ' . e($status->status) . '">
                    <i class="fas fa-user-edit text-secondary"></i>
                </a>';
            return $status;
        });
    return DataTables::of($statuses)
   
        ->rawColumns(['action'])
        ->make(true);
    }
    public function edit($hashedId)
    {
        $status = Statusproduct::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$status) {
            abort(404, 'Status Product not found.');
        }


        return view('pages.Statusproduct.edit', [
            'hashedId' => $hashedId,
            'status' => $status,
        ]);
    }

    public function create()
    {

        return view('pages.Statusproduct.create');
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'status' => [
                'required',
                'string',
                'max:255',
                'unique:statusproduct_tables,status',
                new NoXSSInput()
            ],
          

        ], [
            'statusproduct.required' => 'status product status wajib diisi.',
            'statusproduct.string' => 'status product hanya boleh berupa teks.',
            'statusproduct.max' => 'status product maksimal terdiri dari 255 karakter.',
            'statusproduct.unique' => 'status product harus unique.',
          
        ]);
        try {
            DB::beginTransaction();
            $status = Statusproduct::create([
                'status' => $validatedData['status'],
            ]);
            DB::commit();
            return redirect()->route('pages.Statusproduct')->with('success', 'status product created Succesfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }
    public function update(Request $request, $hashedId)
    {
        $statuses = Statusproduct::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$statuses) {
            return redirect()->route('pages.Statusproduct')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'status' => [
                'required',
                'string',
                'max:255',
                Rule::unique('statusproduct_tables')->ignore($statuses->id),
                new NoXSSInput()
            ],
         
        ], [
            'statusproduct.required' => 'status product status wajib diisi.',
            'statusproduct.string' => 'status product status hanya boleh berupa teks.',
            'statusproduct.max' => 'status product maksimal terdiri dari 255 karakter.',
            'statusproduct.unique' => 'status product harus unique.',
            
        ]);
        $statusesData = [
            'status' => $validatedData['status'],
            
        ];
        DB::beginTransaction();
        $statuses->update($statusesData);
        DB::commit();
        return redirect()->route('pages.Statusproduct')->with('success', 'Status product status Berhasil Diupdate.');
    }
}
