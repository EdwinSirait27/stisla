<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;
use Yajra\DataTables\DataTables;
use App\Rules\NoXSSInput;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
class CategoriesController extends Controller
{
// public function index()
// {
//     $categories = Categories::with(['children' => function($query) {
//         $query->select(['id', 'parent_id', 'category_name']);
//     }])->whereNull('parent_id')->get();

//     return view('pages.Categories.Categories', [
//         'categories' => $categories 
//     ]);
// }
// private function flattenCategories($categories, $prefix = '')
// {
//     $result = [];

//     foreach ($categories as $category) {
//         $item = [
//             'id' => $category->id,
//             'category_name' => $category->category_name,
//             'indent_text' => $prefix,
//             'children' => $category->children 
//         ];

//         $result[] = (object)$item;

//         if ($category->children->count()) {
//             $result = array_merge(
//                 $result, 
//                 $this->flattenCategories($category->children, $prefix . '— ')
//             );
//         }
//     }

//     return $result;
// }

// public function getCategories(Request $request)
// {
//     $query = Categories::with(['parent'])
//         ->select(['id', 'parent_id', 'category_code', 'category_name']);

//     if ($request->filled('parent_id')) {
//         $parent = Categories::with('childrenRecursive')->find($request->parent_id);
        
//         if (!$parent) {
//             return DataTables::of(collect())->make(true);
//         }

//         $ids = $this->getAllCategoryIdsOptimized($parent);
//         $query->whereIn('id', $ids);
//     }

//     if ($request->filled('category_name')) {
//         $query->where('category_name', 'like', '%'.$request->category_name.'%');
//     }

//     $categories = $query->get()
//         ->map(function ($category) {
//             return [
//                 'id' => $category->id,
//                 'id_hashed' => substr(hash('sha256', $category->id . config('app.key')), 0, 8),
//                 'category_code' => $category->category_code,
//                 'category_name' => $category->category_name,
//                 'full_category_name' => $category->full_category_name ?? $category->category_name,
//                 'parent_name' => optional($category->parent)->category_name,
//                 'children_count' => $category->children()->count(),
//                 'action' => '
//                     <a href="'.route('Categories.edit', $category->id).'" class="mx-3" data-bs-toggle="tooltip" title="Edit category">
//                         <i class="fas fa-edit text-secondary"></i>
//                     </a>'
//             ];
//         });

//     return DataTables::of($categories)
//         ->addIndexColumn()
//         ->rawColumns(['action'])
//         ->make(true);
// }

// private function getAllCategoryIdsOptimized($category, $depth = 0, $maxDepth = 10)
// {
//     if ($depth > $maxDepth) {
//         return [$category->id];
//     }
    
//     $ids = [$category->id];
    
//     if ($category->relationLoaded('childrenRecursive') && $category->childrenRecursive->isNotEmpty()) {
//         foreach ($category->childrenRecursive as $child) {
//             $ids = array_merge($ids, $this->getAllCategoryIdsOptimized($child, $depth + 1, $maxDepth));
//         }
//     }
    
//     return $ids;
// }
public function index()
{
    $categories = Categories::with(['children' => function($query) {
        $query->select(['id', 'parent_id', 'category_name']);
    }])
    ->whereNull('parent_id')
    ->get();

    return view('pages.Categories.Categories', [
        'categories' => $categories
    ]);
}

private function flattenCategories($categories, $prefix = '')
{
    $result = [];

    foreach ($categories as $category) {
        $item = (object)[
            'id' => $category->id,
            'category_name' => $category->category_name,
            'indent_text' => $prefix,
            'children' => $category->relationLoaded('children') ? $category->children : collect(),
        ];

        $result[] = $item;

        if ($item->children->isNotEmpty()) {
            $result = array_merge(
                $result,
                $this->flattenCategories($item->children, $prefix . '— ')
            );
        }
    }

    return $result;
}

public function getCategories(Request $request)
{
    $query = Categories::with(['parent'])
        ->select(['id', 'parent_id', 'category_code', 'category_name']);

    // Filter by parent ID (dengan anak-anaknya secara rekursif)
    if ($request->filled('parent_id')) {
        $parent = Categories::with('childrenRecursive')->find($request->parent_id);

        if (!$parent) {
            return DataTables::of(collect())->make(true); // return kosong jika parent tidak ditemukan
        }

        $ids = $this->getAllCategoryIdsOptimized($parent);
        $query->whereIn('id', $ids);
    }

    // Filter berdasarkan nama kategori
    if ($request->filled('category_name')) {
        $query->where('category_name', 'like', '%'.$request->category_name.'%');
    }

    $categories = $query->get()
        ->map(function ($category) {
            return [
                'id' => $category->id,
                'id_hashed' => substr(hash('sha256', $category->id . config('app.key')), 0, 8),
                'category_code' => $category->category_code,
                'category_name' => $category->category_name,
                'full_category_name' => $category->full_category_name ?? $category->category_name,
                'parent_name' => optional($category->parent)->category_name,
                'children_count' => $category->children()->count(),
                'action' => '
                    <a href="'.route('Categories.edit', $category->id).'" class="mx-3" data-bs-toggle="tooltip" title="Edit category">
                        <i class="fas fa-edit text-secondary"></i>
                    </a>'
            ];
        });

    return DataTables::of($categories)
        ->addIndexColumn()
        ->rawColumns(['action'])
        ->make(true);
}

private function getAllCategoryIdsOptimized($category, $depth = 0, $maxDepth = 10)
{
    if ($depth > $maxDepth) {
        return [$category->id]; // Hindari infinite loop
    }

    $ids = [$category->id];

    if ($category->relationLoaded('childrenRecursive') && $category->childrenRecursive->isNotEmpty()) {
        foreach ($category->childrenRecursive as $child) {
            $ids = array_merge($ids, $this->getAllCategoryIdsOptimized($child, $depth + 1, $maxDepth));
        }
    }

    return $ids;
}


public function edit($hashedId)
{
    $category = Categories::with('parent','children')->get()->first(function ($u) use ($hashedId) {
        $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
        return $expectedHash === $hashedId;
    });
    if (!$category) {
        abort(404, 'Categories not found.');
    }
    $parents = Categories::whereNull('parent_id')->pluck('category_name', 'id');

    return view('pages.Categories.edit', [
        'hashedId' => $hashedId,
        'category' => $category,
        'parents' => $parents,
    ]);
}

