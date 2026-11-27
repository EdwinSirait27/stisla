<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Companydocumentconfigs;
use App\Models\Documenttypes;
use App\Services\DocumentGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

/**
 * Pengaturan dokumen per perusahaan (company_document_configs): jenis dokumen mana yang aktif untuk
 * perusahaan mana, plus data tambahan yang dibaca template (bank, PIC, dst).
 *
 * Saat menerbitkan, sistem memilih pengaturan AKTIF berdasarkan perusahaan + singkatan jenis dan
 * mengambil yang pertama (DocumentGeneratorService, DocumentPaklaringService, dll). Karena itu
 * halaman ini menandai kombinasi yang punya lebih dari satu pengaturan aktif. Pengecualian:
 * DocumentGeneratorService::MAHENDRADATA_DW_CONFIG_ID dipakai khusus karyawan DW (diatur di kode).
 *
 * Tidak ada hapus: pengaturan yang tidak dipakai lagi dinonaktifkan.
 * Akses: ManageDocumentType (route group di web.php).
 */
class CompanyDocumentConfigController extends Controller
{
    /** Field data tambahan: [label, contoh isian]. Hanya yang dibaca template jenis terpilih yang ditampilkan. */
    public const EXTRA_FIELDS = [
        'bank_name'      => ['Nama bank', 'PT Bank OCBC NISP Tbk'],
        'savings_type'   => ['Jenis tabungan', 'Tanda 360 Komunitas (TBPAY30001)'],
        'promo_code'     => ['Kode promo', ''],
        'community_code' => ['Kode komunitas', ''],
        'service_name'   => ['Nama layanan', 'NYALA PAYROLL'],
        'pic_name'       => ['Nama PIC', ''],
        'pic_email'      => ['Email PIC', ''],
        'pic_name_2'     => ['Nama PIC kedua', ''],
        'pic_email_2'    => ['Email PIC kedua', ''],
    ];

    private function hashId(string $id): string
    {
        return substr(hash('sha256', 'document-config' . $id . config('app.key')), 0, 8);
    }

    private function findByHash(string $hash): Companydocumentconfigs
    {
        $config = Companydocumentconfigs::all()->first(fn($c) => hash_equals($this->hashId($c->id), $hash));
        abort_if(!$config, 404, 'Pengaturan dokumen tidak ditemukan.');
        return $config;
    }

    /** Field data tambahan yang dibaca template ($config->xxx), per view_name. */
    private function templateFields(?string $viewName): ?array
    {
        if (!$viewName) return null;
        $file = resource_path('views/' . str_replace('.', '/', $viewName) . '.blade.php');
        if (!File::isFile($file)) return null; // template belum ada

        preg_match_all('/\$config->([a-z_0-9]+)/', File::get($file), $m);
        return array_values(array_intersect(array_keys(self::EXTRA_FIELDS), array_unique($m[1])));
    }

    /** Jumlah dokumen terbit per pengaturan. */
    private function documentCounts(): array
    {
        return DB::table('documents')->groupBy('company_document_config_id')
            ->pluck(DB::raw('count(*)'), 'company_document_config_id')->map(fn($v) => (int) $v)->all();
    }

    public function index()
    {
        $companies = Company::orderBy('nickname')->get(['id', 'name', 'nickname']);
        $types = Documenttypes::orderBy('document_name')->get(['id', 'document_name', 'nickname', 'view_name', 'is_active']);
        $configs = Companydocumentconfigs::get();
        $counts = $this->documentCounts();
        $dwId = DocumentGeneratorService::MAHENDRADATA_DW_CONFIG_ID;

        // Matriks: [type_id][company_id] => daftar pengaturan
        $matrix = [];
        foreach ($configs as $c) {
            $matrix[$c->document_type_id][$c->company_id][] = [
                'hash'      => $this->hashId($c->id),
                'active'    => (bool) $c->is_active,
                'label'     => $c->bank_name ?: null,
                'documents' => $counts[$c->id] ?? 0,
                'dw'        => $c->id === $dwId,
            ];
        }

        $templateOk = $types->mapWithKeys(fn($t) => [$t->id => $this->templateFields($t->view_name) !== null]);

        // Kombinasi dengan lebih dari satu pengaturan aktif (selain pengaturan khusus DW)
        $ambiguous = 0;
        foreach ($matrix as $byCompany) {
            foreach ($byCompany as $list) {
                if (collect($list)->where('active', true)->where('dw', false)->count() > 1) $ambiguous++;
            }
        }

        return view('pages.CompanyDocumentConfig.index', [
            'companies'  => $companies,
            'types'      => $types,
            'matrix'     => $matrix,
            'templateOk' => $templateOk,
            'stats'      => [
                'active'    => $configs->where('is_active', true)->count(),
                'total'     => $configs->count(),
                'ambiguous' => $ambiguous,
                'documents' => array_sum($counts),
            ],
        ]);
    }

