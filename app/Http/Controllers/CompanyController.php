<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Rules\NoXSSInput;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
class CompanyController extends Controller
{
    public function index()
    {
        return view('pages.Company.Company');
    }
    public function getCompanys()
    {
        $companys = Company::select(['id', 'foto', 'name', 'address', 'npwp','nickname','remark'])
            ->get()
            ->map(function ($company) {
                $company->id_hashed = substr(hash('sha256', $company->id . env('APP_KEY')), 0, 8);
                $company->action = '
                    <a href="' . route('Company.edit', $company->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit Company: ' . e($company->name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $company;
            });
        return DataTables::of($companys)

            ->rawColumns(['action'])
            ->make(true);
    }
    public function edit($hashedId)
    {
        $company = Company::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        $remarks = ['Holding','Unit'];

        if (!$company) {
            abort(404, 'company not found.');
        }

        return view('pages.Company.edit', [
            'company' => $company,
            'remarks' => $remarks,
            'hashedId' => $hashedId,
        ]);
    }
    public function create()
    {
        $remarks = ['Holding','Unit'];

        return view('pages.Company.create',compact('remarks'));
    }

//     public function store(Request $request)
// {
   
//     $validatedData = $request->validate([
//         'name' => [
//             'required', 'string', 'max:255', 'unique:company_tables,name', new NoXSSInput()
//         ],
//         'address' => [
//             'required', 'max:255', new NoXSSInput()
//         ],
//         'nickname' => [
//             'required','string', 'max:255', new NoXSSInput()
//         ],
//         'remark' => [
//             'required','string', new NoXSSInput()
//         ],
//         'npwp' => [
//             'required', 'max:255', 'unique:company_tables,npwp', new NoXSSInput()
//         ],
//        'foto' => ['required', 'image', 'max:512'],

//     ], [
//         'name.required' => 'name wajib diisi.',
//         'remark.required' => 'remark wajib diisi.',
//         'name.string' => 'name hanya boleh berupa teks.',
//         'name.max' => 'name maksimal terdiri dari 255 karakter.',
//         'name.unique' => 'name sudah ada.',
//         'address.required' => 'address wajib diisi.',
//         'npwp.required' => 'npwp wajib diisi.',
//         'npwp.max' => 'npwp max 255 karakter.',
//         'foto.required' => 'harus diisi.',
//         'foto.max' => 'kurang dari 512 kb.',
//     ]);

//     $filePath = null;

//     if ($request->hasFile('foto')) {
//         $file = $request->file('foto');
//         $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
//         $file->storeAs('public/company', $fileName);
//         $filePath = $fileName;
//     }

//     try {
//         DB::beginTransaction();
//         Company::create([
//             'foto' => $filePath,
//             'name' => $validatedData['name'],
//             'remark' => $validatedData['remark'],
//             'npwp' => $validatedData['npwp'],
//             'nickname' => $validatedData['nickname'],
//             'address' => $validatedData['address'],
//         ]);
//         DB::commit();
//         return redirect()->route('pages.Company')->with('success', 'Companies created successfully!');
//     } catch (\Exception $e) {
//         DB::rollBack();
//         if ($filePath && Storage::exists('public/company/' . $filePath)) {
//             Storage::delete('public/company/' . $filePath);
//         }
//         return redirect()->back()
//             ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
//             ->withInput();
//     }
// }
public function store(Request $request)
{
    // ✅ Validasi input
    $validatedData = $request->validate([
        'name' => [
            'required', 'string', 'max:255', 'unique:company_tables,name', new NoXSSInput()
        ],
        'address' => [
            'required', 'string', 'max:255', new NoXSSInput()
        ],
        'nickname' => [
            'required', 'string', 'max:255', new NoXSSInput()
        ],
        'remark' => [
            'required', 'string', new NoXSSInput()
        ],
        'npwp' => [
            'required', 'string', 'max:255', 'unique:company_tables,npwp', new NoXSSInput()
        ],
        'foto' => [
            'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:512'
        ],
    ], [
        'name.required' => 'name wajib diisi.',
        'remark.required' => 'remark wajib diisi.',
        'name.string' => 'name hanya boleh berupa teks.',
        'name.max' => 'name maksimal terdiri dari 255 karakter.',
        'name.unique' => 'name sudah ada.',
        'address.required' => 'address wajib diisi.',
        'npwp.required' => 'npwp wajib diisi.',
        'npwp.max' => 'npwp maksimal 255 karakter.',
        'foto.required' => 'Foto wajib diisi.',
        'foto.image' => 'File harus berupa gambar.',
        'foto.mimes' => 'Format gambar harus jpg, jpeg, png, atau webp.',
        'foto.max' => 'Ukuran gambar maksimal 512 KB.',
    ]);

    $filePath = null;

    // ✅ Upload file aman
    if ($request->hasFile('foto')) {
        $file = $request->file('foto');
        $fileName = hash('sha256', $file->getClientOriginalName() . time()) . '.' . $file->getClientOriginalExtension();
        $folderPath = 'company/' . date('Y/m'); // rapi per tahun/bulan

        // Simpan ke storage
        Storage::putFileAs('public/' . $folderPath, $file, $fileName);

        // Simpan path relatif ke DB
        $filePath = $folderPath . '/' . $fileName;
    }

    try {
        DB::beginTransaction();

        Company::create([
            'foto' => $filePath,
            'name' => $validatedData['name'],
            'remark' => $validatedData['remark'],
            'npwp' => $validatedData['npwp'],
            'nickname' => $validatedData['nickname'],
            'address' => $validatedData['address'],
        ]);

        DB::commit();

        return redirect()
            ->route('pages.Company')
            ->with('success', 'Companies created successfully!');
    } catch (\Exception $e) {
        DB::rollBack();

        // Hapus file jika gagal
        if ($filePath && Storage::exists('public/' . $filePath)) {
            Storage::delete('public/' . $filePath);
        }

        return back()
            ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
            ->withInput();
    }
}


