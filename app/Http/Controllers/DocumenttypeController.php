<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Documenttypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

/**
 * Master jenis dokumen (document_types): nama, singkatan untuk nomor surat, template Blade, aktif/nonaktif.
 * Dokumen yang diterbitkan (Documents) memakai jenis ini lewat company_document_configs.
 *
 * - Singkatan dipakai di nomor surat (001/SINGKATAN-PERUSAHAAN/BULAN/TAHUN), jadi dikunci kalau
 *   jenis ini sudah punya dokumen terbit: mengubahnya memutus urutan nomor.
 * - Template dipilih dari file yang ada di resources/views/documents/types.
 * - Tidak ada hapus: jenis yang tidak dipakai lagi dinonaktifkan.
 *
 * Akses: ManageDocumentType (route group di web.php).
 */
class DocumenttypeController extends Controller
{
    private const TEMPLATE_DIR = 'documents/types';

    /** Singkatan yang punya aturan khusus di kode (Documents::generateDocumentNumber, Surat Tugas). */
    private const SPECIAL_CODES = ['PAK', 'SPK', 'SPPRP', 'ST', 'OL-DW', 'OL-OJT'];

    private function hashId(string $id): string
    {
        return substr(hash('sha256', 'document-type' . $id . config('app.key')), 0, 8);
    }

    private function findByHash(string $hash): Documenttypes
    {
        $type = Documenttypes::all()->first(fn($t) => hash_equals($this->hashId($t->id), $hash));
        abort_if(!$type, 404, 'Jenis dokumen tidak ditemukan.');
        return $type;
    }

    /** Template yang tersedia: ['documents.types.SPK' => 'SPK', ...] */
    private function templates(): array
    {
        $dir = resource_path('views/' . self::TEMPLATE_DIR);
        if (!File::isDirectory($dir)) return [];

        return collect(File::files($dir))
            ->filter(fn($f) => str_ends_with($f->getFilename(), '.blade.php'))
            ->map(fn($f) => substr($f->getFilename(), 0, -strlen('.blade.php')))
            ->filter(fn($n) => preg_match('/^[A-Za-z0-9_-]+$/', $n))
            ->sort()
            ->mapWithKeys(fn($n) => [str_replace('/', '.', self::TEMPLATE_DIR) . '.' . $n => $n])
            ->all();
    }

    /** [type_id => ['companies' => aktif, 'documents' => terbit, 'last' => tanggal terbit terakhir]] */
    private function usage(): array
    {
        $companies = DB::table('company_document_configs')->where('is_active', true)
            ->groupBy('document_type_id')->pluck(DB::raw('count(*)'), 'document_type_id');

        $docs = DB::table('documents as d')
            ->join('company_document_configs as c', 'c.id', '=', 'd.company_document_config_id')
            ->groupBy('c.document_type_id')
            ->get(['c.document_type_id', DB::raw('count(*) as total'), DB::raw('max(d.issued_date) as last')])
            ->keyBy('document_type_id');

        $out = [];
        foreach (Documenttypes::pluck('id') as $id) {
            $out[$id] = [
                'companies' => (int) ($companies[$id] ?? 0),
                'documents' => (int) ($docs[$id]->total ?? 0),
                'last'      => $docs[$id]->last ?? null,
            ];
        }
        return $out;
    }

    public function index()
    {
        return view('pages.Documenttype.index');
    }

    public function getData(Request $request)
    {
        $templates = $this->templates();
        $usage = $this->usage();
        $tz = config('app.timezone');

        $rows = Documenttypes::orderBy('document_name')->get()->map(function (Documenttypes $t) use ($templates, $usage, $tz) {
            $u = $usage[$t->id] ?? ['companies' => 0, 'documents' => 0, 'last' => null];
            return [
                'name'          => $t->document_name,
                'nickname'      => $t->nickname,
                'template'      => $t->view_name,
                'template_ok'   => isset($templates[$t->view_name]),
                'is_active'     => (bool) $t->is_active,
                'requires_approval' => (bool) $t->requires_approval,
                'companies'     => $u['companies'],
                'documents'     => $u['documents'],
                'last_issued'   => $u['last'] ? \Carbon\Carbon::parse($u['last'])->timezone($tz)->locale('id')->translatedFormat('d M Y') : null,
                'edit_url'      => route('documenttype.edit', $this->hashId($t->id)),
            ];
        });

        $stats = [
            'total'       => $rows->count(),
            'active'      => $rows->where('is_active', true)->count(),
            'no_template' => $rows->where('template_ok', false)->count(),
            'documents'   => $rows->sum('documents'),
        ];

        $rows = match ($request->input('flag')) {
            'active'      => $rows->where('is_active', true),
            'inactive'    => $rows->where('is_active', false),
            'no_template' => $rows->where('template_ok', false),
            default       => $rows,
        };

        return DataTables::of($rows->values())->escapeColumns([])->with('stats', $stats)->make(true);
    }

