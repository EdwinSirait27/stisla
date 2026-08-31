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
        $companys = Company::select(['id', 'foto', 'name', 'address', 'npwp', 'nickname', 'remark', 'header', 'website', 'email'])
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

        $remarks = ['Holding', 'Unit'];

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
        $remarks = ['Holding', 'Unit'];

        return view('pages.Company.create', compact('remarks'));
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:company_tables,name',
                new NoXSSInput()
            ],
            'header' => [
                'required',
                'string',
                'max:255',
                new NoXSSInput()
            ],
            'website' => [
                'required',
                'string',
                'max:255',
                new NoXSSInput()
            ],
            'email' => [
                'required',
                'string',
                'max:255',
                new NoXSSInput()
            ],
            'address' => [
                'required',
                'string',
                'max:255',
                new NoXSSInput()
            ],
            'nickname' => [
                'required',
                'string',
                'max:255',
                new NoXSSInput()
            ],
            'remark' => [
                'required',
                'string',
                new NoXSSInput()
            ],
            'npwp' => [
                'required',
                'string',
                'max:255',
                'unique:company_tables,npwp',
                new NoXSSInput()
            ],
            'foto' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:1024'
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
            'foto.max' => 'Ukuran gambar maksimal 1024 KB.',
        ]);

        $filePath = null;

        // ✅ Upload file aman
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $fileName = hash('sha256', $file->getClientOriginalName() . time()) . '.' . $file->getClientOriginalExtension();
            $folderPath = 'company/' . date('Y/m'); // rapi per tahun/bulan

            // Simpan ke disk 'public' (yang di-serve lewat symlink public/storage,
            // sesuai URL yang dipakai getFotoUrlAttribute() & views)
            $stored = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);

            if (!$stored) {
                return back()
                    ->withErrors(['foto' => 'Gagal mengunggah foto perusahaan. Silakan coba lagi.'])
                    ->withInput();
            }

            // Simpan path relatif ke DB
            $filePath = $folderPath . '/' . $fileName;
        }

        try {
            DB::beginTransaction();

            Company::create([
                'foto' => $filePath,
                'name' => $validatedData['name'],
                'remark' => $validatedData['remark'],
                'header' => $validatedData['header'],
                'website' => $validatedData['website'],
                'email' => $validatedData['email'],
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
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            return back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }

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
                'required',
                'string',
                'max:255',
                Rule::unique('company_tables')->ignore($company->id),
                new NoXSSInput()
            ],
            'address' => [
                'required',
                'string',
                'max:255',
                new NoXSSInput()
            ],
            'nickname' => [
                'required',
                'string',
                'max:255',
                new NoXSSInput()
            ],
            'header' => [
                'required',
                'string',
                'max:255',
                new NoXSSInput()
            ],
            'website' => [
                'required',
                'string',
                'max:255',
                new NoXSSInput()
            ],
            'email' => [
                'required',
                'string',
                'max:255',
                new NoXSSInput()
            ],
            'remark' => [
                'required',
                'string',
                new NoXSSInput()
            ],
            'npwp' => [
                'required',
                'string',
                'max:255',
                Rule::unique('company_tables')->ignore($company->id),
                new NoXSSInput()
            ],
            'foto' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:1024'
            ],
        ], [
            'name.required' => 'Name wajib diisi.',
            'remark.required' => 'Remark wajib diisi.',
            'address.required' => 'Address wajib diisi.',
            'npwp.required' => 'NPWP wajib diisi.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format gambar harus jpg, jpeg, png, atau webp.',
            'foto.max' => 'Ukuran gambar maksimal 1024 KB.',
        ]);

        $filePath = $company->foto; // default: gunakan foto lama

        try {
            DB::beginTransaction();

            // ✅ Jika ada upload baru
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $fileName = hash('sha256', $file->getClientOriginalName() . time()) . '.' . $file->getClientOriginalExtension();
                $folderPath = 'company/' . date('Y/m');

                // Simpan file baru ke disk 'public' (yang di-serve lewat symlink public/storage)
                $stored = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);

                if (!$stored) {
                    throw new \RuntimeException('Gagal mengunggah foto perusahaan.');
                }

                $newFilePath = $folderPath . '/' . $fileName;

                // Hapus file lama kalau ada
                if ($filePath && Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
                $filePath = $newFilePath;
            }
            // ✅ Update data
            $company->update([
                'name' => $validatedData['name'],
                'nickname' => $validatedData['nickname'],
                'header' => $validatedData['header'],
                'website' => $validatedData['website'],
                'email' => $validatedData['email'],
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
            if (!empty($newFilePath) && Storage::disk('public')->exists($newFilePath)) {
                Storage::disk('public')->delete($newFilePath);
            }

            return back()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()])
                ->withInput();
        }
    }
    public function show($id)
    {
        $company = Company::findOrFail($id);

        return response()->json([
            'id' => $company->id,
            'name' => $company->name,
            'foto' => $company->foto,
            'logo_url' => asset('storage/' . $company->foto),
        ]);
    }
}
