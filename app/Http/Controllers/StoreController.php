<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Rules\NoXSSInput;
use App\Models\User;
use App\Models\Stores;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class StoreController extends Controller
{
    public function index()
    {
        return view('pages.Store.Store');
    }
    public function getStores()
    {
        $stores = Stores::with('user.Employee')->select(['id', 'name','address','phone_num','manager_id'])
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
        ->addColumn('employee_name', function ($store) {
            return !empty($store->user->Employee) && !empty($store->user->Employee->employee_name)
                ? $store->user->Employee->employee_name
                : 'Empty';
        })
            ->rawColumns(['action','employee_name'])
            ->make(true);
    }
    public function edit($hashedId)
    {
        $store = Stores::with('user.Employee')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$store) {
            abort(404, 'Store not found.');
        }

        $managers = User::with('Employee')->get();
        return view('pages.Store.edit', [
            'store' => $store,
            'hashedId' => $hashedId,
            'managers' => $managers,
        ]);
    }
 
    public function create()
    {
        $managers = User::with('Employee')->get();
        
        return view('pages.Store.create',compact('managers'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $validatedData = $request->validate([
            'name' => ['required', 'string','max:255', 'unique:stores_tables,name',
                new NoXSSInput()],
            'manager_id' => ['required','max:255', 'unique:stores_tables,manager_id',
                new NoXSSInput()],
            'address' => ['required','max:255', 
                new NoXSSInput()],
            'phone_num' => ['required','max:255',
                new NoXSSInput()],
            
        ], [
            'name.required' => 'name wajib diisi.',
            'name.string' => 'name hanya boleh berupa teks.',
            'name.max' => 'Username maksimal terdiri dari 255 karakter.',
            'manager_id.required' => 'manager wajib diisi.',
            'address.required' => 'address wajib diisi.',
            'phone_num.required' => 'telephone number wajib diisi.',
            'manager_id.unique' => 'Sudah ada manager yang tersimpan.',
        ]);
        try {
            DB::beginTransaction();
            $store = Stores::create([
                'name' => $validatedData['name'], 
                'manager_id' => $validatedData['manager_id'], 
                'address' => $validatedData['address'], 
                'phone_num' => $validatedData['phone_num'],  
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
        $store = Stores::with('user.Employee')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$store) {
            return redirect()->route('pages.Store')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255',Rule::unique('stores_tables')->ignore($store->id),
            new NoXSSInput()],
            'manager_id' => ['required', 'string', 'max:255', Rule::unique('stores_tables')->ignore($store->id),
            new NoXSSInput()],

        'address' => ['required','max:255', 
            new NoXSSInput()],
        'phone_num' => ['required','max:255',
            new NoXSSInput()],
        
    ], [
        'name.required' => 'name wajib diisi.',
        'name.string' => 'name hanya boleh berupa teks.',
        'name.max' => 'Username maksimal terdiri dari 255 karakter.',
        'manager_id.required' => 'manager wajib diisi.',
        'address.required' => 'address wajib diisi.',
        'phone_num.required' => 'telephone number wajib diisi.',
        'manager_id.unique' => 'Sudah ada manager yang tersimpan.',
    ]);

        $storeData = [
            'name' => $validatedData['name'],
            'address' => $validatedData['address'],
            'phone_num' => $validatedData['phone_num'],
            'manager_id' => $validatedData['manager_id'],
            
        ];
        DB::beginTransaction();
        $store->update($storeData);
        DB::commit();

        return redirect()->route('pages.Store')->with('success', 'Store updated Successfully.');
    }
}