    private function formData(?Documenttypes $type = null): array
    {
        $usage = $type ? ($this->usage()[$type->id] ?? null) : null;

        return [
            'templates'   => $this->templates(),
            'existing'    => Documenttypes::orderBy('document_name')
                ->when($type, fn($q) => $q->where('id', '!=', $type->id))
                ->get(['document_name', 'nickname', 'view_name']),
            'companyNick' => Company::orderBy('nickname')->value('nickname') ?? 'PT',
            'usage'       => $usage,
            'lockCode'    => $usage && $usage['documents'] > 0,
            'special'     => self::SPECIAL_CODES,
        ];
    }

    public function create()
    {
        return view('pages.Documenttype.create', $this->formData());
    }

    public function edit(string $hashedId)
    {
        $type = $this->findByHash($hashedId);
        return view('pages.Documenttype.edit', ['type' => $type, 'hashedId' => $hashedId] + $this->formData($type));
    }

    private function validated(Request $request, ?Documenttypes $type = null, bool $lockCode = false): array
    {
        $request->merge([
            'document_name' => strtoupper(trim(preg_replace('/\s+/', ' ', (string) $request->input('document_name')))),
            'nickname'      => strtoupper(trim(preg_replace('/[^A-Za-z0-9-]/', '', (string) $request->input('nickname')), '-')),
        ]);

        // Template harus file yang ada; nilai lama tetap boleh dipertahankan walau filenya belum ada
        $allowedTemplates = array_keys($this->templates());
        if ($type && $type->view_name) $allowedTemplates[] = $type->view_name;

        $data = $request->validate([
            // Data lama sudah punya nama kembar (SKK & PAK sama-sama SURAT KETERANGAN KERJA): cek unik hanya kalau nama diganti
            'document_name' => array_filter(['required', 'string', 'max:255',
                $type && $request->input('document_name') === $type->document_name ? null : Rule::unique('document_types', 'document_name')->ignore($type?->id)]),
            // Tanda hubung dibolehkan: kode offering letter mencari OL-DW dan OL-OJT
            'nickname'      => ['required', 'string', 'min:2', 'max:10', 'regex:/^[A-Z0-9]+(-[A-Z0-9]+)*$/', Rule::unique('document_types', 'nickname')->ignore($type?->id)],
            'view_name'     => ['required', 'string', Rule::in(array_unique($allowedTemplates))],
        ], [
            'document_name.required' => 'Nama dokumen wajib diisi.',
            'document_name.unique'   => 'Nama dokumen sudah ada.',
            'nickname.required'      => 'Singkatan wajib diisi.',
            'nickname.min'           => 'Singkatan minimal 2 karakter.',
            'nickname.max'           => 'Singkatan maksimal 10 karakter.',
            'nickname.regex'         => 'Singkatan hanya huruf, angka, dan tanda hubung (mis. OL-DW).',
            'nickname.unique'        => 'Singkatan sudah dipakai jenis dokumen lain.',
            'view_name.required'     => 'Pilih template dokumen.',
            'view_name.in'           => 'Template tidak ditemukan di resources/views/documents/types.',
        ]);

        // Jenis yang sudah punya dokumen terbit: singkatan tidak boleh berubah (urutan nomor surat)
        if ($lockCode && $type) {
            $data['nickname'] = $type->nickname;
        }
        $data['is_active'] = $request->boolean('is_active');
        $data['requires_approval'] = $request->boolean('requires_approval');

        return $data;
    }

    public function store(Request $request)
    {
        $type = Documenttypes::create($this->validated($request));

        activity('document_type')->causedBy($request->user())->performedOn($type)->event('created')
            ->withProperties(['attributes' => $type->only(['document_name', 'nickname', 'view_name', 'is_active', 'requires_approval'])])
            ->log("Jenis dokumen dibuat: {$type->document_name} ({$type->nickname})");

        return redirect()->route('documenttype.index')->with('success', "Jenis dokumen {$type->document_name} ditambahkan. Aktifkan untuk perusahaan di pengaturan dokumen perusahaan supaya bisa diterbitkan.");
    }

    public function update(Request $request, string $hashedId)
    {
        $type = $this->findByHash($hashedId);
        $lockCode = ($this->usage()[$type->id]['documents'] ?? 0) > 0;
        $old = $type->only(['document_name', 'nickname', 'view_name', 'is_active', 'requires_approval']);

        $type->update($this->validated($request, $type, $lockCode));

        if ($type->wasChanged()) {
            activity('document_type')->causedBy($request->user())->performedOn($type)->event('updated')
                ->withProperties(['old' => $old, 'attributes' => $type->only(['document_name', 'nickname', 'view_name', 'is_active', 'requires_approval'])])
                ->log("Jenis dokumen diubah: {$old['document_name']}");
        }

        return redirect()->route('documenttype.index')->with('success', "Jenis dokumen {$type->document_name} disimpan.");
    }
}
