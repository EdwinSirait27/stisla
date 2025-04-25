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

protected function getCategoryPath($category)
{
    $path = [];
    $current = $category;
    
    // Walk up the hierarchy to build the path
    while ($current) {
        array_unshift($path, [
            'code' => $current->category_code,
            'name' => $current->category_name
        ]);
        
        $current = $current->parent_id ? Categories::find($current->parent_id) : null;
    }
    
    return $path;
}
protected function formatCategoryWithHierarchy($category, $groupedCategories, &$result, $hierarchy = [])
{
    // Add current level to hierarchy
    $level = count($hierarchy) + 1;
    $hierarchy[] = [
        'code' => $category->category_code,
        'name' => $category->category_name
    ];
    
    // Prepare the base record
    $record = [
        'id' => $category->id,
        'id_hashed' => substr(hash('sha256', $category->id . config('app.key')), 0, 8),
        'action' => '<a href="'.route('Categories.edit', $category->id).'" class="mx-3" data-bs-toggle="tooltip" title="Edit category">
            <i class="fas fa-edit text-secondary"></i>
        </a>'
    ];
    
    // Fill in hierarchy levels
    for ($i = 1; $i <= 5; $i++) {
        $record["level_{$i}_code"] = $hierarchy[$i-1]['code'] ?? '-';
        $record["level_{$i}_name"] = $hierarchy[$i-1]['name'] ?? '-';
    }
    
    $result[] = $record;
    
    // Process children recursively
    if ($children = $groupedCategories->get($category->id, null)) {
        foreach ($children as $child) {
            $this->formatCategoryWithHierarchy($child, $groupedCategories, $result, $hierarchy);
        }
    }
}
// public function getCategories(Request $request)
// {
//     // Ambil kategori tingkat atas (parent)
//     $parentQuery = Categories::whereNull('parent_id')
//                   ->orWhere('parent_id', 0)
//                   ->select(['id', 'category_code', 'category_name']);
//     // Filter berdasarkan nama kategori jika ada
//     if ($request->filled('category_name')) {
//         $parentQuery->where('category_name', 'like', '%'.$request->category_name.'%');
//     }
//     $parentCategories = $parentQuery->get();
//     // Siapkan array untuk menyimpan semua data kategori (parent dan children)
//     $formattedCategories = [];
//     foreach ($parentCategories as $parent) {
//         // Tambahkan parent ke dalam hasil
//         $formattedCategories[] = [
//             'id' => $parent->id,
//             'id_hashed' => substr(hash('sha256', $parent->id . config('app.key')), 0, 8),
//             'level_1_code' => $parent->category_code,
//             'level_1_name' => $parent->category_name,
//             'level_2_code' => '-',
//             'level_2_name' => '-',
//             'level_3_code' => '-',
//             'level_3_name' => '-',
//             'level_4_code' => '-',
//             'level_4_name' => '-',
//             'level_5_code' => '-',
//             'level_5_name' => '-',
//             'action' => '
//                 <a href="'.route('Categories.edit', $parent->id).'" class="mx-3" data-bs-toggle="tooltip" title="Edit category">
//                     <i class="fas fa-edit text-secondary"></i>
//                 </a>'
//         ];
        
//         // Ambil level 2 children
//         $level2Children = Categories::where('parent_id', $parent->id)
//                            ->select(['id', 'category_code', 'category_name'])
//                            ->get();
                           
//         foreach ($level2Children as $level2) {
//             $formattedCategories[] = [
//                 'id' => $level2->id,
//                 'id_hashed' => substr(hash('sha256', $level2->id . config('app.key')), 0, 8),
//                 'level_1_code' => $parent->category_code,
//                 'level_1_name' => $parent->category_name,
//                 'level_2_code' => $level2->category_code,
//                 'level_2_name' => $level2->category_name,
//                 'level_3_code' => '-',
//                 'level_3_name' => '-',
//                 'level_4_code' => '-',
//                 'level_4_name' => '-',
//                 'level_5_code' => '-',
//                 'level_5_name' => '-',
//                 'action' => '
//                     <a href="'.route('Categories.edit', $level2->id).'" class="mx-3" data-bs-toggle="tooltip" title="Edit category">
//                         <i class="fas fa-edit text-secondary"></i>
//                     </a>'
//             ];
            
