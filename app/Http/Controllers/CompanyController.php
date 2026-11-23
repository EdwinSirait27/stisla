<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Rules\NoXSSInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

/**
 * Master perusahaan (route web dijaga permission:ManageCompanies; show() dipakai API ber-API key).
 *
 * Data perusahaan dicetak sebagai kop surat di SK dan dokumen karyawan (documents/types/*,
 * SkLetters/pdf): logo dibaca dari disk public lewat public_path('storage/...').
 * FK employees_tables, structures, sk_template, company_document_configs → company_tables
 * ON DELETE CASCADE, jadi perusahaan sengaja tidak bisa dihapus dari aplikasi.
 */
class CompanyController extends Controller
{
    private const REMARKS = ['Holding', 'Unit'];

    public function index()
    {
        return view('pages.Company.Company');
    }

    public function getCompanys()
    {
        $companies = Company::orderByRaw("remark = 'Holding' desc")->orderBy('name')->get();

        $employees = DB::table('employees_tables')
            ->groupBy('company_id')
            ->get(['company_id', DB::raw("sum(status = 'Active') as active"), DB::raw('count(*) as total')])
            ->keyBy('company_id');
        $stores = $this->storeCounts();

        $rows = $companies->map(function (Company $c) use ($employees, $stores) {
            $hash = $this->hashId($c->id);
            $emp  = $employees->get($c->id);

            return [
                'name'      => $c->name,
                'nickname'  => $c->nickname,
                'remark'    => $c->remark,
                'header'    => $c->header,
                'address'   => $c->address,
                'npwp'      => $this->formatNpwp($c->npwp),
                'email'     => $c->email,
                'website'   => $c->website,
                'logo_url'  => $c->foto ? route('Company.logo', $hash) : null,
                'employees' => (int) ($emp->active ?? 0),
                'employees_total' => (int) ($emp->total ?? 0),
                'stores'    => (int) ($stores[$c->id] ?? 0),
                'missing'   => $this->missing($c),
                'edit_url'  => route('Company.edit', $hash),
            ];
        });

        // Teks di-escape di browser (fungsi esc), jadi tidak di-escape lagi di sini supaya "&" tidak tampil sebagai &amp;.
        return DataTables::of($rows)
            ->escapeColumns([])
            ->with('stats', [
                'total'      => $rows->count(),
                'holding'    => $rows->where('remark', 'Holding')->count(),
                'employees'  => $rows->sum('employees'),
                'incomplete' => $rows->filter(fn($r) => $r['missing'])->count(),
            ])
            ->make(true);
    }

    public function create()
    {
        return view('pages.Company.create', ['company' => null, 'remarks' => self::REMARKS, 'usage' => null, 'logoUrl' => null]);
    }