    // public function update(Request $request, $hashedId)
    // {
    //     $company = Company::get()->first(function ($u) use ($hashedId) {
    //         $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
    //         return $expectedHash === $hashedId;
    //     });
    //     if (!$company) {
    //         return redirect()->route('pages.Company')->with('error', 'ID tidak valid.');
    //     }
    //     $validatedData = $request->validate([
    //         'name' => [
    //             'required',
    //             'string',
    //             'max:255',
    //             Rule::unique('company_tables')->ignore($company->id),
    //             new NoXSSInput()
    //         ],
    //         'address' => [
    //             'required',
    //             'max:255',
    //             new NoXSSInput()
    //         ],
    //         'remark' => [
    //             'required',
    //             new NoXSSInput()
    //         ],
    //         'nickname' => [
    //             'required',
    //             'max:255',
    //             new NoXSSInput()
    //         ],
    //         'npwp' => [
    //             'required',
    //             'max:255',
    //             new NoXSSInput()
    //         ],
    //         'foto' => ['nullable', 'image', 'max:512'],
    //     ], [
    //         'name.required' => 'name wajib diisi.',
    //         'remark.required' => 'remark wajib diisi.',
    //         'name.string' => 'name hanya boleh berupa teks.',
    //         'name.max' => 'name maksimal terdiri dari 255 karakter.',
    //         'name.unique' => 'name sudah ada.',
    //         'address.required' => 'address wajib diisi.',
    //         'npwp.required' => 'npwp wajib diisi.',
    //         'npwp.max' => 'npwp max 255 karakter.',
    //         'foto.required' => 'harus diisi.',
           
    //         'foto.max' => 'kurang dari 512 kb.',
    //     ]);
    //     $filePath = $company->foto; // Default: tetap pakai foto lama

