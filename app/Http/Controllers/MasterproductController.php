<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Masterproduct;
use App\Models\Brands;
use App\Models\Statusproduct;
use App\Models\Taxstatus;
use App\Models\Uoms;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Rules\NoXSSInput;
use App\Models\User;
use App\Models\Stores;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MasterproductController extends Controller
{
    public function index()
    {
        return view('pages.Masterproducts.Masterproducts');
    }
    public function getMasterproducts()
    {
        $masters = Masterproduct::with('brand','uom','taxstatus','statusproduct','category')->select(['id', 'plu','description','long_description','brand_id','category_id','uom_id','taxstatus_id','statusproduct_id','good_stock','bad_stock','cogs','retailprice','memberbronzeprice','membersilverprice','membergoldprice','memberplatinumprice','min_stock','max_stock','weight','conversionfactor'])
            ->get()
            ->map(function ($master) {
                $master->id_hashed = substr(hash('sha256', $master->id . env('APP_KEY')), 0, 8);
                $master->action = '
                    <a href="' . route('Masterproduct.edit', $master->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit master: ' . e($master->description) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $master;
            });
        return DataTables::of($masters)
       
        ->addColumn('brand_name', function ($master) {
            return !empty($master->brand) && !empty($master->brand->brand_name)
                ? $master->brand->brand_name
                : 'Empty';
        })
        ->addColumn('uom', function ($master) {
            return !empty($master->uom) && !empty($master->uom->uom)
                ? $master->uom->uom
                : 'Empty';
        })
        ->addColumn('taxstatus', function ($master) {
            return !empty($master->uom) && !empty($master->taxstatus->taxstatus)
                ? $master->taxstatus->taxstatus
                : 'Empty';
        })
        ->addColumn('statusproduct', function ($master) {
            return !empty($master->statusproduct) && !empty($master->statusproduct->status)
                ? $master->statusproduct->status
                : 'Empty';
        })
        ->addColumn('category', function ($master) {
            return !empty($master->category) && !empty($master->category->category_name)
                ? $master->category->category_name
                : 'Empty';
        })
            ->rawColumns(['action','brand_name','uom','taxstatus','statusproduct','category'])
            ->make(true);
    }
    public function edit($hashedId)
    {
        $master = Masterproduct::with('brand','uom','taxstatus','statusproduct','category')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$master) {
            abort(404, 'Master Product not found.');
        }

        $brands = Brands::get();
        $uoms = Uoms::get();
        $taxstatuses = Taxstatus::get();
        $statusproducts = Statusproduct::get();
        $categories = Categories::get();
        return view('pages.Masterproducts.edit', [
            'brands' => $brands,
            'hashedId' => $hashedId,
            'uoms' => $uoms,
            'taxstatuses' => $taxstatuses,
            'statusproducts' => $statusproducts,
            'categories' => $categories,
        ]);
    }
 
    public function create()
    {
        $brands = Brands::get();
        $uoms = Uoms::get();
        $taxstatuses = Taxstatus::get();
        $statusproducts = Statusproduct::get();
        $categories = Categories::get();
        return view('pages.Store.create',compact('brands','uoms','taxstatuses','statusproducts','categories'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $validatedData = $request->validate([
            'plu' => ['required','max:255', 'unique:masterproduct_tables,plu',
            new NoXSSInput()],
            'description' => ['nullable','string','max:255',
            new NoXSSInput()],
            'long_description' => ['nullable','string',
            new NoXSSInput()],
            'brand_id' => ['required','max:255', 'exists:brands_tables,id',
            new NoXSSInput()],
            
            'category_id' => ['required','max:255', 'exists:categories_tables,id',
            new NoXSSInput()],
            'uom_id' => ['required','max:255', 'exists:uoms_tables,id',
            new NoXSSInput()],
            
            'taxstatus_id' => ['required','max:255', 'exists:taxstatus_tables,id',
            new NoXSSInput()],
            'statusproduct_id' => ['required','max:255', 'exists:statusproduct_tables,id',
            new NoXSSInput()],
            'good_stock' => ['nullable','numeric',
            new NoXSSInput()],
            'bad_stock' => ['nullable','numeric',
            new NoXSSInput()],
            
            'cogs' => ['nullable','numeric',
            new NoXSSInput()],
            'retailprice' => ['nullable','numeric',
            new NoXSSInput()],
            'memberbronzeprice' => ['nullable','numeric',
            new NoXSSInput()],
            
            'membersilverprice' => ['nullable','numeric',
            new NoXSSInput()],
            
            'membergoldprice' => ['nullable','numeric',
            new NoXSSInput()],
            'memberplatinumprice' => ['nullable','numeric',
            new NoXSSInput()],
            'min_stock' => ['nullable','numeric',
            new NoXSSInput()],
            
            'max_stock' => ['nullable','numeric',
            new NoXSSInput()],
            
            'weight' => ['nullable','numeric',
            new NoXSSInput()],
            
            'conversionfactor' => ['nullable','numeric',
            new NoXSSInput()],
            
           
            
        ], [
            'plu.required' => 'The PLU field is required.',
        'plu.unique' => 'The PLU has already been taken.',
        'plu.max' => 'The PLU must not exceed 255 characters.',

        'description.max' => 'The description must not exceed 255 characters.',

        'brand_id.required' => 'The brand field is required.',
        'brand_id.exists' => 'The selected brand is invalid.',

        'category_id.required' => 'The category field is required.',
        'category_id.exists' => 'The selected category is invalid.',

        'uom_id.required' => 'The unit of measure (UOM) field is required.',
        'uom_id.exists' => 'The selected UOM is invalid.',

        'taxstatus_id.required' => 'The tax status field is required.',
        'taxstatus_id.exists' => 'The selected tax status is invalid.',

        'statusproduct_id.required' => 'The product status field is required.',
        'statusproduct_id.exists' => 'The selected product status is invalid.',

        'good_stock.numeric' => 'Good stock must be a number.',
        'bad_stock.numeric' => 'Bad stock must be a number.',

        'cogs.numeric' => 'COGS must be a number.',
        'retailprice.numeric' => 'Retail price must be a number.',
        'memberbronzeprice.numeric' => 'Member bronze price must be a number.',
        'membersilverprice.numeric' => 'Member silver price must be a number.',
        'membergoldprice.numeric' => 'Member gold price must be a number.',
        'memberplatinumprice.numeric' => 'Member platinum price must be a number.',
        'min_stock.numeric' => 'Minimum stock must be a number.',
        'max_stock.numeric' => 'Maximum stock must be a number.',
        'weight.numeric' => 'Weight must be a number.',
        'conversionfactor.numeric' => 'Conversion factor must be a number.',
        ]);
        try {
            DB::beginTransaction();
            $master = Masterproduct::create([
                'plu' => $validatedData['plu'], 
                'description' => $validatedData['description'], 
                'long_description' => $validatedData['long_description'], 
                'brand_id' => $validatedData['brand_id'], 
                'category_id' => $validatedData['category_id'], 
                'uom_id' => $validatedData['uom_id'], 
                'taxstatus_id' => $validatedData['taxstatus_id'], 
                'statusproduct_id' => $validatedData['statusproduct_id'], 
                'good_stock' => $validatedData['good_stock'], 
                'bad_stock' => $validatedData['bad_stock'], 
                'cogs' => $validatedData['cogs'], 
                'retailprice' => $validatedData['retailprice'], 
                'memberbronzeprice' => $validatedData['memberbronzeprice'], 
                'membersilverprice' => $validatedData['membersilverprice'], 
                'membergoldprice' => $validatedData['membergoldprice'], 
                'memberplatinumprice' => $validatedData['memberplatinumprice'], 
                'min_stock' => $validatedData['min_stock'], 
                'max_stock' => $validatedData['max_stock'], 
                'weight' => $validatedData['weight'], 
                'conversionfactor' => $validatedData['conversionfactor'], 
            ]);
            DB::commit();
            return redirect()->route('pages.Masterproduct')->with('success', 'Master Product created Succesfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }
    public function update(Request $request, $hashedId)
    {
        $master = Masterproduct::with('brand','uom','taxstatus','statusproduct','category')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$master) {
            return redirect()->route('pages.Masterproduct')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'plu' => ['required', 'string', 'max:255',Rule::unique('masterproduct_tables')->ignore($master->id),
            new NoXSSInput()],
            'description' => ['nullable', 'string', 'max:255', new NoXSSInput()],
            'long_description' => ['nullable', 'string', new NoXSSInput()],
            'brand_id' => ['required', 'max:255', 'exists:brands_tables,id', new NoXSSInput()],
            'category_id' => ['required', 'max:255', 'exists:categories_tables,id', new NoXSSInput()],
            'uom_id' => ['required', 'max:255', 'exists:uoms_tables,id', new NoXSSInput()],
            'taxstatus_id' => ['required', 'max:255', 'exists:taxstatus_tables,id', new NoXSSInput()],
            'statusproduct_id' => ['required', 'max:255', 'exists:statusproduct_tables,id', new NoXSSInput()],
            'good_stock' => ['nullable', 'numeric', new NoXSSInput()],
            'bad_stock' => ['nullable', 'numeric', new NoXSSInput()],
            'cogs' => ['nullable', 'numeric', new NoXSSInput()],
            'retailprice' => ['nullable', 'numeric', new NoXSSInput()],
            'memberbronzeprice' => ['nullable', 'numeric', new NoXSSInput()],
            'membersilverprice' => ['nullable', 'numeric', new NoXSSInput()],
            'membergoldprice' => ['nullable', 'numeric', new NoXSSInput()],
            'memberplatinumprice' => ['nullable', 'numeric', new NoXSSInput()],
            'min_stock' => ['nullable', 'numeric', new NoXSSInput()],
            'max_stock' => ['nullable', 'numeric', new NoXSSInput()],
            'weight' => ['nullable', 'numeric', new NoXSSInput()],
            'conversionfactor' => ['nullable', 'numeric', new NoXSSInput()],

     
        
    ], [
       'plu.required' => 'The PLU field is required.',
        'plu.unique' => 'The PLU has already been taken.',
        'plu.max' => 'The PLU must not exceed 255 characters.',
        'description.max' => 'The description must not exceed 255 characters.',
        'brand_id.required' => 'The brand field is required.',
        'brand_id.exists' => 'The selected brand is invalid.',
        'category_id.required' => 'The category field is required.',
        'category_id.exists' => 'The selected category is invalid.',
        'uom_id.required' => 'The unit of measure (UOM) field is required.',
        'uom_id.exists' => 'The selected UOM is invalid.',
        'taxstatus_id.required' => 'The tax status field is required.',
        'taxstatus_id.exists' => 'The selected tax status is invalid.',
        'statusproduct_id.required' => 'The product status field is required.',
        'statusproduct_id.exists' => 'The selected product status is invalid.',
        'good_stock.numeric' => 'Good stock must be a number.',
        'bad_stock.numeric' => 'Bad stock must be a number.',
        'cogs.numeric' => 'COGS must be a number.',
        'retailprice.numeric' => 'Retail price must be a number.',
        'memberbronzeprice.numeric' => 'Member bronze price must be a number.',
        'membersilverprice.numeric' => 'Member silver price must be a number.',
        'membergoldprice.numeric' => 'Member gold price must be a number.',
        'memberplatinumprice.numeric' => 'Member platinum price must be a number.',
        'min_stock.numeric' => 'Minimum stock must be a number.',
        'max_stock.numeric' => 'Maximum stock must be a number.',
        'weight.numeric' => 'Weight must be a number.',
        'conversionfactor.numeric' => 'Conversion factor must be a number.',
    ]);

    $masterData = [
        'plu' => $validatedData['plu'],
        'description' => $validatedData['description'] ?? null,
        'long_description' => $validatedData['long_description'] ?? null,
        'brand_id' => $validatedData['brand_id'],
        'category_id' => $validatedData['category_id'],
        'uom_id' => $validatedData['uom_id'],
        'taxstatus_id' => $validatedData['taxstatus_id'],
        'statusproduct_id' => $validatedData['statusproduct_id'],
        'good_stock' => $validatedData['good_stock'] ?? 0,
        'bad_stock' => $validatedData['bad_stock'] ?? 0,
        'cogs' => $validatedData['cogs'] ?? 0,
        'retailprice' => $validatedData['retailprice'] ?? 0,
        'memberbronzeprice' => $validatedData['memberbronzeprice'] ?? 0,
        'membersilverprice' => $validatedData['membersilverprice'] ?? 0,
        'membergoldprice' => $validatedData['membergoldprice'] ?? 0,
        'memberplatinumprice' => $validatedData['memberplatinumprice'] ?? 0,
        'min_stock' => $validatedData['min_stock'] ?? 0,
        'max_stock' => $validatedData['max_stock'] ?? 0,
        'weight' => $validatedData['weight'] ?? 0,
        'conversionfactor' => $validatedData['conversionfactor'] ?? 1,
    ];
    
        DB::beginTransaction();
        $master->update($masterData);
        DB::commit();

        return redirect()->route('pages.Masterproduct')->with('success', 'Master Products updated Successfully.');
    }
}
