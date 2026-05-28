<?php

namespace App\Http\Controllers;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use App\Models\AssetCategories;
use Illuminate\Support\Facades\DB;

class AssetCategoriesController extends Controller
{
 public function index()
    {
        return view('pages.AssetCategories.AssetCategories');
    }
    public function getAssetCategories()
    {
        $assetcategories = AssetCategories::select(['id', 'asset_category_name','description'])
            ->get()
            ->map(function ($assetcategory) {
                $assetcategory->id = $assetcategory->id;
                $assetcategory->action = '
                    <a href="' . route('AssetCategories.edit', $assetcategory->id) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit Asset Category: ' . e($assetcategory->asset_category_name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $assetcategory;
            });
        return DataTables::of($assetcategories)
            ->rawColumns(['action'])
            ->make(true);
    }
  
    public function edit($id)
    {
        $assetcategory = AssetCategories::get()->first(function ($u) use ($id) {
            return $u->id === $id;
        });

        if (!$assetcategory) {
            abort(404, 'Asset Category not found.');
        }

        return view('pages.AssetCategories.edit', [
            'assetcategory' => $assetcategory,
            'id' => $id
        ]);
    }

    public function create()
    {
        
        return view('pages.AssetCategories.create');
    }

    public function store(Request $request)
    {
        
       $validatedData = $request->validate([
    'asset_category_name' => ['required', 'string', 'max:255', 'unique:asset_categories,asset_category_name'],
    'description' => ['nullable', 'string', 'max:255']
], [
    'asset_category_name.required' => 'Asset Category Name wajib diisi.',
    'asset_category_name.string'   => 'Asset Category Name hanya boleh berupa teks.',
    'asset_category_name.max'      => 'Asset Category Name maksimal terdiri dari 255 karakter.',
    'asset_category_name.unique'   => 'Asset Category Name sudah terdaftar.',
]);
        try {
            DB::beginTransaction();
            $assetcategory = AssetCategories::create([
                'asset_category_name' => $validatedData['asset_category_name'],
                'description' => $validatedData['description']
            ]);
            DB::commit();
            return redirect()->route('pages.AssetCategories')->with('success', 'Asset Category created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }
   public function update(Request $request, $id)
{
    $assetcategory = AssetCategories::get()->first(function ($u) use ($id) {
        return $u->id === $id;
    });
    if (!$assetcategory) {
        return redirect()->route('pages.AssetCategories')->with('error', 'ID tidak valid.');
    }

    $validatedData = $request->validate([
        'asset_category_name' => [
            'required', 
            'string', 
            'max:255', 
            \Illuminate\Validation\Rule::unique('asset_categories', 'asset_category_name')->ignore($assetcategory->id)
        ],
        'description' => ['nullable', 'string', 'max:255']
    ], [
        'asset_category_name.required' => 'Asset Category Name wajib diisi.',
        'asset_category_name.string'   => 'Asset Category Name hanya boleh berupa teks.',
        'asset_category_name.max'      => 'Asset Category Name maksimal terdiri dari 255 karakter.',
        'asset_category_name.unique'   => 'Asset Category Name sudah terdaftar.',
    ]);

    try {
        DB::beginTransaction();
        $assetcategory->update([
            'asset_category_name' => $validatedData['asset_category_name'],
            'description'         => $validatedData['description']
        ]);
        DB::commit();
        return redirect()->route('pages.AssetCategories')->with('success', 'Asset Category updated successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
            ->withInput();
    }
}
}
