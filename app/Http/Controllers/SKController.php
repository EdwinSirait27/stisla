<?php

namespace App\Http\Controllers;

use App\Models\SkLetter;
use App\Models\Sktype;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

/**
 * Jenis SK (permission ManageSktypes). Flag jenis menentukan apa yang diterapkan sistem ke data
 * karyawan saat SK disetujui penuh (lihat SkLetterService::applyApproved):
 * - affects_salary   : baris Employee Salary baru mulai tanggal efektif
 * - affects_position : jabatan, departemen, lokasi, grading, group
 * - affects_status   : jenis karyawan baru; kategori Termination menjadikan karyawan Resign
 * - generates_contract : tombol "Buat kontrak dari SK ini"
 * Singkatan dipakai di nomor SK (001/{SINGKATAN}-{PERUSAHAAN}/{BULAN}/{TAHUN}).
 */
class SKController extends Controller
{
    private function hashOf(string $id): string
    {
        return substr(hash('sha256', $id . env('APP_KEY')), 0, 8);
    }

    private function findByHash(string $hashedId): ?Sktype
    {
        return Sktype::get()->first(fn($t) => $this->hashOf($t->id) === $hashedId);
    }

    /** Jumlah SK per jenis: total, berjalan (Draft / menunggu), dan sudah disetujui. */
    private function usage()
    {
        return SkLetter::select('sk_type_id')
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("SUM(status IN ('Draft','Approved HR','Approved Director')) AS open_count")
            ->selectRaw("SUM(status = ?) AS approved_count", [SkLetter::FINAL_STATUS])
            ->groupBy('sk_type_id')->get()->keyBy('sk_type_id');
    }

    public function sktype()
    {
        $types = Sktype::all();
        $usage = $this->usage();

        return view('pages.Sktype.Sktype', [
            'stats' => [
                'total'    => $types->count(),
                'used'     => $types->filter(fn($t) => isset($usage[$t->id]))->count(),
                'salary'   => $types->where('affects_salary', true)->count(),
                'contract' => $types->where('generates_contract', true)->count(),
            ],
        ]);
    }

    public function getSktypes()
    {
        $usage = $this->usage();
        $rows = Sktype::orderBy('sk_name')->get(['id', 'sk_name', 'nickname', 'categories', 'generates_contract', 'affects_salary', 'affects_position', 'affects_status'])
            ->map(function ($t) use ($usage) {
                $u = $usage[$t->id] ?? null;
                return [
                    'sk_name'            => $t->sk_name,
                    'nickname'           => $t->nickname,
                    'categories'         => $t->categories,
                    'affects_salary'     => $t->affects_salary,
                    'affects_position'   => $t->affects_position,
                    'affects_status'     => $t->affects_status,
                    'generates_contract' => $t->generates_contract,
                    'termination'        => $t->categories === 'Termination',
                    'total'              => (int) ($u->total ?? 0),
                    'open'               => (int) ($u->open_count ?? 0),
                    'approved'           => (int) ($u->approved_count ?? 0),
                    'edit_url'           => route('Sktype.edit', $this->hashOf($t->id)),
                ];
            });

        return DataTables::of($rows)->make(true);
    }

    /** Jenis lain (untuk cek nama/singkatan kembar di form). */
    private function others(?Sktype $except = null): array
    {
        return Sktype::when($except, fn($q) => $q->where('id', '!=', $except->id))->orderBy('sk_name')
            ->get(['sk_name', 'nickname', 'categories'])
            ->map(fn($t) => ['name' => $t->sk_name, 'nickname' => $t->nickname, 'category' => $t->categories])->all();
    }

    public function create()
    {
        return view('pages.Sktype.create', [
            'categories' => Sktype::getCategoriesOptions(),
            'existing'   => $this->others(),
        ]);
    }

    public function edit($hashedId)
    {
        $sktype = $this->findByHash($hashedId);
        abort_unless($sktype, 404, 'Jenis SK tidak ditemukan.');
        $u = $this->usage()[$sktype->id] ?? null;

        return view('pages.Sktype.edit', [
            'sktype'     => $sktype,
            'hashedId'   => $hashedId,
            'categories' => Sktype::getCategoriesOptions(),
            'existing'   => $this->others($sktype),
            'usage'      => ['total' => (int) ($u->total ?? 0), 'open' => (int) ($u->open_count ?? 0), 'approved' => (int) ($u->approved_count ?? 0)],
            'latest'     => SkLetter::where('sk_type_id', $sktype->id)->latest('created_at')->limit(5)->get(['id', 'sk_number', 'title', 'status', 'returned_at', 'effective_date']),
        ]);
    }

    private function validated(Request $request, ?Sktype $except = null): array
    {
        // Disimpan huruf kapital; singkatan tanpa spasi/tanda baca karena masuk nomor SK
        $request->merge([
            'sk_name'  => strtoupper(trim(preg_replace('/\s+/', ' ', (string) $request->input('sk_name')))),
            'nickname' => strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $request->input('nickname'))),
        ]);
        $unique = fn($col) => Rule::unique('sk_type', $col)->ignore($except?->id);

        return $request->validate([
            'sk_name'            => ['required', 'string', 'max:255', $unique('sk_name')],
            'nickname'           => ['required', 'string', 'min:2', 'max:10', $unique('nickname')],
            'categories'         => ['required', Rule::in(array_keys(Sktype::getCategoriesOptions()))],
            'generates_contract' => ['required', 'boolean'],
            'affects_salary'     => ['required', 'boolean'],
            'affects_position'   => ['required', 'boolean'],
            'affects_status'     => ['required', 'boolean'],
        ], [
            'sk_name.required'  => 'Nama jenis SK wajib diisi.',
            'sk_name.unique'    => 'Nama jenis SK ini sudah ada.',
            'nickname.required' => 'Singkatan wajib diisi.',
            'nickname.min'      => 'Singkatan minimal 2 huruf atau angka.',
            'nickname.max'      => 'Singkatan maksimal 10 huruf atau angka.',
            'nickname.unique'   => 'Singkatan ini sudah dipakai jenis SK lain (nomor SK bisa tertukar).',
            'categories.required' => 'Pilih kategori.',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        try {
            DB::transaction(fn() => Sktype::create($data));
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Jenis SK gagal disimpan. Silakan coba lagi.');
        }

        return redirect()->route('pages.Sktype')->with('success', "Jenis SK {$data['sk_name']} ditambahkan.");
    }

    public function update(Request $request, $hashedId)
    {
        $sktype = $this->findByHash($hashedId);
        if (!$sktype) {
            return redirect()->route('pages.Sktype')->with('error', 'Jenis SK tidak ditemukan.');
        }

        $data = $this->validated($request, $sktype);
        $sktype->update($data);

        return redirect()->route('pages.Sktype')->with('success', "Perubahan jenis SK {$data['sk_name']} disimpan.");
    }
}
