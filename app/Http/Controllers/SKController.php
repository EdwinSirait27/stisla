<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Sktype;
use Illuminate\Support\Facades\DB;
class SKController extends Controller
{
    public function sktype(){
        return view ('pages.Sktype.Sktype');
    }
     public function getSktypes()
    {
        $sktypes = Sktype::select(['id', 'sk_name'])
            ->get()
            ->map(function ($sktype) {
                $sktype->id_hashed = substr(hash('sha256', $sktype->id . env('APP_KEY')), 0, 8);
                $sktype->action = '
                    <a href="' . route('Sktype.edit', $sktype->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit SK Type"title="Edit SK Type: ' . e($sktype->name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $sktype;
            });
        return DataTables::of($sktypes)
            ->rawColumns(['action'])
            ->make(true);
    }
    public function edit($hashedId)
    {
        $sktype = Sktype::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$sktype) {
            abort(404, 'SK Type not found.');
        }

        $selectedName = old('sk_name', $sktype->sk_name ?? '');

        
        return view('pages.Sktype.edit', [
            'sktype' => $sktype,
            'hashedId' => $hashedId,
            'selectedName' => $selectedName
            
        ]);
    }
 
    public function create()
    {
        
        return view('pages.Sktype.create');
    }

    public function store(Request $request)
{
    $validatedData = $request->validate([
        'sk_name' => ['required', 'string', 'max:255'],
    ]);

    try {
        DB::beginTransaction();

        $sktype = Sktype::create([
            'sk_name' => strtoupper($validatedData['sk_name']),
        ]);

        DB::commit();

        return redirect()->route('pages.Sktype')
            ->with('success', 'SK Type created successfully!');
    } catch (\Exception $e) {
        DB::rollBack();

        return redirect()->back()
            ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
            ->withInput();
    }
}
    public function update(Request $request, $hashedId)
    {
        $sktype = Sktype::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$sktype) {
            return redirect()->route('pages.Sktype')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'sk_name' => ['required', 'string', 'max:255'],

        ]);

        $sktypeData = [
             'sk_name' => strtoupper($validatedData['sk_name']),  
        ];
        DB::beginTransaction();
        $sktype->update($sktypeData);
        DB::commit();
        return redirect()->route('pages.Sktype')->with('success', 'SK Type updated successfully.');
    }
}
