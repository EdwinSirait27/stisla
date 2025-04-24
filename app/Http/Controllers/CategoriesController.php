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
        // $categories = Categories::with('parent')->whereNotNull('parent_id')->pluck('parent_id', 'id');
        // $categoriesparents = Categories::with('parent')->whereNull('parent_id')->pluck('parent_id', 'id');
        // $categories = Categories::with('parent')->whereNotNull('parent_id')->get();
   
        $categories = Categories::with('children')
        ->whereNull('parent_id')
        ->get();
        $parentCategories = Categories::whereNull('parent_id')->get();

        return view('pages.Categories.Categories',compact('categories','parentCategories'));
    }
    // public function getCategories(Request $request)
    // {
    //     $query = Categories::with('children','parent')->select(['id', 'parent_id', 'category_code','category_name']);
    //     if ($request->filled('category_name')) {
    //         $query->where('category_name', $request->category_name);
    //     }
    //     $category = $query->get()
    //         ->map(function ($category) {
    //             $category->id_hashed = substr(hash('sha256', $category->id . env('APP_KEY')), 0, 8);
    //             $category->action = '
    //         <a href="' . route('Categories.edit', $category->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit categories">
    //             <i class="fas fa-user-edit text-secondary"></i>
    //         </a>';
    //             return $category;
    //         });
    //     return DataTables::of($category)
    //         ->rawColumns(['action'])
    //         ->make(true);
    // }
    public function getCategories(Request $request)
{
    $query = Categories::with(['children', 'parent'])
        ->select(['id', 'parent_id', 'category_code', 'category_name'])
        ->when($request->filled('category_name'), function ($q) use ($request) {
            $q->where('category_name', 'like', '%'.$request->category_name.'%');
        });

    $categories = $query->get()
        ->map(function ($category) {
            return [
                'id' => $category->id,
                'id_hashed' => substr(hash('sha256', $category->id . config('app.key')), 0, 8),
                'category_code' => $category->category_code,
                'category_name' => $category->category_name,
                'full_category_name' => $category->full_category_name, // Menggunakan accessor
                'parent_name' => optional($category->parent)->category_name,
                'children_count' => $category->children->count(),   
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
        // $parents = Categories::whereNull('parent_id')->pluck('category_name', 'id');
        $parents = Categories::pluck('category_name', 'id'); // ['uuid-1' => 'Drink', ...]

        return view('pages.Categories.create',compact('parents'));
    }
    public function store(Request $request)
    {
        \Log::debug('Request Data:', $request->all());
        $validatedData = $request->validate([
            // 'parent_id' => [
            //     'nullable',
            //     'max:255',
            
            //     new NoXSSInput()
            // ],
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