    //     if ($request->hasFile('foto')) {
    //         $file = $request->file('foto');
    //         $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
    //         $file->storeAs('public/company', $fileName);
    //         $filePath = $fileName;
        
    //         // Hapus file lama jika ada
    //         if ($company && $company->foto && Storage::exists('public/company/' . $company->foto)) {
    //             Storage::delete('public/company/' . $company->foto);
    //         }
    //     }
        
    //     // Siapkan dta yang akan diupdate
    //     $companyData = [
    //         'name' => $validatedData['name'],
    //         'nickname' => $validatedData['nickname'],
    //         'remark' => $validatedData['remark'],
    //         'address' => $validatedData['address'],
    //         'npwp' => $validatedData['npwp'],
    //     ];
    //     // Hanya masukkan foto kalau ada file baru
    //     if ($request->hasFile('foto')) {
    //         $companyData['foto'] = $filePath;
    //     }
    //     DB::beginTransaction();
    //     $company->update($companyData);
    //     DB::commit();
    //     return redirect()->route('pages.Company')->with('success', 'Company updated Successfully.');
    // }
    public function update(Request $request, $hashedId)
{
    // Cari record berdasarkan hashed ID
    $company = Company::get()->first(function ($u) use ($hashedId) {
        $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
        return $expectedHash === $hashedId;
    });

    if (!$company) {
        return redirect()->route('pages.Company')->with('error', 'ID tidak valid.');
    }

    // ✅ Validasi input
    $validatedData = $request->validate([
        'name' => [
            'required', 'string', 'max:255',
            Rule::unique('company_tables')->ignore($company->id),
            new NoXSSInput()
        ],
        'address' => [
            'required', 'string', 'max:255', new NoXSSInput()
        ],
        'nickname' => [
            'required', 'string', 'max:255', new NoXSSInput()
        ],
        'remark' => [
            'required', 'string', new NoXSSInput()
        ],
        'npwp' => [
            'required', 'string', 'max:255',
            Rule::unique('company_tables')->ignore($company->id),
            new NoXSSInput()
        ],
        'foto' => [
            'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:512'
        ],
    ], [
        'name.required' => 'Name wajib diisi.',
        'remark.required' => 'Remark wajib diisi.',
        'address.required' => 'Address wajib diisi.',
        'npwp.required' => 'NPWP wajib diisi.',
        'foto.image' => 'File harus berupa gambar.',
        'foto.mimes' => 'Format gambar harus jpg, jpeg, png, atau webp.',
        'foto.max' => 'Ukuran gambar maksimal 512 KB.',
    ]);

    $filePath = $company->foto; // default: gunakan foto lama

    try {
        DB::beginTransaction();

        // ✅ Jika ada upload baru
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $fileName = hash('sha256', $file->getClientOriginalName() . time()) . '.' . $file->getClientOriginalExtension();
            $folderPath = 'company/' . date('Y/m');

            // Simpan file baru
            Storage::putFileAs('public/' . $folderPath, $file, $fileName);
            $newFilePath = $folderPath . '/' . $fileName;

            // Hapus file lama kalau ada
            if ($filePath && Storage::exists('public/' . $filePath)) {
                Storage::delete('public/' . $filePath);
            }

            $filePath = $newFilePath;
        }

        // ✅ Update data
        $company->update([
            'name' => $validatedData['name'],
            'nickname' => $validatedData['nickname'],
            'remark' => $validatedData['remark'],
            'address' => $validatedData['address'],
            'npwp' => $validatedData['npwp'],
            'foto' => $filePath,
        ]);

        DB::commit();

        return redirect()->route('pages.Company')->with('success', 'Company updated successfully!');
    } catch (\Exception $e) {
        DB::rollBack();

        // Hapus file baru jika transaksi gagal
        if (!empty($newFilePath) && Storage::exists('public/' . $newFilePath)) {
            Storage::delete('public/' . $newFilePath);
        }

        return back()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()])
                     ->withInput();
    }
}


}