//             // Ambil level 3 children
//             $level3Children = Categories::where('parent_id', $level2->id)
//                                ->select(['id', 'category_code', 'category_name'])
//                                ->get();
                               
//             foreach ($level3Children as $level3) {
//                 $formattedCategories[] = [
//                     'id' => $level3->id,
//                     'id_hashed' => substr(hash('sha256', $level3->id . config('app.key')), 0, 8),
//                     'level_1_code' => $parent->category_code,
//                     'level_1_name' => $parent->category_name,
//                     'level_2_code' => $level2->category_code,
//                     'level_2_name' => $level2->category_name,
//                     'level_3_code' => $level3->category_code,
//                     'level_3_name' => $level3->category_name,
//                     'level_4_code' => '-',
//                     'level_4_name' => '-',
//                     'level_5_code' => '-',
//                     'level_5_name' => '-',
//                     'action' => '
//                         <a href="'.route('Categories.edit', $level3->id).'" class="mx-3" data-bs-toggle="tooltip" title="Edit category">
//                             <i class="fas fa-edit text-secondary"></i>
//                         </a>'
//                 ];
                
//                 // Ambil level 4 children
//                 $level4Children = Categories::where('parent_id', $level3->id)
//                                    ->select(['id', 'category_code', 'category_name'])
//                                    ->get();
                                   
//                 foreach ($level4Children as $level4) {
//                     $formattedCategories[] = [
//                         'id' => $level4->id,
//                         'id_hashed' => substr(hash('sha256', $level4->id . config('app.key')), 0, 8),
//                         'level_1_code' => $parent->category_code,
//                         'level_1_name' => $parent->category_name,
//                         'level_2_code' => $level2->category_code,
//                         'level_2_name' => $level2->category_name,
//                         'level_3_code' => $level3->category_code,
//                         'level_3_name' => $level3->category_name,
//                         'level_4_code' => $level4->category_code,
//                         'level_4_name' => $level4->category_name,
//                         'level_5_code' => '-',
//                         'level_5_name' => '-',
//                         'action' => '
//                             <a href="'.route('Categories.edit', $level4->id).'" class="mx-3" data-bs-toggle="tooltip" title="Edit category">
//                                 <i class="fas fa-edit text-secondary"></i>
//                             </a>'
//                     ];
                    
//                     // Ambil level 5 children
//                     $level5Children = Categories::where('parent_id', $level4->id)
//                                        ->select(['id', 'category_code', 'category_name'])
//                                        ->get();
                                       
//                     foreach ($level5Children as $level5) {
//                         $formattedCategories[] = [
//                             'id' => $level5->id,
//                             'id_hashed' => substr(hash('sha256', $level5->id . config('app.key')), 0, 8),
//                             'level_1_code' => $parent->category_code,
//                             'level_1_name' => $parent->category_name,
//                             'level_2_code' => $level2->category_code,
//                             'level_2_name' => $level2->category_name,
//                             'level_3_code' => $level3->category_code,
//                             'level_3_name' => $level3->category_name,
//                             'level_4_code' => $level4->category_code,
//                             'level_4_name' => $level4->category_name,
//                             'level_5_code' => $level5->category_code,
//                             'level_5_name' => $level5->category_name,
//                             'action' => '
//                                 <a href="'.route('Categories.edit', $level5->id).'" class="mx-3" data-bs-toggle="tooltip" title="Edit category">
//                                     <i class="fas fa-edit text-secondary"></i>
//                                 </a>'
//                         ];
//                     }
//                 }
//             }
//         }
//     }
    
