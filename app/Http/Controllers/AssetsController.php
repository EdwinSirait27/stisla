<?php

namespace App\Http\Controllers;

use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use App\Models\AssetCategories;
use App\Models\Assets;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AssetsController extends Controller
{
    public function index()
    {
        return view('pages.Assets.Assets');
    }
    // public function getAssets()
    // {
    //     $assets = Assets::select(['id', 'asset_category_id', 'asset_name', 'purchase_date', 'purchase_price', 'status'])
    //         ->with('assetCategory')
    //         ->map(function ($asset) {
    //             $asset->id = $asset->id;
    //             $asset->action = '
    //                 <a href="' . route('Assets.edit', $asset->id) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit Asset: ' . e($asset->asset_name) . '">
    //                     <i class="fas fa-user-edit text-secondary"></i>
    //                 </a>';
    //             return $asset;
    //         });
    //     return DataTables::of($assets)
    //         ->rawColumns(['action'])
    //         ->make(true);
    // }
    public function getAssets()
    {
        $assets = Assets::select([
            'id',
            'asset_category_id',
            'asset_name',
            'uoms',
            'qty',
            'purchase_date',
            'purchase_price',
            'status'
        ])
            ->with('assetCategory')
            ->get()
            ->map(function ($asset) {

                $asset->asset_category_name = $asset->assetCategory->asset_category_name ?? '-';

                $asset->action = '
                <a href="' . route('Assets.edit', $asset->id) . '" 
                   class="mx-3" 
                   data-bs-toggle="tooltip" 
                   title="Edit Asset: ' . e($asset->asset_name) . '">
                    <i class="fas fa-user-edit text-secondary"></i>
                </a>';

                return $asset;
            });

        return DataTables::of($assets)
            ->rawColumns(['action'])
             ->editColumn('purchase_date', function ($asset) {
                return optional($asset->purchase_date)
                    ->timezone('Asia/Makassar')
                    ->translatedFormat('d F Y');
            })
            ->make(true);
    }

    public function edit($id)
    {
        $asset = Assets::with('assetCategory')->find($id);
        $assetcategories =  AssetCategories::get();
        $uoms = Assets::getUomOptions();
        $statuses = Assets::getStatusOptions();

        if (!$asset) {
            abort(404, 'Asset not found.');
        }
        return view('pages.Assets.edit', [
            'assetcategories' => $assetcategories,
            'asset' => $asset,
            'uoms' => $uoms,
            'statuses' => $statuses,
            'id' => $id
        ]);
    }
    public function create()
    {
        $assetcategories =  AssetCategories::get();
        $uoms = Assets::getUomOptions();
        $statuses = Assets::getStatusOptions();
        return view('pages.Assets.create', compact('assetcategories', 'uoms', 'statuses'));
    }
 public function store(Request $request)
{
    $request->merge([
        'purchase_price' => $request->purchase_price
            ? str_replace(',', '.', str_replace('.', '', $request->purchase_price))
            : null
    ]);

    $validatedData = $request->validate([
        'asset_category_id' => ['required', 'exists:asset_categories,id'],

        'asset_name' => ['required', 'string', 'max:255'],

        'uoms' => [
            'required',
            'string',
            Rule::in(array_keys(Assets::getUomOptions()))
        ],

        'serial_number' => ['nullable', 'string', 'max:255'],

        'brand' => ['nullable', 'string', 'max:255'],

        'model' => ['nullable', 'string', 'max:255'],

        'purchase_date' => ['nullable', 'date'],

        'purchase_price' => ['nullable', 'numeric'],
        'qty' => ['required', 'numeric'],

        'status' => [
            'nullable',
            'string',
            Rule::in(array_keys(Assets::getStatusOptions()))
        ],

        'notes' => ['nullable', 'string']
    ]);

    try {

        DB::beginTransaction();
        Assets::create([
            'asset_category_id' => $validatedData['asset_category_id'],
            'asset_name'        => $validatedData['asset_name'],
            'uoms'               => $validatedData['uoms'],
            'qty'               => $validatedData['qty'],
            'serial_number'     => $validatedData['serial_number'],
            'brand'             => $validatedData['brand'],
            'model'             => $validatedData['model'],
            'purchase_date'     => $validatedData['purchase_date'],
            'purchase_price'    => $validatedData['purchase_price'],
            'status'            => 'Active',
            'notes'             => $validatedData['notes'],
        ]);

        DB::commit();

        return redirect()->route('pages.Assets')
            ->with('success', 'Asset created successfully!');

    } catch (\Exception $e) {

        DB::rollBack();

        return redirect()->back()
            ->withErrors([
                'error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ])
            ->withInput();
    }
}
    public function update(Request $request, $id)
    {
        $asset = Assets::with('assetCategory')->find($id);

        if (!$asset) {
            return redirect()->route('pages.Assets')
                ->with('error', 'Asset not found.');
        }
          $request->merge([
        'purchase_price' => $request->purchase_price
            ? str_replace(',', '.', str_replace('.', '', $request->purchase_price))
            : null
    ]);


        $validatedData = $request->validate([

            'asset_category_id' => [
                'required',
                'exists:asset_categories,id'
            ],

            'asset_name' => [
                'required',
                'string',
                'max:255'
            ],

            'uoms' => [
                'required',
                'string',
                Rule::in(array_keys(Assets::getUomOptions()))
            ],

            'serial_number' => [
                'nullable',
                'string',
                'max:255'
            ],


            'brand' => [
                'nullable',
                'string',
                'max:255'
            ],
            'qty' => [
                'required',
                'integer',
                'min:0'
            ],

            'model' => [
                'nullable',
                'string',
                'max:255'
            ],

            'purchase_date' => [
                'nullable',
                'date'
            ],

            'purchase_price' => [
                'nullable',
                'numeric'
            ],

            'status' => [
                'nullable',
                'string',
                Rule::in(array_keys(Assets::getStatusOptions()))
            ],
            'notes' => [
                'nullable',
                'string'
            ]
        ], [

            'asset_category_id.required' => 'Asset Category wajib diisi.',
            'asset_category_id.exists'   => 'Asset Category tidak valid.',

            'asset_name.required' => 'Asset Name wajib diisi.',

            'uoms.required' => 'UOM wajib diisi.',
            'uoms.in'       => 'UOM tidak valid.',

            'purchase_date.date' => 'Format tanggal tidak valid.',

            'purchase_price.numeric' => 'Purchase Price harus berupa angka.',
        ]);

        try {

            DB::beginTransaction();

            $asset->update([
                'asset_category_id' => $validatedData['asset_category_id'],
                'asset_name'        => $validatedData['asset_name'],
                'uoms'               => $validatedData['uoms'],
                'qty'               => $validatedData['qty'],
                'serial_number'     => $validatedData['serial_number'],
                'brand'             => $validatedData['brand'],
                'model'             => $validatedData['model'],
                'purchase_date'     => $validatedData['purchase_date'],
                'purchase_price'    => $validatedData['purchase_price'],
                'status'            => $validatedData['status'],
                'notes'             => $validatedData['notes']
            ]);
            DB::commit();

            return redirect()->route('pages.Assets')
                ->with('success', 'Asset updated successfully.');
        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()->back()
                ->withErrors([
                    'error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
                ])
                ->withInput();
        }
    }
}
