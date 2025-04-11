<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\DB;


class StoreController extends Controller
{
    public function index()
    {
        return view('pages.Store.Store');
    }
    public function getStore()
    {
        $stores = Stores::select(['id', 'name'])
            ->get()
            ->map(function ($store) {
                $store->id_hashed = substr(hash('sha256', $store->id . env('APP_KEY')), 0, 8);
                $store->action = '
                    <a href="' . route('Store.edit', $store->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit Store: ' . e($store->name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $store;
            });
        return DataTables::of($stores)
            ->rawColumns(['action'])
            ->make(true);
    }
    public function edit($hashedId)
    {
        $store = Stores::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$store) {
            abort(404, 'Store not found.');
        }

        $selectedName = old('name', $stores->name ?? '');

        // Dapatkan role pertama user (untuk selected value)
        
        return view('pages.Store.edit', [
            'store' => $store,
            'hashedId' => $hashedId,
            'selectedName' => $selectedName
            
        ]);
    }
 
    public function create()
    {
        
        return view('pages.Store.create');
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
            $store = Stores::create([
                'name' => $validatedData['name'],
                
            ]);
            DB::commit();
            return redirect()->route('pages.Store')->with('success', 'Store created Succesfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }
    public function update(Request $request, $hashedId)
    {
        $store = Stores::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$store) {
            return redirect()->route('pages.Store')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', new NoXSSInput()],

        ], [
            'name.required' => 'name wajib diisi.',
            'name.string' => 'name hanya boleh berupa teks.',
            'name.max' => 'Username maksimal terdiri dari 255 karakter.',
        ]);

        $storeData = [
            'name' => $validatedData['name'],
            
        ];
        DB::beginTransaction();
        $store->update($storeData);
        DB::commit();

        return redirect()->route('pages.Store')->with('success', 'Store Berhasil Diupdate.');
    }
}