    private function formData(?Companydocumentconfigs $config, ?string $companyId, ?string $typeId): array
    {
        $types = Documenttypes::orderBy('document_name')->get(['id', 'document_name', 'nickname', 'view_name', 'is_active']);

        return [
            'config'     => $config,
            'companies'  => Company::orderBy('nickname')->get(['id', 'name', 'nickname']),
            'types'      => $types,
            // Field yang dipakai template tiap jenis; null = template belum ada
            'typeFields' => $types->mapWithKeys(fn($t) => [$t->id => $this->templateFields($t->view_name)]),
            'extra'      => self::EXTRA_FIELDS,
            'companyId'  => old('company_id', $config?->company_id ?? $companyId),
            'typeId'     => old('document_type_id', $config?->document_type_id ?? $typeId),
            // Pengaturan lain yang aktif, untuk peringatan kombinasi ganda: "company|type" => [label...]
            'activeOthers' => Companydocumentconfigs::where('is_active', true)
                ->when($config, fn($q) => $q->where('id', '!=', $config->id))
                ->where('id', '!=', DocumentGeneratorService::MAHENDRADATA_DW_CONFIG_ID)
                ->get(['company_id', 'document_type_id', 'bank_name'])
                ->groupBy(fn($c) => $c->company_id . '|' . $c->document_type_id)
                ->map(fn($g) => $g->map(fn($c) => $c->bank_name ?: 'tanpa nama bank')->values()),
            'documents'  => $config ? ($this->documentCounts()[$config->id] ?? 0) : 0,
            'isDw'       => $config && $config->id === DocumentGeneratorService::MAHENDRADATA_DW_CONFIG_ID,
        ];
    }

    public function create(Request $request)
    {
        return view('pages.CompanyDocumentConfig.create', $this->formData(null, $request->query('company'), $request->query('type')));
    }

    public function edit(string $hashedId)
    {
        $config = $this->findByHash($hashedId);
        return view('pages.CompanyDocumentConfig.edit', ['hashedId' => $hashedId] + $this->formData($config, null, null));
    }

    private function validated(Request $request, ?Companydocumentconfigs $config = null, bool $locked = false): array
    {
        $rules = [
            'company_id'       => ['required', 'string', Rule::exists('company_tables', 'id')],
            'document_type_id' => ['required', 'string', Rule::exists('document_types', 'id')],
        ];
        foreach (self::EXTRA_FIELDS as $field => [$label]) {
            $rules[$field] = str_starts_with($field, 'pic_email')
                ? ['nullable', 'email', 'max:255']
                : ['nullable', 'string', 'max:255'];
        }

        $data = $request->validate($rules, [
            'company_id.required'       => 'Pilih perusahaan.',
            'document_type_id.required' => 'Pilih jenis dokumen.',
            '*.email'                   => 'Format email tidak valid.',
            '*.max'                     => 'Maksimal 255 karakter.',
        ], collect(self::EXTRA_FIELDS)->map(fn($f) => $f[0])->all());

        foreach (array_keys(self::EXTRA_FIELDS) as $field) {
            $data[$field] = isset($data[$field]) ? (trim($data[$field]) ?: null) : null;
        }
        $data['is_active'] = $request->boolean('is_active');

        // Pengaturan yang sudah punya dokumen: perusahaan & jenis tidak boleh dipindah (nomor surat terikat)
        if ($locked && $config) {
            $data['company_id'] = $config->company_id;
            $data['document_type_id'] = $config->document_type_id;
        }

        return $data;
    }

    private function describe(Companydocumentconfigs $c): string
    {
        $c->loadMissing(['company', 'documenttypes']);
        return ($c->documenttypes?->nickname ?? '?') . ' untuk ' . ($c->company?->nickname ?? '?') . ($c->bank_name ? " ({$c->bank_name})" : '');
    }

    public function store(Request $request)
    {
        $config = Companydocumentconfigs::create($this->validated($request));

        activity('document_config')->causedBy($request->user())->performedOn($config)->event('created')
            ->withProperties(['attributes' => $config->toArray()])
            ->log('Pengaturan dokumen dibuat: ' . $this->describe($config));

        return redirect()->route('documentconfig.index')->with('success', 'Pengaturan ' . $this->describe($config) . ' disimpan.');
    }

    public function update(Request $request, string $hashedId)
    {
        $config = $this->findByHash($hashedId);
        $locked = ($this->documentCounts()[$config->id] ?? 0) > 0;
        $old = $config->toArray();

        $config->update($this->validated($request, $config, $locked));

        if ($config->wasChanged()) {
            activity('document_config')->causedBy($request->user())->performedOn($config)->event('updated')
                ->withProperties(['old' => $old, 'attributes' => $config->fresh()->toArray()])
                ->log('Pengaturan dokumen diubah: ' . $this->describe($config));
        }

        return redirect()->route('documentconfig.index')->with('success', 'Pengaturan ' . $this->describe($config) . ' disimpan.');
    }
}