    public function edit($hashedId)
    {
        $company = $this->findByHash($hashedId);
        abort_unless($company, 404, 'Perusahaan tidak ditemukan.');

        return view('pages.Company.edit', [
            'company'  => $company,
            'remarks'  => self::REMARKS,
            'hashedId' => $hashedId,
            'logoUrl'  => $company->foto ? route('Company.logo', $hashedId) . '?v=' . substr(md5($company->foto), 0, 6) : null,
            'usage'    => [
                'employees' => DB::table('employees_tables')->where('company_id', $company->id)->where('status', 'Active')->count(),
                'stores'    => $this->storeCounts()[$company->id] ?? 0,
                'sk'        => DB::table('sk_letters')->where('company_id', $company->id)->count(),
                'contracts' => DB::table('contracts')->where('company_id', $company->id)->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);

        $path = $this->storeLogo($request);
        if (!$path) {
            return back()->withInput()->withErrors(['foto' => 'Logo gagal diunggah. Coba lagi.']);
        }

        try {
            $company = Company::create($data + ['foto' => $path]);
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            Log::error('Gagal menyimpan perusahaan', ['message' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'Perusahaan tidak bisa disimpan. Coba lagi atau cek storage/logs/laravel.log.']);
        }

        return redirect()->route('pages.Company')->with('success', "Perusahaan {$company->name} ditambahkan.");
    }

    public function update(Request $request, $hashedId)
    {
        $company = $this->findByHash($hashedId);
        if (!$company) {
            return redirect()->route('pages.Company')->with('error', 'Perusahaan tidak ditemukan.');
        }

        $data    = $this->validated($request, $company);
        $oldPath = $company->foto;
        $newPath = null;

        if ($request->hasFile('foto')) {
            $newPath = $this->storeLogo($request);
            if (!$newPath) {
                return back()->withInput()->withErrors(['foto' => 'Logo gagal diunggah. Coba lagi.']);
            }
            $data['foto'] = $newPath;
        }

        try {
            $company->update($data);
        } catch (\Throwable $e) {
            if ($newPath) Storage::disk('public')->delete($newPath);
            Log::error('Gagal memperbarui perusahaan', ['id' => $company->id, 'message' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'Perubahan tidak bisa disimpan. Coba lagi atau cek storage/logs/laravel.log.']);
        }

        // Logo lama baru dihapus setelah data tersimpan, supaya tidak hilang kalau penyimpanan gagal.
        if ($newPath && $oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('pages.Company')->with('success', "Perusahaan {$company->name} disimpan.");
    }

    /**
     * Logo untuk halaman admin. Di production /storage/ diteruskan nginx ke MinIO, sedangkan logo
     * disimpan di disk public lokal, jadi gambar dikirim lewat Laravel.
     */
    public function logo($hashedId)
    {
        $company = $this->findByHash($hashedId);
        abort_unless($company && $company->foto && Storage::disk('public')->exists($company->foto), 404);

        return response()->file(Storage::disk('public')->path($company->foto), ['Cache-Control' => 'private, max-age=86400']);
    }

    /** API (X-Api-Key): dipakai aplikasi formrequest untuk logo di PDF. Bentuk respons dipertahankan. */
    public function show($id)
    {
        $company = Company::findOrFail($id);

        return response()->json([
            'id'       => $company->id,
            'name'     => $company->name,
            'foto'     => $company->foto,
            'logo_url' => $company->foto ? asset('storage/' . $company->foto) : null,
        ]);
    }

    // ── Helper ────────────────────────────────────────────────────

    /** ID pendek di URL. config('app.key'), bukan env(): saat config di-cache env() bernilai null. */
    private function hashId(string $id): string
    {
        return substr(hash('sha256', $id . config('app.key')), 0, 8);
    }

    private function findByHash(string $hashedId): ?Company
    {
        return Company::get()->first(fn($c) => hash_equals($this->hashId($c->id), $hashedId));
    }

    /** Jumlah lokasi per perusahaan lewat struktur organisasi (structures.company_id → store_id). */
    private function storeCounts(): array
    {
        if (!Schema::hasColumn('structures', 'store_id')) {
            return [];
        }

        return DB::table('structures')->whereNotNull('store_id')->groupBy('company_id')
            ->pluck(DB::raw('count(distinct store_id)'), 'company_id')->map(fn($v) => (int) $v)->all();
    }

    /** Isian kop surat yang masih kosong. */
    private function missing(Company $c): array
    {
        return array_values(array_filter([
            !$c->foto ? 'logo' : null,
            !$c->header ? 'subjudul kop' : null,
            !$c->email ? 'email' : null,
            !$c->website ? 'website' : null,
        ]));
    }

    private function formatNpwp(?string $npwp): ?string
    {
        $d = preg_replace('/\D/', '', (string) $npwp);
        if (strlen($d) === 15) {
            return sprintf('%s.%s.%s.%s-%s.%s', substr($d, 0, 2), substr($d, 2, 3), substr($d, 5, 3), substr($d, 8, 1), substr($d, 9, 3), substr($d, 12, 3));
        }
        return $d !== '' ? $d : null;
    }

    private function validated(Request $request, ?Company $current): array
    {
        $website = strtolower(trim((string) $request->input('website')));
        $website = preg_replace('#^https?://#', '', rtrim($website, '/'));
        $request->merge([
            'name'     => preg_replace('/\s+/', ' ', trim((string) $request->input('name'))),
            'nickname' => strtoupper(preg_replace('/\s+/', '', (string) $request->input('nickname'))),
            'npwp'     => preg_replace('/\D/', '', (string) $request->input('npwp')),
            'email'    => strtolower(trim((string) $request->input('email'))) ?: null,
            'website'  => $website ?: null,
        ]);

        $v = $request->validate([
            'name'     => ['required', 'string', 'min:3', 'max:150', Rule::unique('company_tables', 'name')->ignore($current?->id), new NoXSSInput()],
            'nickname' => ['required', 'string', 'min:2', 'max:10', 'alpha_num', Rule::unique('company_tables', 'nickname')->ignore($current?->id)],
            'remark'   => ['required', Rule::in(self::REMARKS)],
            'header'   => ['nullable', 'string', 'max:150', new NoXSSInput()],
            'address'  => ['required', 'string', 'max:255', new NoXSSInput()],
            'npwp'     => ['required', 'regex:/^\d{15,16}$/', Rule::unique('company_tables', 'npwp')->ignore($current?->id)],
            'email'    => ['nullable', 'email:rfc', 'max:150'],
            'website'  => ['nullable', 'max:150', 'regex:/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+(\/[\w\-.\/]*)?$/'],
            'foto'     => [$current ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024', 'dimensions:min_width=64,min_height=64'],
        ], [
            'name.required'     => 'Nama perusahaan wajib diisi.',
            'name.unique'       => 'Nama perusahaan sudah terdaftar.',
            'name.max'          => 'Nama perusahaan maksimal 150 karakter.',
            'nickname.required' => 'Singkatan wajib diisi.',
            'nickname.alpha_num'=> 'Singkatan hanya huruf dan angka, tanpa spasi.',
            'nickname.max'      => 'Singkatan maksimal 10 karakter.',
            'nickname.unique'   => 'Singkatan sudah dipakai perusahaan lain.',
            'remark.required'   => 'Pilih jenis perusahaan.',
            'remark.in'         => 'Jenis perusahaan harus Holding atau Unit.',
            'header.max'        => 'Subjudul kop maksimal 150 karakter.',
            'address.required'  => 'Alamat wajib diisi.',
            'address.max'       => 'Alamat maksimal 255 karakter.',
            'npwp.required'     => 'NPWP wajib diisi.',
            'npwp.regex'        => 'NPWP harus 15 atau 16 digit angka (titik dan strip boleh, akan dibuang).',
            'npwp.unique'       => 'NPWP sudah terdaftar untuk perusahaan lain.',
            'email.email'       => 'Format email tidak valid.',
            'website.regex'     => 'Website harus berupa nama domain, mis. asianbay.co.id.',
            'foto.required'     => 'Logo wajib diunggah.',
            'foto.image'        => 'Logo harus berupa gambar.',
            'foto.mimes'        => 'Logo harus JPG, PNG, atau WEBP.',
            'foto.max'          => 'Ukuran logo maksimal 1 MB.',
            'foto.dimensions'   => 'Logo minimal 64 x 64 piksel supaya tetap tajam di kop surat.',
        ]);

        unset($v['foto']);

        return [
            'name'     => $v['name'],
            'nickname' => $v['nickname'],
            'remark'   => $v['remark'],
            'header'   => filled($v['header'] ?? null) ? trim($v['header']) : null,
            'address'  => trim($v['address']),
            'npwp'     => $v['npwp'],
            'email'    => $v['email'] ?? null,
            'website'  => $v['website'] ?? null,
        ];
    }

    /** Simpan logo ke disk public (dibaca kop surat lewat public_path). Mengembalikan path relatif. */
    private function storeLogo(Request $request): ?string
    {
        $file = $request->file('foto');
        if (!$file) return null;

        // Dikompres & dibersihkan, tetap PNG transparan untuk kop surat
        return app(\App\Services\UploadStorage::class)->put(
            $file, 'company/' . date('Y/m'), 'logo', \App\Services\UploadStorage::IMAGES, 'public', true, 'foto'
        );
    }
}
