<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Sktype;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SKController extends Controller
{
    public function sktype()
    {
        return view('pages.Sktype.Sktype');
    }
    public function getSktypes()
    {
        $sktypes = Sktype::select(['id', 'sk_name', 'nickname', 'categories', 'generates_contract','affects_salary', 'affects_position', 'affects_status'])
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
        $categories = Sktype::getCategoriesOptions();

        return view('pages.Sktype.edit', [
            'sktype' => $sktype,
            'hashedId' => $hashedId,
            'categories' => $categories,
            'selectedName' => $selectedName

        ]);
    }

    public function create()
    {
        $categories = Sktype::getCategoriesOptions();
        return view('pages.Sktype.create', compact('categories'));
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'sk_name' => ['required', 'string', 'max:255'],
            'nickname' => ['required', 'string', 'max:255'],
            'categories' => ['required', Rule::in(Sktype::getCategoriesOptions())],
            'generates_contract'   => ['required', 'boolean'],
            'affects_salary'   => ['required', 'boolean'],
            'affects_position' => ['required', 'boolean'],
            'affects_status'   => ['required', 'boolean'],

        ]);

        try {
            DB::beginTransaction();

            $sktype = Sktype::create([
                'sk_name' => strtoupper($validatedData['sk_name']),
                'nickname' => strtoupper($validatedData['nickname']),
                'categories' => $validatedData['categories'],
                'generates_contract' => $validatedData['generates_contract'],
                'affects_salary' => $validatedData['affects_salary'],
                'affects_position' => $validatedData['affects_position'],
                'affects_status' => $validatedData['affects_status'],
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
            'nickname' => ['required', 'string', 'max:255'],
            'generates_contract'   => ['required', 'boolean'],
            'affects_salary'   => ['required', 'boolean'],
            'affects_position' => ['required', 'boolean'],
            'affects_status'   => ['required', 'boolean'],
            'categories' => ['required', Rule::in(Sktype::getCategoriesOptions())],
        ]);
        $sktypeData = [
            'sk_name' => strtoupper($validatedData['sk_name']),
            'nickname' => strtoupper($validatedData['nickname']),
             'categories' => $validatedData['categories'],
                'generates_contract' => $validatedData['generates_contract'],
                'affects_salary' => $validatedData['affects_salary'],
                'affects_position' => $validatedData['affects_position'],
                'affects_status' => $validatedData['affects_status'],
        ];
        DB::beginTransaction();
        $sktype->update($sktypeData);
        DB::commit();
        return redirect()->route('pages.Sktype')->with('success', 'SK Type updated successfully.');
    }
}