//     return DataTables::of(collect($formattedCategories))
//         ->addIndexColumn()
//         ->rawColumns(['action'])
//         ->make(true);
// }
public function getCategories(Request $request)
{
    // Query dasar untuk semua kategori
    $query = Categories::with('parent')->select(['id', 'parent_id', 'category_code', 'category_name']);
    
    // Filter berdasarkan nama kategori jika ada
    if ($request->filled('category_name')) {
        $query->where('category_name', 'like', '%'.$request->category_name.'%');
    }
    
    // Dapatkan semua kategori yang memenuhi filter
    $allCategories = $query->get();
    
    // Kelompokkan kategori berdasarkan parent_id untuk memudahkan traversal
    $groupedCategories = $allCategories->groupBy('parent_id');
    
    // Mulai dari kategori root (parent_id null atau 0)
    $rootCategories = $groupedCategories->get(null, collect())
                      ->merge($groupedCategories->get(0, collect()));
    
    $formattedCategories = [];
    
    foreach ($rootCategories as $rootCategory) {
        $this->formatCategoryWithHierarchy($rootCategory, $groupedCategories, $formattedCategories);
    }
    
    return DataTables::of(collect($formattedCategories))
        ->addIndexColumn()
        ->rawColumns(['action'])
        ->make(true);
}










// ini yang menjadi patokan
// public function getCategories(Request $request)
// {
//     // Ambil kategori tingkat atas (parent)
//     $parentQuery = Categories::whereNull('parent_id')
//                   ->orWhere('parent_id', 0)
//                   ->select(['id', 'category_code', 'category_name']);
    
//     // Filter berdasarkan nama kategori jika ada
//     if ($request->filled('category_name')) {
//         $parentQuery->where('category_name', 'like', '%'.$request->category_name.'%');
//     }
    
//     $parentCategories = $parentQuery->get();
    
//     // Siapkan array untuk menyimpan semua data kategori (parent dan children)
//     $formattedCategories = [];
    
//     foreach ($parentCategories as $parent) {
//         // Tambahkan parent ke dalam hasil
//         $formattedCategories[] = [
//             'id' => $parent->id,
//             'id_hashed' => substr(hash('sha256', $parent->id . config('app.key')), 0, 8),
//             'parent_code' => $parent->category_code,
            
            
//             'child_code' => '-',
//             'child_name' => '-',
//             'action' => '
//                 <a href="'.route('Categories.edit', $parent->id).'" class="mx-3" data-bs-toggle="tooltip" title="Edit category">
//                     <i class="fas fa-edit text-secondary"></i>
//                 </a>'
//         ];
        
//         // Ambil semua child untuk parent ini
//         $children = Categories::where('parent_id', $parent->id)
//                    ->select(['id', 'category_code', 'category_name'])
//                    ->get();
                   
//         // Tambahkan setiap child ke dalam hasil
//         foreach ($children as $child) {
//             $formattedCategories[] = [
//                 'id' => $child->id,
//                 'id_hashed' => substr(hash('sha256', $child->id . config('app.key')), 0, 8),
//                 'parent_code' => $parent->category_code,
//                 // 'parent_name' => $parent->category_name,
//                 'parent_name' => $parent->category_name ?? 'Parent',
//                 'child_code' => $child->category_code,
//                 'child_name' => $child->category_name,
//                 'action' => '
//                     <a href="'.route('Categories.edit', $child->id).'" class="mx-3" data-bs-toggle="tooltip" title="Edit category">
//                         <i class="fas fa-edit text-secondary"></i>
//                     </a>'
//             ];
//         }
//     }
    
//     return DataTables::of(collect($formattedCategories))
//         ->addIndexColumn()
//         ->rawColumns(['action'])
//         ->make(true);
// }


































// public function getCategories(Request $request)
// {
//     $query = Categories::with(['parent'])
//         ->select(['id', 'parent_id', 'category_code', 'category_name']);

//     // Filter by parent ID (dengan anak-anaknya secara rekursif)
//     if ($request->filled('parent_id')) {
//         $parent = Categories::with('childrenRecursive')->find($request->parent_id);

//         if (!$parent) {
//             return DataTables::of(collect())->make(true); // return kosong jika parent tidak ditemukan
//         }

//         $ids = $this->getAllCategoryIdsOptimized($parent);
//         $query->whereIn('id', $ids);
//     }

//     // Filter berdasarkan nama kategori
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
//                 // 'children_count' => $category->children()->count(),
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