    public function create()
    {
        $parents = Categories::pluck('category_name', 'id');
        return view('pages.Categories.create',compact('parents'));
    }
    public function store(Request $request)
    {
        \Log::debug('Request Data:', $request->all());
        $validatedData = $request->validate([
            'parent_id' => [
                'nullable',
                'uuid', 
                'exists:categories_tables,id', // Pastikan ID ada di database
                new NoXSSInput()
            ],
            'category_code' => [
                'required',
                'string',
                'max:255',
                'unique:categories_tables,category_code',
                new NoXSSInput()
            ],
            'category_name' => [
                'required',
                'string',
                'max:255',
                'unique:categories_tables,category_name',
                new NoXSSInput()
            ],
        ], [
            'parent_id.max' => 'parent maksimal terdiri dari 255 karakter.',
            'category_code.required' => 'category code wajib diisi.',
            'category_code.string' => 'category code hanya boleh berupa teks.',
            'category_code.max' => 'category code maksimal terdiri dari 255 karakter.',
            'category_code.unique' => 'category code harus unique.',
            'category_name.required' => 'category name wajib diisi.',
            'category_name.string' => 'category name hanya boleh berupa teks.',
            'category_name.max' => 'category name maksimal terdiri dari 255 karakter.',
            'category_name.unique' => 'category name harus unique.',
        ]);
        try {
            DB::beginTransaction();
            $category = Categories::create([
                'parent_id' => $validatedData['parent_id'] ?? null,
                'category_code' => $validatedData['category_code'],
                'category_name' => $validatedData['category_name'],
            ]);
            DB::commit();
            return redirect()->route('pages.Categories')->with('success', 'Categories created Succesfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }
    public function update(Request $request, $hashedId)
    {
        $categories = Categories::with('parent','children')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$categories) {
            return redirect()->route('pages.Categories')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'parent_id' => [
                'string',
                'max:255',
                new NoXSSInput()
            ],
            'category_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories_tables')->ignore($categories->id),
                new NoXSSInput()
            ],
            'category_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories_tables')->ignore($categories->id),
                new NoXSSInput()
            ],
            
        ], [
            'parent_id.max' => 'parent maksimal terdiri dari 255 karakter.',
            'parent_id.unique' => 'parent harus unique.',
            'category_code.required' => 'category code wajib diisi.',
            'category_code.string' => 'category code hanya boleh berupa teks.',
            'category_code.max' => 'category code maksimal terdiri dari 255 karakter.',
            'category_code.unique' => 'category code harus unique.',
            'category_name.required' => 'category name wajib diisi.',
            'category_name.string' => 'category name hanya boleh berupa teks.',
            'category_name.max' => 'category name maksimal terdiri dari 255 karakter.',
            'category_name.unique' => 'category name harus unique.',
        ]);
        $categoriesData = [
            'parent_id' => $validatedData['parent_id'] ?? null,
            'category_name' => $validatedData['category_name'],
            'category_code' => $validatedData['category_code'],
            
        ];
        DB::beginTransaction();
        $categories->update($categoriesData);
        DB::commit();
        return redirect()->route('pages.Categories')->with('success', 'Categories Berhasil Diupdate.');
    }
}
